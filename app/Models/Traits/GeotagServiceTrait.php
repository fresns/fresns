<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\StrHelper;

trait GeotagServiceTrait
{
    public function getGeotagInfo(?string $langTag = null): array
    {
        $geotagData = $this;
        $locationInfo = $geotagData->location_info;

        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'website_geotag_detail_path',
            'geotag_like_public_count',
            'geotag_dislike_public_count',
            'geotag_follow_public_count',
            'geotag_block_public_count',
        ]);

        $siteUrl = ConfigHelper::fresnsSiteUrl();

        $info['gtid'] = $geotagData->gtid;
        $info['url'] = $siteUrl.'/'.$configKeys['website_geotag_detail_path'].'/'.$geotagData->gtid; // https://example.com/geotag/{gtid}
        $info['name'] = StrHelper::languageContent($geotagData->name, $langTag);
        $info['cover'] = FileHelper::fresnsFileUrlByTableColumn($geotagData->cover_file_id, $geotagData->cover_file_url);
        $info['description'] = StrHelper::languageContent($geotagData->description, $langTag);
        $info['placeId'] = $geotagData->place_id;
        $info['placeType'] = $geotagData->place_type;
        $info['mapId'] = $geotagData->map_id;
        $info['latitude'] = $geotagData->map_latitude;
        $info['longitude'] = $geotagData->map_longitude;
        $info['scale'] = $locationInfo['scale'] ?? null;
        $info['continent'] = $locationInfo['continent'] ?? null;
        $info['continentCode'] = $geotagData->continent_code;
        $info['country'] = $locationInfo['country'] ?? null;
        $info['countryCode'] = $geotagData->country_code;
        $info['region'] = $locationInfo['region'] ?? null;
        $info['regionCode'] = $geotagData->region_code;
        $info['city'] = $locationInfo['city'] ?? null;
        $info['cityCode'] = $geotagData->city_code;
        $info['district'] = $locationInfo['district'] ?? null;
        $info['address'] = $locationInfo['address'] ?? null;
        $info['zip'] = $geotagData->zip;
        $info['viewCount'] = $geotagData->view_count;
        $info['likeCount'] = $configKeys['geotag_like_public_count'] ? $geotagData->like_count : null;
        $info['dislikeCount'] = $configKeys['geotag_dislike_public_count'] ? $geotagData->dislike_count : null;
        $info['followCount'] = $configKeys['geotag_follow_public_count'] ? $geotagData->follow_count : null;
        $info['blockCount'] = $configKeys['geotag_block_public_count'] ? $geotagData->block_count : null;
        $info['postCount'] = $geotagData->post_count;
        $info['postDigestCount'] = $geotagData->post_digest_count;
        $info['commentCount'] = $geotagData->comment_count;
        $info['commentDigestCount'] = $geotagData->comment_digest_count;
        $info['createdDatetime'] = $geotagData->created_at;
        $info['createdTimeAgo'] = null;
        $info['lastPublishPostDateTime'] = $geotagData->last_post_at;
        $info['lastPublishPostTimeAgo'] = null;
        $info['lastPublishCommentDateTime'] = $geotagData->last_comment_at;
        $info['lastPublishCommentTimeAgo'] = null;
        $info['moreInfo'] = $geotagData->more_info;

        return $info;
    }
}
