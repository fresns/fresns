<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\AccountEmailDTO;
use App\Fresns\Api\Http\DTO\AccountPhoneDTO;
use App\Fresns\Api\Http\DTO\CommonCallbacksDTO;
use App\Fresns\Api\Http\DTO\CommonFileLinkDTO;
use App\Fresns\Api\Http\DTO\CommonInputTipsDTO;
use App\Fresns\Api\Http\DTO\CommonSendVerifyCodeDTO;
use App\Fresns\Api\Http\DTO\CommonUploadFileDTO;
use App\Fresns\Api\Http\DTO\CommonUploadLogDTO;
use App\Fresns\Api\Http\DTO\PaginationDTO;
use App\Fresns\Api\Services\UserService;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\Account;
use App\Models\Comment;
use App\Models\CommentLog;
use App\Models\ConversationMessage;
use App\Models\Extend;
use App\Models\File;
use App\Models\FileDownload;
use App\Models\FileUsage;
use App\Models\Hashtag;
use App\Models\Language;
use App\Models\Plugin;
use App\Models\PluginCallback;
use App\Models\Post;
use App\Models\PostLog;
use App\Models\User;
use App\Utilities\ConfigUtility;
use App\Utilities\PermissionUtility;
use App\Utilities\ValidationUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommonController extends Controller
{
    // inputTips
    public function inputTips(Request $request)
    {
        $dtoRequest = new CommonInputTipsDTO($request->all());
        $langTag = $this->langTag();

        switch ($dtoRequest->type) {
            // user
            case 'user':
                $userQuery = User::where('username', 'like', "%$dtoRequest->key%")
                    ->orWhere('nickname', 'like', "%$dtoRequest->key%")
                    ->isEnable()
                    ->limit(10)
                    ->get();

                $data = null;
                if (! empty($userQuery)) {
                    if (ConfigHelper::fresnsConfigFileValueTypeByItemKey('default_avatar') == 'URL') {
                        $defaultAvatar = ConfigHelper::fresnsConfigByItemKey('default_avatar');
                    } else {
                        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getFileInfo([
                            'fileId' => ConfigHelper::fresnsConfigByItemKey('default_avatar'),
                        ]);
                        $defaultAvatar = $fresnsResp->getData('imageAvatarUrl');
                    }

                    foreach ($userQuery as $user) {
                        $avatar = FileHelper::fresnsFileUrlByTableColumn($user->avatar_file_id, $user->avatar_file_url, 'image_thumb_avatar');

                        $item['fsid'] = $user->uid;
                        $item['name'] = $user->username;
                        $item['image'] = $avatar = $avatar ?: $defaultAvatar;
                        $item['nickname'] = $user->nickname;
                        $item['followStatus'] = false;
                        $data[] = $item;
                    }
                }
            break;

            // group
            case 'group':
                $tipQuery = Language::where('table_name', 'groups')
                    ->where('table_column', 'name')
                    ->where('lang_content', 'like', "%$dtoRequest->key%")
                    ->value('table_id')
                    ?->limit(15)
                    ->get()
                    ->toArray();

                $data = null;
                if (! empty($tipQuery)) {
                    $groupIds = array_unique($tipQuery);

                    $groupQuery = Language::whereIn('id', $groupIds)->isEnable()->get();

                    foreach ($groupQuery as $group) {
                        $item['fsid'] = $group->gid;
                        $item['name'] = LanguageHelper::fresnsLanguageByTableId('groups', 'name', $group->id, $langTag);
                        $item['image'] = FileHelper::fresnsFileUrlByTableColumn($group->cover_file_id, $group->cover_file_url);
                        $item['nickname'] = null;
                        $item['followStatus'] = false;
                        $data[] = $item;
                    }
                }
            break;

            // hashtag
            case 'hashtag':
                $hashtagQuery = Hashtag::where('name', 'like', "%$dtoRequest->key%")->isEnable()->limit(10)->get();

                $data = null;
                if (! empty($hashtagQuery)) {
                    foreach ($hashtagQuery as $hashtag) {
                        $item['fsid'] = $hashtag->slug;
                        $item['name'] = $hashtag->name;
                        $item['image'] = FileHelper::fresnsFileUrlByTableColumn($hashtag->cover_file_id, $hashtag->cover_file_url);
                        $item['nickname'] = null;
                        $item['followStatus'] = false;
                        $data[] = $item;
                    }
                }
            break;

            // post
            case 'post':
                $postQuery = Post::where('title', 'like', "%$dtoRequest->key%")->isEnable()->limit(10)->get();

                $data = null;
                if (! empty($postQuery)) {
                    foreach ($postQuery as $post) {
                        $item['fsid'] = $post->pid;
                        $item['name'] = $post->title;
                        $item['image'] = null;
                        $item['nickname'] = null;
                        $item['followStatus'] = false;
                        $data[] = $item;
                    }
                }
            break;

            // comment
            case 'comment':
                $data = null;
            break;

            // extend
            case 'extend':
                $tipQuery = Language::where('table_name', 'extends')
                    ->where('table_column', 'title')
                    ->where('lang_content', 'like', "%$dtoRequest->key%")
                    ->value('table_id')
                    ?->limit(10)
                    ->get()
                    ->toArray();

                $data = null;
                if (! empty($tipQuery)) {
                    $extendIds = array_unique($tipQuery);

                    $extendQuery = Extend::whereIn('id', $extendIds)->isEnable()->get();

                    foreach ($extendQuery as $extend) {
                        $item['fsid'] = $extend->eid;
                        $item['name'] = LanguageHelper::fresnsLanguageByTableId('extends', 'title', $extend->id, $langTag);
                        $item['image'] = FileHelper::fresnsFileUrlByTableColumn($extend->cover_file_id, $extend->cover_file_url);
                        $item['nickname'] = null;
                        $item['followStatus'] = false;
                        $data[] = $item;
                    }
                }
            break;
        }

        return $this->success($data);
    }

    // callback
    public function callback(Request $request)
    {
        $dtoRequest = new CommonCallbacksDTO($request->all());

        $plugin = Plugin::whereUnikey($dtoRequest->unikey)->first();

        if (empty($plugin)) {
            throw new ApiException(32101);
        }

        if ($plugin->is_enable == 0) {
            throw new ApiException(32102);
        }

        $callback = PluginCallback::where('ulid', $dtoRequest->ulid)->first();

        if (empty($callback)) {
            throw new ApiException(32303);
        }

        if ($callback->is_use == 1) {
            throw new ApiException(32204);
        }

        $timeDifference = time() - strtotime($callback->created_at);
        // 30 minutes
        if ($timeDifference > 1800) {
            throw new ApiException(32203);
        }

        $data = $callback->content;

        $callback->is_use = 1;
        $callback->use_plugin_unikey = $dtoRequest->unikey;
        $callback->save();

        return $this->success($data);
    }

    // send verify code
    public function sendVerifyCode(Request $request)
    {
        $dtoRequest = new CommonSendVerifyCodeDTO($request->all());
        $authAccount = $this->account();
        $langTag = $this->langTag();

        $sendConfigs = ConfigHelper::fresnsConfigByItemKeys([
            'send_email_service',
            'send_sms_service',
            'site_login_or_register',
        ]);

        if ($dtoRequest->type == 'email' && empty($sendConfigs['send_email_service'])) {
            throw new ApiException(32100);
        } elseif ($dtoRequest->type == 'sms' && empty($sendConfigs['send_sms_service'])) {
            throw new ApiException(32100);
        }

        if ($dtoRequest->type == 'email') {
            $checkEmail = ValidationUtility::disposableEmail($dtoRequest->account);
            if (! $checkEmail) {
                throw new ApiException(34110);
            }

            $account = Account::where('email', $dtoRequest->account)->first();
            $accountConfig = $account?->email;

            $checkSend = ValidationUtility::sendCode($dtoRequest->account);
        } else {
            $phone = $dtoRequest->countryCode.$dtoRequest->account;
            $account = Account::where('phone', $phone)->first();
            $accountConfig = $account?->phone;

            $checkSend = ValidationUtility::sendCode($dtoRequest->countryCode.$dtoRequest->account);
        }

        $sendType = match ($dtoRequest->type) {
            'email' => 1,
            'sms' => 2,
        };
        $wordBody = [
            'type' => $sendType,
            'account' => $dtoRequest->account,
            'countryCode' => $dtoRequest->countryCode,
            'templateId' => $dtoRequest->templateId,
            'langTag' => $langTag,
        ];

        if ($dtoRequest->useType == 1 && ! empty($account)) {
            switch ($dtoRequest->type) {
                case 'email':
                    throw new ApiException(34205);
                break;
                case 'sms':
                    throw new ApiException(34206);
                break;
            }
        }

        if ($dtoRequest->useType == 2 && ! $sendConfigs['site_login_or_register'] && empty($account)) {
            throw new ApiException(34301);
        }

        if ($dtoRequest->useType == 3 && ! empty($accountConfig)) {
            switch ($dtoRequest->type) {
                case 'email':
                    throw new ApiException(34401);
                break;
                case 'sms':
                    throw new ApiException(34402);
                break;
            }
        }

        if ($dtoRequest->useType == 4 && empty($authAccount?->aid)) {
            throw new ApiException(31501);
        } elseif ($dtoRequest->useType == 4 && ! empty($authAccount?->aid)) {
            switch ($dtoRequest->type) {
                case 'email':
                    $wordBody['account'] = $authAccount->email;

                    $checkSend = ValidationUtility::sendCode($authAccount->email);
                break;
                case 'sms':
                    $wordBody['account'] = $authAccount->pure_phone;
                    $wordBody['countryCode'] = $authAccount->country_code;

                    $checkSend = ValidationUtility::sendCode($authAccount->phone);
                break;
            }
        }

        if (! $checkSend) {
            throw new ApiException(33201);
        }

        if ($dtoRequest->type == 'email') {
            new AccountEmailDTO($wordBody);
            $fresnsResp = \FresnsCmdWord::plugin($sendConfigs['send_email_service'])->sendCode($wordBody);
        } else {
            new AccountPhoneDTO($wordBody);
            $fresnsResp = \FresnsCmdWord::plugin($sendConfigs['send_sms_service'])->sendCode($wordBody);
        }

        return $fresnsResp->getOrigin();
    }

    // upload log
    public function uploadLog(Request $request)
    {
        $dtoRequest = new CommonUploadLogDTO($request->all());

        $wordBody = [
            'type' => $dtoRequest->type,
            'pluginUnikey' => $dtoRequest->pluginUnikey,
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'langTag' => $this->langTag(),
            'aid' => \request()->header('X-Fresns-Aid'),
            'uid' => \request()->header('X-Fresns-Uid'),
            'objectName' => $dtoRequest->objectName,
            'objectAction' => $dtoRequest->objectAction,
            'objectResult' => $dtoRequest->objectResult,
            'objectOrderId' => $dtoRequest->objectOrderId,
            'deviceInfo' => \request()->header('X-Fresns-Client-Device-Info'),
            'deviceToken' => $dtoRequest->deviceToken,
            'moreJson' => $dtoRequest->moreJson,
        ];

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($wordBody);

        return $fresnsResp->getOrigin();
    }

    // upload file
    public function uploadFile(Request $request)
    {
        $dtoRequest = new CommonUploadFileDTO($request->all());

        $langTag = $this->langTag();
        $authUser = $this->user();

        $fileType = match ($dtoRequest->type) {
            'image' => 1,
            'video' => 2,
            'audio' => 3,
            'document' => 4,
        };

        // check upload service
        $storageConfig = FileHelper::fresnsFileStorageConfigByType($fileType);

        if (! $storageConfig['storageConfigStatus']) {
            throw new ApiException(32100);
        }

        $servicePlugin = Plugin::where('unikey', $storageConfig['service'])->isEnable()->first();

        if (! $servicePlugin) {
            throw new ApiException(32102);
        }

        // check request data
        if (in_array($dtoRequest->tableName, [
            'users',
            'posts',
            'comments',
            'conversation_messages',
        ]) && empty($dtoRequest->tableKey)) {
            throw new ApiException(30001, 'Fresns', 'Missing tableKey');
        }

        if (in_array($dtoRequest->tableName, ['post_logs', 'comment_logs']) && empty($dtoRequest->tableId)) {
            throw new ApiException(30001, 'Fresns', 'Missing tableId');
        }

        switch ($dtoRequest->tableName) {
            case 'users':
                if (StrHelper::isPureInt($dtoRequest->tableKey)) {
                    $checkQuery = User::where('uid', $dtoRequest->tableKey)->first();
                } else {
                    $checkQuery = User::where('username', $dtoRequest->tableKey)->first();
                }

                $checkUser = ($checkQuery?->id == $authUser->id) ? true : false;
            break;

            case 'posts':
                $checkQuery = Post::where('pid', $dtoRequest->tableKey)->first();

                $checkUser = ($checkQuery?->user_id == $authUser->id) ? true : false;
            break;

            case 'comments':
                $checkQuery = Comment::where('cid', $dtoRequest->tableKey)->first();

                $checkUser = ($checkQuery?->user_id == $authUser->id) ? true : false;
            break;

            case 'conversation_messages':
                if (StrHelper::isPureInt($dtoRequest->tableKey)) {
                    $checkQuery = User::where('uid', $dtoRequest->tableKey)->first();
                } else {
                    $checkQuery = User::where('username', $dtoRequest->tableKey)->first();
                }

                $checkUser = true;
            break;

            case 'post_logs':
                $checkQuery = PostLog::where('id', $dtoRequest->tableId)->first();

                $checkUser = ($checkQuery?->user_id == $authUser->id) ? true : false;
            break;

            case 'comment_logs':
                $checkQuery = CommentLog::where('id', $dtoRequest->tableId)->first();

                $checkUser = ($checkQuery?->user_id == $authUser->id) ? true : false;
            break;
        }

        if (empty($checkQuery)) {
            throw new ApiException(32201);
        }

        if (! $checkUser) {
            throw new ApiException(36500);
        }

        if ($dtoRequest->tableName == 'conversation_messages') {
            $conversationPermInt = PermissionUtility::checkUserConversationPerm($checkQuery?->id, $authUser->id, $langTag);
            if ($conversationPermInt != 0) {
                throw new ApiException($conversationPermInt);
            }
        }

        // usage type
        $usageType = match ($dtoRequest->tableName) {
            'users' => FileUsage::TYPE_USER,
            'posts' => FileUsage::TYPE_POST,
            'comments' => FileUsage::TYPE_COMMENT,
            'conversation_messages' => FileUsage::TYPE_CONVERSATION,
            'post_logs' => FileUsage::TYPE_POST,
            'comment_logs' => FileUsage::TYPE_COMMENT,
            default => null,
        };

        // check publish file count
        $publishType = match ($usageType) {
            FileUsage::TYPE_POST => 'post',
            FileUsage::TYPE_COMMENT => 'comment',
            default => null,
        };

        if ($publishType) {
            $authUserId = $this->user()->id;

            $editorConfig = ConfigUtility::getEditorConfigByType($authUserId, $publishType);

            $uploadNumber = match ($dtoRequest->type) {
                'image' => $editorConfig['toolbar']['image']['uploadNumber'],
                'video' => $editorConfig['toolbar']['video']['uploadNumber'],
                'audio' => $editorConfig['toolbar']['audio']['uploadNumber'],
                'document' => $editorConfig['toolbar']['document']['uploadNumber'],
            };

            $fileCount = FileUsage::where('file_type', $fileType)
                ->where('usage_type', $usageType)
                ->where('table_name', $dtoRequest->tableName)
                ->where('table_column', $dtoRequest->tableColumn)
                ->where('table_id', $dtoRequest->tableId)
                ->count();

            if ($fileCount >= $uploadNumber) {
                throw new ApiException(36115);
            }
        }

        switch ($dtoRequest->uploadMode) {
            case 'file':
                $wordBody = [
                    'usageType' => $usageType,
                    'platformId' => \request()->header('X-Fresns-Client-Platform-Id'),
                    'tableName' => $dtoRequest->tableName,
                    'tableColumn' => $dtoRequest->tableColumn,
                    'tableId' => $dtoRequest->tableId,
                    'tableKey' => $dtoRequest->tableKey,
                    'aid' => \request()->header('X-Fresns-Aid'),
                    'uid' => \request()->header('X-Fresns-Uid'),
                    'type' => $fileType,
                    'moreJson' => $dtoRequest->moreJson,
                    'file' => $dtoRequest->file,
                ];

                $fresnsResp = \FresnsCmdWord::plugin($storageConfig['service'])->uploadFile($wordBody);
            break;

            case 'fileInfo':
                $wordBody = [
                    'usageType' => $usageType,
                    'platformId' => \request()->header('X-Fresns-Client-Platform-Id'),
                    'tableName' => $dtoRequest->tableName,
                    'tableColumn' => $dtoRequest->tableColumn,
                    'tableId' => $dtoRequest->tableId,
                    'tableKey' => $dtoRequest->tableKey,
                    'aid' => \request()->header('X-Fresns-Aid'),
                    'uid' => \request()->header('X-Fresns-Uid'),
                    'type' => $fileType,
                    'fileInfo' => $dtoRequest->fileInfo,
                ];

                $fresnsResp = \FresnsCmdWord::plugin($storageConfig['service'])->uploadFileInfo($wordBody);
            break;
        }

        return $fresnsResp->getOrigin();
    }

    // file download link
    public function fileLink(string $fid, Request $request)
    {
        $dtoRequest = new CommonFileLinkDTO($request->all());
        $authAccountId = $this->account()->id;
        $authUserId = $this->user()->id;

        $mainRolePerms = PermissionUtility::getUserMainRole($authUserId, $this->langTag())['permissions'];

        // check down count
        $roleDownloadCount = $mainRolePerms['download_file_count'] ?? 0;
        if ($roleDownloadCount == 0) {
            throw new ApiException(36102);
        }

        $userDownloadCount = FileDownload::where('user_id', $authUserId)->whereDate('created_at', now())->count();
        if ($roleDownloadCount < $userDownloadCount) {
            throw new ApiException(36117);
        }

        // check file
        $file = File::whereFid($fid)->first();
        if (empty($file)) {
            throw new ApiException(37500);
        }

        if ($file->is_enable == 0) {
            throw new ApiException(37501);
        }

        // get model
        if ($dtoRequest->type == 'conversation') {
            $model = ConversationMessage::where('id', $dtoRequest->fsid)->first();
            $fileUsage = FileUsage::where('file_id', $file->id)
                ->where('table_name', 'conversation_messages')
                ->where('table_column', 'message_file_id')
                ->where('table_id', $model?->id)
                ->first();
        } else {
            $model = PrimaryHelper::fresnsModelByFsid($dtoRequest->type, $dtoRequest->fsid);
            $fileUsage = FileUsage::where('file_id', $file->id)
                ->where('table_name', "{$dtoRequest->type}s")
                ->where('table_column', 'id')
                ->where('table_id', $model?->id)
                ->first();
        }

        // check model
        if (empty($model)) {
            throw new ApiException(32201);
        }

        if ($model->deleted_at) {
            throw new ApiException(32304);
        }

        // check permission
        if ($dtoRequest->type == 'post' && $model?->postAppend?->is_allow == 0) {
            $checkPostAllow = PermissionUtility::checkPostAllow($model->id, $authUserId);

            if (! $checkPostAllow) {
                throw new ApiException(35301);
            }
        }

        if ($dtoRequest->type == 'conversation') {
            if ($model->send_user_id != $authUserId && $model->receive_user_id != $authUserId) {
                throw new ApiException(36602);
            }
        }

        if (empty($fileUsage)) {
            throw new ApiException(32304);
        }

        $data['originalUrl'] = FileHelper::fresnsFileOriginalUrlById($file->id);

        $objectType = match ($dtoRequest->type) {
            'post' => FileDownload::TYPE_POST,
            'comment' => FileDownload::TYPE_COMMENT,
            'extend' => FileDownload::TYPE_EXTEND,
            'conversation' => FileDownload::TYPE_CONVERSATION,
        };
        $downloader = [
            'file_id' => $file->id,
            'file_type' => $file->type,
            'account_id' => $authAccountId,
            'user_id' => $authUserId,
            'object_type' => $objectType,
            'object_id' => $model->id,
        ];
        FileDownload::create($downloader);

        return $this->success($data);
    }

    // file download users
    public function fileUsers(string $fid, Request $request)
    {
        $dtoRequest = new PaginationDTO($request->all());
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $file = File::whereFid($fid)->first();
        if (empty($file)) {
            throw new ApiException(37500);
        }

        if ($file->is_enable == 0) {
            throw new ApiException(37501);
        }

        $downUsers = FileDownload::with('user')
            ->select([
                DB::raw('any_value(id) as id'),
                DB::raw('any_value(file_id) as file_id'),
                DB::raw('any_value(file_type) as file_type'),
                DB::raw('any_value(account_id) as account_id'),
                DB::raw('any_value(user_id) as user_id'),
                DB::raw('any_value(plugin_unikey) as plugin_unikey'),
                DB::raw('any_value(object_type) as object_type'),
                DB::raw('any_value(object_id) as object_id'),
                DB::raw('any_value(created_at) as created_at'),
            ])
            ->where('file_id', $file->id)
            ->latest()
            ->groupBy('user_id')
            ->paginate($request->get('pageSize', 15));

        $userService = new UserService;

        $items = [];
        foreach ($downUsers as $down) {
            if (empty($down->user)) {
                continue;
            }

            $item['downloadTime'] = DateHelper::fresnsFormatDateTime($down->created_at, $timezone, $langTag);
            $item['downloadTimeFormat'] = DateHelper::fresnsFormatTime($down->created_at, $langTag);
            $item['downloadUser'] = $userService->userData($down->user, $langTag, $timezone, $authUser?->id);
            $items[] = $item;
        }

        return $this->fresnsPaginate($items, $downUsers->total(), $downUsers->perPage());
    }
}
