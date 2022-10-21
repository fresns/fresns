<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\DialogDTO;
use App\Fresns\Api\Http\DTO\DialogSendMessageDTO;
use App\Fresns\Api\Http\DTO\PaginationDTO;
use App\Fresns\Api\Services\UserService;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\Dialog;
use App\Models\DialogMessage;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\User;
use App\Utilities\ContentUtility;
use App\Utilities\PermissionUtility;
use App\Utilities\ValidationUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DialogController extends Controller
{
    // list
    public function list(Request $request)
    {
        $dtoRequest = new PaginationDTO($request->all());
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $aDialogs = Dialog::where('a_user_id', $authUser->id)->where('a_is_display', 1);
        $bDialogs = Dialog::where('b_user_id', $authUser->id)->where('b_is_display', 1);

        $dialogs = $aDialogs->union($bDialogs)->latest('updated_at')->paginate($request->get('pageSize', 15));

        $item = null;
        foreach ($dialogs as $dialog) {
            if ($dialog->a_user_id == $authUser->id) {
                $userProfile = $dialog->aUser?->getUserProfile($langTag, $timezone);
                $userMainRole = $dialog->aUser?->getUserMainRole($langTag, $timezone);
            } else {
                $userProfile = $dialog->bUser?->getUserProfile($langTag, $timezone);
                $userMainRole = $dialog->bUser?->getUserMainRole($langTag, $timezone);
            }

            $dialogUser = array_merge($userProfile, $userMainRole);

            $latestMessage['messageId'] = $dialog->latest_message_id;
            $latestMessage['time'] = DateHelper::fresnsDateTimeByTimezone($dialog->created_at, $timezone, $langTag);
            $latestMessage['timeFormat'] = DateHelper::fresnsFormatDateTime($dialog->created_at, $timezone, $langTag);
            $latestMessage['message'] = ContentUtility::replaceBlockWords('dialog', $dialog->latest_message_text);

            $item['dialogId'] = $dialog->id;
            $item['dialogUser'] = $dialogUser;
            $item['latestMessage'] = $latestMessage;
            $item['unreadCount'] = DialogMessage::where('dialog_id', $dialog->id)->where('receive_user_id', $authUser->id)->whereNull('receive_read_at')->whereNull('receive_deleted_at')->isEnable()->count();
            $item[] = $item;
        }

        return $this->fresnsPaginate($item, $dialogs->total(), $dialogs->perPage());
    }

    // detail
    public function detail($dialogId)
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        if (empty($dialogId) || ! StrHelper::isPureInt($dialogId)) {
            throw new ApiException(30000);
        }

        $dialog = Dialog::where('id', $dialogId)->first();

        if (empty($dialog)) {
            throw new ApiException(36600);
        }

        if ($dialog->a_user_id != $authUser->id && $dialog->b_user_id != $authUser->id) {
            throw new ApiException(36602);
        }

        if ($dialog->a_user_id != $authUser->id) {
            $dialogUser = User::withTrashed()->where('id', $dialog->a_user_id)->first();
        } else {
            $dialogUser = User::withTrashed()->where('id', $dialog->b_user_id)->first();
        }

        $userService = new UserService();
        $detail['user'] = $userService->userData($dialogUser, $langTag, $timezone, $authUser->id);

        return $this->success($detail);
    }

    // messages
    public function messages($dialogId, Request $request)
    {
        $dtoRequest = new PaginationDTO($request->all());
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        if (empty($dialogId) || ! StrHelper::isPureInt($dialogId)) {
            throw new ApiException(30000);
        }

        $dialog = Dialog::where('id', $dialogId)->first();

        if (empty($dialog)) {
            throw new ApiException(36600);
        }

        if ($dialog->a_user_id != $authUser->id && $dialog->b_user_id != $authUser->id) {
            throw new ApiException(36602);
        }

        $messages = DialogMessage::where('dialog_id', $dialog->id)->isEnable()->latest()->paginate($request->get('pageSize', 15));

        $messageList = null;
        foreach ($messages as $message) {
            $sendUserIsMe = false;
            if ($message->send_user_id == $authUser->id) {
                $sendUserIsMe = true;
            }

            if ($sendUserIsMe && ! is_null($message->send_deleted_at)) {
                continue;
            } elseif (! $sendUserIsMe && ! is_null($message->receive_deleted_at)) {
                continue;
            }

            $item['messageId'] = $message->id;
            $item['sendUser'] = $message->sendUser?->getUserProfile($langTag, $timezone);
            $item['sendUserIsMe'] = $sendUserIsMe;
            $item['sendTime'] = DateHelper::fresnsDateTimeByTimezone($message->created_at, $timezone, $langTag);
            $item['sendTimeFormat'] = DateHelper::fresnsFormatDateTime($message->created_at, $timezone, $langTag);
            $item['type'] = $message->message_type;
            $item['content'] = ContentUtility::replaceBlockWords('dialog', $message->message_text);
            $item['file'] = FileHelper::fresnsFileInfoById($message->message_file_id);
            $item['readStatus'] = (bool) $message->receive_read_at;
            $messageList[] = $item;
        }

        return $this->fresnsPaginate($messageList, $messages->total(), $messages->perPage());
    }

    // sendMessage
    public function sendMessage(Request $request)
    {
        $dtoRequest = new DialogSendMessageDTO($request->all());

        $config = ConfigHelper::fresnsConfigByItemKeys(['dialog_status', 'dialog_files']);

        if (! $config['dialog_status']) {
            throw new ApiException(36600);
        }

        if (preg_match('/^\d*?$/', $dtoRequest->uidOrUsername)) {
            $receiveUser = User::withTrashed()->where('uid', $dtoRequest->uidOrUsername)->first();
        } else {
            $receiveUser = User::withTrashed()->where('username', $dtoRequest->uidOrUsername)->first();
        }

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        if (empty($receiveUser) || empty($authUser->id)) {
            throw new ApiException(31602);
        }

        // check send
        $checkSend = PermissionUtility::checkUserDialogPerm($receiveUser->id, $authUser->id, $langTag);
        if (! $checkSend['status']) {
            return $this->failure(
                $checkSend['code'],
                $checkSend['message']
            );
        }

        // message content
        if ($dtoRequest->message) {
            $message = Str::of($dtoRequest->message)->trim();
            $validateMessage = ValidationUtility::messageBanWords($message);

            if (! $validateMessage) {
                throw new ApiException(36605);
            }

            $messageType = 1;
            $messageText = $message;
            $messageFileId = null;
        } else {
            $messageType = 2;
            $messageText = null;
            $messageFileId = PrimaryHelper::fresnsFileIdByFid($dtoRequest->fid);
        }

        // dialog
        $aDialog = Dialog::where('a_user_id', $authUser->id)->where('b_user_id', $receiveUser->id)->first();
        $bDialog = Dialog::where('b_user_id', $receiveUser->id)->where('a_user_id', $authUser->id)->first();

        if (empty($aDialog) && empty($bDialog)) {
            $dialogColumn['a_user_id'] = $authUser->id;
            $dialogColumn['b_user_id'] = $receiveUser->id;

            $dialog = Dialog::create($dialogColumn)->first();
        } elseif (empty($aDialog)) {
            $dialog = $bDialog;
        } else {
            $dialog = $aDialog;
        }

        // dialog message
        $messageInput = [
            'dialog_id' => $dialog->id,
            'send_user_id' => $authUser->id,
            'message_type' => $messageType,
            'message_text' => $messageText,
            'message_file_id' => $messageFileId,
            'receive_user_id' => $receiveUser->id,
        ];

        $dialogMessage = DialogMessage::create($messageInput)->first();

        if ($messageFileId) {
            $fileType = FileUsage::where('file_id', $messageFileId)->latest()->first()?->update([
                'table_name' => 'dialog_messages',
                'table_column' => 'message_file_id',
                'table_id' => $dialogMessage->id,
            ])?->value('file_type');

            $messageText = match ($fileType) {
                File::TYPE_IMAGE => '[Image]',
                File::TYPE_VIDEO => '[Video]',
                File::TYPE_AUDIO => '[Audio]',
                File::TYPE_DOCUMENT => '[Document]',
                default => null,
            };
        }

        $dialog->update([
            'latest_message_id' => $dialogMessage->id,
            'latest_message_time' => now(),
            'latest_message_text' => Str::limit($messageText, 140),
        ]);

        // return
        $data['messageId'] = $dialogMessage->id;
        $data['sendUser'] = $dialogMessage->sendUser?->getUserProfile($langTag, $timezone);
        $data['sendTime'] = DateHelper::fresnsDateTimeByTimezone($dialogMessage->created_at, $timezone, $langTag);
        $data['sendTimeFormat'] = DateHelper::fresnsFormatDateTime($dialogMessage->created_at, $timezone, $langTag);
        $data['sendUserIsMe'] = true;
        $data['type'] = $dialogMessage->message_type;
        $data['content'] = $dialogMessage->message_text;
        $data['file'] = FileHelper::fresnsFileInfoById($dialogMessage->message_file_id);
        $data['readStatus'] = (bool) $dialogMessage->receive_read_at;

        return $this->success($data);
    }

    // markAsRead
    public function markAsRead(Request $request)
    {
        $dtoRequest = new DialogDTO($request->all());
        $authUser = $this->user();

        if ($dtoRequest->type == 'dialog') {
            $aDialog = Dialog::where('id', $dtoRequest->dialogId)->where('a_user_id', $authUser->id)->first();
            $bDialog = Dialog::where('id', $dtoRequest->dialogId)->where('b_user_id', $authUser->id)->first();

            if (empty($aDialog) && empty($bDialog)) {
                throw new ApiException(36602);
            }

            $aDialog->update([
                'a_is_read' => 1,
            ]);

            $bDialog->update([
                'b_is_read' => 1,
            ]);
        } else {
            $idArr = array_filter(explode(',', $dtoRequest->messageIds));

            DialogMessage::where('receive_user_id', $authUser->id)->whereIn('id', $idArr)->whereNull('receive_read_at')->update([
                'receive_read_at' => now(),
            ]);
        }

        return $this->success();
    }

    // delete
    public function delete(Request $request)
    {
        $dtoRequest = new DialogDTO($request->all());
        $authUser = $this->user();

        if ($dtoRequest->type == 'dialog') {
            $aDialog = Dialog::where('id', $dtoRequest->dialogId)->where('a_user_id', $authUser->id)->first();
            $bDialog = Dialog::where('id', $dtoRequest->dialogId)->where('b_user_id', $authUser->id)->first();

            if (empty($aDialog) && empty($bDialog)) {
                throw new ApiException(36602);
            }

            $aDialog->update([
                'a_is_display' => 0,
            ]);

            $bDialog->update([
                'b_is_display' => 0,
            ]);

            DialogMessage::where('dialog_id', $dtoRequest->dialogId)->where('send_user_id', $authUser->id)->whereNull('send_deleted_at')->update([
                'send_deleted_at' => now(),
            ]);

            DialogMessage::where('dialog_id', $dtoRequest->dialogId)->where('receive_user_id', $authUser->id)->whereNull('receive_deleted_at')->update([
                'receive_deleted_at' => now(),
            ]);
        } else {
            $idArr = array_filter(explode(',', $dtoRequest->messageIds));

            DialogMessage::where('send_user_id', $authUser->id)->whereIn('id', $idArr)->whereNull('send_deleted_at')->update([
                'send_deleted_at' => now(),
            ]);

            DialogMessage::where('receive_user_id', $authUser->id)->whereIn('id', $idArr)->whereNull('receive_deleted_at')->update([
                'receive_deleted_at' => now(),
            ]);
        }

        return $this->success();
    }
}
