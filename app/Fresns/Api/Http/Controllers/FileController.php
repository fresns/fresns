<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\FileLinkDTO;
use App\Fresns\Api\Http\DTO\FileStorageTokenDTO;
use App\Fresns\Api\Http\DTO\FileUploadsDTO;
use App\Fresns\Api\Http\DTO\FileUsersDTO;
use App\Fresns\Api\Http\DTO\FileWarningDTO;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\App;
use App\Models\Comment;
use App\Models\CommentLog;
use App\Models\ConversationMessage;
use App\Models\File;
use App\Models\FileDownload;
use App\Models\FileUsage;
use App\Models\Post;
use App\Models\PostLog;
use App\Models\User;
use App\Utilities\ConfigUtility;
use App\Utilities\DetailUtility;
use App\Utilities\PermissionUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FileController extends Controller
{
    // storage token
    public function storageToken(Request $request)
    {
        $dtoRequest = new FileStorageTokenDTO($request->all());

        $type = match ($dtoRequest->type) {
            'image' => File::TYPE_IMAGE,
            'video' => File::TYPE_VIDEO,
            'audio' => File::TYPE_AUDIO,
            'document' => File::TYPE_DOCUMENT,
        };

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getStorageToken([
            'type' => $type,
        ]);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->errorResponse();
        }

        $usageType = match ($dtoRequest->usageType) {
            'userAvatar' => FileUsage::TYPE_USER,
            'userBanner' => FileUsage::TYPE_USER,
            'conversationMessage' => FileUsage::TYPE_CONVERSATION,
            'post' => FileUsage::TYPE_POST,
            'comment' => FileUsage::TYPE_COMMENT,
            'postDraft' => FileUsage::TYPE_POST,
            'commentDraft' => FileUsage::TYPE_COMMENT,
        };

        $pathPrefix = FileHelper::fresnsFileStoragePath($type, $usageType);

        $data = $fresnsResp->getData();
        $data['pathPrefix'] = $pathPrefix;

        return $this->success($data);
    }

    // uploads
    public function uploads(Request $request)
    {
        $dtoRequest = new FileUploadsDTO($request->all());

        $langTag = $this->langTag();
        $authUser = $this->user();

        $fileType = match ($dtoRequest->type) {
            'image' => File::TYPE_IMAGE,
            'video' => File::TYPE_VIDEO,
            'audio' => File::TYPE_AUDIO,
            'document' => File::TYPE_DOCUMENT,
        };

        // check upload service
        $storageConfig = FileHelper::fresnsFileStorageConfigByType($fileType);

        if (! $storageConfig['storageConfigStatus']) {
            throw new ApiException(32100);
        }

        $servicePlugin = App::where('fskey', $storageConfig['service'])->isEnabled()->first();

        if (! $servicePlugin) {
            throw new ApiException(32102);
        }

        $tableName = match ($dtoRequest->usageType) {
            'userAvatar' => 'users',
            'userBanner' => 'users',
            'conversation' => 'conversations',
            'post' => 'posts',
            'comment' => 'comments',
            'postDraft' => 'post_logs',
            'commentDraft' => 'comment_logs',
        };

        $tableColumn = match ($dtoRequest->usageType) {
            'userAvatar' => 'avatar_file_id',
            'userBanner' => 'banner_file_id',
            'conversation' => 'id',
            'post' => 'id',
            'comment' => 'id',
            'postDraft' => 'id',
            'commentDraft' => 'id',
        };

        $fsid = $dtoRequest->usageFsid;

        switch ($tableName) {
            case 'users':
                if (StrHelper::isPureInt($fsid)) {
                    $checkQuery = User::where('uid', $fsid)->first();
                } else {
                    $checkQuery = User::where('username', $fsid)->first();
                }

                $checkUser = $checkQuery?->id == $authUser->id;
                break;

            case 'posts':
                $checkQuery = Post::where('pid', $fsid)->first();

                $checkUser = $checkQuery?->user_id == $authUser->id;
                break;

            case 'comments':
                $checkQuery = Comment::where('cid', $fsid)->first();

                $checkUser = $checkQuery?->user_id == $authUser->id;
                break;

            case 'conversations':
                if (StrHelper::isPureInt($fsid)) {
                    $checkQuery = User::where('uid', $fsid)->first();
                } else {
                    $checkQuery = User::where('username', $fsid)->first();
                }

                $checkUser = true;
                break;

            case 'post_logs':
                $checkQuery = PostLog::where('hpid', $fsid)->first();

                $checkUser = $checkQuery?->user_id == $authUser->id;
                break;

            case 'comment_logs':
                $checkQuery = CommentLog::where('hcid', $fsid)->first();

                $checkUser = $checkQuery?->user_id == $authUser->id;
                break;

            default:
                $checkQuery = null;
                $checkUser = false;
        }

        if (empty($checkQuery)) {
            throw new ApiException(32201);
        }

        if (! $checkUser) {
            throw new ApiException(36500);
        }

        $tableId = $checkQuery->id;

        // conversation message
        if ($tableName == 'conversations') {
            $conversationPermInt = PermissionUtility::checkUserConversationPerm($checkQuery->id, $authUser->id, $langTag);
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

            $conversation = PrimaryHelper::fresnsModelConversation($authUser->id, $checkQuery->id);
            $tableId = $conversation->id;
        }

        // usage type
        $usageType = match ($tableName) {
            'users' => FileUsage::TYPE_USER,
            'posts' => FileUsage::TYPE_POST,
            'comments' => FileUsage::TYPE_COMMENT,
            'conversations' => FileUsage::TYPE_CONVERSATION,
            'post_logs' => FileUsage::TYPE_POST,
            'comment_logs' => FileUsage::TYPE_COMMENT,
            default => FileUsage::TYPE_OTHER,
        };

        // check publish file count
        $publishType = match ($usageType) {
            FileUsage::TYPE_POST => 'post',
            FileUsage::TYPE_COMMENT => 'comment',
            default => null,
        };

        if ($publishType) {
            $editorConfig = ConfigUtility::getEditorConfigByType($publishType, $authUser->id, $langTag);

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
                ->where('table_name', $tableName)
                ->where('table_column', $tableColumn)
                ->where('table_id', $checkQuery->id)
                ->count();

            if ($fileCount >= $uploadNumber) {
                throw new ApiException(36115);
            }
        }

        $warningType = match ($dtoRequest->warning) {
            'nudity' => File::WARNING_NUDITY,
            'violence' => File::WARNING_VIOLENCE,
            'sensitive' => File::WARNING_SENSITIVE,
            default => File::WARNING_NONE,
        };

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
                    'platformId' => \request()->header('X-Fresns-Client-Platform-Id'),
                    'usageType' => $usageType,
                    'tableName' => $tableName,
                    'tableColumn' => $tableColumn,
                    'tableId' => $tableId,
                    'tableKey' => $fsid,
                    'aid' => \request()->header('X-Fresns-Aid'),
                    'uid' => \request()->header('X-Fresns-Uid'),
                    'type' => $fileType,
                    'file' => $dtoRequest->file,
                    'warningType' => $warningType,
                    'moreInfo' => $dtoRequest->moreInfo,
                ];

                $fresnsResp = \FresnsCmdWord::plugin($storageConfig['service'])->uploadFile($wordBody);
                break;

            case 'fileInfo':
                $wordBody = [
                    'platformId' => \request()->header('X-Fresns-Client-Platform-Id'),
                    'usageType' => $usageType,
                    'tableName' => $tableName,
                    'tableColumn' => $tableColumn,
                    'tableId' => $tableId,
                    'tableKey' => $fsid,
                    'aid' => \request()->header('X-Fresns-Aid'),
                    'uid' => \request()->header('X-Fresns-Uid'),
                    'type' => $fileType,
                    'fileInfo' => $dtoRequest->fileInfo,
                    'warningType' => $warningType,
                    'moreInfo' => $dtoRequest->moreInfo,
                ];

                $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFileInfo($wordBody);
                break;
        }

        // user avatar or banner
        if ($fresnsResp->isSuccessResponse() && $tableName == 'users') {
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            if ($tableColumn == 'avatar_file_id') {
                $authUser->update([
                    'avatar_file_id' => $fileId,
                ]);
            }

            if ($tableColumn == 'banner_file_id') {
                $authUser->update([
                    'banner_file_id' => $fileId,
                ]);
            }

            CacheHelper::forgetFresnsUser($authUser->id, $authUser->uid);
        }

        return $fresnsResp->getOrigin();
    }

    // file warning
    public function updateWarning(string $fid, Request $request)
    {
        $dtoRequest = new FileWarningDTO($request->all());
        $authAccountId = $this->account()->id;
        $authUserId = $this->user()->id;

        // check file
        $file = File::whereFid($fid)->first();
        if (empty($file)) {
            throw new ApiException(37600);
        }

        if (! $file->is_enabled) {
            throw new ApiException(37601);
        }

        $checkUploader = FileUsage::where('file_id', $file->id)->where('account_id', $authAccountId)->where('user_id', $authUserId)->first();

        if (! $checkUploader) {
            throw new ApiException(37602);
        }

        $warningType = match ($dtoRequest->warning) {
            'nudity' => File::WARNING_NUDITY,
            'violence' => File::WARNING_VIOLENCE,
            'sensitive' => File::WARNING_SENSITIVE,
            default => File::WARNING_NONE,
        };

        $file->update([
            'warning_type' => $warningType,
        ]);

        $data = null;

        return $this->success($data);
    }

    // file download link
    public function fileLink(string $fid, Request $request)
    {
        $dtoRequest = new FileLinkDTO($request->all());
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
            $model = ConversationMessage::where('cmid', $dtoRequest->fsid)->first();
        } else {
            $model = PrimaryHelper::fresnsModelByFsid($dtoRequest->type, $dtoRequest->fsid);
        }

        // check model
        if (empty($model)) {
            throw new ApiException(32201);
        }

        if ($model->deleted_at) {
            throw new ApiException(32304);
        }

        $permissions = $model?->permissions ?? [];
        $isReadLocked = $permissions['readConfig']['isReadLocked'] ?? false;

        // check permission
        if ($dtoRequest->type == 'post' && $isReadLocked) {
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

        $fileUsage = FileUsage::where('file_id', $file->id)
            ->where('table_name', "{$dtoRequest->type}s")
            ->where('table_column', 'id')
            ->where('table_id', $model?->id)
            ->first();

        if (empty($fileUsage)) {
            throw new ApiException(32304);
        }

        $data['link'] = FileHelper::fresnsFileOriginalUrlById($file->id);

        $objectType = match ($dtoRequest->type) {
            'post' => FileDownload::TYPE_POST,
            'comment' => FileDownload::TYPE_COMMENT,
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
        $dtoRequest = new FileUsersDTO($request->all());
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

        $userOptions = [
            'viewType' => 'list',
            'isLiveStats' => false,
            'filter' => [
                'type' => $dtoRequest->filterUserType,
                'keys' => $dtoRequest->filterUserKeys,
            ],
        ];

        $items = [];
        foreach ($downUsers as $downloader) {
            if (empty($downloader->user)) {
                continue;
            }

            $item['datetime'] = DateHelper::fresnsFormatDateTime($downloader->created_at, $timezone, $langTag);
            $item['timeAgo'] = DateHelper::fresnsHumanReadableTime($downloader->created_at, $langTag);
            $item['user'] = DetailUtility::userDetail($downloader->user, $langTag, $timezone, $authUser?->id, $userOptions);

            $items[] = $item;
        }

        return $this->fresnsPaginate($items, $downUsers->total(), $downUsers->perPage());
    }
}
