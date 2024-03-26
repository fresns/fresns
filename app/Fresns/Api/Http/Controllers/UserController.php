<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Fresns\Api\Exceptions\ResponseException;
use App\Fresns\Api\Http\DTO\DetailDTO;
use App\Fresns\Api\Http\DTO\InteractionDTO;
use App\Fresns\Api\Http\DTO\PaginationDTO;
use App\Fresns\Api\Http\DTO\UserDeviceTokenDTO;
use App\Fresns\Api\Http\DTO\UserEditDTO;
use App\Fresns\Api\Http\DTO\UserExtcreditsLogsDTO;
use App\Fresns\Api\Http\DTO\UserExtendActionDTO;
use App\Fresns\Api\Http\DTO\UserListDTO;
use App\Fresns\Api\Http\DTO\UserLoginDTO;
use App\Fresns\Api\Http\DTO\UserMarkDTO;
use App\Fresns\Api\Http\DTO\UserMarkListDTO;
use App\Fresns\Api\Http\DTO\UserMarkNoteDTO;
use App\Fresns\Api\Services\InteractionService;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\ArchiveUsage;
use App\Models\CommentLog;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\DomainLinkUsage;
use App\Models\Extend;
use App\Models\ExtendUser;
use App\Models\File;
use App\Models\Group;
use App\Models\HashtagUsage;
use App\Models\Mention;
use App\Models\Notification;
use App\Models\PostLog;
use App\Models\Role;
use App\Models\Seo;
use App\Models\SessionLog;
use App\Models\SessionToken;
use App\Models\User;
use App\Models\UserExtcreditsLog;
use App\Models\UserFollow;
use App\Models\UserLike;
use App\Models\UserLog;
use App\Models\UserStat;
use App\Utilities\ContentUtility;
use App\Utilities\DetailUtility;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractionUtility;
use App\Utilities\PermissionUtility;
use App\Utilities\SubscribeUtility;
use App\Utilities\ValidationUtility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    // login
    public function login(Request $request)
    {
        $dtoRequest = new UserLoginDTO($request->all());

        if (StrHelper::isPureInt($dtoRequest->uidOrUsername)) {
            $authUser = User::where('uid', $dtoRequest->uidOrUsername)->first();
        } else {
            $authUser = User::where('username', $dtoRequest->uidOrUsername)->first();
        }

        if (empty($authUser)) {
            throw new ResponseException(31602);
        }

        $langTag = $this->langTag();
        $timezone = $this->timezone();

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_USER_LOGIN,
            'fskey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'langTag' => $this->langTag(),
            'aid' => $this->account()?->aid,
            'uid' => $authUser->uid,
            'actionName' => \request()->path(),
            'actionDesc' => 'User Login',
            'actionState' => SessionLog::STATE_SUCCESS,
            'actionId' => null,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => $dtoRequest->deviceToken,
            'loginToken' => null,
            'moreInfo' => null,
        ];

        // login
        $wordBody = [
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'aid' => \request()->header('X-Fresns-Aid'),
            'aidToken' => $this->accountToken(),
            'uid' => $authUser->uid,
            'pin' => $dtoRequest->pin,
        ];
        $fresnsResponse = \FresnsCmdWord::plugin('Fresns')->verifyUser($wordBody);

        if ($fresnsResponse->isErrorResponse()) {
            // upload session log
            $sessionLog['actionDesc'] = 'verifyUser';
            $sessionLog['actionState'] = SessionLog::STATE_FAILURE;
            \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

            return $fresnsResponse->getErrorResponse();
        }

        // create token
        $createTokenWordBody = [
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'aid' => $fresnsResponse->getData('aid'),
            'aidToken' => $fresnsResponse->getData('aidToken'),
            'uid' => $fresnsResponse->getData('uid'),
            'deviceToken' => $dtoRequest->deviceToken,
            'expiredTime' => null,
        ];
        $fresnsTokenResponse = \FresnsCmdWord::plugin('Fresns')->createUserToken($createTokenWordBody);

        if ($fresnsTokenResponse->isErrorResponse()) {
            // upload session log
            $sessionLog['actionDesc'] = 'createUserToken';
            $sessionLog['actionState'] = SessionLog::STATE_FAILURE;
            \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

            return $fresnsTokenResponse->getErrorResponse();
        }

        // login time
        $authUser->update([
            'last_login_at' => now(),
        ]);

        // get user data
        $userOptions = [
            'viewType' => 'list',
            'isLiveStats' => true,
        ];

        $data = [
            'authToken' => [
                'aid' => $fresnsTokenResponse->getData('aid'),
                'aidToken' => $fresnsTokenResponse->getData('aidToken'),
                'uid' => $fresnsTokenResponse->getData('uid'),
                'uidToken' => $fresnsTokenResponse->getData('uidToken'),
                'expiredHours' => $fresnsTokenResponse->getData('expiredHours'),
                'expiredDays' => $fresnsTokenResponse->getData('expiredDays'),
                'expiredDateTime' => $fresnsTokenResponse->getData('expiredDateTime'),
            ],
            'detail' => DetailUtility::userDetail($authUser, $langTag, $timezone, $authUser?->id, $userOptions),
        ];

        // upload session log
        $sessionLog['actionId'] = $fresnsResponse->getData('uidTokenId');
        \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

        // notify subscribe
        $authAccount = $this->account();

        $accountDetail = DetailUtility::accountDetail($authAccount, $langTag, $timezone);

        SubscribeUtility::notifyAccountAndUserLogin($authAccount->id, $data['authToken'], $accountDetail, $authUser->id, $data['detail']);

        return $this->success($data);
    }

    // overview
    public function overview(Request $request)
    {
        $uidOrUsername = $request->uidOrUsername;
        $langTag = $this->langTag();
        $authUser = $this->user();

        if (empty($uidOrUsername) && empty($authUser)) {
            throw new ResponseException(30005);
        }

        if ($uidOrUsername) {
            $authAccount = $this->account();
            $authUser = PrimaryHelper::fresnsModelByFsid('user', $uidOrUsername);

            if ($authUser->account_id != $authAccount->id) {
                throw new ResponseException(35201);
            }
        }

        $userId = $authUser->id;
        $userUid = $authUser->uid;

        $cacheTag = 'fresnsUsers';
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);

        // conversations
        $conversationsCacheKey = "fresns_user_overview_conversations_{$userUid}";
        $conversations = CacheHelper::get($conversationsCacheKey, $cacheTag);
        if (empty($conversations)) {
            $aConversations = Conversation::where('a_user_id', $userId)->where('a_is_display', true);
            $bConversations = Conversation::where('b_user_id', $userId)->where('b_is_display', true);

            $conversationCount = $aConversations->union($bConversations)->count();
            $conversationMessageCount = ConversationMessage::where('receive_user_id', $userId)->whereNull('receive_read_at')->whereNull('receive_deleted_at')->isEnabled()->count();

            $conversations = [
                'conversationCount' => $conversationCount,
                'unreadMessages' => $conversationMessageCount,
            ];

            CacheHelper::put($conversations, $conversationsCacheKey, $cacheTag, null, $cacheTime);
        }

        // unread notifications
        $notificationsCacheKey = "fresns_user_overview_notifications_{$userUid}";
        $unreadNotifications = CacheHelper::get($notificationsCacheKey, $cacheTag);
        if (empty($unreadNotifications)) {
            $unreadModel = Notification::where('user_id', $userId)->where('is_read', false)->get();
            $unreadNotifications = [
                'all' => $unreadModel->count(),
                'systems' => $unreadModel->where('type', Notification::TYPE_SYSTEM)->count(),
                'recommends' => $unreadModel->where('type', Notification::TYPE_RECOMMEND)->count(),
                'likes' => $unreadModel->where('type', Notification::TYPE_LIKE)->count(),
                'dislikes' => $unreadModel->where('type', Notification::TYPE_DISLIKE)->count(),
                'follows' => $unreadModel->where('type', Notification::TYPE_FOLLOW)->count(),
                'blocks' => $unreadModel->where('type', Notification::TYPE_BLOCK)->count(),
                'mentions' => $unreadModel->where('type', Notification::TYPE_MENTION)->count(),
                'comments' => $unreadModel->where('type', Notification::TYPE_COMMENT)->count(),
                'quotes' => $unreadModel->where('type', Notification::TYPE_QUOTE)->count(),
            ];

            CacheHelper::put($unreadNotifications, $notificationsCacheKey, $cacheTag, null, $cacheTime);
        }

        // draft count
        $draftsCacheKey = "fresns_user_overview_drafts_{$userUid}";
        $draftCount = CacheHelper::get($draftsCacheKey, $cacheTag);
        if (empty($draftCount)) {
            $draftCount = [
                'posts' => PostLog::where('user_id', $userId)->whereIn('state', [PostLog::STATE_DRAFT, PostLog::STATE_FAILURE])->count(),
                'comments' => CommentLog::where('user_id', $userId)->whereIn('state', [CommentLog::STATE_DRAFT, CommentLog::STATE_FAILURE])->count(),
            ];

            CacheHelper::put($draftCount, $draftsCacheKey, $cacheTag, null, $cacheTime);
        }

        $data['features'] = ExtendUtility::getUserExtensions('features', $userId, $langTag);
        $data['profiles'] = ExtendUtility::getUserExtensions('profiles', $userId, $langTag);
        $data['conversations'] = $conversations;
        $data['unreadNotifications'] = $unreadNotifications;
        $data['draftCount'] = $draftCount;

        return $this->success($data);
    }

    // extcredits records
    public function extcreditsRecords(Request $request)
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $dtoRequest = new UserExtcreditsLogsDTO($request->all());

        $logQuery = UserExtcreditsLog::where('user_id', $authUser->id)->orderBy('created_at', 'desc');

        $logQuery->when($dtoRequest->extcreditsId, function ($query, $value) {
            $query->where('extcredits_id', $value);
        });

        $extcreditsLogs = $logQuery->paginate($dtoRequest->pageSize ?? 15);

        $logList = [];
        foreach ($extcreditsLogs as $log) {
            $item['extcreditsId'] = $log->extcredits_id;
            $item['type'] = $log->type == 1 ? 'increment' : 'decrement';
            $item['amount'] = $log->amount;
            $item['openingAmount'] = $log->opening_amount;
            $item['closingAmount'] = $log->closing_amount;
            $item['fskey'] = $log->app_fskey;
            $item['remark'] = $log->remark;
            $item['datetime'] = DateHelper::fresnsDateTimeByTimezone($log->created_at, $timezone, $langTag);
            $item['datetimeFormat'] = DateHelper::fresnsFormatDateTime($log->created_at, $timezone, $langTag);
            $item['timeAgo'] = DateHelper::fresnsHumanReadableTime($log->created_at, $langTag);

            $logList[] = $item;
        }

        return $this->fresnsPaginate($logList, $extcreditsLogs->total(), $extcreditsLogs->perPage());
    }

    // edit
    public function edit(Request $request)
    {
        $dtoRequest = new UserEditDTO($request->all());

        if ($dtoRequest->isEmpty()) {
            throw new ResponseException(30001);
        }

        $authUser = $this->user();

        if (! $authUser->is_enabled) {
            throw new ResponseException(35202);
        }

        if ($dtoRequest->avatarFid && $dtoRequest->avatarUrl) {
            throw new ResponseException(30005);
        }

        if ($dtoRequest->bannerFid && $dtoRequest->bannerUrl) {
            throw new ResponseException(30005);
        }

        $editNameConfig = ConfigHelper::fresnsConfigByItemKeys([
            'username_edit',
            'nickname_edit',
        ]);

        // edit username
        if ($dtoRequest->username) {
            $isEmpty = Str::of($dtoRequest->username)->trim()->isEmpty();
            if ($isEmpty) {
                throw new ResponseException(35102);
            }

            if ($authUser->last_username_at) {
                $nextEditUsernameTime = Carbon::parse($authUser->last_username_at)->addDays($editNameConfig['username_edit']);

                if (now() < $nextEditUsernameTime) {
                    throw new ResponseException(35101);
                }
            }

            $username = Str::of($dtoRequest->username)->trim();
            $validateUsername = ValidationUtility::username($username);

            if (! $validateUsername['formatString'] || ! $validateUsername['formatHyphen'] || ! $validateUsername['formatNumeric']) {
                throw new ResponseException(35102);
            }

            if (! $validateUsername['minLength']) {
                throw new ResponseException(35104);
            }

            if (! $validateUsername['maxLength']) {
                throw new ResponseException(35103);
            }

            if (! $validateUsername['use']) {
                throw new ResponseException(35105);
            }

            if (! $validateUsername['banName']) {
                throw new ResponseException(35106);
            }

            $authUser->fill([
                'username' => $username,
                'last_username_at' => now(),
            ]);

            if ($authUser->last_username_at) {
                UserLog::create([
                    'user_id' => $authUser->id,
                    'type' => UserLog::TYPE_USERNAME,
                    'content' => $authUser->username,
                ]);
            }
        }

        // edit nickname
        if ($dtoRequest->nickname) {
            $isEmpty = Str::of($dtoRequest->nickname)->trim()->isEmpty();
            if ($isEmpty) {
                throw new ResponseException(35107);
            }

            if ($authUser->last_nickname_at) {
                $nextEditNicknameTime = Carbon::parse($authUser->last_nickname_at)->addDays($editNameConfig['nickname_edit']);

                if (now() < $nextEditNicknameTime) {
                    throw new ResponseException(35101);
                }
            }

            $nickname = Str::of($dtoRequest->nickname)->trim();

            $validateNickname = ValidationUtility::nickname($nickname);

            if (! $validateNickname['formatString'] || ! $validateNickname['formatSpace']) {
                throw new ResponseException(35107);
            }

            if (! $validateNickname['minLength']) {
                throw new ResponseException(35109);
            }

            if (! $validateNickname['maxLength']) {
                throw new ResponseException(35108);
            }

            if (! $validateNickname['use']) {
                throw new ResponseException(35111);
            }

            if (! $validateNickname['banName']) {
                throw new ResponseException(35110);
            }

            $authUser->fill([
                'nickname' => $nickname,
                'last_nickname_at' => now(),
            ]);

            if ($authUser->last_nickname_at) {
                UserLog::create([
                    'user_id' => $authUser->id,
                    'type' => UserLog::TYPE_NICKNAME,
                    'content' => $authUser->nickname,
                ]);
            }
        }

        // edit avatarFid
        if ($dtoRequest->avatarFid) {
            $avatarFileId = PrimaryHelper::fresnsPrimaryId('file', $dtoRequest->avatarFid);

            $authUser->fill([
                'avatar_file_id' => $avatarFileId,
                'avatar_file_url' => null,
            ]);

            if ($authUser->avatar_file_id && $authUser->avatar_file_id != $avatarFileId) {
                UserLog::create([
                    'user_id' => $authUser->id,
                    'type' => UserLog::TYPE_AVATAR,
                    'content' => $authUser->avatar_file_id,
                ]);
            }
        }

        // edit avatarUrl
        if ($dtoRequest->avatarUrl) {
            $authUser->fill([
                'avatar_file_id' => null,
                'avatar_file_url' => $dtoRequest->avatarUrl,
            ]);

            if ($authUser->avatar_file_url && $authUser->avatar_file_url != $dtoRequest->avatarUrl) {
                UserLog::create([
                    'user_id' => $authUser->id,
                    'type' => UserLog::TYPE_AVATAR,
                    'content' => $authUser->avatar_file_url,
                ]);
            }
        }

        // edit bannerFid
        if ($dtoRequest->bannerFid) {
            $bannerFileId = PrimaryHelper::fresnsPrimaryId('file', $dtoRequest->bannerFid);

            $authUser->fill([
                'banner_file_id' => $bannerFileId,
                'banner_file_url' => null,
            ]);

            if ($authUser->banner_file_id && $authUser->banner_file_id != $bannerFileId) {
                UserLog::create([
                    'user_id' => $authUser->id,
                    'type' => UserLog::TYPE_BANNER,
                    'content' => $authUser->banner_file_id,
                ]);
            }
        }

        // edit bannerUrl
        if ($dtoRequest->bannerUrl) {
            $authUser->fill([
                'banner_file_id' => null,
                'banner_file_url' => $dtoRequest->bannerUrl,
            ]);

            if ($authUser->banner_file_url && $authUser->banner_file_url != $dtoRequest->bannerUrl) {
                UserLog::create([
                    'user_id' => $authUser->id,
                    'type' => UserLog::TYPE_BANNER,
                    'content' => $authUser->banner_file_url,
                ]);
            }
        }

        // edit gender
        if ($dtoRequest->gender) {
            $authUser->fill([
                'gender' => $dtoRequest->gender,
            ]);
        }
        if ($dtoRequest->genderCustom) {
            $authUser->fill([
                'gender_pronoun' => $dtoRequest->genderCustom,
            ]);
        }
        if ($dtoRequest->genderPronoun) {
            $authUser->fill([
                'gender_custom' => $dtoRequest->genderPronoun,
            ]);
        }

        // edit birthday display type
        if ($dtoRequest->birthdayDisplayType) {
            $authUser->fill([
                'birthday_display_type' => $dtoRequest->birthdayDisplayType,
            ]);
        }

        // edit bio
        if ($dtoRequest->bio) {
            $bio = Str::of($dtoRequest->bio)->trim();

            $validateBio = ValidationUtility::bio($bio);

            if (! $validateBio['length']) {
                throw new ResponseException(33302);
            }

            $bioConfig = ConfigHelper::fresnsConfigByItemKeys([
                'bio_support_mention',
                'bio_support_link',
                'bio_support_hashtag',
            ]);

            if ($bioConfig['bio_support_mention']) {
                ContentUtility::saveMention($bio, Mention::TYPE_USER, $authUser->id, $authUser->id);
            }

            if ($bioConfig['bio_support_link']) {
                ContentUtility::saveLink($bio, DomainLinkUsage::TYPE_USER, $authUser->id);
            }

            if ($bioConfig['bio_support_hashtag']) {
                ContentUtility::saveHashtag($bio, HashtagUsage::TYPE_USER, $authUser->id);
            }

            $authUser->fill([
                'bio' => $bio,
            ]);

            if ($authUser->bio && $authUser->bio != $bio) {
                UserLog::create([
                    'user_id' => $authUser->id,
                    'type' => UserLog::TYPE_BIO,
                    'content' => $bio,
                ]);
            }
        }

        // edit location
        if ($dtoRequest->location) {
            $location = Str::of($dtoRequest->location)->trim();
            $authUser->fill([
                'location' => $location,
            ]);
        }

        // edit conversationPolicy
        if ($dtoRequest->conversationPolicy) {
            $authUser->fill([
                'conversation_policy' => $dtoRequest->conversationPolicy,
            ]);
        }

        // edit commentPolicy
        if ($dtoRequest->commentPolicy) {
            $authUser->fill([
                'comment_policy' => $dtoRequest->commentPolicy,
            ]);
        }

        // edit more info
        if ($dtoRequest->moreInfo) {
            $moreInfo = $authUser->more_info ?? [];

            $newMoreInfo = array_replace($moreInfo, $dtoRequest->moreInfo);

            $authUser->fill([
                'moreInfo' => $newMoreInfo,
            ]);
        }

        // edit save
        $authUser->save();

        // edit archives
        if ($dtoRequest->archives) {
            ContentUtility::saveArchiveUsages(ArchiveUsage::TYPE_USER, $authUser->id, $dtoRequest->archives);
        }

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_USER_EDIT_DATA,
            'fskey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'langTag' => $this->langTag(),
            'aid' => $this->account()->aid,
            'uid' => $authUser->uid,
            'actionName' => \request()->path(),
            'actionDesc' => 'User Edit Data',
            'actionState' => SessionLog::STATE_SUCCESS,
            'actionId' => null,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'loginToken' => null,
            'moreInfo' => null,
        ];
        // upload session log
        \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

        CacheHelper::forgetFresnsUser($authUser->id, $authUser->uid);

        return $this->success();
    }

    // device token
    public function deviceToken(Request $request)
    {
        $dtoRequest = new UserDeviceTokenDTO($request->all());

        $platformId = $this->platformId();
        $appId = $this->appId();
        $authAccount = $this->account();
        $authAccountToken = $this->accountToken();
        $authUser = $this->user();
        $authUserToken = $this->userToken();

        $sessionToken = SessionToken::where('platform_id', $platformId)
            ->where('app_id', $appId)
            ->where('account_id', $authAccount->id)
            ->where('account_token', $authAccountToken)
            ->where('user_id', $authUser->id)
            ->where('user_token', $authUserToken)
            ->first();

        $sessionToken->update([
            'device_token' => $dtoRequest->deviceToken,
        ]);

        return $this->success();
    }

    // mark
    public function mark(Request $request)
    {
        $dtoRequest = new UserMarkDTO($request->all());

        $authUser = $this->user();
        if (! $authUser->is_enabled) {
            throw new ResponseException(35202);
        }

        $authUserId = $authUser->id;

        $configKey = "{$dtoRequest->type}_{$dtoRequest->markType}_enabled";

        $markSet = ConfigHelper::fresnsConfigByItemKey($configKey);
        if (! $markSet) {
            throw new ResponseException(36200);
        }

        $primaryId = PrimaryHelper::fresnsPrimaryId($dtoRequest->type, $dtoRequest->fsid);
        if (empty($primaryId)) {
            throw new ResponseException(32201);
        }

        $contentType = match ($dtoRequest->type) {
            'user' => InteractionUtility::TYPE_USER,
            'group' => InteractionUtility::TYPE_GROUP,
            'hashtag' => InteractionUtility::TYPE_HASHTAG,
            'geotag' => InteractionUtility::TYPE_GEOTAG,
            'post' => InteractionUtility::TYPE_POST,
            'comment' => InteractionUtility::TYPE_COMMENT,
        };

        switch ($dtoRequest->markType) {
            case 'like':
                InteractionUtility::markUserLike($authUserId, $contentType, $primaryId);

                if ($dtoRequest->type == 'comment') {
                    $commentModel = PrimaryHelper::fresnsModelById('comment', $primaryId);

                    if ($commentModel->post->user_id == $authUserId) {
                        CacheHelper::forgetFresnsMultilingual("fresns_detail_comment_{$commentModel->cid}", 'fresnsComments');
                    }
                }
                break;

            case 'dislike':
                InteractionUtility::markUserDislike($authUserId, $contentType, $primaryId);
                break;

            case 'follow':
                $validMark = ValidationUtility::userMarkOwn($authUserId, $contentType, $primaryId);
                if (! $validMark) {
                    throw new ResponseException(36202);
                }

                if ($contentType == UserFollow::TYPE_USER) {
                    $mainRolePerms = PermissionUtility::getUserMainRole($authUserId)['permissions'];
                    $rolePerm = $mainRolePerms['follow_user_max_count'] ?? 0;

                    $followCount = UserFollow::where('mark_type', UserFollow::MARK_TYPE_FOLLOW)->where('user_id', $authUserId)->where('follow_type', $markType)->count();

                    if ($rolePerm <= $followCount) {
                        throw new ResponseException(36118);
                    }
                }

                if ($contentType == UserFollow::TYPE_GROUP) {
                    $group = PrimaryHelper::fresnsModelById('group', $primaryId);

                    if ($group?->follow_type != Group::FOLLOW_FRESNS) {
                        throw new ResponseException(36200);
                    }
                }

                InteractionUtility::markUserFollow($authUserId, $contentType, $primaryId);
                break;

            case 'block':
                $validMark = ValidationUtility::userMarkOwn($authUserId, $contentType, $primaryId);
                if (! $validMark) {
                    throw new ResponseException(36202);
                }

                if ($contentType == UserFollow::TYPE_USER) {
                    $mainRolePerms = PermissionUtility::getUserMainRole($authUserId)['permissions'];
                    $rolePerm = $mainRolePerms['block_user_max_count'] ?? 0;

                    $blockCount = UserFollow::where('mark_type', UserFollow::MARK_TYPE_BLOCK)->where('user_id', $authUserId)->where('follow_type', $markType)->count();

                    if ($rolePerm <= $blockCount) {
                        throw new ResponseException(36118);
                    }
                }

                InteractionUtility::markUserBlock($authUserId, $contentType, $primaryId);
                break;
        }

        if (in_array($dtoRequest->markType, ['follow', 'block'])) {
            $userNote = UserFollow::where('user_id', $authUser->id)->type($contentType)->where('follow_id', $primaryId)->first();

            $userNote?->update([
                'user_note' => $dtoRequest->note ?? null,
            ]);
        }

        CacheHelper::forgetFresnsInteraction($contentType, $primaryId, $authUserId);

        return $this->success();
    }

    // mark note
    public function markNote(Request $request)
    {
        $dtoRequest = new UserMarkNoteDTO($request->all());

        $authUser = $this->user();
        if (! $authUser->is_enabled) {
            throw new ResponseException(35202);
        }

        $primaryId = PrimaryHelper::fresnsPrimaryId($dtoRequest->type, $dtoRequest->fsid);
        if (empty($primaryId)) {
            throw new ResponseException(32201);
        }

        $contentType = match ($dtoRequest->type) {
            'user' => InteractionUtility::TYPE_USER,
            'group' => InteractionUtility::TYPE_GROUP,
            'hashtag' => InteractionUtility::TYPE_HASHTAG,
            'geotag' => InteractionUtility::TYPE_GEOTAG,
            'post' => InteractionUtility::TYPE_POST,
            'comment' => InteractionUtility::TYPE_COMMENT,
        };

        $userNote = UserFollow::where('user_id', $authUser->id)->type($contentType)->where('follow_id', $primaryId)->first();

        if (empty($userNote)) {
            throw new ResponseException(32201);
        }

        $userNote->update([
            'user_note' => $dtoRequest->note ?? null,
        ]);

        CacheHelper::forgetFresnsInteraction($contentType, $primaryId, $authUser->id);

        return $this->success();
    }

    // extend action
    public function extendAction(Request $request)
    {
        $dtoRequest = new UserExtendActionDTO($request->all());

        $authUser = $this->user();
        if (! $authUser->is_enabled) {
            throw new ResponseException(35202);
        }

        $extend = PrimaryHelper::fresnsModelByFsid('extend', $dtoRequest->eid);
        if (empty($extend)) {
            throw new ResponseException(37700);
        }

        if (! $extend->is_enabled) {
            throw new ResponseException(37701);
        }

        $actionItems = $extend->action_items;
        $keys = array_column($actionItems, 'key');

        $hasKey = in_array($dtoRequest->key, $keys);

        if (! $hasKey) {
            throw new ResponseException(37702);
        }

        ExtendUser::updateOrCreate([
            'extend_id' => $extend->id,
            'user_id' => $authUser->id,
            'action_key' => $dtoRequest->key,
        ]);

        CacheHelper::forgetFresnsModel('extend', $extend->eid);

        $langTag = $this->langTag();

        $newExtend = Extend::where('id', $extend->id)->first();
        $extendAction = $newExtend->getExtendInfo($langTag);

        $data = ExtendUtility::handleExtendAction($extendAction, $authUser->id);

        return $this->success($data);
    }

    // list
    public function list(Request $request)
    {
        $dtoRequest = new UserListDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $userQuery = UserStat::with(['profile']);

        $userQuery->whereRelation('profile', 'is_enabled', true);

        $userQuery->whereRelation('profile', 'wait_delete', false);

        $blockIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $userQuery->when($blockIds, function ($query, $value) {
            $query->whereNotIn('user_id', $value);
        });

        $userQuery->when($dtoRequest->roles, function ($query, $value) {
            $ridArr = array_filter(explode(',', $value));

            $roleIdArr = Role::whereIn('rid', $ridArr)->pluck('id')->toArray();

            $query->whereRelation('mainRoleId', 'role_id', $roleIdArr);
        });

        if (isset($dtoRequest->verified)) {
            $userQuery->whereRelation('profile', 'verified_status', $dtoRequest->verified);
        }

        $userQuery->when($dtoRequest->gender, function ($query, $value) {
            $query->whereRelation('profile', 'gender', $value);
        });

        if ($dtoRequest->createdDate) {
            switch ($dtoRequest->createdDate) {
                case 'today':
                    $userQuery->whereDate('created_at', now()->format('Y-m-d'));
                    break;

                case 'yesterday':
                    $userQuery->whereDate('created_at', now()->subDay()->format('Y-m-d'));
                    break;

                case 'week':
                    $userQuery->whereDate('created_at', '>=', now()->startOfWeek()->format('Y-m-d'))
                        ->whereDate('created_at', '<=', now()->endOfWeek()->format('Y-m-d'));
                    break;

                case 'lastWeek':
                    $userQuery->whereDate('created_at', '>=', now()->subWeek()->startOfWeek()->format('Y-m-d'))
                        ->whereDate('created_at', '<=', now()->subWeek()->endOfWeek()->format('Y-m-d'));
                    break;

                case 'month':
                    $userQuery->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    break;

                case 'lastMonth':
                    $lastMonth = now()->subMonth()->month;
                    $year = now()->year;
                    if ($lastMonth == 12) {
                        $year = now()->subYear()->year;
                    }
                    $userQuery->whereMonth('created_at', $lastMonth)->whereYear('created_at', $year);
                    break;

                case 'year':
                    $userQuery->whereYear('created_at', now()->year);
                    break;

                case 'lastYear':
                    $userQuery->whereYear('created_at', now()->subYear()->year);
                    break;
            }
        } else {
            $userQuery->when($dtoRequest->createdDateGt, function ($query, $value) {
                $query->whereDate('created_at', '>=', $value);
            });

            $userQuery->when($dtoRequest->createdDateLt, function ($query, $value) {
                $query->whereDate('created_at', '<=', $value);
            });
        }

        $userQuery->when($dtoRequest->viewCountGt, function ($query, $value) {
            $query->where('view_count', '>=', $value);
        });

        $userQuery->when($dtoRequest->viewCountLt, function ($query, $value) {
            $query->where('view_count', '<=', $value);
        });

        $userQuery->when($dtoRequest->likeCountGt, function ($query, $value) {
            $query->where('liker_count', '>=', $value);
        });

        $userQuery->when($dtoRequest->likeCountLt, function ($query, $value) {
            $query->where('liker_count', '<=', $value);
        });

        $userQuery->when($dtoRequest->dislikeCountGt, function ($query, $value) {
            $query->where('disliker_count', '>=', $value);
        });

        $userQuery->when($dtoRequest->dislikeCountLt, function ($query, $value) {
            $query->where('disliker_count', '<=', $value);
        });

        $userQuery->when($dtoRequest->followCountGt, function ($query, $value) {
            $query->where('follower_count', '>=', $value);
        });

        $userQuery->when($dtoRequest->followCountLt, function ($query, $value) {
            $query->where('follower_count', '<=', $value);
        });

        $userQuery->when($dtoRequest->blockCountGt, function ($query, $value) {
            $query->where('blocker_count', '>=', $value);
        });

        $userQuery->when($dtoRequest->blockCountLt, function ($query, $value) {
            $query->where('blocker_count', '<=', $value);
        });

        $userQuery->when($dtoRequest->postCountGt, function ($query, $value) {
            $query->where('post_publish_count', '>=', $value);
        });

        $userQuery->when($dtoRequest->postCountLt, function ($query, $value) {
            $query->where('post_publish_count', '<=', $value);
        });

        $userQuery->when($dtoRequest->commentCountGt, function ($query, $value) {
            $query->where('comment_publish_count', '>=', $value);
        });

        $userQuery->when($dtoRequest->commentCountLt, function ($query, $value) {
            $query->where('comment_publish_count', '<=', $value);
        });

        $userQuery->when($dtoRequest->postDigestCountGt, function ($query, $value) {
            $query->where('post_digest_count', '>=', $value);
        });

        $userQuery->when($dtoRequest->postDigestCountLt, function ($query, $value) {
            $query->where('post_digest_count', '<=', $value);
        });

        $userQuery->when($dtoRequest->commentDigestCountGt, function ($query, $value) {
            $query->where('comment_digest_count', '>=', $value);
        });

        $userQuery->when($dtoRequest->commentDigestCountLt, function ($query, $value) {
            $query->where('comment_digest_count', '<=', $value);
        });

        $userQuery->when($dtoRequest->extcredits1CountGt, function ($query, $value) {
            $query->where('extcredits1', '>=', $value);
        });

        $userQuery->when($dtoRequest->extcredits1CountLt, function ($query, $value) {
            $query->where('extcredits1', '<=', $value);
        });

        $userQuery->when($dtoRequest->extcredits2CountGt, function ($query, $value) {
            $query->where('extcredits2', '>=', $value);
        });

        $userQuery->when($dtoRequest->extcredits2CountLt, function ($query, $value) {
            $query->where('extcredits2', '<=', $value);
        });

        $userQuery->when($dtoRequest->extcredits3CountGt, function ($query, $value) {
            $query->where('extcredits3', '>=', $value);
        });

        $userQuery->when($dtoRequest->extcredits3CountLt, function ($query, $value) {
            $query->where('extcredits3', '<=', $value);
        });

        $userQuery->when($dtoRequest->extcredits4CountGt, function ($query, $value) {
            $query->where('extcredits4', '>=', $value);
        });

        $userQuery->when($dtoRequest->extcredits4CountLt, function ($query, $value) {
            $query->where('extcredits4', '<=', $value);
        });

        $userQuery->when($dtoRequest->extcredits5CountGt, function ($query, $value) {
            $query->where('extcredits5', '>=', $value);
        });

        $userQuery->when($dtoRequest->extcredits5CountLt, function ($query, $value) {
            $query->where('extcredits5', '<=', $value);
        });

        if ($dtoRequest->orderType == 'random') {
            $userQuery->inRandomOrder();
        } else {
            $orderType = match ($dtoRequest->orderType) {
                'createdTime' => 'created_at',
                'view' => 'view_count',
                'like' => 'liker_count',
                'dislike' => 'disliker_count',
                'follow' => 'follower_count',
                'block' => 'blocker_count',
                'post' => 'post_publish_count',
                'comment' => 'comment_publish_count',
                'postDigest' => 'post_digest_count',
                'commentDigest' => 'comment_digest_count',
                'extcredits1' => 'extcredits1',
                'extcredits2' => 'extcredits2',
                'extcredits3' => 'extcredits3',
                'extcredits4' => 'extcredits4',
                'extcredits5' => 'extcredits5',
                default => 'created_at',
            };

            $orderDirection = match ($dtoRequest->orderDirection) {
                'asc' => 'asc',
                'desc' => 'desc',
                default => 'desc',
            };

            $userQuery->orderBy($orderType, $orderDirection);
        }

        $userData = $userQuery->paginate($dtoRequest->pageSize ?? 15);

        $userOptions = [
            'viewType' => 'list',
            'isLiveStats' => false,
            'filter' => [
                'type' => $dtoRequest->filterType,
                'keys' => $dtoRequest->filterKeys,
            ],
        ];

        $userList = [];
        foreach ($userData as $user) {
            $userList[] = DetailUtility::userDetail($user->profile, $langTag, $timezone, $authUserId, $userOptions);
        }

        return $this->fresnsPaginate($userList, $userData->total(), $userData->perPage());
    }

    // detail
    public function detail(int|string $uidOrUsername, Request $request)
    {
        $dtoRequest = new DetailDTO($request->all());

        if (StrHelper::isPureInt($uidOrUsername)) {
            $viewUser = User::where('uid', $uidOrUsername)->first();
        } else {
            $viewUser = User::where('username', $uidOrUsername)->first();
        }

        if (empty($viewUser)) {
            throw new ResponseException(31602);
        }

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $seoData = PrimaryHelper::fresnsModelSeo(Seo::TYPE_USER, $viewUser->id);

        $item['title'] = StrHelper::languageContent($seoData?->title, $langTag);
        $item['keywords'] = StrHelper::languageContent($seoData?->keywords, $langTag);
        $item['description'] = StrHelper::languageContent($seoData?->description, $langTag);
        $item['manages'] = ExtendUtility::getManageExtensions('user', $langTag, $authUserId);

        $userOptions = [
            'viewType' => 'detail',
            'isLiveStats' => true,
            'filter' => [
                'type' => $dtoRequest->filterType,
                'keys' => $dtoRequest->filterKeys,
            ],
        ];

        $data = [
            'items' => $item,
            'detail' => DetailUtility::userDetail($viewUser, $langTag, $timezone, $authUserId, $userOptions),
        ];

        return $this->success($data);
    }

    // followers you follow
    public function followersYouFollow(int|string $uidOrUsername, Request $request)
    {
        $dtoRequest = new PaginationDTO($request->all());

        $pageSize = $dtoRequest->pageSize ?? 15;

        $authUser = $this->user();
        if (empty($authUser)) {
            return $this->warning(31601);
        }

        $viewUser = PrimaryHelper::fresnsModelByFsid('user', $uidOrUsername);

        if (empty($viewUser)) {
            throw new ResponseException(31602);
        }

        $langTag = $this->langTag();
        $timezone = $this->timezone();

        $userOptions = [
            'viewType' => 'list',
            'isLiveStats' => false,
            'filter' => [
                'type' => $dtoRequest->filterType,
                'keys' => $dtoRequest->filterKeys,
            ],
        ];

        // mutual follow
        if ($authUser->id == $viewUser->id) {
            $userFollows = UserFollow::with(['user'])
                ->markType(UserFollow::MARK_TYPE_FOLLOW)
                ->type(UserFollow::TYPE_USER)
                ->where('user_id', $authUser->id)
                ->where('is_mutual', true)
                ->latest()
                ->paginate($pageSize);

            $userList = [];
            foreach ($userFollows as $userFollow) {
                if (empty($userFollow->user)) {
                    continue;
                }

                $userList[] = DetailUtility::userDetail($userFollow->user, $langTag, $timezone, $authUser->id, $userOptions);
            }

            return $this->fresnsPaginate($userList, $userFollows->total(), $userFollows->perPage());
        }

        // followers you follow
        $authUserFollowing = UserFollow::markType(UserFollow::MARK_TYPE_FOLLOW)->type(UserFollow::TYPE_USER)->where('user_id', $authUser->id)->latest()->pluck('follow_id')->toArray();
        $viewUserFollowers = UserFollow::markType(UserFollow::MARK_TYPE_FOLLOW)->type(UserFollow::TYPE_USER)->where('follow_id', $viewUser->id)->latest()->pluck('user_id')->toArray();

        if (empty($authUserFollowing) && empty($viewUserFollowers)) {
            return $this->fresnsPaginate([], 0, $pageSize);
        }

        $userIdArr = array_merge($viewUserFollowers, $authUserFollowing);
        $uniqueArr = array_unique($userIdArr);

        $youKnowArr = array_diff_assoc($userIdArr, $uniqueArr);

        if (empty($youKnowArr)) {
            return $this->fresnsPaginate([], 0, $pageSize);
        }

        $userQuery = User::whereIn('id', $youKnowArr)
            ->where('is_enabled', true)
            ->where('wait_delete', false)
            ->paginate($pageSize);

        $userList = [];
        foreach ($userQuery as $user) {
            $userList[] = DetailUtility::userDetail($user, $langTag, $timezone, $authUser->id, $userOptions);
        }

        return $this->fresnsPaginate($userList, $userQuery->total(), $userQuery->perPage());
    }

    // interaction
    public function interaction(int|string $uidOrUsername, string $type, Request $request)
    {
        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new InteractionDTO($requestData);

        $viewUser = PrimaryHelper::fresnsModelByFsid('user', $uidOrUsername);

        if (empty($viewUser)) {
            throw new ResponseException(31602);
        }

        $orderDirection = $dtoRequest->orderDirection ?: 'desc';

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $isMe = $viewUser->id == $authUserId;

        InteractionService::checkInteractionSetting('user', $dtoRequest->type, $isMe);

        $service = new InteractionService();
        $data = $service->getUsersWhoMarkIt($dtoRequest->type, InteractionService::TYPE_USER, $viewUser->id, $orderDirection, $langTag, $timezone, $authUserId);

        return $this->fresnsPaginate($data['paginateData'], $data['interactionData']->total(), $data['interactionData']->perPage());
    }

    // markList
    public function markList(int|string $uidOrUsername, string $markType, string $listType, Request $request)
    {
        $viewUser = PrimaryHelper::fresnsModelByFsid('user', $uidOrUsername);

        if (empty($viewUser)) {
            throw new ResponseException(31602);
        }

        $requestData = $request->all();
        $requestData['markType'] = $markType;
        $requestData['listType'] = $listType;
        $dtoRequest = new UserMarkListDTO($requestData);

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $profileMarkType = match ($markType) {
            'like' => 'likes',
            'dislike' => 'dislikes',
            'follow' => 'following',
            'block' => 'blocking',
        };

        if ($viewUser->id != $authUserId) {
            $setKey = "profile_{$profileMarkType}_{$dtoRequest->listType}_enabled";

            $markSet = ConfigHelper::fresnsConfigByItemKey($setKey);

            if (! $markSet) {
                throw new ResponseException(36201);
            }
        }

        switch ($dtoRequest->markType) {
            case 'like':
                $markQuery = UserLike::markType(UserLike::MARK_TYPE_LIKE);
                break;

            case 'dislike':
                $markQuery = UserLike::markType(UserLike::MARK_TYPE_DISLIKE);
                break;

            case 'follow':
                $markQuery = UserFollow::markType(UserFollow::MARK_TYPE_FOLLOW);
                break;

            case 'block':
                $markQuery = UserFollow::markType(UserFollow::MARK_TYPE_BLOCK);
                break;
        }

        $markTypeInt = match ($dtoRequest->listType) {
            'users' => InteractionService::TYPE_USER,
            'groups' => InteractionService::TYPE_GROUP,
            'hashtags' => InteractionService::TYPE_HASHTAG,
            'geotags' => InteractionService::TYPE_GEOTAG,
            'posts' => InteractionService::TYPE_POST,
            'comments' => InteractionService::TYPE_COMMENT,
        };

        $orderDirection = $dtoRequest->orderDirection ?: 'desc';

        $markData = $markQuery->with(['user'])
            ->where('user_id', $authUserId)
            ->type($markTypeInt)
            ->orderBy('created_at', $orderDirection)
            ->paginate($dtoRequest->pageSize ?? 15);

        // options
        $options = [
            'viewType' => 'list',
            'contentFormat' => \request()->header('X-Fresns-Client-Content-Format'),
            'isLiveStats' => false,
            'filter' => [
                'type' => $dtoRequest->filterType,
                'keys' => $dtoRequest->filterKeys,
            ],
        ];

        // data
        $paginateData = [];

        switch ($dtoRequest->listType) {
            case 'users':
                foreach ($markData as $mark) {
                    if (empty($mark->user)) {
                        continue;
                    }

                    $paginateData[] = DetailUtility::userDetail($mark->user, $langTag, $timezone, $authUserId, $options);
                }
                break;

            case 'groups':
                foreach ($markData as $mark) {
                    if (empty($mark->group)) {
                        continue;
                    }

                    $paginateData[] = DetailUtility::groupDetail($mark->group, $langTag, $timezone, $authUserId, $options);
                }
                break;

            case 'hashtags':
                foreach ($markData as $mark) {
                    if (empty($mark->hashtag)) {
                        continue;
                    }

                    $paginateData[] = DetailUtility::hashtagDetail($mark->hashtag, $langTag, $timezone, $authUserId, $options);
                }
                break;

            case 'geotags':
                foreach ($markData as $mark) {
                    if (empty($mark->geotag)) {
                        continue;
                    }

                    $paginateData[] = DetailUtility::geotagDetail($mark->geotag, $langTag, $timezone, $authUserId, $options);
                }
                break;

            case 'posts':
                foreach ($markData as $mark) {
                    if (empty($mark->post)) {
                        continue;
                    }

                    $paginateData[] = DetailUtility::postDetail($mark->post, $langTag, $timezone, $authUserId, $options);
                }
                break;

            case 'comments':
                foreach ($markData as $mark) {
                    if (empty($mark->comment)) {
                        continue;
                    }

                    $paginateData[] = DetailUtility::commentDetail($mark->comment, $langTag, $timezone, $authUserId, $options);
                }
                break;
        }

        return $this->fresnsPaginate($paginateData, $markData->total(), $markData->perPage());
    }
}
