<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\DetailDTO;
use App\Fresns\Api\Http\DTO\GeotagListDTO;
use App\Fresns\Api\Http\DTO\InteractionDTO;
use App\Fresns\Api\Services\InteractionService;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\Geotag;
use App\Models\Seo;
use App\Utilities\DetailUtility;
use App\Utilities\InteractionUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeotagController extends Controller
{
    // list
    public function list(Request $request)
    {
        $dtoRequest = new GeotagListDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $geotagQuery = Geotag::isEnabled();

        $blockIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GEOTAG, $authUserId);
        $geotagQuery->when($blockIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        if ($dtoRequest->mapLng && $dtoRequest->mapLat) {
            $nearbyConfig = ConfigHelper::fresnsConfigByItemKeys([
                'nearby_length_km',
                'nearby_length_mi',
            ]);

            $unit = $dtoRequest->unit ?? ConfigHelper::fresnsConfigLengthUnit($langTag);
            $length = $dtoRequest->length ?? $nearbyConfig["nearby_length_{$unit}"];

            $nearbyLength = match ($unit) {
                'km' => $length,
                'mi' => $length * 0.6214,
                default => $length,
            };

            switch (config('database.default')) {
                case 'pgsql':
                    $geotagQuery->select(DB::raw("*, ST_Distance(map_location, ST_MakePoint($dtoRequest->mapLng, $dtoRequest->mapLat)::geography) AS distance"))
                        ->having('distance', '<=', $nearbyLength * 1000)
                        ->orderBy('distance');
                    break;

                case 'sqlsrv':
                    $geotagQuery->select(DB::raw("*, map_location.STDistance(geography::Point($dtoRequest->mapLat, $dtoRequest->mapLng, 4326)) AS distance"))
                        ->having('distance', '<=', $nearbyLength * 1000)
                        ->orderBy('distance');
                    break;

                default:
                    $geotagQuery->select(DB::raw("*, ( 6371 * acos( cos( radians($dtoRequest->mapLat) ) * cos( radians( map_latitude ) ) * cos( radians( map_longitude ) - radians($dtoRequest->mapLng) ) + sin( radians($dtoRequest->mapLat) ) * sin( radians( map_latitude ) ) ) ) AS distance"))
                        ->having('distance', '<=', $nearbyLength)
                        ->orderBy('distance');
            }
        }

        $geotagQuery->when($dtoRequest->type, function ($query, $value) {
            $query->where('type', $value);
        });

        if ($dtoRequest->createdDays || $dtoRequest->createdDate) {
            switch ($dtoRequest->createdDate) {
                case 'today':
                    $geotagQuery->whereDate('created_at', now()->format('Y-m-d'));
                    break;

                case 'yesterday':
                    $geotagQuery->whereDate('created_at', now()->subDay()->format('Y-m-d'));
                    break;

                case 'week':
                    $geotagQuery->whereDate('created_at', '>=', now()->startOfWeek()->format('Y-m-d'))
                        ->whereDate('created_at', '<=', now()->endOfWeek()->format('Y-m-d'));
                    break;

                case 'lastWeek':
                    $geotagQuery->whereDate('created_at', '>=', now()->subWeek()->startOfWeek()->format('Y-m-d'))
                        ->whereDate('created_at', '<=', now()->subWeek()->endOfWeek()->format('Y-m-d'));
                    break;

                case 'month':
                    $geotagQuery->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    break;

                case 'lastMonth':
                    $lastMonth = now()->subMonth()->month;
                    $year = now()->year;
                    if ($lastMonth == 12) {
                        $year = now()->subYear()->year;
                    }
                    $geotagQuery->whereMonth('created_at', $lastMonth)->whereYear('created_at', $year);
                    break;

                case 'year':
                    $geotagQuery->whereYear('created_at', now()->year);
                    break;

                case 'lastYear':
                    $geotagQuery->whereYear('created_at', now()->subYear()->year);
                    break;

                default:
                    $geotagQuery->whereDate('created_at', '>=', now()->subDays($dtoRequest->createdDays ?? 1)->format('Y-m-d'));
            }
        } else {
            $geotagQuery->when($dtoRequest->createdDateGt, function ($query, $value) {
                $query->whereDate('created_at', '>=', $value);
            });

            $geotagQuery->when($dtoRequest->createdDateLt, function ($query, $value) {
                $query->whereDate('created_at', '<=', $value);
            });
        }

        $geotagQuery->when($dtoRequest->viewCountGt, function ($query, $value) {
            $query->where('view_count', '>=', $value);
        });

        $geotagQuery->when($dtoRequest->viewCountLt, function ($query, $value) {
            $query->where('view_count', '<=', $value);
        });

        $geotagQuery->when($dtoRequest->likeCountGt, function ($query, $value) {
            $query->where('like_count', '>=', $value);
        });

        $geotagQuery->when($dtoRequest->likeCountLt, function ($query, $value) {
            $query->where('like_count', '<=', $value);
        });

        $geotagQuery->when($dtoRequest->dislikeCountGt, function ($query, $value) {
            $query->where('dislike_count', '>=', $value);
        });

        $geotagQuery->when($dtoRequest->dislikeCountLt, function ($query, $value) {
            $query->where('dislike_count', '<=', $value);
        });

        $geotagQuery->when($dtoRequest->followCountGt, function ($query, $value) {
            $query->where('follow_count', '>=', $value);
        });

        $geotagQuery->when($dtoRequest->followCountLt, function ($query, $value) {
            $query->where('follow_count', '<=', $value);
        });

        $geotagQuery->when($dtoRequest->blockCountGt, function ($query, $value) {
            $query->where('block_count', '>=', $value);
        });

        $geotagQuery->when($dtoRequest->blockCountLt, function ($query, $value) {
            $query->where('block_count', '<=', $value);
        });

        $geotagQuery->when($dtoRequest->postCountGt, function ($query, $value) {
            $query->where('post_count', '>=', $value);
        });

        $geotagQuery->when($dtoRequest->postCountLt, function ($query, $value) {
            $query->where('post_count', '<=', $value);
        });

        $geotagQuery->when($dtoRequest->postDigestCountGt, function ($query, $value) {
            $query->where('post_digest_count', '>=', $value);
        });

        $geotagQuery->when($dtoRequest->postDigestCountLt, function ($query, $value) {
            $query->where('post_digest_count', '<=', $value);
        });

        if ($dtoRequest->orderType == 'random') {
            $geotagQuery->inRandomOrder();
        } else {
            $orderType = match ($dtoRequest->orderType) {
                'createdTime' => 'created_at',
                'lastPostTime' => 'last_post_at',
                'lastCommentTime' => 'last_comment_at',
                'view' => 'view_count',
                'like' => 'like_count',
                'dislike' => 'dislike_count',
                'follow' => 'follow_count',
                'block' => 'block_count',
                'post' => 'post_count',
                'postDigest' => 'post_digest_count',
                default => 'created_at',
            };

            $orderDirection = match ($dtoRequest->orderDirection) {
                default => 'desc',
                'asc' => 'asc',
                'desc' => 'desc',
            };

            $geotagQuery->orderBy($orderType, $orderDirection);
        }

        $geotagData = $geotagQuery->paginate($dtoRequest->pageSize ?? 30);

        $geotagOptions = [
            'viewType' => 'list',
            'filter' => [
                'type' => $dtoRequest->filterType,
                'keys' => $dtoRequest->filterKeys,
            ],
        ];

        $geotagList = [];
        foreach ($geotagData as $geotag) {
            $geotagList[] = DetailUtility::geotagDetail($geotag, $langTag, $timezone, $authUserId, $geotagOptions);
        }

        return $this->fresnsPaginate($geotagList, $geotagData->total(), $geotagData->perPage());
    }

    // detail
    public function detail(string $gtid, Request $request)
    {
        $dtoRequest = new DetailDTO($request->all());

        $geotag = Geotag::where('gtid', $gtid)->first();

        if (empty($geotag)) {
            throw new ApiException(37300);
        }

        if (! $geotag->is_enabled) {
            throw new ApiException(37301);
        }

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $seoData = PrimaryHelper::fresnsModelSeo(Seo::TYPE_GEOTAG, $geotag->id);

        $item['title'] = StrHelper::languageContent($seoData?->title, $langTag);
        $item['keywords'] = StrHelper::languageContent($seoData?->keywords, $langTag);
        $item['description'] = StrHelper::languageContent($seoData?->description, $langTag);

        $geotagOptions = [
            'viewType' => 'detail',
            'filter' => [
                'type' => $dtoRequest->filterType,
                'keys' => $dtoRequest->filterKeys,
            ],
        ];

        $data = [
            'items' => $item,
            'detail' => DetailUtility::geotagDetail($geotag, $langTag, $timezone, $authUserId, $geotagOptions),
        ];

        return $this->success($data);
    }

    // interaction
    public function interaction(string $gtid, string $type, Request $request)
    {
        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new InteractionDTO($requestData);

        $geotag = PrimaryHelper::fresnsModelByFsid('geotag', $gtid);

        if (empty($geotag) || $geotag?->deleted_at) {
            throw new ApiException(37300);
        }

        if (! $geotag->is_enabled) {
            throw new ApiException(37301);
        }

        InteractionService::checkInteractionSetting('geotag', $dtoRequest->type);

        $orderDirection = $dtoRequest->orderDirection ?: 'desc';

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $service = new InteractionService();
        $data = $service->getUsersWhoMarkIt($dtoRequest->type, InteractionService::TYPE_GEOTAG, $geotag->id, $orderDirection, $langTag, $timezone, $authUserId);

        return $this->fresnsPaginate($data['paginateData'], $data['interactionData']->total(), $data['interactionData']->perPage());
    }
}
