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
use App\Fresns\Api\Services\HeaderService;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Models\BlockWord;
use App\Models\Dialog;
use App\Models\DialogMessage;
use App\Models\File;
use App\Models\FileAppend;
use App\Models\User;
use App\Utilities\ContentUtility;
use App\Utilities\ValidationUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DialogController extends Controller
{
    // list
    public function list(Request $request)
    {
        $dtoRequest = new PaginationDTO($request->all());
        $headers = HeaderService::getHeaders();

        $authUserId = PrimaryHelper::fresnsUserIdByUid($headers['uid']);

        $aDialogs = Dialog::where('a_user_id', $authUserId)->where('a_is_display', 1);
        $bDialogs = Dialog::where('b_user_id', $authUserId)->where('b_is_display', 1);

        $dialogs = $aDialogs->union($bDialogs)->latest('updated_at')->paginate($request->get('pageSize', 15));

        $item = null;
        foreach ($dialogs as $dialog) {
            if ($dialog->a_user_id == $authUserId) {
                $userProfile = $dialog->aUser?->getUserProfile($headers['langTag'], $headers['timezone']);
                $userMainRole = $dialog->aUser?->getUserMainRole($headers['langTag'], $headers['timezone']);
            } else {
                $userProfile = $dialog->bUser?->getUserProfile($headers['langTag'], $headers['timezone']);
                $userMainRole = $dialog->bUser?->getUserMainRole($headers['langTag'], $headers['timezone']);
            }

            $dialogUser = array_merge($userProfile, $userMainRole);

            $latestMessage['messageId'] = $dialog->latest_message_id;
            $latestMessage['time'] = DateHelper::fresnsDateTimeByTimezone($dialog->created_at, $headers['timezone'], $headers['langTag']);
            $latestMessage['timeFormat'] = DateHelper::fresnsFormatDateTime($dialog->created_at, $headers['timezone'], $headers['langTag']);
            $latestMessage['message'] = $dialog->latest_message_text;

            $item['dialogId'] = $dialog->id;
            $item['dialogUser'] = $dialogUser;
            $item['latestMessage'] = $latestMessage;
            $item['unreadCount'] = DialogMessage::where('dialog_id', $dialog->id)->where('receive_user_id', $authUserId)->whereNull('receive_read_at')->whereNull('receive_deleted_at')->isEnable()->count();
            $item[] = $item;
        }

        return $this->fresnsPaginate($item, $dialogs->total(), $dialogs->perPage());
    }

    // detail
    public function detail($dialogId)
    {
        $headers = HeaderService::getHeaders();

        $authUserId = PrimaryHelper::fresnsUserIdByUid($headers['uid']);

        if (empty($dialogId) || ! is_int($dialogId)) {
            throw new ApiException(30000);
        }

        $dialog = Dialog::where('id', $dialogId)->first();

        if (empty($dialog)) {
            throw new ApiException(36600);
        }

        if ($dialog->a_user_id != $authUserId && $dialog->b_user_id != $authUserId) {
            throw new ApiException(36601);
        }

        if ($dialog->a_user_id != $authUserId) {
            $userProfile = $dialog->aUser?->getUserProfile($headers['langTag'], $headers['timezone']);
            $userMainRole = $dialog->aUser?->getUserMainRole($headers['langTag'], $headers['timezone']);
        } else {
            $userProfile = $dialog->bUser?->getUserProfile($headers['langTag'], $headers['timezone']);
            $userMainRole = $dialog->bUser?->getUserMainRole($headers['langTag'], $headers['timezone']);
        }

        $dialogUser = array_merge($userProfile, $userMainRole);

        $data['user'] = $dialogUser;

        $config = ConfigHelper::fresnsConfigByItemKeys(['dialog_status', 'dialog_files']);

        $data['config']['status'] = $config['dialog_status'];
        $data['config']['files'] = $config['dialog_files'];

        return $this->success($data);
    }

    // messages
    public function messages($dialogId, Request $request)
    {
        $dtoRequest = new PaginationDTO($request->all());
        $headers = HeaderService::getHeaders();

        $authUserId = PrimaryHelper::fresnsUserIdByUid($headers['uid']);

        if (empty($dialogId) || ! is_int($dialogId)) {
            throw new ApiException(30000);
        }

        $dialog = Dialog::where('id', $dialogId)->first();

        if (empty($dialog)) {
            throw new ApiException(36600);
        }

        if ($dialog->a_user_id != $authUserId && $dialog->b_user_id != $authUserId) {
            throw new ApiException(36601);
        }

        $messages = DialogMessage::where('dialog_id', $dialog->id)->isEnable()->latest()->paginate($request->get('pageSize', 15));

        $messageList = null;
        foreach ($messages as $message) {
            $sendUserIsMe = false;
            if ($message->send_user_id == $authUserId) {
                $sendUserIsMe = true;
            }

            if ($sendUserIsMe && ! is_null($message->send_deleted_at)) {
                continue;
            } elseif (! $sendUserIsMe && ! is_null($message->receive_deleted_at)) {
                continue;
            }

            $item['messageId'] = $message->id;
            $item['sendUser'] = $message->sendUser?->getUserProfile($headers['langTag'], $headers['timezone']);
            $item['sendTime'] = DateHelper::fresnsDateTimeByTimezone($message->created_at, $headers['timezone'], $headers['langTag']);
            $item['sendTimeFormat'] = DateHelper::fresnsFormatDateTime($message->created_at, $headers['timezone'], $headers['langTag']);
            $item['sendUserIsMe'] = $sendUserIsMe;
            $item['type'] = $message->message_type;
            $item['content'] = $message->message_text;
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
        $headers = HeaderService::getHeaders();

        if (is_int($dtoRequest->uidOrUsername)) {
            $receiveUser = User::withTrashed()->where('uid', $dtoRequest->uidOrUsername)->first();
        } else {
            $receiveUser = User::withTrashed()->where('username', $dtoRequest->uidOrUsername)->first();
        }

        $authUser = User::withTrashed()->where('uid', $headers['uid'])->first();

        // check send
        if (empty($receiveUser) || empty($authUser)) {
            throw new ApiException(31602);
        }

        if ($receiveUser->id == $authUser->id) {
            throw new ApiException(36602);
        }

        if (! is_null($receiveUser->deleted_at) || ! is_null($authUser->deleted_at)) {
            throw new ApiException(35203);
        }

        if (! $receiveUser->is_enable || ! $authUser->is_enable) {
            throw new ApiException(35202);
        }

        // message content
        if ($dtoRequest->message) {
            $message = Str::of($dtoRequest->message)->trim();
            $validateMessage = ValidationUtility::messageBanWords($message);

            if (! $validateMessage) {
                throw new ApiException(36604);
            }

            $blockWords = BlockWord::where('dialog_mode', 2)->get('word', 'replace_word');

            $messageType = 1;
            $messageText = str_ireplace($blockWords->pluck('word')->toArray(), $blockWords->pluck('replace_word')->toArray(), $message);
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
        $messageColumn['dialog_id'] = $dialog->id;
        $messageColumn['send_user_id'] = $authUser->id;
        $messageColumn['message_type'] = $messageType;
        $messageColumn['message_text'] = $messageText;
        $messageColumn['message_file_id'] = $messageFileId;
        $messageColumn['receive_user_id'] = $receiveUser->id;

        $dialogMessage = DialogMessage::create($messageColumn)->first();

        if ($messageFileId) {
            $fileType = FileAppend::where('file_id', $messageFileId)->update([
                'table_id' => $dialogMessage->id,
            ])->value('file_type');

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
            'latest_message_text' => $messageText,
        ]);

        // return
        $data['messageId'] = $dialogMessage->id;
        $data['sendUser'] = $dialogMessage->sendUser?->getUserProfile($headers['langTag'], $headers['timezone']);
        $data['sendTime'] = DateHelper::fresnsDateTimeByTimezone($dialogMessage->created_at, $headers['timezone'], $headers['langTag']);
        $data['sendTimeFormat'] = DateHelper::fresnsFormatDateTime($dialogMessage->created_at, $headers['timezone'], $headers['langTag']);
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
        $headers = HeaderService::getHeaders();

        $authUserId = PrimaryHelper::fresnsUserIdByUid($headers['uid']);

        if ($dtoRequest->type == 'dialog') {
            $aDialog = Dialog::where('id', $dtoRequest->dialogId)->where('a_user_id', $authUserId)->first();
            $bDialog = Dialog::where('id', $dtoRequest->dialogId)->where('b_user_id', $authUserId)->first();

            if (empty($aDialog) && empty($bDialog)) {
                throw new ApiException(36601);
            }

            $aDialog->update([
                'a_is_read' => 1,
            ]);

            $bDialog->update([
                'b_is_read' => 1,
            ]);
        } else {
            $idArr = array_filter(explode(',', $dtoRequest->messageIds));

            DialogMessage::where('receive_user_id', $authUserId)->whereIn('id', $idArr)->whereNull('receive_read_at')->update([
                'receive_read_at' => now(),
            ]);
        }

        return $this->success();
    }

    // delete
    public function delete(Request $request)
    {
        $dtoRequest = new DialogDTO($request->all());
        $headers = HeaderService::getHeaders();

        $authUserId = PrimaryHelper::fresnsUserIdByUid($headers['uid']);

        if ($dtoRequest->type == 'dialog') {
            $aDialog = Dialog::where('id', $dtoRequest->dialogId)->where('a_user_id', $authUserId)->first();
            $bDialog = Dialog::where('id', $dtoRequest->dialogId)->where('b_user_id', $authUserId)->first();

            if (empty($aDialog) && empty($bDialog)) {
                throw new ApiException(36601);
            }

            $aDialog->update([
                'a_is_display' => 0,
            ]);

            $bDialog->update([
                'b_is_display' => 0,
            ]);

            DialogMessage::where('dialog_id', $dtoRequest->dialogId)->where('send_user_id', $authUserId)->whereNull('send_deleted_at')->update([
                'send_deleted_at' => now(),
            ]);

            DialogMessage::where('dialog_id', $dtoRequest->dialogId)->where('receive_user_id', $authUserId)->whereNull('receive_deleted_at')->update([
                'receive_deleted_at' => now(),
            ]);
        } else {
            $idArr = array_filter(explode(',', $dtoRequest->messageIds));

            DialogMessage::where('send_user_id', $authUserId)->whereIn('id', $idArr)->whereNull('send_deleted_at')->update([
                'send_deleted_at' => now(),
            ]);

            DialogMessage::where('receive_user_id', $authUserId)->whereIn('id', $idArr)->whereNull('receive_deleted_at')->update([
                'receive_deleted_at' => now(),
            ]);
        }

        return $this->success();
    }
}
