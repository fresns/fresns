<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Models\Seo;
use App\Models\Post;
use App\Models\User;
use App\Models\Plugin;
use App\Fresns\Api\Services\HeaderService;
use Illuminate\Http\Request;
use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\PostListDTO;
use App\Fresns\Api\Services\PostService;
use App\Fresns\Api\Http\DTO\PostDetailDTO;
use App\Fresns\Api\Http\DTO\PostFollowDTO;
use App\Fresns\Api\Http\DTO\PostNearbyDTO;
use App\Fresns\Api\Services\PostFollowService;
use App\Helpers\ConfigHelper;
use App\Utilities\ExtendUtility;
use App\Utilities\LbsUtility;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function list(Request $request)
    {
        $dtoRequest = new PostListDTO($request->all());

        // Plugin provides data
        if ($dtoRequest->contentType) {
            $dataPluginUnikey = ExtendUtility::getDataExtend($dtoRequest->contentType, 'postByAll');

            if ($dataPluginUnikey) {
                $fresnsResp = \FresnsCmdWord::plugin($dataPluginUnikey)->getPostByAll($dtoRequest->toArray());

                return $fresnsResp->getOrigin();
            }
        }

        // Fresns provides data
        $headers = HeaderService::getHeaders();
        $user = !empty($headers['uid']) ? User::whereUid($headers['uid'])->first() : null;

        $postQuery = Post::isEnable();
        $posts = $postQuery->paginate($request->get('pageSize', 15));

        $postList = [];
        $service = new PostService();
        foreach ($posts as $post) {
            $postList[] = $service->postDetail($post, 'list', $dtoRequest->mapId, $dtoRequest->mapLng, $dtoRequest->mapLat);
        }

        return $this->fresnsPaginate($postList, $posts->total(), $posts->perPage());
    }

    public function detail(string $pid, Request $request)
    {
        $dtoRequest = new PostDetailDTO($request->all());

        $post = Post::with('creator')->wherePid($pid)->first();
        if (empty($post)) {
            throw new ApiException(37300);
        }

        // Plugin provides data
        $dataPluginUnikey = ConfigHelper::fresnsConfigByItemKey('post_detail_service');
        $dataPlugin = Plugin::where('unikey', $dataPluginUnikey)->isEnable()->first();

        if ($dataPlugin) {
            $fresnsResp = \FresnsCmdWord::plugin($dataPlugin->unikey)->getPostDetail($dtoRequest->toArray());

            return $fresnsResp->getOrigin();
        }

        // Fresns provides data
        $headers = HeaderService::getHeaders();

        $seoData = Seo::where('linked_type', 4)->where('linked_id', $post->id)->where('lang_tag', $headers['langTag'])->first();
        $common['title'] = $seoData->title ?? null;
        $common['keywords'] = $seoData->keywords ?? null;
        $common['description'] = $seoData->description ?? null;
        $data['commons'] = $common;

        $service = new PostService();
        $data['detail'] = $service->postDetail($post, 'detail', $dtoRequest->mapId, $dtoRequest->mapLng, $dtoRequest->mapLat);

        return $this->success($data);
    }

    public function follow(string $type, Request $request)
    {
        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new PostFollowDTO($requestData);

        // Plugin provides data
        if ($dtoRequest->contentType) {
            $dataPluginUnikey = ExtendUtility::getDataExtend($dtoRequest->contentType, 'postByFollow');

            if ($dataPluginUnikey) {
                $fresnsResp = \FresnsCmdWord::plugin($dataPluginUnikey)->getPostByFollow($dtoRequest->toArray());

                return $fresnsResp->getOrigin();
            }
        }

        // Fresns provides data
        $headers = HeaderService::getHeaders();
        $user = User::whereUid($headers['uid'])->first();

        $postFollowService = new PostFollowService($user, $dtoRequest);

        ['data' => $data, 'posts' => $posts] = $postFollowService->handle();

        if (!$posts) {
            return $this->success();
        }

        return $this->fresnsPaginate(
            $data,
            $posts->total(),
            $posts->perPage(),
        );
    }

    public function nearby(Request $request)
    {
        $dtoRequest = new PostNearbyDTO($request->all());
        $headers = HeaderService::getHeaders();

        // Plugin provides data
        if ($dtoRequest->contentType) {
            $dataPluginUnikey = ExtendUtility::getDataExtend($dtoRequest->contentType, 'postByNearby');

            if ($dataPluginUnikey) {
                $fresnsResp = \FresnsCmdWord::plugin($dataPluginUnikey)->getPostByNearby($dtoRequest->toArray());

                return $fresnsResp->getOrigin();
            }
        }

        // Fresns provides data
        $nearbyConfig = ConfigHelper::fresnsConfigByItemKeys([
            'nearby_length_km',
            'nearby_length_mi',
        ]);

        $unit = $dtoRequest->unit ?? ConfigHelper::fresnsConfigLengthUnit($headers['langTag']);
        $length = $dtoRequest->length ?? $nearbyConfig["nearby_length_{$unit}"];

        $nearbyLength = match ($unit) {
            'km' => $length,
            'mi' => $length * 0.6214,
            default => $length,
        };

        $posts = Post::query()
            ->select([
                DB::raw("*"),
                DB::raw(LbsUtility::getDistanceSql('map_longitude', 'map_latitude', $dtoRequest->mapLng, $dtoRequest->mapLat))
            ])
            ->having('distance', '<=', $nearbyLength)
            ->orderBy('distance')
            ->paginate();

        $postList = [];
        $service = new PostService();
        foreach ($posts as $post) {
            $postList[] = $service->postDetail($post, 'list', $dtoRequest->mapId, $dtoRequest->mapLng, $dtoRequest->mapLat);
        }

        return $this->fresnsPaginate($postList, $posts->total(), $posts->perPage());
    }
}
