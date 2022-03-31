<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Models\File;

trait FileServiceInfoTrait
{
    public function getFileServiceInfo()
    {
        $key = $this->getTypeKey();

        $data = ConfigHelper::fresnsConfigByItemKeys([
            "{$key}_service",
            "{$key}_url_status",
            "{$key}_url_key",
            "{$key}_url_expire",
        ]);

        return [
            'type' => $this->file_type,
            'unikey' => $data["{$key}_service"],
            'url_anti_status' => $data["{$key}_url_status"],
            'url_key' => $data["{$key}_url_key"],
            'url_expire' => $data["{$key}_url_expire"],
        ];
    }

    public static function getFileServiceInfoByFileType(int $fileType)
    {
        return (new File([
            'file_type' => $fileType,
        ]))->getFileServiceInfo();
    }

    public static function isEnableAntiTheftChainOfFileType(int $fileType)
    {
        $serviceInfo = File::getFileServiceInfoByFileType($fileType);

        return (bool) $serviceInfo['url_anti_status'];
    }
}
