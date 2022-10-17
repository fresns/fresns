<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\InteractiveDTO;
use App\Fresns\Api\Http\DTO\UserAuthDTO;
use App\Fresns\Api\Http\DTO\UserEditDTO;
use App\Fresns\Api\Http\DTO\UserListDTO;
use App\Fresns\Api\Http\DTO\UserMarkDTO;
use App\Fresns\Api\Http\DTO\UserMarkListDTO;
use App\Fresns\Api\Http\DTO\UserMarkNoteDTO;
use App\Fresns\Api\Services\InteractiveService;
use App\Fresns\Api\Services\UserService;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Models\ArchiveUsage;
use App\Models\BlockWord;
use App\Models\CommentLog;
use App\Models\Dialog;
use App\Models\DialogMessage;
use App\Models\DomainLinkUsage;
use App\Models\File;
use App\Models\HashtagUsage;
use App\Models\Mention;
use App\Models\Notify;
use App\Models\PluginUsage;
use App\Models\PostLog;
use App\Models\Seo;
use App\Models\SessionLog;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserFollow;
use App\Models\UserStat;
use App\Utilities\ConfigUtility;
use App\Utilities\ContentUtility;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractiveUtility;
use App\Utilities\ValidationUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        $userQuery = UserStat::with('user')->whereRelation('user', 'is_enable', 1)->whereRelation('user', 'wait_delete', 0);

        $userQuery->when($dtoRequest->gender, function ($query, $value) {
            $query->whereRelation('user', 'gender', $value);
        });

        $userQuery->when($dtoRequest->createDateGt, function ($query, $value) {
            $query->whereDate('created_at', '>=', $value);
        });

        $userQuery->when($dtoRequest->createDateLt, function ($query, $value) {
            $query->whereDate('created_at', '<=', $value);
        });

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

        $userData = $userQuery->paginate($request->get('pageSize', 15));

        $userList = [];
        $service = new UserService();
        foreach ($userData as $user) {
            $userList[] = $service->userData($user->user, $langTag, $timezone, $authUserId);
        }

        return $this->fresnsPaginate($userList, $userData->total(), $userData->perPage());
    }

    // detail
    public function detail(string $uidOrUsername)
    {
        if (preg_match('/^\d*?$/', $uidOrUsername)) {
            $viewUser = User::where('uid', $uidOrUsername)->first();
        } else {
            $viewUser = User::where('username', $uidOrUsername)->first();
        }

        if (empty($viewUser)) {
            throw new ApiException(31602);
        }

        if ($viewUser->is_enable == 0) {
            throw new ApiException(35202);
        }

        if ($viewUser->wait_delete == 1) {
            throw new ApiException(35203);
        }

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $seoData = Seo::where('usage_type', Seo::TYPE_USER)->where('usage_id', $viewUser->id)->where('lang_tag', $langTag)->first();

        $item['title'] = $seoData->title ?? null;
        $item['keywords'] = $seoData->keywords ?? null;
        $item['description'] = $seoData->description ?? null;
        $item['manages'] = ExtendUtility::getPluginUsages(PluginUsage::TYPE_MANAGE, null, PluginUsage::SCENE_USER, $authUserId, $langTag);
        $data['items'] = $item;

        $service = new UserService();
        $data['detail'] = $service->userData($viewUser, $langTag, $timezone, $authUserId);

        return $this->success($data);
    }

    // interactive
    public function interactive(string $uidOrUsername, string $type, Request $request)
    {
        if (preg_match('/^\d*?$/', $uidOrUsername)) {
            $viewUser = User::where('uid', $uidOrUsername)->first();
        } else {
            $viewUser = User::where('username', $uidOrUsername)->first();
        }

        if (empty($viewUser)) {
            throw new ApiException(31602);
        }

        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new InteractiveDTO($requestData);

        $orderDirection = $dtoRequest->orderDirection ?: 'desc';

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        if ($viewUser->id == $authUserId) {
            InteractiveService::checkMyInteractiveSetting($dtoRequest->type, 'user');
        } else {
            InteractiveService::checkInteractiveSetting($dtoRequest->type, 'user');
        }

        $service = new InteractiveService();
        $data = $service->getUsersWhoMarkIt($dtoRequest->type, InteractiveService::TYPE_USER, $viewUser->id, $orderDirection, $langTag, $timezone, $authUserId);

        return $this->fresnsPaginate($data['paginateData'], $data['interactiveData']->total(), $data['interactiveData']->perPage());
    }

    // markList
    public function markList(string $uidOrUsername, string $markType, string $listType, Request $request)
    {
        if (preg_match('/^\d*?$/', $uidOrUsername)) {
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

        $markSet = ConfigHelper::fresnsConfigByItemKey("it_{$dtoRequest->markType}_{$dtoRequest->listType}");
        if (! $markSet) {
            throw new ApiException(36201);
        }

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $orderDirection = $dtoRequest->orderDirection ?: 'desc';

        $service = new InteractiveService();
        $data = $service->getItMarkList($dtoRequest->markType, $dtoRequest->listType, $viewUser->id, $orderDirection, $langTag, $timezone, $authUserId);

        return $this->fresnsPaginate($data['paginateData'], $data['markData']->total(), $data['markData']->perPage());
    }

    // auth
    public function auth(Request $request)
    {
        $dtoRequest = new UserAuthDTO($request->all());

        if (preg_match('/^\d*?$/', $dtoRequest->uidOrUsername)) {
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
            'pluginUnikey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
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
            'aid' => $request->header('aid'),
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
            'platformId' => $request->header('platformId'),
            'aid' => $fresnsResponse->getData('aid'),
            'uid' => $fresnsResponse->getData('uid'),
            'expiredTime' => null,
        ];
        $fresnsTokenResponse = \FresnsCmdWord::plugin('Fresns')->createSessionToken($createTokenWordBody);

        if ($fresnsTokenResponse->isErrorResponse()) {
            // upload session log
            $sessionLog['objectAction'] = 'createSessionToken';
            $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

            return $fresnsTokenResponse->errorResponse();
        }

        // get user token
        $token['token'] = $fresnsTokenResponse->getData('token');
        $token['expiredTime'] = $fresnsTokenResponse->getData('expiredTime');
        $data['sessionToken'] = $token;

        // get user data
        $service = new UserService();
        $data['detail'] = $service->userData($authUser, $langTag, $timezone);

        // upload session log
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        return $this->success($data);
    }

    // panel
    public function panel()
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()->id;

        $data['features'] = ExtendUtility::getPluginUsages(PluginUsage::TYPE_FEATURE, null, null, $authUserId, $langTag);
        $data['profiles'] = ExtendUtility::getPluginUsages(PluginUsage::TYPE_PROFILE, null, null, $authUserId, $langTag);

        $dialogACount = Dialog::where('a_user_id', $authUserId)->where('a_is_read', 0)->where('a_is_display', 1)->count();
        $dialogBCount = Dialog::where('b_user_id', $authUserId)->where('b_is_read', 0)->where('b_is_display', 1)->count();
        $dialogMessageCount = DialogMessage::where('receive_user_id', $authUserId)->whereNull('receive_read_at')->whereNull('receive_deleted_at')->isEnable()->count();
        $dialogUnread['dialog'] = $dialogACount + $dialogBCount;
        $dialogUnread['message'] = $dialogMessageCount;
        $data['dialogUnread'] = $dialogUnread;

        $notify = Notify::where('user_id', $authUserId)->where('is_read', 0);
        $notifyUnread['bulletin'] = Notify::where('type', 1)->where('is_read', 0)->count();
        $notifyUnread['system'] = $notify->where('type', 2)->count();
        $notifyUnread['recommend'] = $notify->where('type', 3)->count();
        $notifyUnread['follow'] = $notify->where('type', 4)->count();
        $notifyUnread['like'] = $notify->where('type', 5)->count();
        $notifyUnread['mention'] = $notify->where('type', 6)->count();
        $notifyUnread['comment'] = $notify->where('type', 7)->count();
        $data['notifyUnread'] = $notifyUnread;

        $draftCount['posts'] = PostLog::where('user_id', $authUserId)->whereIn('state', [1, 4])->count();
        $draftCount['comments'] = CommentLog::where('user_id', $authUserId)->whereIn('state', [1, 4])->count();
        $data['draftCount'] = $draftCount;

        $cacheKey = "fresns_api_publish_{$authUserId}_{$langTag}_{$timezone}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $publish = Cache::remember($cacheKey, $cacheTime, function () use ($authUserId, $langTag, $timezone) {
            $publish['post'] = ConfigUtility::getPublishConfigByType($authUserId, 'post', $langTag, $timezone);
            $publish['comment'] = ConfigUtility::getPublishConfigByType($authUserId, 'comment', $langTag, $timezone);

            return $publish;
        });
        $data['publish'] = $publish;

        $fileAccept['images'] = FileHelper::fresnsFileAcceptByType(File::TYPE_IMAGE);
        $fileAccept['videos'] = FileHelper::fresnsFileAcceptByType(File::TYPE_VIDEO);
        $fileAccept['audios'] = FileHelper::fresnsFileAcceptByType(File::TYPE_AUDIO);
        $fileAccept['documents'] = FileHelper::fresnsFileAcceptByType(File::TYPE_DOCUMENT);
        $data['fileAccept'] = $fileAccept;

        return $this->success($data);
    }

    // edit
    public function edit(Request $request)
    {
        $dtoRequest = new UserEditDTO($request->all());

        $authUser = $this->user();

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

            $nextEditUsernameTime = $authUser->last_username_at?->addDays($editNameConfig['username_edit']);

            if (now() < $nextEditUsernameTime) {
                throw new ApiException(35101);
            }

            $username = Str::of($dtoRequest->username)->trim();
            $validateUsername = ValidationUtility::username($username);

            if (! $validateUsername['formatString'] || ! $validateUsername['formatHyphen'] || ! $validateUsername['formatNumeric']) {
                throw new ApiException(35102);
            }

            if (! $validateUsername['minLength']) {
                throw new ApiException(35103);
            }

            if (! $validateUsername['maxLength']) {
                throw new ApiException(35104);
            }

            if (! $validateUsername['use']) {
                throw new ApiException(35105);
            }

            if (! $validateUsername['banName']) {
                throw new ApiException(35106);
            }

            $authUser->update([
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

            $nextEditNicknameTime = $authUser->last_nickname_at?->addDays($editNameConfig['nickname_edit']);

            if (now() < $nextEditNicknameTime) {
                throw new ApiException(35101);
            }

            $nickname = Str::of($dtoRequest->nickname)->trim();

            $validateNickname = ValidationUtility::nickname($nickname);

            if (! $validateNickname['formatString'] || ! $validateNickname['formatSpace']) {
                throw new ApiException(35107);
            }

            if (! $validateNickname['minLength']) {
                throw new ApiException(35108);
            }

            if (! $validateNickname['maxLength']) {
                throw new ApiException(35109);
            }

            if (! $validateNickname['banName']) {
                throw new ApiException(35110);
            }

            $blockWords = BlockWord::where('user_mode', 2)->get('word', 'replace_word');

            $newNickname = str_ireplace($blockWords->pluck('word')->toArray(), $blockWords->pluck('replace_word')->toArray(), $nickname);

            $authUser->update([
                'nickname' => $newNickname,
                'last_nickname_at' => now(),
            ]);
        }

        // edit avatarFid
        if ($dtoRequest->avatarFid) {
            $fileId = PrimaryHelper::fresnsFileIdByFid($dtoRequest->avatarFid);

            $authUser->update([
                'avatar_file_id' => $fileId,
                'avatar_file_url' => null,
            ]);
        }

        // edit avatarUrl
        if ($dtoRequest->avatarUrl) {
            $authUser->update([
                'avatar_file_id' => null,
                'avatar_file_url' => $dtoRequest->avatarUrl,
            ]);
        }

        // edit bannerFid
        if ($dtoRequest->bannerFid) {
            $fileId = PrimaryHelper::fresnsFileIdByFid($dtoRequest->bannerFid);

            $authUser->update([
                'banner_file_id' => $fileId,
                'banner_file_url' => null,
            ]);
        }

        // edit bannerUrl
        if ($dtoRequest->bannerUrl) {
            $authUser->update([
                'banner_file_id' => null,
                'banner_file_url' => $dtoRequest->bannerUrl,
            ]);
        }

        // edit gender
        if ($dtoRequest->gender) {
            $authUser->update([
                'gender' => $dtoRequest->gender,
            ]);
        }

        // edit birthday
        if ($dtoRequest->birthday) {
            $authUser->update([
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

            $authUser->update([
                'bio' => $bio,
            ]);
        }

        // edit location
        if ($dtoRequest->location) {
            $location = Str::of($dtoRequest->location)->trim();
            $authUser->update([
                'location' => $location,
            ]);
        }

        // edit dialogLimit
        if ($dtoRequest->dialogLimit) {
            $authUser->update([
                'dialog_limit' => $dtoRequest->dialogLimit,
            ]);
        }

        // edit commentLimit
        if ($dtoRequest->commentLimit) {
            $authUser->update([
                'comment_limit' => $dtoRequest->commentLimit,
            ]);
        }

        // edit timezone
        if ($dtoRequest->timezone) {
            $authUser->update([
                'timezone' => $dtoRequest->timezone,
            ]);
        }

        // edit archives
        if ($dtoRequest->archives) {
            ContentUtility::saveArchiveUsages(ArchiveUsage::TYPE_USER, $authUser->id, $dtoRequest->archives);
        }

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_USER_EDIT_DATA,
            'pluginUnikey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'langTag' => $this->langTag(),
            'aid' => $this->account()->aid,
            'uid' => $authUser->uid,
            'objectName' => \request()->path(),
            'objectAction' => 'User Edit Data',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => null,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'moreJson' => null,
        ];
        // upload session log
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        CacheHelper::forgetApiUser($authUser->uid);

        return $this->success();
    }

    // mark
    public function mark(Request $request)
    {
        $dtoRequest = new UserMarkDTO($request->all());

        $markSet = ConfigHelper::fresnsConfigByItemKey("{$dtoRequest->interactiveType}_{$dtoRequest->markType}_setting");
        if (! $markSet) {
            throw new ApiException(36200);
        }

        $primaryId = PrimaryHelper::fresnsPrimaryId($dtoRequest->markType, $dtoRequest->fsid);
        if (empty($primaryId)) {
            throw new ApiException(32201);
        }

        $authUserId = $this->user()->id;

        $markType = match ($dtoRequest->markType) {
            'user' => 1,
            'group' => 2,
            'hashtag' => 3,
            'post' => 4,
            'comment' => 5,
        };

        switch ($dtoRequest->interactiveType) {
            // like
            case 'like':
                InteractiveUtility::markUserLike($authUserId, $markType, $primaryId);
            break;

            // dislike
            case 'dislike':
                InteractiveUtility::markUserDislike($authUserId, $markType, $primaryId);
            break;

            // follow
            case 'follow':
                $validMark = ValidationUtility::userMarkOwn($authUserId, $markType, $primaryId);
                if (! $validMark) {
                    throw new ApiException(36202);
                }

                InteractiveUtility::markUserFollow($authUserId, $markType, $primaryId);
            break;

            // block
            case 'block':
                $validMark = ValidationUtility::userMarkOwn($authUserId, $markType, $primaryId);
                if (! $validMark) {
                    throw new ApiException(36202);
                }

                InteractiveUtility::markUserBlock($authUserId, $markType, $primaryId);
            break;
        }

        if ($dtoRequest->markType == 'group') {
            $cacheKey = "fresns_api_user_{$authUserId}_groups";

            Cache::forget($cacheKey);
        }

        return $this->success();
    }

    // mark note
    public function markNote(Request $request)
    {
        $dtoRequest = new UserMarkNoteDTO($request->all());

        $primaryId = PrimaryHelper::fresnsPrimaryId($dtoRequest->markType, $dtoRequest->fsid);
        if (empty($primaryId)) {
            throw new ApiException(32201);
        }

        $authUserId = $this->user()->id;

        $markType = match ($dtoRequest->markType) {
            'user' => 1,
            'group' => 2,
            'hashtag' => 3,
            'post' => 4,
            'comment' => 5,
        };

        switch ($dtoRequest->interactiveType) {
            // follow
            case 'follow':
                $userNote = UserFollow::withTrashed()->where('user_id', $authUserId)->type($markType)->where('follow_id', $primaryId)->first();
            break;

            // block
            case 'block':
                $userNote = UserBlock::withTrashed()->where('user_id', $authUserId)->type($markType)->where('block_id', $primaryId)->first();
            break;
        }

        if (empty($dtoRequest->note)) {
            $userNote->update([
                'user_note' => null,
            ]);
        }

        $userNote->update([
            'user_note' => $dtoRequest->note,
        ]);

        return $this->success();
    }
}
