<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\InteractionDTO;
use App\Fresns\Api\Http\DTO\PaginationDTO;
use App\Fresns\Api\Http\DTO\UserAuthDTO;
use App\Fresns\Api\Http\DTO\UserEditDTO;
use App\Fresns\Api\Http\DTO\UserExtcreditsLogsDTO;
use App\Fresns\Api\Http\DTO\UserListDTO;
use App\Fresns\Api\Http\DTO\UserMarkDTO;
use App\Fresns\Api\Http\DTO\UserMarkListDTO;
use App\Fresns\Api\Http\DTO\UserMarkNoteDTO;
use App\Fresns\Api\Services\AccountService;
use App\Fresns\Api\Services\InteractionService;
use App\Fresns\Api\Services\UserService;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PluginHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\ArchiveUsage;
use App\Models\BlockWord;
use App\Models\CommentLog;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\DomainLinkUsage;
use App\Models\File;
use App\Models\Group;
use App\Models\HashtagUsage;
use App\Models\Mention;
use App\Models\Notification;
use App\Models\PostLog;
use App\Models\SessionLog;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserExtcreditsLog;
use App\Models\UserFollow;
use App\Models\UserStat;
use App\Utilities\ConfigUtility;
use App\Utilities\ContentUtility;
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
    // list
    public function list(Request $request)
    {
        $dtoRequest = new UserListDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $userQuery = UserStat::with('profile', 'mainRoleId')->whereRelation('profile', 'is_enabled', true)->whereRelation('profile', 'wait_delete', false);

        if ($dtoRequest->roles) {
            $roleArr = array_filter(explode(',', $dtoRequest->roles));

            $userQuery->whereHas('mainRoleId', function ($query) use ($roleArr) {
                $query->whereIn('role_id', $roleArr);
            });
        }

        if (isset($dtoRequest->verified)) {
            $userQuery->whereRelation('profile', 'verified_status', $dtoRequest->verified);
        }

        $userQuery->when($dtoRequest->gender, function ($query, $value) {
            $query->whereRelation('profile', 'gender', $value);
        });

        if ($dtoRequest->createDate) {
            switch ($dtoRequest->createDate) {
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
            $userQuery->when($dtoRequest->createDateGt, function ($query, $value) {
                $query->whereDate('created_at', '>=', $value);
            });

            $userQuery->when($dtoRequest->createDateLt, function ($query, $value) {
                $query->whereDate('created_at', '<=', $value);
            });
        }

        $userQuery->when($dtoRequest->likeCountGt, function ($query, $value) {
            $query->where('like_me_count', '>=', $value);
        });

        $userQuery->when($dtoRequest->likeCountLt, function ($query, $value) {
            $query->where('like_me_count', '<=', $value);
        });

        $userQuery->when($dtoRequest->dislikeCountGt, function ($query, $value) {
            $query->where('dislike_me_count', '>=', $value);
        });

        $userQuery->when($dtoRequest->dislikeCountLt, function ($query, $value) {
            $query->where('dislike_me_count', '<=', $value);
        });

        $userQuery->when($dtoRequest->followCountGt, function ($query, $value) {
            $query->where('follow_me_count', '>=', $value);
        });

        $userQuery->when($dtoRequest->followCountLt, function ($query, $value) {
            $query->where('follow_me_count', '<=', $value);
        });

        $userQuery->when($dtoRequest->blockCountGt, function ($query, $value) {
            $query->where('block_me_count', '>=', $value);
        });

        $userQuery->when($dtoRequest->blockCountLt, function ($query, $value) {
            $query->where('block_me_count', '<=', $value);
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

        $orderType = match ($dtoRequest->orderType) {
            default => 'created_at',
            'createDate' => 'created_at',
            'like' => 'like_me_count',
            'dislike' => 'dislike_me_count',
            'follow' => 'follow_me_count',
            'block' => 'block_me_count',
            'post' => 'post_publish_count',
            'comment' => 'comment_publish_count',
            'postDigest' => 'post_digest_count',
            'commentDigest' => 'comment_digest_count',
            'extcredits1' => 'extcredits1',
            'extcredits2' => 'extcredits2',
            'extcredits3' => 'extcredits3',
            'extcredits4' => 'extcredits4',
            'extcredits5' => 'extcredits5',
        };

        $orderDirection = match ($dtoRequest->orderDirection) {
            default => 'desc',
            'asc' => 'asc',
            'desc' => 'desc',
        };

        $userQuery->orderBy($orderType, $orderDirection);

        $userData = $userQuery->paginate($dtoRequest->pageSize ?? 15);

        $userList = [];
        $service = new UserService();
        foreach ($userData as $user) {
            $userList[] = $service->userData($user->profile, 'list', $langTag, $timezone, $authUserId);
        }

        return $this->fresnsPaginate($userList, $userData->total(), $userData->perPage());
    }

    // detail
    public function detail(string $uidOrUsername)
    {
        if (StrHelper::isPureInt($uidOrUsername)) {
            $viewUser = User::where('uid', $uidOrUsername)->first();
        } else {
            $viewUser = User::where('username', $uidOrUsername)->first();
        }

        if (empty($viewUser)) {
            throw new ApiException(31602);
        }

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $seoData = LanguageHelper::fresnsLanguageSeoDataById('user', $viewUser->id, $langTag);

        $item['title'] = $seoData?->title;
        $item['keywords'] = $seoData?->keywords;
        $item['description'] = $seoData?->description;
        $item['manages'] = ExtendUtility::getManageExtensions('user', $langTag, $authUserId);
        $data['items'] = $item;

        $service = new UserService();
        $data['detail'] = $service->userData($viewUser, 'detail', $langTag, $timezone, $authUserId);

        return $this->success($data);
    }

    // followers you follow
    public function followersYouFollow(Request $request, string $uidOrUsername)
    {
        $dtoRequest = new PaginationDTO($request->all());

        $pageSize = $dtoRequest->pageSize ?? 15;

        $authUser = $this->user();
        if (empty($authUser)) {
            return $this->warning(31601);
        }

        if (StrHelper::isPureInt($uidOrUsername)) {
            $viewUser = User::where('uid', $uidOrUsername)->first();
        } else {
            $viewUser = User::where('username', $uidOrUsername)->first();
        }

        if (empty($viewUser)) {
            throw new ApiException(31602);
        }

        $langTag = $this->langTag();
        $timezone = $this->timezone();

        if ($authUser->id != $viewUser->id) {
            $viewUserFollowers = UserFollow::type(UserFollow::TYPE_USER)->where('follow_id', $viewUser->id)->latest()->pluck('user_id')->toArray();
            $authUserFollowing = UserFollow::type(UserFollow::TYPE_USER)->where('user_id', $authUser->id)->latest()->pluck('follow_id')->toArray();

            if (empty($viewUserFollowers) && empty($authUserFollowing)) {
                return $this->fresnsPaginate([], 0, $pageSize);
            }

            $userIdArr = array_merge($viewUserFollowers, $authUserFollowing);
            $uniqueArr = array_unique($userIdArr);

            $youKnowArr = array_diff_assoc($userIdArr, $uniqueArr);
        } else {
            $youKnowArr = UserFollow::type(UserFollow::TYPE_USER)
                ->where('user_id', $authUser->id)
                ->where('is_mutual', 1)
                ->latest()
                ->pluck('follow_id')
                ->toArray();
        }

        if (empty($youKnowArr)) {
            return $this->fresnsPaginate([], 0, $pageSize);
        }

        $userQuery = User::whereIn('id', $youKnowArr)
            ->where('is_enabled', true)
            ->where('wait_delete', false)
            ->paginate($pageSize);

        $service = new UserService();

        $userList = [];
        foreach ($userQuery as $user) {
            $userList[] = $service->userData($user, 'list', $langTag, $timezone, $authUser->id);
        }

        return $this->fresnsPaginate($userList, $userQuery->total(), $userQuery->perPage());
    }

    // interaction
    public function interaction(string $uidOrUsername, string $type, Request $request)
    {
        if (StrHelper::isPureInt($uidOrUsername)) {
            $viewUser = User::where('uid', $uidOrUsername)->first();
        } else {
            $viewUser = User::where('username', $uidOrUsername)->first();
        }

        if (empty($viewUser)) {
            throw new ApiException(31602);
        }

        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new InteractionDTO($requestData);

        $orderDirection = $dtoRequest->orderDirection ?: 'desc';

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        if ($viewUser->id == $authUserId) {
            InteractionService::checkMyInteractionSetting($dtoRequest->type, 'user');
        } else {
            InteractionService::checkInteractionSetting($dtoRequest->type, 'user');
        }

        $service = new InteractionService();
        $data = $service->getUsersWhoMarkIt($dtoRequest->type, InteractionService::TYPE_USER, $viewUser->id, $orderDirection, $langTag, $timezone, $authUserId);

        return $this->fresnsPaginate($data['paginateData'], $data['interactionData']->total(), $data['interactionData']->perPage());
    }

    // markList
    public function markList(string $uidOrUsername, string $markType, string $listType, Request $request)
    {
        if (StrHelper::isPureInt($uidOrUsername)) {
            $viewUser = User::where('uid', $uidOrUsername)->first();
        } else {
            $viewUser = User::where('username', $uidOrUsername)->first();
        }

        if (empty($viewUser)) {
            throw new ApiException(31602);
        }

        $requestData = $request->all();
        $requestData['markType'] = $markType;
        $requestData['listType'] = $listType;
        $dtoRequest = new UserMarkListDTO($requestData);

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        if ($viewUser->id != $authUserId) {
            $markSet = ConfigHelper::fresnsConfigByItemKey("it_{$dtoRequest->markType}_{$dtoRequest->listType}");
            if (! $markSet) {
                throw new ApiException(36201);
            }
        }

        $orderDirection = $dtoRequest->orderDirection ?: 'desc';

        $service = new InteractionService();
        $data = $service->getItMarkList($dtoRequest->markType, $dtoRequest->listType, $viewUser->id, $orderDirection, $langTag, $timezone, $authUserId);

        return $this->fresnsPaginate($data['paginateData'], $data['markData']->total(), $data['markData']->perPage());
    }

    // auth
    public function auth(Request $request)
    {
        $dtoRequest = new UserAuthDTO($request->all());

        if (StrHelper::isPureInt($dtoRequest->uidOrUsername)) {
            $authUser = User::where('uid', $dtoRequest->uidOrUsername)->first();
        } else {
            $authUser = User::where('username', $dtoRequest->uidOrUsername)->first();
        }

        if (empty($authUser)) {
            throw new ApiException(31602);
        }

        $langTag = $this->langTag();
        $timezone = $this->timezone();

        $password = base64_decode($dtoRequest->password, true);

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
            'objectName' => \request()->path(),
            'objectAction' => 'User Auth',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => null,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => $dtoRequest->deviceToken,
            'moreJson' => null,
        ];

        // login
        $wordBody = [
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'aid' => \request()->header('X-Fresns-Aid'),
            'aidToken' => \request()->header('X-Fresns-Aid-Token'),
            'uid' => $authUser->uid,
            'password' => $password,
        ];
        $fresnsResponse = \FresnsCmdWord::plugin('Fresns')->verifyUser($wordBody);

        if ($fresnsResponse->isErrorResponse()) {
            // upload session log
            $sessionLog['objectAction'] = 'verifyUser';
            $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

            return $fresnsResponse->errorResponse();
        }

        // create token
        $createTokenWordBody = [
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'aid' => $fresnsResponse->getData('aid'),
            'aidToken' => $fresnsResponse->getData('aidToken'),
            'uid' => $fresnsResponse->getData('uid'),
            'expiredTime' => null,
        ];
        $fresnsTokenResponse = \FresnsCmdWord::plugin('Fresns')->createUserToken($createTokenWordBody);

        if ($fresnsTokenResponse->isErrorResponse()) {
            // upload session log
            $sessionLog['objectAction'] = 'createUserToken';
            $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

            return $fresnsTokenResponse->errorResponse();
        }

        // get user token
        $token['token'] = $fresnsTokenResponse->getData('uidToken');
        $token['expiredHours'] = $fresnsTokenResponse->getData('expiredHours');
        $token['expiredDays'] = $fresnsTokenResponse->getData('expiredDays');
        $token['expiredDateTime'] = $fresnsTokenResponse->getData('expiredDateTime');
        $data['sessionToken'] = $token;

        // get user data
        $service = new UserService();
        $data['detail'] = $service->userData($authUser, 'list', $langTag, $timezone);

        // upload session log
        $sessionLog['objectOrderId'] = $fresnsResponse->getData('uidTokenId');
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        // notify subscribe
        $authAccount = $this->account();
        $accountToken = [
            'token' => \request()->header('X-Fresns-Aid-Token'),
            'expiredHours' => null,
            'expiredDays' => null,
            'expiredDateTime' => null,
        ];

        $accountService = new AccountService();
        $accountDetail = $accountService->accountDetail($authAccount, $langTag, $timezone);

        SubscribeUtility::notifyAccountAndUserLogin($authAccount->id, $accountToken, $accountDetail, $authUser->id, $data['sessionToken'], $data['detail']);

        return $this->success($data);
    }

    // panel
    public function panel()
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $userId = $authUser->id;
        $userUid = $authUser->uid;

        $cacheTag = 'fresnsUsers';
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);

        // multi user
        $multiUserConfigs = ConfigHelper::fresnsConfigByItemKeys([
            'multi_user_status',
            'multi_user_service',
            'multi_user_roles',
        ]);

        $multiUserStatus = $multiUserConfigs['multi_user_status'];
        if ($multiUserConfigs['multi_user_status'] && $multiUserConfigs['multi_user_roles']) {
            $authUserRoles = PermissionUtility::getUserRoles($userId, $langTag);
            $authUserRoleIdArr = array_column($authUserRoles, 'rid');

            $intersect = array_intersect($multiUserConfigs['multi_user_roles'], $authUserRoleIdArr);

            $multiUserStatus = $intersect ? true : false;
        }
        $multiUser = [
            'status' => $multiUserStatus,
            'service' => PluginHelper::fresnsPluginUrlByFskey($multiUserConfigs['multi_user_service']),
        ];

        // conversations
        $conversationsCacheKey = "fresns_api_user_panel_conversations_{$userUid}";
        $conversations = CacheHelper::get($conversationsCacheKey, $cacheTag);
        if (empty($conversations)) {
            $aConversations = Conversation::where('a_user_id', $userId)->where('a_is_display', 1);
            $bConversations = Conversation::where('b_user_id', $userId)->where('b_is_display', 1);

            $conversationCount = $aConversations->union($bConversations)->count();
            $conversationMessageCount = ConversationMessage::where('receive_user_id', $userId)->whereNull('receive_read_at')->whereNull('receive_deleted_at')->isEnabled()->count();

            $conversations = [
                'conversationCount' => $conversationCount,
                'unreadMessages' => $conversationMessageCount,
            ];

            CacheHelper::put($conversations, $conversationsCacheKey, $cacheTag, null, $cacheTime);
        }

        // unread notifications
        $notificationsCacheKey = "fresns_api_user_panel_notifications_{$userUid}";
        $unreadNotifications = CacheHelper::get($notificationsCacheKey, $cacheTag);
        if (empty($unreadNotifications)) {
            $unreadNotifications = [
                'systems' => Notification::where('type', Notification::TYPE_SYSTEM)->where('user_id', $userId)->where('is_read', false)->count(),
                'recommends' => Notification::where('type', Notification::TYPE_RECOMMEND)->where('user_id', $userId)->where('is_read', false)->count(),
                'likes' => Notification::where('type', Notification::TYPE_LIKE)->where('user_id', $userId)->where('is_read', false)->count(),
                'dislikes' => Notification::where('type', Notification::TYPE_DISLIKE)->where('user_id', $userId)->where('is_read', false)->count(),
                'follows' => Notification::where('type', Notification::TYPE_FOLLOW)->where('user_id', $userId)->where('is_read', false)->count(),
                'blocks' => Notification::where('type', Notification::TYPE_BLOCK)->where('user_id', $userId)->where('is_read', false)->count(),
                'mentions' => Notification::where('type', Notification::TYPE_MENTION)->where('user_id', $userId)->where('is_read', false)->count(),
                'comments' => Notification::where('type', Notification::TYPE_COMMENT)->where('user_id', $userId)->where('is_read', false)->count(),
                'quotes' => Notification::where('type', Notification::TYPE_QUOTE)->where('user_id', $userId)->where('is_read', false)->count(),
            ];

            CacheHelper::put($unreadNotifications, $notificationsCacheKey, $cacheTag, null, $cacheTime);
        }

        // draft count
        $draftsCacheKey = "fresns_api_user_panel_drafts_{$userUid}";
        $draftCount = CacheHelper::get($draftsCacheKey, $cacheTag);
        if (empty($draftCount)) {
            $draftCount = [
                'posts' => PostLog::where('user_id', $userId)->whereIn('state', [1, 4])->count(),
                'comments' => CommentLog::where('user_id', $userId)->whereIn('state', [1, 4])->count(),
            ];

            CacheHelper::put($draftCount, $draftsCacheKey, $cacheTag, null, $cacheTime);
        }

        $publishConfig = [
            'post' => ConfigUtility::getPublishConfigByType($userId, 'post', $langTag, $timezone),
            'comment' => ConfigUtility::getPublishConfigByType($userId, 'comment', $langTag, $timezone),
        ];

        $data['multiUser'] = $multiUser;
        $data['features'] = ExtendUtility::getUserExtensions('features', $userId, $langTag);
        $data['profiles'] = ExtendUtility::getUserExtensions('profiles', $userId, $langTag);
        $data['conversations'] = $conversations;
        $data['unreadNotifications'] = $unreadNotifications;
        $data['draftCount'] = $draftCount;
        $data['publishConfig'] = $publishConfig;
        $data['fileAccept'] = FileHelper::fresnsFileAcceptByType();

        return $this->success($data);
    }

    // extcredits logs
    public function extcreditsLogs(Request $request)
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
            $item['fskey'] = $log->plugin_fskey;
            $item['remark'] = $log->remark;
            $item['createdDatetime'] = DateHelper::fresnsFormatDateTime($log->created_at, $timezone, $langTag);
            $item['createdTimeAgo'] = DateHelper::fresnsHumanReadableTime($log->created_at, $langTag);

            $logList[] = $item;
        }

        return $this->fresnsPaginate($logList, $extcreditsLogs->total(), $extcreditsLogs->perPage());
    }

    // edit
    public function edit(Request $request)
    {
        $dtoRequest = new UserEditDTO($request->all());

        $authUser = $this->user();

        if (! $authUser->is_enabled) {
            throw new ApiException(35202);
        }

        if ($dtoRequest->avatarFid && $dtoRequest->avatarUrl) {
            throw new ApiException(30005);
        }

        if ($dtoRequest->bannerFid && $dtoRequest->bannerUrl) {
            throw new ApiException(30005);
        }

        $editNameConfig = ConfigHelper::fresnsConfigByItemKeys([
            'username_edit',
            'nickname_edit',
        ]);

        // edit username
        if ($dtoRequest->username) {
            $isEmpty = Str::of($dtoRequest->username)->trim()->isEmpty();
            if ($isEmpty) {
                throw new ApiException(35102);
            }

            if ($authUser->last_username_at) {
                $nextEditUsernameTime = Carbon::parse($authUser->last_username_at)->addDays($editNameConfig['username_edit']);

                if (now() < $nextEditUsernameTime) {
                    throw new ApiException(35101);
                }
            }

            $username = Str::of($dtoRequest->username)->trim();
            $validateUsername = ValidationUtility::username($username);

            if (! $validateUsername['formatString'] || ! $validateUsername['formatHyphen'] || ! $validateUsername['formatNumeric']) {
                throw new ApiException(35102);
            }

            if (! $validateUsername['minLength']) {
                throw new ApiException(35104);
            }

            if (! $validateUsername['maxLength']) {
                throw new ApiException(35103);
            }

            if (! $validateUsername['use']) {
                throw new ApiException(35105);
            }

            if (! $validateUsername['banName']) {
                throw new ApiException(35106);
            }

            $authUser->fill([
                'username' => $username,
                'last_username_at' => now(),
            ]);
        }

        // edit nickname
        if ($dtoRequest->nickname) {
            $isEmpty = Str::of($dtoRequest->nickname)->trim()->isEmpty();
            if ($isEmpty) {
                throw new ApiException(35107);
            }

            if ($authUser->last_nickname_at) {
                $nextEditNicknameTime = Carbon::parse($authUser->last_nickname_at)->addDays($editNameConfig['nickname_edit']);

                if (now() < $nextEditNicknameTime) {
                    throw new ApiException(35101);
                }
            }

            $nickname = Str::of($dtoRequest->nickname)->trim();

            $validateNickname = ValidationUtility::nickname($nickname);

            if (! $validateNickname['formatString'] || ! $validateNickname['formatSpace']) {
                throw new ApiException(35107);
            }

            if (! $validateNickname['minLength']) {
                throw new ApiException(35109);
            }

            if (! $validateNickname['maxLength']) {
                throw new ApiException(35108);
            }

            if (! $validateNickname['use']) {
                throw new ApiException(35111);
            }

            if (! $validateNickname['banName']) {
                throw new ApiException(35110);
            }

            $blockWords = BlockWord::where('user_mode', 2)->get('word', 'replace_word');

            $newNickname = str_ireplace($blockWords->pluck('word')->toArray(), $blockWords->pluck('replace_word')->toArray(), $nickname);

            $authUser->fill([
                'nickname' => $newNickname,
                'last_nickname_at' => now(),
            ]);
        }

        // edit avatarFid
        if ($dtoRequest->avatarFid) {
            $fileId = PrimaryHelper::fresnsFileIdByFid($dtoRequest->avatarFid);

            $authUser->fill([
                'avatar_file_id' => $fileId,
                'avatar_file_url' => null,
            ]);
        }

        // edit avatarUrl
        if ($dtoRequest->avatarUrl) {
            $authUser->fill([
                'avatar_file_id' => null,
                'avatar_file_url' => $dtoRequest->avatarUrl,
            ]);
        }

        // edit bannerFid
        if ($dtoRequest->bannerFid) {
            $fileId = PrimaryHelper::fresnsFileIdByFid($dtoRequest->bannerFid);

            $authUser->fill([
                'banner_file_id' => $fileId,
                'banner_file_url' => null,
            ]);
        }

        // edit bannerUrl
        if ($dtoRequest->bannerUrl) {
            $authUser->fill([
                'banner_file_id' => null,
                'banner_file_url' => $dtoRequest->bannerUrl,
            ]);
        }

        // edit gender
        if ($dtoRequest->gender) {
            $authUser->fill([
                'gender' => $dtoRequest->gender,
            ]);
        }

        // edit birthday
        if ($dtoRequest->birthday) {
            $authUser->fill([
                'birthday' => $dtoRequest->birthday,
            ]);
        }

        // edit bio
        if ($dtoRequest->bio) {
            $bio = Str::of($dtoRequest->bio)->trim();

            $validateBio = ValidationUtility::bio($bio);

            if (! $validateBio['banWord']) {
                throw new ApiException(33301);
            }

            if (! $validateBio['length']) {
                throw new ApiException(33302);
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
        }

        // edit location
        if ($dtoRequest->location) {
            $location = Str::of($dtoRequest->location)->trim();
            $authUser->fill([
                'location' => $location,
            ]);
        }

        // edit conversationLimit
        if ($dtoRequest->conversationLimit) {
            $authUser->fill([
                'conversation_limit' => $dtoRequest->conversationLimit,
            ]);
        }

        // edit commentLimit
        if ($dtoRequest->commentLimit) {
            $authUser->fill([
                'comment_limit' => $dtoRequest->commentLimit,
            ]);
        }

        // edit timezone
        if ($dtoRequest->timezone) {
            $authUser->fill([
                'timezone' => $dtoRequest->timezone,
            ]);
        }

        // edit save
        if (! $authUser->isDirty() && empty($dtoRequest->archives) && empty($dtoRequest->deviceToken)) {
            throw new ApiException(30001);
        }

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
            'objectName' => \request()->path(),
            'objectAction' => 'User Edit Data',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => null,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => $dtoRequest->deviceToken,
            'moreJson' => null,
        ];
        // upload session log
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        CacheHelper::forgetFresnsUser($authUser->id, $authUser->uid);

        return $this->success();
    }

    // mark
    public function mark(Request $request)
    {
        $dtoRequest = new UserMarkDTO($request->all());
        $authUser = $this->user();
        if (! $authUser->is_enabled) {
            throw new ApiException(35202);
        }

        $authUserId = $authUser->id;

        $markSet = ConfigHelper::fresnsConfigByItemKey("{$dtoRequest->interactionType}_{$dtoRequest->markType}_setting");
        if (! $markSet) {
            throw new ApiException(36200);
        }

        $primaryId = PrimaryHelper::fresnsPrimaryId($dtoRequest->markType, $dtoRequest->fsid);
        if (empty($primaryId)) {
            throw new ApiException(32201);
        }

        $markType = match ($dtoRequest->markType) {
            'user' => 1,
            'group' => 2,
            'hashtag' => 3,
            'post' => 4,
            'comment' => 5,
        };

        switch ($dtoRequest->interactionType) {
            case 'like':
                InteractionUtility::markUserLike($authUserId, $markType, $primaryId);

                if ($dtoRequest->markType == 'comment') {
                    $commentModel = PrimaryHelper::fresnsModelById('comment', $primaryId);

                    if ($commentModel->post->user_id == $authUserId) {
                        CacheHelper::forgetFresnsMultilingual("fresns_api_comment_{$commentModel->cid}", 'fresnsComments');
                    }
                }
                break;

            case 'dislike':
                InteractionUtility::markUserDislike($authUserId, $markType, $primaryId);
                break;

            case 'follow':
                $validMark = ValidationUtility::userMarkOwn($authUserId, $markType, $primaryId);
                if (! $validMark) {
                    throw new ApiException(36202);
                }

                if ($markType == UserFollow::TYPE_USER) {
                    $mainRolePerms = PermissionUtility::getUserMainRole($authUserId)['permissions'];
                    $rolePerm = $mainRolePerms['follow_user_max_count'] ?? 0;
                    $followCount = UserFollow::where('user_id', $authUserId)->where('follow_type', $markType)->count();

                    if ($rolePerm <= $followCount) {
                        throw new ApiException(36118);
                    }
                }

                if ($markType == UserFollow::TYPE_GROUP) {
                    $group = PrimaryHelper::fresnsModelById('group', $primaryId);

                    if ($group?->type_follow != Group::FOLLOW_FRESNS) {
                        throw new ApiException(36200);
                    }
                }

                InteractionUtility::markUserFollow($authUserId, $markType, $primaryId);
                break;

            case 'block':
                $validMark = ValidationUtility::userMarkOwn($authUserId, $markType, $primaryId);
                if (! $validMark) {
                    throw new ApiException(36202);
                }

                if ($markType == UserBlock::TYPE_USER) {
                    $mainRolePerms = PermissionUtility::getUserMainRole($authUserId)['permissions'];
                    $rolePerm = $mainRolePerms['block_user_max_count'] ?? 0;
                    $blockCount = UserBlock::where('user_id', $authUserId)->where('block_type', $markType)->count();

                    if ($rolePerm <= $blockCount) {
                        throw new ApiException(36118);
                    }
                }

                InteractionUtility::markUserBlock($authUserId, $markType, $primaryId);
                break;
        }

        CacheHelper::forgetFresnsInteraction($markType, $primaryId, $authUserId);

        return $this->success();
    }

    // mark note
    public function markNote(Request $request)
    {
        $dtoRequest = new UserMarkNoteDTO($request->all());

        $authUser = $this->user();
        if (! $authUser->is_enabled) {
            throw new ApiException(35202);
        }

        $primaryId = PrimaryHelper::fresnsPrimaryId($dtoRequest->markType, $dtoRequest->fsid);
        if (empty($primaryId)) {
            throw new ApiException(32201);
        }

        $markType = match ($dtoRequest->markType) {
            'user' => 1,
            'group' => 2,
            'hashtag' => 3,
            'post' => 4,
            'comment' => 5,
        };

        switch ($dtoRequest->interactionType) {
            case 'follow':
                $userNote = UserFollow::withTrashed()->where('user_id', $authUser->id)->type($markType)->where('follow_id', $primaryId)->first();
                break;

            case 'block':
                $userNote = UserBlock::withTrashed()->where('user_id', $authUser->id)->type($markType)->where('block_id', $primaryId)->first();
                break;
        }

        if (empty($dtoRequest->note)) {
            $userNote->update([
                'user_note' => null,
            ]);

            return $this->success();
        }

        $userNote->update([
            'user_note' => $dtoRequest->note,
        ]);

        return $this->success();
    }
}
