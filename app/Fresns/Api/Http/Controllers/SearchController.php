<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Fresns\Api\Http\DTO\SearchCommentDTO;
use App\Fresns\Api\Http\DTO\SearchGeotagDTO;
use App\Fresns\Api\Http\DTO\SearchGroupDTO;
use App\Fresns\Api\Http\DTO\SearchHashtagDTO;
use App\Fresns\Api\Http\DTO\SearchPostDTO;
use App\Fresns\Api\Http\DTO\SearchUserDTO;
use App\Helpers\AppHelper;
use App\Helpers\ConfigHelper;
use App\Utilities\ConfigUtility;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // users
    public function users(Request $request)
    {
        new SearchUserDTO($request->all());

        $searchService = ConfigHelper::fresnsConfigByItemKey('search_users_service');

        if (! $searchService) {
            $langTag = $this->langTag();

            return $this->failure(
                32100,
                ConfigUtility::getCodeMessage(32100, 'Fresns', $langTag),
            );
        }

        $wordBody = [
            'headers' => AppHelper::getHeaders(),
            'body' => $request->all(),
        ];

        $fresnsResp = \FresnsCmdWord::plugin($searchService)->searchUsers($wordBody);

        return $fresnsResp->getOrigin();
    }

    // groups
    public function groups(Request $request)
    {
        new SearchGroupDTO($request->all());

        $searchService = ConfigHelper::fresnsConfigByItemKey('search_groups_service');

        if (! $searchService) {
            $langTag = $this->langTag();

            return $this->failure(
                32100,
                ConfigUtility::getCodeMessage(32100, 'Fresns', $langTag),
            );
        }

        $wordBody = [
            'headers' => AppHelper::getHeaders(),
            'body' => $request->all(),
        ];

        $fresnsResp = \FresnsCmdWord::plugin($searchService)->searchGroups($wordBody);

        return $fresnsResp->getOrigin();
    }

    // hashtags
    public function hashtags(Request $request)
    {
        new SearchHashtagDTO($request->all());

        $searchService = ConfigHelper::fresnsConfigByItemKey('search_hashtags_service');

        if (! $searchService) {
            $langTag = $this->langTag();

            return $this->failure(
                32100,
                ConfigUtility::getCodeMessage(32100, 'Fresns', $langTag),
            );
        }

        $wordBody = [
            'headers' => AppHelper::getHeaders(),
            'body' => $request->all(),
        ];

        $fresnsResp = \FresnsCmdWord::plugin($searchService)->searchHashtags($wordBody);

        return $fresnsResp->getOrigin();
    }

    // geotags
    public function geotags(Request $request)
    {
        new SearchGeotagDTO($request->all());

        $searchService = ConfigHelper::fresnsConfigByItemKey('search_geotags_service');

        if (! $searchService) {
            $langTag = $this->langTag();

            return $this->failure(
                32100,
                ConfigUtility::getCodeMessage(32100, 'Fresns', $langTag),
            );
        }

        $wordBody = [
            'headers' => AppHelper::getHeaders(),
            'body' => $request->all(),
        ];

        $fresnsResp = \FresnsCmdWord::plugin($searchService)->searchGeotags($wordBody);

        return $fresnsResp->getOrigin();
    }

    // posts
    public function posts(Request $request)
    {
        new SearchPostDTO($request->all());

        $searchService = ConfigHelper::fresnsConfigByItemKey('search_posts_service');

        if (! $searchService) {
            $langTag = $this->langTag();

            return $this->failure(
                32100,
                ConfigUtility::getCodeMessage(32100, 'Fresns', $langTag),
            );
        }

        $wordBody = [
            'headers' => AppHelper::getHeaders(),
            'body' => $request->all(),
        ];

        $fresnsResp = \FresnsCmdWord::plugin($searchService)->searchPosts($wordBody);

        return $fresnsResp->getOrigin();
    }

    // comments
    public function comments(Request $request)
    {
        new SearchCommentDTO($request->all());

        $searchService = ConfigHelper::fresnsConfigByItemKey('search_comments_service');

        if (! $searchService) {
            $langTag = $this->langTag();

            return $this->failure(
                32100,
                ConfigUtility::getCodeMessage(32100, 'Fresns', $langTag),
            );
        }

        $wordBody = [
            'headers' => AppHelper::getHeaders(),
            'body' => $request->all(),
        ];

        $fresnsResp = \FresnsCmdWord::plugin($searchService)->searchComments($wordBody);

        return $fresnsResp->getOrigin();
    }
}
