<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\CommonCallbacksDTO;
use App\Fresns\Api\Http\DTO\CommonCmdWordDTO;
use App\Fresns\Api\Http\DTO\CommonInputTipsDTO;
use App\Fresns\Api\Http\DTO\CommonIpInfoDTO;
use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\PluginHelper;
use App\Models\Hashtag;
use App\Models\User;
use App\Utilities\InteractionUtility;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    // ip info
    public function ipInfo(Request $request)
    {
        $dtoRequest = new CommonIpInfoDTO($request->all());

        $ip = $dtoRequest->ip ?? $request->ip();

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->ipInfo([
            'ip' => $ip,
        ]);

        return $fresnsResp->getOrigin();
    }

    // input tips
    public function inputTips(Request $request)
    {
        $dtoRequest = new CommonInputTipsDTO($request->all());
        $authUserId = $this->user()?->id;

        $list = [];

        switch ($dtoRequest->type) {
            case 'user':
                $userIdentifier = ConfigHelper::fresnsConfigByItemKey('user_identifier');

                if ($userIdentifier == 'uid') {
                    $userQuery = User::where('uid', 'like', "%$dtoRequest->key%");
                } else {
                    $userQuery = User::where('username', 'like', "%$dtoRequest->key%");
                }

                $users = $userQuery->orWhere('nickname', 'like', "%$dtoRequest->key%")->isEnabled()->limit(10)->get();

                if ($users) {
                    foreach ($users as $user) {
                        $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_USER, $user->id, $authUserId);

                        $item['fsid'] = ($userIdentifier == 'uid') ? $user->uid : $user->username;
                        $item['name'] = $user->nickname;
                        $item['image'] = $user->getUserAvatar();
                        $item['interaction'] = [
                            'likeStatus' => $interactionStatus['likeStatus'],
                            'dislikeStatus' => $interactionStatus['dislikeStatus'],
                            'followStatus' => $interactionStatus['followStatus'],
                            'blockStatus' => $interactionStatus['blockStatus'],
                            'note' => $interactionStatus['note'],
                        ];

                        $list[] = $item;
                    }
                }
                break;

            case 'hashtag':
                $hashtagQuery = Hashtag::where('name', 'like', "%$dtoRequest->key%")->isEnabled()->limit(10)->get();

                if ($hashtagQuery) {
                    foreach ($hashtagQuery as $hashtag) {
                        $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_HASHTAG, $hashtag->id, $authUserId);

                        $item['fsid'] = $hashtag->slug;
                        $item['name'] = $hashtag->name;
                        $item['image'] = FileHelper::fresnsFileUrlByTableColumn($hashtag->cover_file_id, $hashtag->cover_file_url);
                        $item['interaction'] = [
                            'likeStatus' => $interactionStatus['likeStatus'],
                            'dislikeStatus' => $interactionStatus['dislikeStatus'],
                            'followStatus' => $interactionStatus['followStatus'],
                            'blockStatus' => $interactionStatus['blockStatus'],
                            'note' => $interactionStatus['note'],
                        ];

                        $list[] = $item;
                    }
                }
                break;
        }

        return $this->success($list);
    }

    // callback
    public function callback(Request $request)
    {
        $dtoRequest = new CommonCallbacksDTO($request->all());

        $callback = PluginHelper::fresnsPluginCallback($dtoRequest->fskey, $dtoRequest->ulid);

        if ($callback['code']) {
            throw new ApiException($callback['code']);
        }

        return $this->success($callback['data']);
    }

    // cmd word
    public function cmdWord(Request $request)
    {
        $dtoRequest = new CommonCmdWordDTO($request->all());

        $fskey = $dtoRequest->fskey;
        $wordName = $dtoRequest->cmdWord;
        $wordBody = $dtoRequest->wordBody ?? [];

        $commandWords = ConfigHelper::fresnsConfigByItemKey('interface_command_words');

        $filtered = array_filter($commandWords, function ($item) use ($fskey, $wordName) {
            return $item['fskey'] == $fskey && $item['cmdWord'] == $wordName;
        });

        $cmdWordArr = array_values($filtered);

        if (empty($cmdWordArr)) {
            throw new ApiException(32100);
        }

        $fresnsResp = \FresnsCmdWord::plugin($fskey)->$wordName($wordBody);

        return $fresnsResp->getOrigin();
    }
}
