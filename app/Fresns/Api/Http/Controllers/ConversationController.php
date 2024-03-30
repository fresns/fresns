<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Fresns\Api\Exceptions\ResponseException;
use App\Fresns\Api\Http\DTO\ConversationDeleteDTO;
use App\Fresns\Api\Http\DTO\ConversationListDTO;
use App\Fresns\Api\Http\DTO\ConversationMessagesDTO;
use App\Fresns\Api\Http\DTO\ConversationReadDTO;
use App\Fresns\Api\Http\DTO\ConversationSendMessageDTO;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Helpers\PluginHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\File;
use App\Utilities\DetailUtility;
use App\Utilities\PermissionUtility;
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

        $aConversationsQuery = Conversation::with(['aUser', 'latestMessage'])->where('a_user_id', $authUser->id)->where('a_is_display', true);
        $bConversationsQuery = Conversation::with(['bUser', 'latestMessage'])->where('b_user_id', $authUser->id)->where('b_is_display', true);

        if (isset($dtoRequest->pinned)) {
            $aConversationsQuery->where('a_is_pin', $dtoRequest->pinned);
            $bConversationsQuery->where('b_is_pin', $dtoRequest->pinned);
        }

        $allConversations = $aConversationsQuery->union($bConversationsQuery)->latest('latest_message_at')->paginate($dtoRequest->pageSize ?? 15);

        // filter
        $userOptions = [
            'viewType' => 'quoted',
            'isLiveStats' => false,
            'filter' => [
                'type' => $dtoRequest->filterUserType,
                'keys' => $dtoRequest->filterUserKeys,
            ],
        ];

        $list = null;
        foreach ($allConversations as $conversation) {
            if ($conversation->a_user_id == $authUser->id) {
                $conversationUser = DetailUtility::userDetail($conversation?->bUser, $langTag, $timezone, $authUser->id, $userOptions);
                $pinned = $conversation->a_is_pin;
            } else {
                $conversationUser = DetailUtility::userDetail($conversation?->aUser, $langTag, $timezone, $authUser->id, $userOptions);
                $pinned = $conversation->b_is_pin;
            }

            $latestMessageModel = $conversation?->latestMessage;

            $latestMessage = [
                'cmid' => $latestMessageModel?->cmid,
                'type' => ($latestMessageModel?->message_type == ConversationMessage::TYPE_TEXT) ? 'text' : 'file',
                'message' => $latestMessageModel?->message_type == ConversationMessage::TYPE_FILE ? File::TYPE_MAP[$latestMessageModel?->file?->type] : $latestMessageModel?->message_text,
                'datetime' => DateHelper::fresnsDateTimeByTimezone($latestMessageModel?->created_at, $timezone, $langTag),
                'datetimeFormat' => DateHelper::fresnsFormatDateTime($latestMessageModel?->created_at, $timezone, $langTag),
                'timeAgo' => DateHelper::fresnsHumanReadableTime($latestMessageModel?->created_at, $langTag),
            ];

            $aMessages = conversationMessage::where('conversation_id', $conversation->id)
                ->where('send_user_id', $authUser->id)
                ->whereNull('send_deleted_at')
                ->isEnabled();
            $bMessages = conversationMessage::where('conversation_id', $conversation->id)
                ->where('receive_user_id', $authUser->id)
                ->whereNull('receive_deleted_at')
                ->isEnabled();
            $messageCount = $aMessages->union($bMessages)->count();

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
    public function detail(int|string $uidOrUsername, Request $request)
    {
        $dtoRequest = new ConversationListDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $conversationUser = PrimaryHelper::fresnsModelByFsid('user', $uidOrUsername);

        if (empty($conversationUser)) {
            throw new ResponseException(31602);
        }

        $conversation = PrimaryHelper::fresnsModelConversation($authUser->id, $conversationUser->id);

        if (empty($conversation)) {
            throw new ResponseException(36601);
        }

        if ($conversation->a_user_id != $authUser->id && $conversation->b_user_id != $authUser->id) {
            throw new ResponseException(36602);
        }

        // configs
        $rolePerm = PermissionUtility::getUserMainRole($authUser->id)['permissions'];
        $conversationConfigs = ConfigHelper::fresnsConfigByItemKeys([
            'conversation_status',
            'conversation_files',
            'conversation_file_upload_type',
            'image_service',
            'video_service',
            'audio_service',
            'document_service',
            'image_extension_names',
            'image_max_size',
            'video_extension_names',
            'video_max_size',
            'video_max_duration',
            'audio_extension_names',
            'audio_max_size',
            'audio_max_duration',
            'document_extension_names',
            'document_max_size',
        ]);

        $imageUploadType = $conversationConfigs['conversation_file_upload_type']['image'] ?? 'api';
        $videoUploadType = $conversationConfigs['conversation_file_upload_type']['video'] ?? 'api';
        $audioUploadType = $conversationConfigs['conversation_file_upload_type']['audio'] ?? 'api';
        $documentUploadType = $conversationConfigs['conversation_file_upload_type']['document'] ?? 'api';

        $imageUploadUrl = PluginHelper::fresnsPluginUrlByFskey($conversationConfigs['image_service']);
        $videoUploadUrl = PluginHelper::fresnsPluginUrlByFskey($conversationConfigs['video_service']);
        $audioUploadUrl = PluginHelper::fresnsPluginUrlByFskey($conversationConfigs['audio_service']);
        $documentUploadUrl = PluginHelper::fresnsPluginUrlByFskey($conversationConfigs['document_service']);

        $imageMaxSize = (int) (empty($rolePerm['image_max_size']) ? $conversationConfigs['image_max_size'] : $rolePerm['image_max_size']);
        $image = [
            'status' => in_array('image', $conversationConfigs['conversation_files']),
            'extensions' => Str::lower($conversationConfigs['image_extension_names']),
            'inputAccept' => FileHelper::fresnsFileAcceptByType(File::TYPE_IMAGE),
            'maxSize' => $imageMaxSize + 1,
            'maxDuration' => null,
            'uploadType' => $imageUploadUrl ? $imageUploadType : 'api',
            'uploadUrl' => $imageUploadUrl,
        ];

        $videoMaxSize = (int) (empty($rolePerm['video_max_size']) ? $conversationConfigs['video_max_size'] : $rolePerm['video_max_size']);
        $videoMaxDuration = (int) (empty($rolePerm['video_max_duration']) ? $conversationConfigs['video_max_duration'] : $rolePerm['video_max_duration']);
        $video = [
            'status' => in_array('video', $conversationConfigs['conversation_files']),
            'extensions' => Str::lower($conversationConfigs['video_extension_names']),
            'inputAccept' => FileHelper::fresnsFileAcceptByType(File::TYPE_VIDEO),
            'maxSize' => $videoMaxSize + 1,
            'maxDuration' => $videoMaxDuration + 1,
            'uploadType' => $videoUploadUrl ? $videoUploadType : 'api',
            'uploadUrl' => $videoUploadUrl,
        ];

        $audioMaxSize = (int) (empty($rolePerm['audio_max_size']) ? $conversationConfigs['audio_max_size'] : $rolePerm['audio_max_size']);
        $audioMaxDuration = (int) (empty($rolePerm['audio_max_duration']) ? $conversationConfigs['audio_max_duration'] : $rolePerm['audio_max_duration']);
        $audio = [
            'status' => in_array('audio', $conversationConfigs['conversation_files']),
            'extensions' => Str::lower($conversationConfigs['audio_extension_names']),
            'inputAccept' => FileHelper::fresnsFileAcceptByType(File::TYPE_AUDIO),
            'maxSize' => $audioMaxSize + 1,
            'maxDuration' => $audioMaxDuration + 1,
            'uploadType' => $audioUploadUrl ? $audioUploadType : 'api',
            'uploadUrl' => $audioUploadUrl,
        ];

        $documentMaxSize = (int) (empty($rolePerm['document_max_size']) ? $conversationConfigs['document_max_size'] : $rolePerm['document_max_size']);
        $document = [
            'status' => in_array('document', $conversationConfigs['conversation_files']),
            'extensions' => Str::lower($conversationConfigs['document_extension_names']),
            'inputAccept' => FileHelper::fresnsFileAcceptByType(File::TYPE_DOCUMENT),
            'maxSize' => $documentMaxSize + 1,
            'maxDuration' => null,
            'uploadType' => $documentUploadUrl ? $documentUploadType : 'api',
            'uploadUrl' => $documentUploadUrl,
        ];

        // detail
        $unreadCount = conversationMessage::where('conversation_id', $conversation->id)
            ->where('receive_user_id', $authUser->id)
            ->whereNull('receive_read_at')
            ->whereNull('receive_deleted_at')
            ->isEnabled()
            ->count();

        $aMessages = conversationMessage::where('conversation_id', $conversation->id)
            ->where('send_user_id', $authUser->id)
            ->whereNull('send_deleted_at')
            ->isEnabled();
        $bMessages = conversationMessage::where('conversation_id', $conversation->id)
            ->where('receive_user_id', $authUser->id)
            ->whereNull('receive_deleted_at')
            ->isEnabled();
        $messageCount = $aMessages->union($bMessages)->count();

        // filter
        $userOptions = [
            'viewType' => 'quoted',
            'isLiveStats' => false,
            'filter' => [
                'type' => $dtoRequest->filterUserType,
                'keys' => $dtoRequest->filterUserKeys,
            ],
        ];
        $userDetail = DetailUtility::userDetail($conversationUser, $langTag, $timezone, $authUser->id, $userOptions);

        $detail = [
            'user' => $userDetail,
            'messageCount' => $messageCount,
            'unreadCount' => $unreadCount,
        ];

        $data = [
            'configs' => [
                'status' => $conversationConfigs['conversation_status'],
                'files' => [
                    'image' => $image,
                    'video' => $video,
                    'audio' => $audio,
                    'document' => $document,
                ],
            ],
            'detail' => $detail,
        ];

        return $this->success($data);
    }

    // messages
    public function messages(int|string $uidOrUsername, Request $request)
    {
        $dtoRequest = new ConversationMessagesDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $conversationUser = PrimaryHelper::fresnsModelByFsid('user', $uidOrUsername);

        if (empty($conversationUser)) {
            throw new ResponseException(31602);
        }

        $conversation = PrimaryHelper::fresnsModelConversation($authUser->id, $conversationUser->id);

        if (empty($conversation)) {
            throw new ResponseException(36601);
        }

        if ($conversation->a_user_id != $authUser->id && $conversation->b_user_id != $authUser->id) {
            throw new ResponseException(36602);
        }

        // messages
        $sendMessages = ConversationMessage::with(['sendUser'])
            ->where('conversation_id', $conversation->id)
            ->where('send_user_id', $authUser->id)
            ->whereNull('send_deleted_at')
            ->isEnabled();
        $receiveMessages = ConversationMessage::with(['sendUser'])
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

        // filter
        $userOptions = [
            'viewType' => 'quoted',
            'isLiveStats' => false,
            'filter' => [
                'type' => $dtoRequest->filterUserType,
                'keys' => $dtoRequest->filterUserKeys,
            ],
        ];

        // list
        $messageList = [];
        foreach ($messages as $message) {
            $item['cmid'] = $message->cmid;
            $item['user'] = DetailUtility::userDetail($message?->sendUser, $langTag, $timezone, $authUser->id, $userOptions);
            $item['isMe'] = ($message->send_user_id == $authUser->id) ? true : false;
            $item['type'] = ($message->message_type == ConversationMessage::TYPE_TEXT) ? 'text' : 'file';
            $item['content'] = $message->message_text;
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

    // pin
    public function pin(int|string $uidOrUsername)
    {
        $conversationUser = PrimaryHelper::fresnsModelByFsid('user', $uidOrUsername);

        if (empty($conversationUser)) {
            throw new ResponseException(31602);
        }

        $authUser = $this->user();

        $conversation = PrimaryHelper::fresnsModelConversation($authUser->id, $conversationUser->id);

        if (empty($conversation)) {
            throw new ResponseException(36601);
        }

        if ($conversation->a_user_id != $authUser->id && $conversation->b_user_id != $authUser->id) {
            throw new ResponseException(36602);
        }

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
        }

        CacheHelper::forgetFresnsKeys([
            "fresns_model_conversation_{$conversation->a_user_id}_{$conversation->b_user_id}",
            "fresns_model_conversation_{$conversation->b_user_id}_{$conversation->a_user_id}",
        ], 'fresnsUsers');

        return $this->success();
    }

    // readStatus
    public function readStatus(int|string $uidOrUsername, Request $request)
    {
        $dtoRequest = new ConversationReadDTO($request->all());

        $conversationUser = PrimaryHelper::fresnsModelByFsid('user', $uidOrUsername);

        if (empty($conversationUser)) {
            throw new ResponseException(31602);
        }

        $authUser = $this->user();

        $conversation = PrimaryHelper::fresnsModelConversation($authUser->id, $conversationUser->id);

        if (empty($conversation)) {
            throw new ResponseException(36601);
        }

        if ($conversation->a_user_id != $authUser->id && $conversation->b_user_id != $authUser->id) {
            throw new ResponseException(36602);
        }

        // messages
        if ($dtoRequest->cmids) {
            $cmidArr = array_filter(explode(',', $dtoRequest->cmids));

            ConversationMessage::where('receive_user_id', $authUser->id)->whereIn('cmid', $cmidArr)->whereNull('receive_read_at')->update([
                'receive_read_at' => now(),
            ]);

            CacheHelper::forgetFresnsKey("fresns_user_overview_conversations_{$authUser->uid}", 'fresnsUsers');

            return $this->success();
        }

        // all messages
        ConversationMessage::where('conversation_id', $conversation->id)->where('receive_user_id', $authUser->id)->whereNull('receive_read_at')->update([
            'receive_read_at' => now(),
        ]);

        CacheHelper::forgetFresnsKey("fresns_user_overview_conversations_{$authUser->uid}", 'fresnsUsers');

        return $this->success();
    }

    // delete
    public function delete(int|string $uidOrUsername, Request $request)
    {
        $dtoRequest = new ConversationDeleteDTO($request->all());

        $conversationUser = PrimaryHelper::fresnsModelByFsid('user', $uidOrUsername);

        if (empty($conversationUser)) {
            throw new ResponseException(31602);
        }

        $authUser = $this->user();

        $conversation = PrimaryHelper::fresnsModelConversation($authUser->id, $conversationUser->id);

        if (empty($conversation)) {
            throw new ResponseException(36601);
        }

        if ($conversation->a_user_id != $authUser->id && $conversation->b_user_id != $authUser->id) {
            throw new ResponseException(36602);
        }

        // messages
        if ($dtoRequest->type == 'messages') {
            $cmidArr = array_filter(explode(',', $dtoRequest->cmids));

            ConversationMessage::where('send_user_id', $authUser->id)->whereIn('cmid', $cmidArr)->whereNull('send_deleted_at')->update([
                'send_deleted_at' => now(),
            ]);

            ConversationMessage::where('receive_user_id', $authUser->id)->whereIn('cmid', $cmidArr)->whereNull('receive_deleted_at')->update([
                'receive_deleted_at' => now(),
            ]);

            CacheHelper::forgetFresnsKey("fresns_user_overview_conversations_{$authUser->uid}", 'fresnsUsers');

            return $this->success();
        }

        // conversation
        $aConversation = Conversation::where('id', $conversation->id)->where('a_user_id', $authUser->id)->first();
        $bConversation = Conversation::where('id', $conversation->id)->where('b_user_id', $authUser->id)->first();

        if (empty($aConversation) && empty($bConversation)) {
            throw new ResponseException(36602);
        }

        $aConversation?->update([
            'a_is_display' => false,
        ]);

        $bConversation?->update([
            'b_is_display' => false,
        ]);

        ConversationMessage::where('conversation_id', $conversation->id)->where('send_user_id', $authUser->id)->whereNull('send_deleted_at')->update([
            'send_deleted_at' => now(),
        ]);

        ConversationMessage::where('conversation_id', $conversation->id)->where('receive_user_id', $authUser->id)->whereNull('receive_deleted_at')->update([
            'receive_deleted_at' => now(),
        ]);

        CacheHelper::forgetFresnsKeys([
            "fresns_model_conversation_{$conversation->a_user_id}_{$conversation->b_user_id}",
            "fresns_model_conversation_{$conversation->b_user_id}_{$conversation->a_user_id}",
        ], 'fresnsUsers');

        return $this->success();
    }

    // sendMessage
    public function sendMessage(Request $request)
    {
        $dtoRequest = new ConversationSendMessageDTO($request->all());

        $conversationStatus = ConfigHelper::fresnsConfigByItemKey('conversation_status');

        if (! $conversationStatus) {
            throw new ResponseException(36600);
        }

        $receiveUser = PrimaryHelper::fresnsModelByFsid('user', $dtoRequest->uidOrUsername);

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        if (empty($authUser) || empty($receiveUser)) {
            throw new ResponseException(31602);
        }

        if (! $authUser->is_enabled || ! $receiveUser->is_enabled) {
            throw new ResponseException(35202);
        }

        // check send
        $conversationPermInt = PermissionUtility::checkUserConversationPerm($receiveUser->id, $authUser->id, $langTag);
        if ($conversationPermInt) {
            throw new ResponseException($conversationPermInt);
        }

        // message type
        $messageType = 'message';
        if ($dtoRequest->fid) {
            $messageType = 'file';
        }

        // conversation
        $conversation = PrimaryHelper::fresnsModelConversation($authUser->id, $receiveUser->id);

        // message content
        switch ($messageType) {
            case 'message':
                $messageInput = [
                    'conversation_id' => $conversation->id,
                    'send_user_id' => $authUser->id,
                    'message_type' => ConversationMessage::TYPE_TEXT,
                    'message_text' => Str::of($dtoRequest->message)->trim(),
                    'receive_user_id' => $receiveUser->id,
                ];

                $conversationMessage = ConversationMessage::create($messageInput);
                break;

            case 'file':
                $fileId = PrimaryHelper::fresnsPrimaryId('file', $dtoRequest->fid);

                $fileMessage = ConversationMessage::where('conversation_id', $conversation->id)
                    ->where('send_user_id', $authUser->id)
                    ->where('message_type', ConversationMessage::TYPE_FILE)
                    ->isEnabled()
                    ->latest('id')
                    ->first();

                $conversationMessage = $fileMessage;
                if ($fileId != $fileMessage?->message_file_id) {
                    $messageInput = [
                        'conversation_id' => $conversation->id,
                        'send_user_id' => $authUser->id,
                        'message_type' => ConversationMessage::TYPE_FILE,
                        'message_file_id' => PrimaryHelper::fresnsPrimaryId('file', $dtoRequest->fid),
                        'receive_user_id' => $receiveUser->id,
                    ];

                    $conversationMessage = ConversationMessage::create($messageInput);
                }
                break;
        }

        $conversation->update([
            'a_is_display' => true,
            'b_is_display' => true,
            'latest_message_at' => now(),
        ]);

        $data['cmid'] = $conversationMessage->cmid;
        $data['user'] = DetailUtility::userDetail($conversationMessage?->sendUser, $langTag, $timezone, $authUser->id);
        $data['isMe'] = true;
        $data['type'] = ($conversationMessage->message_type == ConversationMessage::TYPE_TEXT) ? 'text' : 'file';
        $data['content'] = $conversationMessage->message_text;
        $data['file'] = $conversationMessage->message_file_id ? FileHelper::fresnsFileInfoById($conversationMessage->message_file_id) : null;
        $data['datetime'] = DateHelper::fresnsDateTimeByTimezone($conversationMessage->created_at, $timezone, $langTag);
        $data['datetimeFormat'] = DateHelper::fresnsFormatDateTime($conversationMessage->created_at, $timezone, $langTag);
        $data['timeAgo'] = DateHelper::fresnsHumanReadableTime($conversationMessage->created_at, $langTag);
        $data['readStatus'] = (bool) $conversationMessage->receive_read_at;

        CacheHelper::forgetFresnsKey("fresns_user_overview_conversations_{$authUser->uid}", 'fresnsUsers');
        CacheHelper::forgetFresnsKey("fresns_user_overview_conversations_{$receiveUser->uid}", 'fresnsUsers');

        return $this->success($data);
    }
}
