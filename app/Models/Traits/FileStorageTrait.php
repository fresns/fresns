<?php

namespace App\Models\Traits;

use App\Models\File;
use App\Helpers\ConfigHelper;

trait FileStorageTrait
{
    public function getFileStorageConfig()
    {
        $key = $this->getTypeKey();

        $data = ConfigHelper::fresnsConfigByItemKeys([
            "{$key}_secret_id",
            "{$key}_secret_key",
            "{$key}_bucket_domain",
            "{$key}_bucket_name",
            "{$key}_bucket_area",
        ]);

        $config = [
            'type' => $this->file_type,
            'access_key' => $data["{$key}_secret_id"],
            'secret_key' => $data["{$key}_secret_key"],
            'bucket' => $data["{$key}_bucket_name"],
            'domain' => $data["{$key}_bucket_domain"],
            'area' => $data["{$key}_bucket_area"],
        ];

        $this->validateFileStorageConfig($config);

        return $config;
    }

    public static function getFileStorageConfigByFileType(int $fileType)
    {
        return (new File([
            'file_type' => $fileType
        ]))->getFileStorageConfig();
    }

    public function validateFileStorageConfig(array $data)
    {
        \request()->validate($data, [
            'access_key' => 'required|string',
            'secret_key' => 'required|string',
            'bucket' => 'required|string',
            'domain' => 'required|string',
        ]);
    }
}
