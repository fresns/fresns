<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\ConversationDTO;
use App\Fresns\Api\Http\DTO\ConversationSendMessageDTO;
use App\Fresns\Api\Http\DTO\PaginationDTO;
use App\Fresns\Api\Services\UserService;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\User;
use App\Utilities\ContentUtility;
use App\Utilities\PermissionUtility;
use App\Utilities\ValidationUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConversationController extends Controller
{
    // list
    public function list(Request $request)
    {
        $dtoRequest = new PaginationDTO($request->all());
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $aConversations = Conversation::where('a_user_id', $authUser->id)->where('a_is_display', 1);
        $bConversations = Conversation::where('b_user_id', $authUser->id)->where('b_is_display', 1);

        $allConversations = $aConversations->union($bConversations)->latest('updated_at')->paginate($request->get('pageSize', 15));

        $userService = new UserService;

        $list = null;
        foreach ($allConversations as $conversation) {
            if ($conversation->a_user_id == $authUser->id) {
                $conversationUser = $userService->userData($conversation->bUser, $langTag, $timezone, $authUser->id);
            } else {
                $conversationUser = $userService->userData($conversation->aUser, $langTag, $timezone, $authUser->id);
            }

            $latestMessage['messageId'] = $conversation->latest_message_id;
            $latestMessage['time'] = DateHelper::fresnsDateTimeByTimezone($conversation->created_at, $timezone, $langTag);
            $latestMessage['timeFormat'] = DateHelper::fresnsFormatDateTime($conversation->created_at, $timezone, $langTag);
            $latestMessage['message'] = ContentUtility::replaceBlockWords('conversation', $conversation->latest_message_text);

            $item['id'] = $conversation->id;
            $item['user'] = $conversationUser;
            $item['latestMessage'] = $latestMessage;
            $item['unreadCount'] = conversationMessage::where('conversation_id', $conversation->id)->where('receive_user_id', $authUser->id)->whereNull('receive_read_at')->whereNull('receive_deleted_at')->isEnable()->count();
            $list[] = $item;
        }

        return $this->fresnsPaginate($list, $allConversations->total(), $allConversations->perPage());
    }

    // detail
    public function detail($uidOrUsername)
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        if (empty($conversationId) || ! StrHelper::isPureInt($conversationId)) {
            throw new ApiException(30000);
        }

        $conversation = Conversation::where('id', $conversationId)->first();

        if (empty($conversation)) {
            throw new ApiException(36601);
        }

        if ($conversation->a_user_id != $authUser->id && $conversation->b_user_id != $authUser->id) {
            throw new ApiException(36602);
        }

        if ($conversation->a_user_id != $authUser->id) {
            $conversationUser = User::withTrashed()->where('id', $conversation->a_user_id)->first();
        } else {
            $conversationUser = User::withTrashed()->where('id', $conversation->b_user_id)->first();
        }

        $userService = new UserService();
        $detail['user'] = $userService->userData($conversationUser, $langTag, $timezone, $authUser->id);

        return $this->success($detail);
    }

    // messages
    public function messages($uidOrUsername, Request $request)
    {
        $dtoRequest = new PaginationDTO($request->all());
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        if (empty($conversationId) || ! StrHelper::isPureInt($conversationId)) {
            throw new ApiException(30000);
        }

        $conversation = Conversation::where('id', $conversationId)->first();

        if (empty($conversation)) {
            throw new ApiException(36601);
        }

        if ($conversation->a_user_id != $authUser->id && $conversation->b_user_id != $authUser->id) {
            throw new ApiException(36602);
        }

        $messages = ConversationMessage::where('conversation_id', $conversation->id)->isEnable()->latest()->paginate($request->get('pageSize', 15));

        $userService = new UserService;

        $messageList = [];
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
            $item['sendUser'] = $userService->userData($message->sendUser, $langTag, $timezone, $authUser->id);
            $item['sendUserIsMe'] = $sendUserIsMe;
            $item['sendTime'] = DateHelper::fresnsDateTimeByTimezone($message->created_at, $timezone, $langTag);
            $item['sendTimeFormat'] = DateHelper::fresnsFormatDateTime($message->created_at, $timezone, $langTag);
            $item['type'] = $message->message_type;
            $item['content'] = ContentUtility::replaceBlockWords('conversation', $message->message_text);
            $item['file'] = $message->message_file_id ? FileHelper::fresnsFileInfoById($message->message_file_id) : null;
            $item['readStatus'] = (bool) $message->receive_read_at;
            $messageList[] = $item;
        }

        return $this->fresnsPaginate($messageList, $messages->total(), $messages->perPage());
    }

    // sendMessage
    public function sendMessage(Request $request)
    {
        $dtoRequest = new ConversationSendMessageDTO($request->all());

        $config = ConfigHelper::fresnsConfigByItemKeys(['conversation_status', 'conversation_files']);

        if (! $config['conversation_status']) {
            throw new ApiException(36600);
        }

        if (StrHelper::isPureInt($dtoRequest->uidOrUsername)) {
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
        $checkSend = PermissionUtility::checkUserConversationPerm($receiveUser->id, $authUser->id, $langTag);
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

        // conversation
        $aConversation = Conversation::where('a_user_id', $authUser->id)->where('b_user_id', $receiveUser->id)->first();
        $bConversation = Conversation::where('b_user_id', $receiveUser->id)->where('a_user_id', $authUser->id)->first();

        if (empty($aConversation) && empty($bConversation)) {
            $conversationColumn['a_user_id'] = $authUser->id;
            $conversationColumn['b_user_id'] = $receiveUser->id;

            $conversation = Conversation::create($conversationColumn)->first();
        } elseif (empty($aConversation)) {
            $conversation = $bConversation;
        } else {
            $conversation = $aConversation;
        }

        // conversation message
        $messageInput = [
            'conversation_id' => $conversation->id,
            'send_user_id' => $authUser->id,
            'message_type' => $messageType,
            'message_text' => $messageText,
            'message_file_id' => $messageFileId,
            'receive_user_id' => $receiveUser->id,
        ];

        $conversationMessage = ConversationMessage::create($messageInput)->first();

        if ($messageFileId) {
            $fileType = FileUsage::where('file_id', $messageFileId)->latest()->first()?->update([
                'table_name' => 'conversation_messages',
                'table_column' => 'message_file_id',
                'table_id' => $conversationMessage->id,
            ])?->value('file_type');

            $messageText = match ($fileType) {
                File::TYPE_IMAGE => '[Image]',
                File::TYPE_VIDEO => '[Video]',
                File::TYPE_AUDIO => '[Audio]',
                File::TYPE_DOCUMENT => '[Document]',
                default => null,
            };
        }

        $conversation->update([
            'latest_message_id' => $conversationMessage->id,
            'latest_message_time' => now(),
            'latest_message_text' => Str::limit($messageText, 140),
        ]);

        $userService = new UserService;

        // return
        $data['messageId'] = $conversationMessage->id;
        $data['sendUser'] = $userService->userData($conversationMessage->sendUser, $langTag, $timezone, $authUser->id);
        $data['sendTime'] = DateHelper::fresnsDateTimeByTimezone($conversationMessage->created_at, $timezone, $langTag);
        $data['sendTimeFormat'] = DateHelper::fresnsFormatDateTime($conversationMessage->created_at, $timezone, $langTag);
        $data['sendUserIsMe'] = true;
        $data['type'] = $conversationMessage->message_type;
        $data['content'] = $conversationMessage->message_text;
        $data['file'] = FileHelper::fresnsFileInfoById($conversationMessage->message_file_id);
        $data['readStatus'] = (bool) $conversationMessage->receive_read_at;

        return $this->success($data);
    }

    // markAsRead
    public function markAsRead(Request $request)
    {
        $dtoRequest = new ConversationDTO($request->all());
        $authUser = $this->user();

        if ($dtoRequest->type == 'conversation') {
            $aConversation = Conversation::where('id', $dtoRequest->conversationId)->where('a_user_id', $authUser->id)->first();
            $bConversation = Conversation::where('id', $dtoRequest->conversationId)->where('b_user_id', $authUser->id)->first();

            if (empty($aConversation) && empty($bConversation)) {
                throw new ApiException(36602);
            }

            $aConversation->update([
                'a_is_read' => 1,
            ]);

            $bConversation->update([
                'b_is_read' => 1,
            ]);
        } else {
            $idArr = array_filter(explode(',', $dtoRequest->messageIds));

            ConversationMessage::where('receive_user_id', $authUser->id)->whereIn('id', $idArr)->whereNull('receive_read_at')->update([
                'receive_read_at' => now(),
            ]);
        }

        return $this->success();
    }

    // delete
    public function delete(Request $request)
    {
        $dtoRequest = new ConversationDTO($request->all());
        $authUser = $this->user();

        if ($dtoRequest->type == 'conversation') {
            $aConversation = Conversation::where('id', $dtoRequest->conversationId)->where('a_user_id', $authUser->id)->first();
            $bConversation = Conversation::where('id', $dtoRequest->conversationId)->where('b_user_id', $authUser->id)->first();

            if (empty($aConversation) && empty($bConversation)) {
                throw new ApiException(36602);
            }

            $aConversation->update([
                'a_is_display' => 0,
            ]);

            $bConversation->update([
                'b_is_display' => 0,
            ]);

            ConversationMessage::where('conversation_id', $dtoRequest->conversationId)->where('send_user_id', $authUser->id)->whereNull('send_deleted_at')->update([
                'send_deleted_at' => now(),
            ]);

            ConversationMessage::where('conversation_id', $dtoRequest->conversationId)->where('receive_user_id', $authUser->id)->whereNull('receive_deleted_at')->update([
                'receive_deleted_at' => now(),
            ]);
        } else {
            $idArr = array_filter(explode(',', $dtoRequest->messageIds));

            ConversationMessage::where('send_user_id', $authUser->id)->whereIn('id', $idArr)->whereNull('send_deleted_at')->update([
                'send_deleted_at' => now(),
            ]);

            ConversationMessage::where('receive_user_id', $authUser->id)->whereIn('id', $idArr)->whereNull('receive_deleted_at')->update([
                'receive_deleted_at' => now(),
            ]);
        }

        return $this->success();
    }
}
