<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Center\Scene;

class FileSceneConfig
{
    // File Info
    const FILE_TYPE_DEFAULT = 'file_default';
    const FILE_TYPE_IMAGE = 'file_image';
    const FILE_TYPE_VIDEO = 'file_video';
    const FILE_TYPE_AUDIO = 'file_audio';
    const FILE_TYPE_DOCUMENT = 'file_document';
    const FILE_TYPE_CONFIG = 'file_config';

    // File Type
    const FILE_TYPE_1 = 1; // image
    const FILE_TYPE_2 = 2; // video
    const FILE_TYPE_3 = 3; // audio
    const FILE_TYPE_4 = 4; // doc

    // File Scene Type (data table type)
    const TABLE_TYPE_1 = 1;
    const TABLE_TYPE_2 = 2;
    const TABLE_TYPE_3 = 3;
    const TABLE_TYPE_4 = 4;
    const TABLE_TYPE_5 = 5;
    const TABLE_TYPE_6 = 6;
    const TABLE_TYPE_7 = 7;
    const TABLE_TYPE_8 = 8;
    const TABLE_TYPE_9 = 9;
    const TABLE_TYPE_10 = 10;
    const TABLE_TYPE_11 = 11;

    // Upload File
    const UPLOAD = 'upload';
    const UPLOAD_BASE64 = 'upload_base64'; // Base64 file
    const UPLOAD_BLOB = 'upload_blob'; // Binary file

    // Upload Mode
    const UPLOAD_PROVIDER_LOCAL = 'local';
    const UPLOAD_PROVIDER_PLUGIN = 'plugin';

    // File Upload Rules
    public function uploadRule()
    {
        $rule = [
            'file' => 'required|file',
            'file_type' => 'required|min:2',
        ];

        return $rule;
    }

    // File Upload Rules (base64 file)
    public function uploadBase64Rule()
    {
        $rule = [
            'fileBase64' => 'required|min:30',
            'file_type' => 'required|min:2',
        ];

        return $rule;
    }
}
