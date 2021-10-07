<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsImplants;

use App\Base\Services\BaseAdminService;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;

class FsService extends BaseAdminService
{
    public function __construct()
    {
        $this->model = new FsModel();
        $this->resource = FsResource::class;
        $this->resourceDetail = FsResourceDetail::class;
    }

    public function common()
    {
        $common = parent::common();

        return $common;
    }

    public static function getImplants($size, $pageSize, $type)
    {
        $startNum = ($size - 1) * $pageSize;
        $endNum = $size * $pageSize;
        $data = FresnsImplants::where('implant_type', $type)->where('position', '>=', $startNum)->where('position', '<', $endNum)->get(['id', 'implant_template', 'type', 'target', 'value', 'support', 'position']);

        // Determine if it is expired
        if ($data) {
            foreach ($data as &$v) {
                $v['template'] = $v['implant_template'];
                $v['name'] = ApiLanguageHelper::getLanguagesByTableId(FsConfig::CFG_TABLE, 'name', $v['id']);
                $v['position'] = $v['position'];
                $v['pageType'] = $v['type'];
                $v['pageTarget'] = $v['target'];
                $v['pageValue'] = $v['value'];
                $v['pageSupport'] = $v['support'];
                $v['position'] = $v['position'];
                if ($v['expired_at'] && $v['expired_at'] < date('Y-m-d H:i:s', time())) {
                    unset($v);
                }
                unset($v['implant_template']);
                unset($v['type']);
                unset($v['target']);
                unset($v['value']);
                unset($v['support']);
            }
        }

        return $data;
    }
}
