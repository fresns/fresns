<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\ConversationDTO;
use App\Fresns\Api\Http\DTO\ConversationListDTO;
use App\Fresns\Api\Http\DTO\ConversationSendMessageDTO;
use App\Fresns\Api\Http\DTO\PaginationDTO;
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

        $aConversations = Conversation::with(['aUser', 'latestMessage'])
            ->where('a_user_id', $authUser->id)
            ->when($dtoRequest->isPin, function ($query, $value) {
                $query->where('a_is_pin', $value);
            })
            ->where('a_is_display', 1);
        $bConversations = Conversation::with(['bUser', 'latestMessage'])
            ->where('b_user_id', $authUser->id)
            ->when($dtoRequest->isPin, function ($query, $value) {
                $query->where('b_is_pin', $value);
            })
            ->where('b_is_display', 1);

        if ($dtoRequest->isPin) {
            $allConversations = $aConversations->union($bConversations)->latest('latest_message_at')->get();

            $total = $allConversations->count();
            $perPage = $total;
        } else {
            $allConversations = $aConversations->union($bConversations)->latest('latest_message_at')->paginate($request->get('pageSize', 15));

            $total = $allConversations->total();
            $perPage = $allConversations->perPage();
        }

        $userService = new UserService;

        $list = null;
        foreach ($allConversations as $conversation) {
            if ($conversation->a_user_id == $authUser->id) {
                $conversationUser = $userService->userData($conversation?->bUser, $langTag, $timezone, $authUser->id);
                $isPin = $conversation->a_is_pin;
            } else {
                $conversationUser = $userService->userData($conversation?->aUser, $langTag, $timezone, $authUser->id);
                $isPin = $conversation->b_is_pin;
            }

            $userIsDeactivate = $conversationUser ? false : true;
            if ($conversationUser) {
                $userIsDeactivate = $conversationUser['deactivate'];
            }

            $latestMessageModel = $conversation?->latestMessage;

            if ($latestMessageModel?->message_type == 2) {
                $message = File::TYPE_MAP[$latestMessageModel->file->type];
            } else {
                $message = ContentUtility::replaceBlockWords('conversation', $latestMessageModel?->message_text);
            }

            $latestMessage['id'] = $latestMessageModel?->id;
            $latestMessage['type'] = $latestMessageModel?->type;
            $latestMessage['message'] = $message;
            $latestMessage['datetime'] = DateHelper::fresnsDateTimeByTimezone($latestMessageModel?->created_at, $timezone, $langTag);
            $latestMessage['datetimeFormat'] = DateHelper::fresnsFormatDateTime($latestMessageModel?->created_at, $timezone, $langTag);

            $aMessages = conversationMessage::where('conversation_id', $conversation->id)
                ->where('send_user_id', $authUser->id)
                ->whereNull('send_deleted_at')
                ->isEnable();
            $bMessages = conversationMessage::where('conversation_id', $conversation->id)
                ->where('receive_user_id', $authUser->id)
                ->whereNull('receive_deleted_at')
                ->isEnable();
            $messageCount = $aMessages->union($bMessages)->count();

            $item['id'] = $conversation->id;
            $item['userIsDeactivate'] = $userIsDeactivate;
            $item['user'] = $conversationUser;
            $item['latestMessage'] = $latestMessage;
            $item['isPin'] = (bool) $isPin;
            $item['messageCount'] = $messageCount;
            $item['unreadCount'] = conversationMessage::where('conversation_id', $conversation->id)->where('receive_user_id', $authUser->id)->whereNull('receive_read_at')->whereNull('receive_deleted_at')->isEnable()->count();
            $list[] = $item;
        }

        return $this->fresnsPaginate($list, $total, $perPage);
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
            ->isEnable()
            ->count();

        $userService = new UserService();

        if ($conversation->a_user_id == $authUser->id) {
            $conversationUser = $userService->userData($conversation?->bUser, $langTag, $timezone, $authUser->id);
        } else {
            $conversationUser = $userService->userData($conversation?->aUser, $langTag, $timezone, $authUser->id);
        }

        $userIsDeactivate = $conversationUser ? false : true;
        if ($conversationUser) {
            $userIsDeactivate = $conversationUser['deactivate'];
        }

        $aMessages = conversationMessage::where('conversation_id', $conversation->id)
            ->where('send_user_id', $authUser->id)
            ->whereNull('send_deleted_at')
            ->isEnable();
        $bMessages = conversationMessage::where('conversation_id', $conversation->id)
            ->where('receive_user_id', $authUser->id)
            ->whereNull('receive_deleted_at')
            ->isEnable();
        $messageCount = $aMessages->union($bMessages)->count();

        // return
        $detail['id'] = $conversation->id;
        $detail['userIsDeactivate'] = $userIsDeactivate;
        $detail['user'] = $conversationUser;
        $detail['messageCount'] = $messageCount;
        $detail['unreadCount'] = $unreadCount;

        return $this->success($detail);
    }

    // messages
    public function messages(Request $request, int $conversationId)
    {
        $dtoRequest = new PaginationDTO($request->all());
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
            ->isEnable()
            ->latest();
        $receiveMessages = ConversationMessage::with(['sendUser', 'file'])
            ->where('conversation_id', $conversation->id)
            ->where('receive_user_id', $authUser->id)
            ->whereNull('receive_deleted_at')
            ->isEnable()
            ->latest();

        $messages = $sendMessages->union($receiveMessages)->latest()->paginate($request->get('pageSize', 15));

        // list
        $userService = new UserService;

        $messageList = [];
        foreach ($messages as $message) {
            $item['id'] = $message->id;
            $item['user'] = $userService->userData($message->sendUser, $langTag, $timezone, $authUser->id);
            $item['isMe'] = ($message->send_user_id == $authUser->id) ? true : false;
            $item['type'] = $message->message_type;
            $item['content'] = ContentUtility::replaceBlockWords('conversation', $message->message_text);
            $item['file'] = $message->message_file_id ? FileHelper::fresnsFileInfoById($message->message_file_id) : null;
            $item['datetime'] = DateHelper::fresnsDateTimeByTimezone($message->created_at, $timezone, $langTag);
            $item['datetimeFormat'] = DateHelper::fresnsFormatDateTime($message->created_at, $timezone, $langTag);
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

        $receiveUser = PrimaryHelper::fresnsModelByFsid('user', $dtoRequest->uidOrUsername);

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        if (empty($receiveUser) || empty($authUser?->id)) {
            throw new ApiException(31602);
        }

        // check send
        $checkSend = PermissionUtility::checkUserConversationPerm($receiveUser->id, $authUser->id, $langTag);
        if (! $checkSend['status']) {
            return $this->failure(
                $checkSend['code'],
                $checkSend['message'],
            );
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
        $data['user'] = $userService->userData($conversationMessage->sendUser, $langTag, $timezone, $authUser->id);
        $data['isMe'] = true;
        $data['type'] = $conversationMessage->message_type;
        $data['content'] = $conversationMessage->message_text;
        $data['file'] = $conversationMessage->message_file_id ? FileHelper::fresnsFileInfoById($conversationMessage->message_file_id) : null;
        $data['datetime'] = DateHelper::fresnsDateTimeByTimezone($conversationMessage->created_at, $timezone, $langTag);
        $data['datetimeFormat'] = DateHelper::fresnsFormatDateTime($conversationMessage->created_at, $timezone, $langTag);
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
                if ($conversation->a_is_pin == 0) {
                    $conversation->update([
                        'a_is_pin' => 1,
                    ]);
                } else {
                    $conversation->update([
                        'a_is_pin' => 0,
                    ]);
                }
            break;

            case 'b':
                if ($conversation->b_is_pin == 0) {
                    $conversation->update([
                        'b_is_pin' => 1,
                    ]);
                } else {
                    $conversation->update([
                        'b_is_pin' => 0,
                    ]);
                }
            break;

            default:
                throw new ApiException(36602);
            break;
        }

        $cacheKey = "fresns_model_conversation_{$conversationId}";
        CacheHelper::forgetFresnsKeys([$cacheKey]);

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
