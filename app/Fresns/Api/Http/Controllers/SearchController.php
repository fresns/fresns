<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Fresns\Api\Http\DTO\SearchCommentDTO;
use App\Fresns\Api\Http\DTO\SearchGroupDTO;
use App\Fresns\Api\Http\DTO\SearchHashtagDTO;
use App\Fresns\Api\Http\DTO\SearchPostDTO;
use App\Fresns\Api\Http\DTO\SearchUserDTO;
use App\Helpers\ConfigHelper;
use App\Utilities\ConfigUtility;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // users
    public function users(Request $request)
    {
        $langTag = $this->langTag();
        $dtoRequest = new SearchUserDTO($request->all());

        $searchService = ConfigHelper::fresnsConfigByItemKey('search_users_service');

        if ($searchService) {
            $wordBody = [
                'headers' => \request()->headers->all(),
                'body' => $dtoRequest->toArray(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($searchService)->getPostByAll($wordBody);

            return $fresnsResp->getOrigin();
        }

        return $this->failure(
            32100,
            ConfigUtility::getCodeMessage(32100, 'Fresns', $langTag),
        );
    }

    // groups
    public function groups(Request $request)
    {
        $langTag = $this->langTag();
        $dtoRequest = new SearchGroupDTO($request->all());

        $searchService = ConfigHelper::fresnsConfigByItemKey('search_users_service');

        if ($searchService) {
            $wordBody = [
                'headers' => \request()->headers->all(),
                'body' => $dtoRequest->toArray(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($searchService)->getPostByAll($wordBody);

            return $fresnsResp->getOrigin();
        }

        return $this->failure(
            32100,
            ConfigUtility::getCodeMessage(32100, 'Fresns', $langTag),
        );
    }

    // hashtags
    public function hashtags(Request $request)
    {
        $langTag = $this->langTag();
        $dtoRequest = new SearchHashtagDTO($request->all());

        $searchService = ConfigHelper::fresnsConfigByItemKey('search_users_service');

        if ($searchService) {
            $wordBody = [
                'headers' => \request()->headers->all(),
                'body' => $dtoRequest->toArray(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($searchService)->getPostByAll($wordBody);

            return $fresnsResp->getOrigin();
        }

        return $this->failure(
            32100,
            ConfigUtility::getCodeMessage(32100, 'Fresns', $langTag),
        );
    }

    // posts
    public function posts(Request $request)
    {
        $langTag = $this->langTag();
        $dtoRequest = new SearchPostDTO($request->all());

        $searchService = ConfigHelper::fresnsConfigByItemKey('search_users_service');

        if ($searchService) {
            $wordBody = [
                'headers' => \request()->headers->all(),
                'body' => $dtoRequest->toArray(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($searchService)->getPostByAll($wordBody);

            return $fresnsResp->getOrigin();
        }

        return $this->failure(
            32100,
            ConfigUtility::getCodeMessage(32100, 'Fresns', $langTag),
        );
    }

    // comments
    public function comments(Request $request)
    {
        $langTag = $this->langTag();
        $dtoRequest = new SearchCommentDTO($request->all());

        $searchService = ConfigHelper::fresnsConfigByItemKey('search_users_service');

        if ($searchService) {
            $wordBody = [
                'headers' => \request()->headers->all(),
                'body' => $dtoRequest->toArray(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($searchService)->getPostByAll($wordBody);

            return $fresnsResp->getOrigin();
        }

        return $this->failure(
            32100,
            ConfigUtility::getCodeMessage(32100, 'Fresns', $langTag),
        );
    }
}
