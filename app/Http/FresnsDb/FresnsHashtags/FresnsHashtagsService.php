<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsHashtags;

use App\Http\FresnsApi\Base\FresnsBaseService;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsagesService;
use Illuminate\Support\Facades\DB;

class FresnsHashtagsService extends FresnsBaseService
{
    public $needCommon = true;

    public function __construct()
    {
        $this->config = new FsConfig();
        $this->model = new FsModel();
        $this->resource = FsResource::class;
        $this->resourceDetail = FsResourceDetail::class;
    }

    public function common()
    {
        // $common =  parent::common();
        $id = request()->input('huri');
        $langTag = request()->header('langTag');
        $mid = request()->header('mid');
        $group = FresnsHashtags::where('slug', $id)->first();
        $common['seoInfo'] = [];
        if (! $langTag) {
            $langTag = FresnsPluginUsagesService::getDefaultLanguage();
        }
        // $seo = null;
        if ($group) {
            $seo = DB::table('seo')->where('linked_type', 3)->where('linked_id', $group['id'])->where('lang_tag',
                $langTag)->where('deleted_at', null)->first();
            $seoInfo = [];
            if ($seo) {
                $seoInfo['title'] = $seo->title;
                $seoInfo['keywords'] = $seo->keywords;
                $seoInfo['description'] = $seo->description;
                $common['seoInfo'] = $seoInfo;
            }
        }
        $common['seoInfo'] = (object) $common['seoInfo'];

        return $common;
    }
}
