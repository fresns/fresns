<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\AccountEmailDTO;
use App\Fresns\Api\Http\DTO\AccountPhoneDTO;
use App\Fresns\Api\Http\DTO\CommonCallbacksDTO;
use App\Fresns\Api\Http\DTO\CommonFileLinkDTO;
use App\Fresns\Api\Http\DTO\CommonInputTipsDTO;
use App\Fresns\Api\Http\DTO\CommonIpInfoDTO;
use App\Fresns\Api\Http\DTO\CommonSendVerifyCodeDTO;
use App\Fresns\Api\Http\DTO\CommonUploadFileDTO;
use App\Fresns\Api\Http\DTO\CommonUploadLogDTO;
use App\Fresns\Api\Http\DTO\PaginationDTO;
use App\Fresns\Api\Services\UserService;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PluginHelper;
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
    // ip info
    public function ipInfo(Request $request)
    {
        $dtoRequest = new CommonIpInfoDTO($request->all());

        $ip = $dtoRequest->ip ?? $request->ip();

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->ipInfo([
            'ip' => $ip,
        ]);

        return $fresnsResp->getOrigin();
    }

    // inputTips
    public function inputTips(Request $request)
    {
        $dtoRequest = new CommonInputTipsDTO($request->all());
        $langTag = $this->langTag();

        switch ($dtoRequest->type) {
            case 'user':
                $userIdentifier = ConfigHelper::fresnsConfigByItemKey('user_identifier');

                if ($userIdentifier == 'uid') {
                    $userQuery = User::where('uid', 'like', "%$dtoRequest->key%");
                } else {
                    $userQuery = User::where('username', 'like', "%$dtoRequest->key%");
                }

                $users = $userQuery->orWhere('nickname', 'like', "%$dtoRequest->key%")->isEnabled()->limit(10)->get();

                $data = [];
                if ($users) {
                    foreach ($users as $user) {
                        $item['fsid'] = ($userIdentifier == 'uid') ? $user->uid : $user->username;
                        $item['name'] = $user->nickname;
                        $item['image'] = $user->getUserAvatar();
                        $item['followStatus'] = false;

                        $data[] = $item;
                    }
                }
                break;

            case 'group':
                $tipQuery = Language::where('table_name', 'groups')
                    ->where('table_column', 'name')
                    ->where('lang_content', 'like', "%$dtoRequest->key%")
                    ->value('table_id')
                    ?->limit(10)
                    ->get()
                    ->toArray();

                $data = [];
                if ($tipQuery) {
                    $groupIds = array_unique($tipQuery);

                    $groupQuery = Language::whereIn('id', $groupIds)->isEnabled()->get();

                    foreach ($groupQuery as $group) {
                        $item['fsid'] = $group->gid;
                        $item['name'] = LanguageHelper::fresnsLanguageByTableId('groups', 'name', $group->id, $langTag);
                        $item['image'] = FileHelper::fresnsFileUrlByTableColumn($group->cover_file_id, $group->cover_file_url);
                        $item['followStatus'] = false;

                        $data[] = $item;
                    }
                }
                break;

            case 'hashtag':
                $hashtagQuery = Hashtag::where('name', 'like', "%$dtoRequest->key%")->isEnabled()->limit(10)->get();

                $data = [];
                if ($hashtagQuery) {
                    foreach ($hashtagQuery as $hashtag) {
                        $item['fsid'] = $hashtag->slug;
                        $item['name'] = $hashtag->name;
                        $item['image'] = FileHelper::fresnsFileUrlByTableColumn($hashtag->cover_file_id, $hashtag->cover_file_url);
                        $item['followStatus'] = false;

                        $data[] = $item;
                    }
                }
                break;

            case 'post':
                $postQuery = Post::where('title', 'like', "%$dtoRequest->key%")->isEnabled()->limit(10)->get();

                $data = [];
                if ($postQuery) {
                    foreach ($postQuery as $post) {
                        $item['fsid'] = $post->pid;
                        $item['name'] = $post->title;
                        $item['image'] = null;
                        $item['followStatus'] = false;

                        $data[] = $item;
                    }
                }
                break;

            case 'comment':
                $commentQuery = Comment::where('content', 'like', "%$dtoRequest->key%")->isEnabled()->limit(10)->get();

                $data = [];
                if ($commentQuery) {
                    foreach ($commentQuery as $comment) {
                        $item['fsid'] = $comment->cid;
                        $item['name'] = \Str::limit(strip_tags($comment->content), 60);
                        $item['image'] = null;
                        $item['followStatus'] = false;

                        $data[] = $item;
                    }
                }
                break;

            case 'extend':
                $tipQuery = Language::where('table_name', 'extends')
                    ->where('table_column', 'title')
                    ->where('lang_content', 'like', "%$dtoRequest->key%")
                    ->value('table_id')
                    ?->limit(10)
                    ->get()
                    ->toArray();

                $data = [];
                if ($tipQuery) {
                    $extendIds = array_unique($tipQuery);

                    $extendQuery = Extend::whereIn('id', $extendIds)->isEnabled()->get();

                    foreach ($extendQuery as $extend) {
                        $item['fsid'] = $extend->eid;
                        $item['name'] = LanguageHelper::fresnsLanguageByTableId('extends', 'title', $extend->id, $langTag);
                        $item['image'] = FileHelper::fresnsFileUrlByTableColumn($extend->cover_file_id, $extend->cover_file_url);
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

        $callback = PluginHelper::fresnsPluginCallback($dtoRequest->fskey, $dtoRequest->ulid);

        if ($callback['code']) {
            throw new ApiException($callback['code']);
        }

        return $this->success($callback['data']);
    }

    // send verify code
    public function sendVerifyCode(Request $request)
    {
        $dtoRequest = new CommonSendVerifyCodeDTO($request->all());

        $authAccount = $this->account();
        $langTag = $this->langTag();

        if ($dtoRequest->useType == 3 || $dtoRequest->useType == 4) {
            if (empty($authAccount)) {
                throw new ApiException(31501);
            }
        }

        $sendConfigs = ConfigHelper::fresnsConfigByItemKeys([
            'send_email_service',
            'send_sms_service',
            'site_login_or_register',
        ]);

        switch ($dtoRequest->type) {
            case 'email':
                if (empty($sendConfigs['send_email_service'])) {
                    throw new ApiException(32100);
                }

                $checkDisposableEmail = ValidationUtility::disposableEmail($dtoRequest->account);
                if (! $checkDisposableEmail) {
                    throw new ApiException(34110);
                }

                $accountName = $dtoRequest->account;
                $account = Account::where('email', $accountName)->first();

                $authAccountConfig = $authAccount?->email;
                break;
            case 'sms':
                if ($dtoRequest->useType != 4 && empty($dtoRequest->countryCode)) {
                    throw new ApiException(30001);
                }

                if (empty($sendConfigs['send_sms_service'])) {
                    throw new ApiException(32100);
                }

                $accountName = $dtoRequest->countryCode.$dtoRequest->account;
                $account = Account::where('phone', $accountName)->first();

                $authAccountConfig = $authAccount?->phone;
                break;
        }

        $checkSend = ValidationUtility::sendCode($accountName);

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

        if (($dtoRequest->useType == 1 || $dtoRequest->useType == 3) && $account) {
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

        if ($dtoRequest->useType == 3 && $authAccountConfig) {
            switch ($dtoRequest->type) {
                case 'email':
                    throw new ApiException(34401);
                    break;
                case 'sms':
                    throw new ApiException(34402);
                    break;
            }
        }

        if ($dtoRequest->useType == 4) {
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

        $deviceInfo = $this->deviceInfo();

        $wordBody = [
            'type' => $dtoRequest->type,
            'fskey' => $dtoRequest->fskey,
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
            'deviceInfo' => $deviceInfo,
            'deviceToken' => $dtoRequest->deviceToken,
            'moreInfo' => $dtoRequest->moreInfo,
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

        $servicePlugin = Plugin::where('fskey', $storageConfig['service'])->isEnabled()->first();

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

        if (in_array($dtoRequest->tableName, [
            'post_logs',
            'comment_logs',
        ]) && empty($dtoRequest->tableId)) {
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

            default:
                $checkQuery = 'customize';
                $checkUser = true;
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

            $conversationFiles = ConfigHelper::fresnsConfigByItemKey('conversation_files');
            if (! in_array($dtoRequest->type, $conversationFiles)) {
                switch ($dtoRequest->type) {
                    case 'image':
                        throw new ApiException(36109);
                        break;

                    case 'video':
                        throw new ApiException(36110);
                        break;

                    case 'audio':
                        throw new ApiException(36111);
                        break;

                    case 'document':
                        throw new ApiException(36112);
                        break;

                    default:
                        throw new ApiException(36200);
                }
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
            default => $dtoRequest->usageType,
        };

        // check publish file count
        $publishType = match ($usageType) {
            FileUsage::TYPE_POST => 'post',
            FileUsage::TYPE_COMMENT => 'comment',
            default => null,
        };

        if ($publishType) {
            $authUserId = $this->user()->id;

            $editorConfig = ConfigUtility::getEditorConfigByType($authUserId, $publishType, $langTag);

            switch ($dtoRequest->type) {
                case 'image':
                    $uploadStatus = $editorConfig['toolbar']['image']['status'];

                    if (! $uploadStatus) {
                        throw new ApiException(36109);
                    }
                    break;

                case 'video':
                    $uploadStatus = $editorConfig['toolbar']['video']['status'];

                    if (! $uploadStatus) {
                        throw new ApiException(36110);
                    }
                    break;

                case 'audio':
                    $uploadStatus = $editorConfig['toolbar']['audio']['status'];

                    if (! $uploadStatus) {
                        throw new ApiException(36111);
                    }
                    break;

                case 'document':
                    $uploadStatus = $editorConfig['toolbar']['document']['status'];

                    if (! $uploadStatus) {
                        throw new ApiException(36112);
                    }
                    break;
            }

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

        // upload
        switch ($dtoRequest->uploadMode) {
            case 'file':
                $extension = $dtoRequest->file->extension();

                $extensionNames = ConfigHelper::fresnsConfigByItemKey("{$dtoRequest->type}_extension_names");
                $extensionArr = explode(',', $extensionNames);

                if (! in_array($extension, $extensionArr)) {
                    throw new ApiException(36310, 'Fresns', ['currentFileExtension' => $extension]);
                }

                $maxMb = ConfigHelper::fresnsConfigByItemKey("{$dtoRequest->type}_max_size") + 1;
                $maxBytes = $maxMb * 1024 * 1024;
                $fileSize = $dtoRequest->file->getSize();
                if ($fileSize > $maxBytes) {
                    throw new ApiException(36113);
                }

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
                    'moreInfo' => $dtoRequest->moreInfo,
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

        // user avatar or banner
        if ($fresnsResp->isSuccessResponse() && $dtoRequest->tableName == 'users') {
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            if ($dtoRequest->tableColumn == 'avatar_file_id') {
                $authUser->update([
                    'avatar_file_id' => $fileId,
                ]);
            }

            if ($dtoRequest->tableColumn == 'banner_file_id') {
                $authUser->update([
                    'banner_file_id' => $fileId,
                ]);
            }

            CacheHelper::forgetFresnsUser($authUser->id, $authUser->uid);
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
            throw new ApiException(37600);
        }

        if (! $file->is_enabled) {
            throw new ApiException(37601);
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
        if ($dtoRequest->type == 'post' && $model?->postAppend?->is_read_locked) {
            $checkPostAuth = PermissionUtility::checkPostAuth($model->id, $authUserId);

            if (! $checkPostAuth) {
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
            throw new ApiException(37600);
        }

        if (! $file->is_enabled) {
            throw new ApiException(37601);
        }

        $dbType = config('database.default');

        switch ($dbType) {
            case 'mysql':
                $downUsers = FileDownload::with('user')
                    ->select([
                        DB::raw('any_value(id) as id'),
                        DB::raw('any_value(file_id) as file_id'),
                        DB::raw('any_value(file_type) as file_type'),
                        DB::raw('any_value(account_id) as account_id'),
                        DB::raw('any_value(user_id) as user_id'),
                        DB::raw('any_value(plugin_fskey) as plugin_fskey'),
                        DB::raw('any_value(object_type) as object_type'),
                        DB::raw('any_value(object_id) as object_id'),
                        DB::raw('any_value(created_at) as created_at'),
                    ])
                    ->where('file_id', $file->id)
                    ->groupBy('user_id')
                    ->latest()
                    ->paginate($dtoRequest->pageSize ?? 15);
                break;

            case 'pgsql':
                $downUsers = FileDownload::with('user')
                    ->select([
                        DB::raw('DISTINCT ON (user_id) id'),
                        'file_id',
                        'file_type',
                        'account_id',
                        'user_id',
                        'plugin_fskey',
                        'object_type',
                        'object_id',
                        'created_at',
                    ])
                    ->where('file_id', $file->id)
                    ->orderBy('user_id')
                    ->orderByDesc('created_at')
                    ->paginate($dtoRequest->pageSize ?? 15);
                break;

            case 'sqlsrv':
                $downUsers = FileDownload::with('user')
                    ->select([
                        DB::raw('DISTINCT user_id'),
                        'id',
                        'file_id',
                        'file_type',
                        'account_id',
                        'plugin_fskey',
                        'object_type',
                        'object_id',
                        'created_at',
                    ])
                    ->where('file_id', $file->id)
                    ->orderBy('user_id')
                    ->orderByDesc('created_at')
                    ->paginate($dtoRequest->pageSize ?? 15);
                break;

            case 'sqlite':
                $downUsers = FileDownload::with('user')
                    ->select([
                        'id',
                        'file_id',
                        'file_type',
                        'account_id',
                        'user_id',
                        'plugin_fskey',
                        'object_type',
                        'object_id',
                        'created_at',
                    ])
                    ->whereIn('id', function ($query) use ($file) {
                        $query->select(DB::raw('max(id)'))
                            ->from('file_downloads')
                            ->where('file_id', $file->id)
                            ->groupBy('user_id');
                    })
                    ->orderByDesc('created_at')
                    ->paginate($dtoRequest->pageSize ?? 15);
                break;

            default:
                $downUsers = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        }

        $userService = new UserService;

        $items = [];
        foreach ($downUsers as $down) {
            if (empty($down->user)) {
                continue;
            }

            $item['datetime'] = DateHelper::fresnsFormatDateTime($down->created_at, $timezone, $langTag);
            $item['timeAgo'] = DateHelper::fresnsHumanReadableTime($down->created_at, $langTag);
            $item['user'] = $userService->userData($down->user, 'list', $langTag, $timezone, $authUser?->id);
            $items[] = $item;
        }

        return $this->fresnsPaginate($items, $downUsers->total(), $downUsers->perPage());
    }
}
