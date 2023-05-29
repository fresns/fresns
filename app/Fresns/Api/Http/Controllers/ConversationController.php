<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\ConversationDTO;
use App\Fresns\Api\Http\DTO\ConversationListDTO;
use App\Fresns\Api\Http\DTO\ConversationMessagesDTO;
use App\Fresns\Api\Http\DTO\ConversationSendMessageDTO;
use App\Fresns\Api\Services\UserService;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\File;
use App\Models\FileUsage;
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
        $dtoRequest = new ConversationListDTO($request->all());
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $aConversationsQuery = Conversation::with(['aUser', 'latestMessage'])->where('a_user_id', $authUser->id)->where('a_is_display', 1);
        $bConversationsQuery = Conversation::with(['bUser', 'latestMessage'])->where('b_user_id', $authUser->id)->where('b_is_display', 1);

        if (isset($dtoRequest->pinned)) {
            $aConversationsQuery->where('a_is_pin', $dtoRequest->pinned);
            $bConversationsQuery->where('b_is_pin', $dtoRequest->pinned);
        }

        $allConversations = $aConversationsQuery->union($bConversationsQuery)->latest('latest_message_at')->paginate($dtoRequest->pageSize ?? 15);

        $userService = new UserService;

        $list = null;
        foreach ($allConversations as $conversation) {
            if ($conversation->a_user_id == $authUser->id) {
                $conversationUser = $userService->userData($conversation?->bUser, 'list', $langTag, $timezone, $authUser->id);
                $pinned = $conversation->a_is_pin;
            } else {
                $conversationUser = $userService->userData($conversation?->aUser, 'list', $langTag, $timezone, $authUser->id);
                $pinned = $conversation->b_is_pin;
            }

            $latestMessageModel = $conversation?->latestMessage;

            if ($latestMessageModel?->message_type == 2) {
                $message = File::TYPE_MAP[$latestMessageModel->file->type];
            } else {
                $message = ContentUtility::replaceBlockWords('conversation', $latestMessageModel?->message_text);
            }

            $latestMessage['id'] = $latestMessageModel?->id;
            $latestMessage['type'] = $latestMessageModel?->message_type;
            $latestMessage['message'] = $message;
            $latestMessage['datetime'] = DateHelper::fresnsDateTimeByTimezone($latestMessageModel?->created_at, $timezone, $langTag);
            $latestMessage['datetimeFormat'] = DateHelper::fresnsFormatDateTime($latestMessageModel?->created_at, $timezone, $langTag);
            $latestMessage['timeAgo'] = DateHelper::fresnsHumanReadableTime($latestMessageModel?->created_at, $langTag);

            $aMessages = conversationMessage::where('conversation_id', $conversation->id)
                ->where('send_user_id', $authUser->id)
                ->whereNull('send_deleted_at')
                ->isEnabled();
            $bMessages = conversationMessage::where('conversation_id', $conversation->id)
                ->where('receive_user_id', $authUser->id)
                ->whereNull('receive_deleted_at')
                ->isEnabled();
            $messageCount = $aMessages->union($bMessages)->count();

            $item['id'] = $conversation->id;
            $item['user'] = $conversationUser;
            $item['latestMessage'] = $latestMessage;
            $item['pinned'] = (bool) $pinned;
            $item['messageCount'] = $messageCount;
            $item['unreadCount'] = conversationMessage::where('conversation_id', $conversation->id)->where('receive_user_id', $authUser->id)->whereNull('receive_read_at')->whereNull('receive_deleted_at')->isEnabled()->count();

            $list[] = $item;
        }

        return $this->fresnsPaginate($list, $allConversations->total(), $allConversations->perPage());
    }

    // detail
    public function detail(int $conversationId)
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $conversation = PrimaryHelper::fresnsModelById('conversation', $conversationId);

        if (empty($conversation)) {
            throw new ApiException(36601);
        }

        if ($conversation->a_user_id != $authUser->id && $conversation->b_user_id != $authUser->id) {
            throw new ApiException(36602);
        }

        if ($conversation->a_user_id == $authUser->id && $conversation->b_user_id == $authUser->id) {
            throw new ApiException(36603);
        }

        $unreadCount = conversationMessage::where('conversation_id', $conversation->id)
            ->where('receive_user_id', $authUser->id)
            ->whereNull('receive_read_at')
            ->whereNull('receive_deleted_at')
            ->isEnabled()
            ->count();

        $userService = new UserService();

        if ($conversation->a_user_id == $authUser->id) {
            $conversationUser = $userService->userData($conversation?->bUser, 'list', $langTag, $timezone, $authUser->id);
        } else {
            $conversationUser = $userService->userData($conversation?->aUser, 'list', $langTag, $timezone, $authUser->id);
        }

        $aMessages = conversationMessage::where('conversation_id', $conversation->id)
            ->where('send_user_id', $authUser->id)
            ->whereNull('send_deleted_at')
            ->isEnabled();
        $bMessages = conversationMessage::where('conversation_id', $conversation->id)
            ->where('receive_user_id', $authUser->id)
            ->whereNull('receive_deleted_at')
            ->isEnabled();
        $messageCount = $aMessages->union($bMessages)->count();

        // return
        $detail['id'] = $conversation->id;
        $detail['user'] = $conversationUser;
        $detail['messageCount'] = $messageCount;
        $detail['unreadCount'] = $unreadCount;

        return $this->success($detail);
    }

    // messages
    public function messages(int $conversationId, Request $request)
    {
        $dtoRequest = new ConversationMessagesDTO($request->all());
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $conversation = PrimaryHelper::fresnsModelById('conversation', $conversationId);

        if (empty($conversation)) {
            throw new ApiException(36601);
        }

        if ($conversation->a_user_id != $authUser->id && $conversation->b_user_id != $authUser->id) {
            throw new ApiException(36602);
        }

        if ($conversation->a_user_id == $authUser->id && $conversation->b_user_id == $authUser->id) {
            throw new ApiException(36603);
        }

        // messages
        $sendMessages = ConversationMessage::with(['sendUser', 'file'])
            ->where('conversation_id', $conversation->id)
            ->where('send_user_id', $authUser->id)
            ->whereNull('send_deleted_at')
            ->isEnabled();
        $receiveMessages = ConversationMessage::with(['sendUser', 'file'])
            ->where('conversation_id', $conversation->id)
            ->where('receive_user_id', $authUser->id)
            ->whereNull('receive_deleted_at')
            ->isEnabled();

        $orderDirection = match ($dtoRequest->orderDirection) {
            default => 'latest',
            'asc' => 'oldest',
            'desc' => 'latest',
        };

        $messages = $sendMessages->union($receiveMessages)->$orderDirection()->paginate($dtoRequest->pageSize ?? 15);

        // list
        $userService = new UserService;

        $messageList = [];
        foreach ($messages as $message) {
            $item['id'] = $message->id;
            $item['user'] = $userService->userData($message?->sendUser, 'list', $langTag, $timezone, $authUser->id);
            $item['isMe'] = ($message->send_user_id == $authUser->id) ? true : false;
            $item['type'] = $message->message_type;
            $item['content'] = ContentUtility::replaceBlockWords('conversation', $message->message_text);
            $item['file'] = $message->message_file_id ? FileHelper::fresnsFileInfoById($message->message_file_id) : null;
            $item['datetime'] = DateHelper::fresnsDateTimeByTimezone($message->created_at, $timezone, $langTag);
            $item['datetimeFormat'] = DateHelper::fresnsFormatDateTime($message->created_at, $timezone, $langTag);
            $item['timeAgo'] = DateHelper::fresnsHumanReadableTime($message->created_at, $langTag);
            $item['readStatus'] = (bool) $message->receive_read_at;

            $messageList[] = $item;
        }

        if ($dtoRequest->pageListDirection == 'oldest') {
            $messageList = array_values(array_reverse($messageList, true));
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

        $receiveUser = PrimaryHelper::fresnsModelByFsid('user', $dtoRequest->uidOrUsername);

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        if (empty($authUser) || empty($receiveUser)) {
            throw new ApiException(31602);
        }

        if (! $authUser->is_enabled || ! $receiveUser->is_enabled) {
            throw new ApiException(35202);
        }

        // check send
        $conversationPermInt = PermissionUtility::checkUserConversationPerm($receiveUser->id, $authUser->id, $langTag);
        if ($conversationPermInt != 0) {
            throw new ApiException($conversationPermInt);
        }

        // message content
        if ($dtoRequest->fid) {
            $messageType = 2;
            $messageText = null;
            $messageFileId = PrimaryHelper::fresnsFileIdByFid($dtoRequest->fid);
        } else {
            $message = Str::of($dtoRequest->message)->trim();
            $validateMessage = ValidationUtility::messageBanWords($message);

            if (! $validateMessage) {
                throw new ApiException(36605);
            }

            $messageType = 1;
            $messageText = $message;
            $messageFileId = null;
        }

        // conversation
        $conversation = PrimaryHelper::fresnsModelConversation($authUser->id, $receiveUser->id);

        // conversation message
        $messageInput = [
            'conversation_id' => $conversation->id,
            'send_user_id' => $authUser->id,
            'message_type' => $messageType,
            'message_text' => $messageText,
            'message_file_id' => $messageFileId,
            'receive_user_id' => $receiveUser->id,
        ];
        $conversationMessage = ConversationMessage::create($messageInput);

        $conversation->update([
            'a_is_display' => 1,
            'b_is_display' => 1,
            'latest_message_at' => now(),
        ]);

        if ($messageType == 2) {
            $fileUsage = FileUsage::where('file_id', $messageFileId)
                ->where('table_name', 'conversation_messages')
                ->where('table_column', 'message_file_id')
                ->whereNull('table_id')
                ->first();

            $fileUsage->update([
                'table_id' => $conversationMessage->id,
                'table_key' => 'Conversation-'.$conversation->id,
            ]);
        }

        $userService = new UserService;

        // return
        $data['id'] = $conversationMessage->id;
        $data['user'] = $userService->userData($conversationMessage->sendUser, 'list', $langTag, $timezone, $authUser->id);
        $data['isMe'] = true;
        $data['type'] = $conversationMessage->message_type;
        $data['content'] = $conversationMessage->message_text;
        $data['file'] = $conversationMessage->message_file_id ? FileHelper::fresnsFileInfoById($conversationMessage->message_file_id) : null;
        $data['datetime'] = DateHelper::fresnsDateTimeByTimezone($conversationMessage->created_at, $timezone, $langTag);
        $data['datetimeFormat'] = DateHelper::fresnsFormatDateTime($conversationMessage->created_at, $timezone, $langTag);
        $data['timeAgo'] = DateHelper::fresnsHumanReadableTime($conversationMessage->created_at, $langTag);
        $data['readStatus'] = (bool) $conversationMessage->receive_read_at;

        CacheHelper::forgetFresnsKey("fresns_api_user_panel_conversations_{$authUser->uid}", 'fresnsUsers');
        CacheHelper::forgetFresnsKey("fresns_api_user_panel_conversations_{$receiveUser->uid}", 'fresnsUsers');

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

            ConversationMessage::where('conversation_id', $dtoRequest->conversationId)
                ->where('receive_user_id', $authUser->id)
                ->whereNull('receive_read_at')
                ->update([
                    'receive_read_at' => now(),
                ]);
        } else {
            $idArr = array_filter(explode(',', $dtoRequest->messageIds));

            ConversationMessage::where('receive_user_id', $authUser->id)->whereIn('id', $idArr)->whereNull('receive_read_at')->update([
                'receive_read_at' => now(),
            ]);
        }

        CacheHelper::forgetFresnsKey("fresns_api_user_panel_conversations_{$authUser->uid}", 'fresnsUsers');

        return $this->success();
    }

    // pin
    public function pin(Request $request)
    {
        $conversationId = $request->conversationId;

        if (! StrHelper::isPureInt($conversationId)) {
            throw new ApiException(30002);
        }

        $conversation = PrimaryHelper::fresnsModelById('conversation', $conversationId);

        if (! $conversation) {
            throw new ApiException(36601);
        }

        $authUser = $this->user();

        $authUserType = null;
        if ($conversation->a_user_id == $authUser->id) {
            $authUserType = 'a';
        }

        if ($conversation->b_user_id == $authUser->id) {
            $authUserType = 'b';
        }

        switch ($authUserType) {
            case 'a':
                if ($conversation->a_is_pin) {
                    $conversation->update([
                        'a_is_pin' => false,
                    ]);
                } else {
                    $conversation->update([
                        'a_is_pin' => true,
                    ]);
                }
                break;

            case 'b':
                if ($conversation->b_is_pin) {
                    $conversation->update([
                        'b_is_pin' => false,
                    ]);
                } else {
                    $conversation->update([
                        'b_is_pin' => true,
                    ]);
                }
                break;

            default:
                throw new ApiException(36602);
                break;
        }

        $cacheKey = "fresns_model_conversation_{$conversationId}";
        CacheHelper::forgetFresnsKey($cacheKey);

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

            $aConversation?->update([
                'a_is_display' => 0,
            ]);

            $bConversation?->update([
                'b_is_display' => 0,
            ]);

            ConversationMessage::where('conversation_id', $dtoRequest->conversationId)->where('send_user_id', $authUser->id)->whereNull('send_deleted_at')->update([
                'send_deleted_at' => now(),
            ]);

            ConversationMessage::where('conversation_id', $dtoRequest->conversationId)->where('receive_user_id', $authUser->id)->whereNull('receive_deleted_at')->update([
                'receive_deleted_at' => now(),
            ]);

            $aUserId = $aConversation?->a_user_id ?? $bConversation?->a_user_id;
            $bUserId = $aConversation?->b_user_id ?? $bConversation?->b_user_id;

            CacheHelper::forgetFresnsKeys([
                "fresns_model_conversation_{$aUserId}_{$bUserId}",
                "fresns_model_conversation_{$bUserId}_{$aUserId}",
            ], [
                'fresnsModels',
                'fresnsUsers',
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

        CacheHelper::forgetFresnsKey("fresns_api_user_panel_conversations_{$authUser->uid}", 'fresnsUsers');

        return $this->success();
    }
}
