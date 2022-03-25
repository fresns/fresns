<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Fresns\Words\File\DTO\GetFileUrlOfAntiLinkDTO;
use App\Models\File;
use App\Models\FileAppend;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FileHelper
{
    protected static $fileType = [1 => 'imageFile', 2 => 'videoFile', 3 => 'audioFile', 4 => 'documentFile'];

    /**
     * Get link based on file FID.
     *
     * @param  string  $fid
     * @return array|string
     */
    public static function fresnsFileUrlByFid(string $fid)
    {
        $fileData = (new self())->getFile('fid', $fid);

        return $fileData;
    }

    /**
     * Get link based on file ID.
     *
     * @param  int  $fileId
     * @return array|string
     */
    public static function fresnsFileUrlById(int $fileId)
    {
        $fileData = (new self())->getFile('id', $fileId);

        return $fileData;
    }

    /**
     * Get file info based on file ID.
     *
     * @param  int  $filedId
     * @return array|string
     */
    public static function fresnsFileInfoById(int $filedId)
    {
        $fileInfo = (new self())->getFileInfo('id', $filedId);

        return $fileInfo;
    }

    /**
     * Get file info based on file FID.
     *
     * @param  string  $fid
     * @return array|string
     */
    public static function fresnsFileInfoByFid(string $fid)
    {
        $fileInfo = (new self())->getFileInfo('fid', $fid);

        return $fileInfo;
    }

    /**
     * @param  string  $column
     * @param $value
     * @return array|string
     */
    protected function getFileInfo(string $column, $value)
    {
        $fileData = DB::table('files as f')->leftJoin('file_appends as a', 'f.id', '=', 'a.file_id')->where('f.'.$column, '=', $value)->first();
        if (empty($fileData)) {
            return [];
        } else {
            $fileData = get_object_vars($fileData);
        }
        $fileType = self::$fileType[$fileData['file_type']];
        $result = (new self())->getFileResult($fileType, $fileData);

        return $result;
    }

    /**
     * @param  string  $fileType
     * @param  array  $fileData
     * @return array|string
     */
    protected function getFileResult(string $fileType, array $fileData)
    {
        $fileResult = self::getFile('id', $fileData['id']);
        switch ($fileType) {
            case 'imageFile':
                $fileResult['imageWidth'] = $fileData['image_width'] ?? 0;
                $fileResult['imageHeight'] = $fileData['image_height'] ?? 0;
                $fileResult['imageLong'] = $fileData['image_is_long'] ?? 0;
                break;
            case 'videoFile':
                $fileResult['videoTime'] = $fileData['video_time'] ?? 0;
                $fileResult['transcodingState'] = $fileData['transcoding_state'] ?? 0;
                break;
            case 'audioFile':
                $fileResult['audioTime'] = $fileData['audio_time'] ?? 0;
                $fileResult['transcodingState'] = $fileData['transcoding_state'] ?? 0;
                break;
            case 'documentFile':
                break;
        }
        $fileResult['fid'] = $fileData['fid'] ?? 0;
        $fileResult['type'] = $fileData['file_type'] ?? 0;
        $fileResult['rankNum'] = $fileData['rank_num'] ?? 0;
        $fileResult['name'] = $fileData['file_name'] ?? '';
        $fileResult['extension'] = $fileData['file_extension'] ?? 0;
        $fileResult['mime'] = $fileData['file_mime'] ?? '';
        $fileResult['size'] = $fileData['file_size'] ?? 0;
        $fileResult['moreJson'] = $fileData['moreJson'] ?? [];

        return $fileResult;
    }

    /**
     * @param  string  $column
     * @param $value
     * @return array|string
     */
    protected function getFile(string $column, $value)
    {
        $fileData = File::where($column, '=', $value)->first();
        if (empty($fileData)) {
            return [];
        }
        $fileType = self::$fileType[$fileData['file_type']];
        $path = (new self())->getFileUrl($fileType, $fileData->toArray());

        return $path;
    }

    /**
     * @param  string  $fileType
     * @param  array  $fileData
     * @return array
     */
    protected function getFileUrl(string $fileType, array $fileData)
    {
        $urlArr = [];
        $urlArr['file_type'] = $fileData['file_type'];
        $fileAppend = FileAppend::where('file_id', $fileData['id'])->first();
        switch ($fileType) {
            case 'imageFile':
                $urlArr['imageDefaultUrl'] = ConfigHelper::fresnsConfigByItemKey('image_bucket_domain').$fileData['file_path'] ?? '';
                $urlArr['imageConfigUrl'] = ConfigHelper::fresnsConfigByItemKey('image_bucket_domain').$fileData['file_path'] ?? ''.ConfigHelper::fresnsConfigByItemKey('image_thumb_config');
                $urlArr['imageAvatarUrl'] = ConfigHelper::fresnsConfigByItemKey('image_bucket_domain').$fileData['file_path'] ?? ''.ConfigHelper::fresnsConfigByItemKey('image_thumb_avatar');
                $urlArr['imageRatioUrl'] = ConfigHelper::fresnsConfigByItemKey('image_bucket_domain').$fileData['file_path'] ?? ''.ConfigHelper::fresnsConfigByItemKey('image_thumb_ratio');
                $urlArr['imageSquareUrl'] = ConfigHelper::fresnsConfigByItemKey('image_bucket_domain').$fileData['file_path'] ?? ''.ConfigHelper::fresnsConfigByItemKey('image_thumb_square');
                $urlArr['imageBigUrl'] = ConfigHelper::fresnsConfigByItemKey('image_bucket_domain').$fileData['file_path'] ?? ''.ConfigHelper::fresnsConfigByItemKey('image_thumb_big');
                $urlArr['imageOriginalUrl'] = ConfigHelper::fresnsConfigByItemKey('image_bucket_domain').$fileAppend['file_original_path'];
                break;
            case 'videoFile':
                $urlArr['videoCover'] = ConfigHelper::fresnsConfigByItemKey('video_bucket_domain').Arr::get($fileAppend, 'video_cover');
                $urlArr['videoGif'] = ConfigHelper::fresnsConfigByItemKey('video_bucket_domain').Arr::get($fileAppend, 'video_gif').ConfigHelper::fresnsConfigByItemKey('image_thumb_config');
                $urlArr['videoUrl'] = ConfigHelper::fresnsConfigByItemKey('video_bucket_domain').$fileData['file_path'] ?? ''.ConfigHelper::fresnsConfigByItemKey('image_thumb_avatar');
                $urlArr['videoOriginalUrl'] = ConfigHelper::fresnsConfigByItemKey('video_bucket_domain').$fileAppend['file_original_path'];
                break;
            case 'audioFile':
                $urlArr['audioUrl'] = ConfigHelper::fresnsConfigByItemKey('audio_bucket_domain').$fileData['file_path'] ?? '';
                $urlArr['audioOriginalUrl'] = ConfigHelper::fresnsConfigByItemKey('audio_bucket_domain').$fileAppend['file_original_path'];
                break;
            case 'documentFile':
                $urlArr['documentUrl'] = ConfigHelper::fresnsConfigByItemKey('document_bucket_domain').$fileData['file_path'] ?? '';
                $urlArr['documentOriginalUrl'] = ConfigHelper::fresnsConfigByItemKey('document_bucket_domain').$fileAppend['file_original_path'];
                break;
        }

        return $urlArr;
    }

    /**
     * Output image links based on file column
     * This function is used to get the file url of the anti-link.
     *
     * @param fileId The file id of the file.
     * @param fileUrl the original file url
     * @param urlType
     * @return The file url of the file.
     */
    public static function fresnsFileImageUrlByColumn($fileId, $fileUrl, $urlType)
    {
        if (empty($fileId)) {
            return $fileUrl;
        }
        $imageUrlStatus = ConfigHelper::fresnsConfigByItemKey('image_url_status');
        if (! $imageUrlStatus) {
            return $fileUrl;
        }
        $data = \FresnsCmdWord::plugin('Fresns')->getFileUrlOfAntiLink(new GetFileUrlOfAntiLinkDTO($fileId));

        return Arr::get($data, 'data.'.$urlType, '');
    }
}
