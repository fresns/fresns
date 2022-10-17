<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\CommonDownloadFileDTO;
use App\Fresns\Api\Http\DTO\CommonInputTipsDTO;
use App\Fresns\Api\Http\DTO\CommonSendVerifyCodeDTO;
use App\Fresns\Api\Http\DTO\CommonUploadFileDTO;
use App\Fresns\Api\Http\DTO\CommonUploadLogDTO;
use App\Fresns\Api\Http\DTO\PaginationDTO;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use App\Models\Account;
use App\Models\Extend;
use App\Models\File;
use App\Models\FileDownload;
use App\Models\Hashtag;
use App\Models\Language;
use App\Models\Post;
use App\Models\User;
use App\Utilities\ValidationUtility;
use Illuminate\Http\Request;

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

    // send verify code
    public function sendVerifyCode(Request $request)
    {
        $dtoRequest = new CommonSendVerifyCodeDTO($request->all());
        $authAccount = $this->account();
        $langTag = $this->langTag();

        $sendService = ConfigHelper::fresnsConfigByItemKeys([
            'send_email_service',
            'send_sms_service',
        ]);

        if ($dtoRequest->type == 'email' && empty($sendService['send_email_service'])) {
            throw new ApiException(32100);
        } elseif ($dtoRequest->type == 'sms' && empty($sendService['send_sms_service'])) {
            throw new ApiException(32100);
        }

        if ($dtoRequest->type == 'email') {
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

        if ($dtoRequest->useType == 2 && empty($account)) {
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
            $fresnsResp = \FresnsCmdWord::plugin($sendService['send_email_service'])->sendCode($wordBody);
        } else {
            $fresnsResp = \FresnsCmdWord::plugin($sendService['send_sms_service'])->sendCode($wordBody);
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
            'platformId' => $request->header('platformId'),
            'version' => $request->header('version'),
            'langTag' => $request->header('langTag'),
            'aid' => $request->header('aid'),
            'uid' => $request->header('uid'),
            'objectName' => $dtoRequest->objectName,
            'objectAction' => $dtoRequest->objectAction,
            'objectResult' => $dtoRequest->objectResult,
            'objectOrderId' => $dtoRequest->objectOrderId,
            'deviceInfo' => $request->header('deviceInfo'),
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

        $fileType = match ($dtoRequest->type) {
            'image' => 1,
            'video' => 2,
            'audio' => 3,
            'document' => 4,
        };

        $storageConfig = FileHelper::fresnsFileStorageConfigByType($fileType);

        if (! $storageConfig['storageConfigStatus']) {
            throw new ApiException(32100);
        }

        switch ($dtoRequest->uploadMode) {
            case 'file':
                $wordBody = [
                    'usageType' => $dtoRequest->usageType,
                    'platformId' => $request->header('platformId'),
                    'tableName' => $dtoRequest->tableName,
                    'tableColumn' => $dtoRequest->tableColumn,
                    'tableId' => $dtoRequest->tableId,
                    'tableKey' => $dtoRequest->tableKey,
                    'aid' => $request->header('aid'),
                    'uid' => $request->header('uid'),
                    'type' => $fileType,
                    'moreJson' => $dtoRequest->moreJson,
                    'file' => $dtoRequest->file,
                ];

                $fresnsResp = \FresnsCmdWord::plugin($storageConfig['service'])->uploadFile($wordBody);
            break;

            case 'fileInfo':
                $wordBody = [
                    'usageType' => $dtoRequest->usageType,
                    'platformId' => $request->header('platformId'),
                    'tableName' => $dtoRequest->tableName,
                    'tableColumn' => $dtoRequest->tableColumn,
                    'tableId' => $dtoRequest->tableId,
                    'tableKey' => $dtoRequest->tableKey,
                    'aid' => $request->header('aid'),
                    'uid' => $request->header('uid'),
                    'type' => $fileType,
                    'fileInfo' => $dtoRequest->fileInfo,
                ];

                $fresnsResp = \FresnsCmdWord::plugin($storageConfig['service'])->uploadFileInfo($wordBody);
            break;
        }

        return $fresnsResp->getOrigin();
    }

    // download file
    public function downloadFile(string $fid, Request $request)
    {
        $dtoRequest = new CommonDownloadFileDTO($request->all());

        $file = File::whereFid($fid)->first();
        if (empty($file)) {
            throw new ApiException(37500);
        }

        if ($file->is_enable == 0) {
            throw new ApiException(37501);
        }

        switch ($dtoRequest->type) {
            // post
            case 'post':
                $data = null;
            break;

            // comment
            case 'comment':
                $data = null;
            break;

            // extend
            case 'extend':
                $data = null;
            break;
        }

        return $this->success($data);
    }

    // file download users
    public function downloadUsers(string $fid, Request $request)
    {
        $dtoRequest = new PaginationDTO($request->all());
        $langTag = $this->langTag();
        $timezone = $this->timezone();

        $file = File::whereFid($fid)->first();
        if (empty($file)) {
            throw new ApiException(37500);
        }

        if ($file->is_enable == 0) {
            throw new ApiException(37501);
        }

        $downUsers = FileDownload::with('user')->latest()->paginate($request->get('pageSize', 15));

        $item = null;
        foreach ($downUsers as $down) {
            $item['downloadTime'] = DateHelper::fresnsFormatDateTime($down->created_at, $timezone, $langTag);
            $item['downloadTimeFormat'] = DateHelper::fresnsFormatTime($down->created_at, $langTag);
            $item['downloadUser'] = $down->user->getUserProfile();
            $item[] = $item;
        }

        return $this->fresnsPaginate($item, $downUsers->total(), $downUsers->perPage());
    }
}
