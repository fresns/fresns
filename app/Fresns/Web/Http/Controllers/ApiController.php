<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Controllers;

use App\Fresns\Web\Exceptions\ErrorException;
use App\Fresns\Web\Helpers\ApiHelper;
use App\Fresns\Web\Helpers\DataHelper;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    // top list
    public function topList()
    {
        return DataHelper::getTopList();
    }

    // url sign
    public function urlSign()
    {
        $headers = Arr::except(ApiHelper::getHeaders(), ['Accept']);

        $sign = urlencode(base64_encode(json_encode($headers)));

        return \response()->json([
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'sign' => $sign,
            ],
        ]);
    }

    // get input tips
    public function getInputTips(Request $request): JsonResponse
    {
        if ($request->get('type') && $request->get('key')) {
            $result = ApiHelper::make()->get('/api/v2/common/input-tips', [
                'query' => [
                    'type' => $request->get('type'),
                    'key' => $request->get('key'),
                ],
            ]);

            if ($result['code'] !== 0) {
                throw new ErrorException($result['message'], $result['code']);
            }

            return Response::json($result['data']);
        }

        return Response::json();
    }

    // get archives
    public function getArchives(Request $request, string $type)
    {
        $response = ApiHelper::make()->get("/api/v2/global/{$type}/archives", [
            'query' => [
                'unikey' => $request->get('unikey'),
            ],
        ]);

        return \response()->json($response);
    }

    // send verify code
    public function sendVerifyCode(Request $request)
    {
        if (\request('useType') == 4) {
            \request()->offsetSet('account', 'fresns_random_string:'.uniqid());
        }

        if (empty(\request('countryCode'))) {
            \request()->offsetSet('countryCode', fs_account()->get('detail.countryCode'));
        }

        if (empty(\request('phone'))) {
            \request()->offsetSet('phone', fs_account()->get('detail.phone'));
        }

        $response = ApiHelper::make()->post('/api/v2/common/send-verify-code', [
            'json' => \request()->all(),
        ]);

        return \response()->json($response);
    }

    // upload file
    public function uploadFile()
    {
        $multipart = [];

        foreach (\request()->file() as $name => $file) {
            if ($file instanceof UploadedFile) {
                /** @var UploadedFile $file */
                $multipart[] = [
                    'name' => $name,
                    'filename' => $file->getClientOriginalName(),
                    'contents' => $file->getContent(),
                    'headers' => ['Content-Type' => $file->getClientMimeType()],
                ];
            }
        }

        foreach (\request()->post() as $name => $contents) {
            $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
            $multipart[] = compact('name', 'contents', 'headers');
        }

        $response = ApiHelper::make()->post('/api/v2/common/upload-file', [
            'multipart' => $multipart,
        ]);

        return \response()->json($response);
    }

    // account register
    public function accountRegister(Request $request)
    {
        $response = ApiHelper::make()->post('/api/v2/account/register', [
            'json' => [
                'type' => $request->type,
                'account' => $request->{$request->type},
                'countryCode' => $request->countryCode ?? null,
                'verifyCode' => $request->verifyCode,
                'password' => $request->password,
                'nickname' => $request->nickname,
                'deviceToken' => $request->deviceToken ?? null,
            ],
        ]);

        return \response()->json($response);
    }

    // account login
    public function accountLogin(Request $request)
    {
        $result = ApiHelper::make()->post('/api/v2/account/login', [
            'json' => [
                'type' => $request->type,
                'account' => $request->{$request->type},
                'countryCode' => $request->countryCode ?? null,
                'password' => $request->password ?? null,
                'verifyCode' => $request->verifyCode ?? null,
                'deviceToken' => $request->deviceToken ?? null,
            ],
        ]);

        if ($result['code'] != 0) {
            return back()->with([
                'code' => $result['code'],
                'failure' => $result['message'],
            ]);
        }

        // api data
        $data = $result['data'];

        // Account Login
        Cookie::queue('fs_aid', $data['detail']['aid']);
        Cookie::queue('fs_aid_token', $data['sessionToken']['token']);

        // Number of users under the account
        $users = $data['detail']['users'];
        $userCount = count($users);

        // Only one user and no password
        if ($userCount == 1) {
            $user = $users[0];

            if ($user['hasPassword']) {
                // User has password
                // user-auth.blade.php

                if ($request->wantsJson()) {
                    return \response()->json([
                        'code' => 0,
                        'message' => 'success',
                        'data' => [
                            'prev_url' => fs_route(route('fresns.account.login')),
                        ],
                    ]);
                }

                return redirect()->intended(fs_route(route('fresns.account.login')));
            } else {
                // User does not have a password
                \request()->offsetSet('fs_aid', $data['detail']['aid']);
                \request()->offsetSet('fs_aid_token', $data['sessionToken']['token']);

                $userResult = ApiHelper::make()->post('/api/v2/user/auth', [
                    'json' => [
                        'uidOrUsername' => strval($user['uid']),
                        'password' => null,
                        'deviceToken' => $request->deviceToken ?? null,
                    ],
                ]);

                Cookie::queue('fs_uid', data_get($userResult, 'data.detail.uid'));
                Cookie::queue('fs_uid_token', data_get($userResult, 'data.sessionToken.token'));
                Cookie::queue('timezone', data_get($userResult, 'data.detail.timezone'));

                if ($request->wantsJson()) {
                    return \response()->json([
                        'code' => 0,
                        'message' => 'success',
                        'data' => [
                            'prev_url' => fs_route(route('fresns.account.index')),
                        ],
                    ]);
                }

                return redirect()->intended(fs_route(route('fresns.account.index')));
            }
        } elseif ($userCount > 1) {
            // There are more than one user
            // user-auth.blade.php

            if ($request->wantsJson()) {
                return \response()->json([
                    'code' => 0,
                    'message' => 'success',
                    'data' => [
                        'prev_url' => fs_route(route('fresns.account.login')),
                    ],
                ]);
            }

            return redirect()->intended(fs_route(route('fresns.account.login')));
        }
    }

    // account reset password
    public function accountResetPassword(Request $request)
    {
        if (\request('password') !== \request('password_confirmation')) {
            return \response()->json([
                'code' => 30000,
                'message' => fs_api_config('passwordAgainError'),
                'data' => null,
            ]);
        }

        $response = ApiHelper::make()->put('/api/v2/account/reset-password', [
            'json' => [
                'type' => $request->type,
                'account' => $request->{$request->type},
                'countryCode' => $request->countryCode ?? null,
                'verifyCode' => $request->verifyCode ?? null,
                'newPassword' => $request->password ?? null,
            ],
        ]);

        return \response()->json($response);
    }

    // account verify identity
    public function accountVerifyIdentity(Request $request)
    {
        $response = ApiHelper::make()->post('/api/v2/account/verify-identity', [
            'json' => \request()->all(),
        ]);

        return \response()->json($response);
    }

    // account edit
    public function accountEdit()
    {
        if ($editType = \request('edit_type')) {
            $editTypeMode = \request($editType.'_mode');

            $codeType = match ($editTypeMode) {
                default => null,
                'phone_to_editPassword' => 'sms',
                'email_to_editPassword' => 'email',
            };

            $verifyCode = match ($editTypeMode) {
                default => null,
                'phone_to_editPassword' => \request('phone_verifyCode'),
                'email_to_editPassword' => \request('email_verifyCode'),
            };

            \request()->offsetSet('codeType', $codeType);
            \request()->offsetSet('verifyCode', $verifyCode);
        }

        switch ($editType) {
            case 'editPassword':
                \request()->offsetSet('password', \request('now_editPassword'));
                \request()->offsetSet('editPassword', \request('new_editPassword'));
                \request()->offsetSet('editPasswordConfirm', \request('new_editPassword_confirmation'));
                break;
            case 'editWalletPassword':
                \request()->offsetSet('editWalletPassword', \request('new_editWalletPassword'));
                \request()->offsetSet('editWalletPasswordConfirm', \request('new_editWalletPassword_confirmation'));
                break;
        }

        $response = ApiHelper::make()->put('/api/v2/account/edit', [
            'json' => \request()->all(),
        ]);

        return \response()->json($response);
    }

    // user auth
    public function userAuth(Request $request)
    {
        $result = ApiHelper::make()->post('/api/v2/user/auth', [
            'json' => [
                'uidOrUsername' => $request->uidOrUsername,
                'password' => $request->password ?? null,
                'deviceToken' => $request->deviceToken ?? null,
            ],
        ]);

        if ($result['code'] != 0) {
            throw new ErrorException($result['message'], $result['code']);
        }

        Cookie::queue('fs_uid', $result['data']['detail']['uid']);
        Cookie::queue('fs_uid_token', $result['data']['sessionToken']['token']);
        Cookie::queue('timezone', $result['data']['detail']['timezone']);

        if ($request->wantsJson()) {
            return \response()->json([
                'code' => 0,
                'message' => 'success',
                'data' => [
                    'prev_url' => fs_route(route('fresns.account.index')),
                ],
            ]);
        }

        return redirect()->intended(fs_route(route('fresns.account.index')));
    }

    // user edit
    public function userEdit()
    {
        $response = ApiHelper::make()->put('/api/v2/user/edit', [
            'json' => \request()->all(),
        ]);

        return \response()->json($response);
    }

    // user mark
    public function userMark(Request $request)
    {
        $response = ApiHelper::make()->post('/api/v2/user/mark', [
            'json' => \request()->all(),
        ]);

        return \response()->json($response);
    }

    // message mark-as-read
    public function messageMarkAsRead(Request $request, string $type)
    {
        $response = ApiHelper::make()->put("/api/v2/{$type}/mark-as-read", [
            'json' => \request()->all(),
        ]);

        return \response()->json($response);
    }

    // message delete
    public function messageDelete(Request $request, string $type)
    {
        $response = ApiHelper::make()->delete("/api/v2/{$type}/delete", [
            'json' => \request()->all(),
        ]);

        return \response()->json($response);
    }

    // send message
    public function messageSend(Request $request)
    {
        $response = ApiHelper::make()->post('/api/v2/conversation/send-message', [
            'json' => \request()->all(),
        ]);

        return \response()->json($response);
    }

    // content type
    public function contentType()
    {
        $response = ApiHelper::make()->get('/api/v2/global/content-type');

        return \response()->json($response);
    }

    // content download file
    public function contentFileLink(Request $request, $fid)
    {
        $response = ApiHelper::make()->get("/api/v2/common/file/{$fid}/link", [
            'query' => [
                'type' => $request->get('type'),
                'fsid' => $request->get('fsid'),
            ],
        ]);

        return \response()->json($response);
    }

    // content download users
    public function contentFileUsers(Request $request, $fid)
    {
        $response = ApiHelper::make()->get("/api/v2/common/file/{$fid}/users", [
            'query' => [
                'pageSize' => 30,
                'page' => 1,
            ],
        ]);

        return \response()->json($response);
    }

    // content delete
    public function contentDelete(string $type, string $fsid)
    {
        $type = match ($type) {
            'posts' => 'post',
            'comments' => 'comment',
            'post' => 'post',
            'comment' => 'comment',
            default => 'post',
        };

        $response = ApiHelper::make()->delete("/api/v2/{$type}/{$fsid}");

        return \response()->json($response);
    }

    /**
     * @param  string  $gid
     * @return JsonResponse
     *
     * @throws GuzzleException
     */
    public function groupList(string $gid): JsonResponse
    {
        $response = ApiHelper::make()->get('/api/v2/group/list', [
            'query' => [
                'gid' => $gid,
                'pageSize' => request()->get('pageSize'),
                'page' => request()->get('page'),
            ],
        ]);

        return Response::json(data_get($response, 'data', []));
    }

    // quick publish
    public function editorQuickPublish(Request $request, string $type)
    {
        $validator = Validator::make($request->post(),
            [
                'content' => 'required',
                'postGid' => ($type === 'post' && fs_api_config('post_editor_group_required')) ? 'required' : 'nullable',
                'postTitle' => ($type === 'post' && fs_api_config('post_editor_title_required')) ? 'required' : 'nullable',
                'commentPid' => ($type === 'comment') ? 'required' : 'nullable',
            ]
        );

        if ($validator->fails()) {
            return Response::json(['message' => $validator->errors()->all()[0], 'code' => 400]);
        }

        $multipart = [
            [
                'name' => 'postGid',
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'contents' => $request->post('postGid'),
            ],
            [
                'name' => 'postTitle',
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'contents' => $request->post('postTitle'),
            ],
            [
                'name' => 'content',
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'contents' => $request->post('content'),
            ],
            [
                'name' => 'isAnonymous',
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'contents' => (bool) $request->post('anonymous', false),
            ],
            [
                'name' => 'commentPid',
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'contents' => $request->post('commentPid'),
            ],
            [
                'name' => 'commentCid',
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'contents' => $request->post('commentCid'),
            ],
        ];
        if ($request->file('file')) {
            $multipart[] = [
                'name' => 'file',
                'filename' => $request->file('file')->getClientOriginalName(),
                'contents' => $request->file('file')->getContent(),
                'headers' => ['Content-Type' => $request->file('file')->getClientMimeType()],
            ];
        }

        $result = ApiHelper::make()->post("/api/v2/editor/{$type}/quick-publish", [
            'multipart' => array_filter($multipart, fn ($val) => isset($val['contents'])),
        ]);

        return Response::json($result);
    }

    // editor upload file
    public function editorUploadFile(Request $request)
    {
        $multipart = [
            [
                'name' => 'type',
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'contents' => $request->post('type'),
            ],
            [
                'name' => 'usageType',
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'contents' => $request->post('usageType'),
            ],
            [
                'name' => 'tableId',
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'contents' => $request->post('tableId'),
            ],
            [
                'name' => 'uploadMode',
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'contents' => $request->post('uploadMode'),
            ],
            [
                'name' => 'tableName',
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'contents' => $request->post('tableName'),
            ],
            [
                'name' => 'tableColumn',
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'contents' => $request->post('tableColumn', 'id'),
            ],
        ];

        if (! $request->file('files')) {
            return Response::json([]);
        }

        $postAsyncs = [];
        foreach ($request->file('files') as $key => $file) {
            $params = $multipart;
            $params[] = [
                'name' => 'file',
                'filename' => $file->getClientOriginalName(),
                'contents' => $file->getContent(),
                'headers' => ['Content-Type' => $file->getClientMimeType()],
            ];
            $postAsyncs[] = ApiHelper::make()->postAsync('/api/v2/common/upload-file', [
                'multipart' => array_filter($params, fn ($val) => isset($val['contents'])),
            ]);
        }

        $results = ApiHelper::make()->unwrapRequests($postAsyncs);

        $data = [];
        foreach ($results as $result) {
            if (data_get($result, 'code') !== 0) {
                return Response::json($result);
            }
            $data[] = data_get($result, 'data');
        }

        return Response::json(['data' => $data, 'code' => 0]);
    }

    // editor update
    public function editorUpdate(Request $request, string $type, int $draftId)
    {
        $params = [
            'postGid' => $request->post('postGid'),
            'postTitle' => $request->post('postTitle'),
            'postIsComment' => $request->post('postIsComment'),
            'postIsCommentPublic' => $request->post('postIsCommentPublic'),
            'content' => $request->post('content'),
            'isMarkdown' => $request->post('isMarkdown'),
            'isAnonymous' => $request->post('isAnonymous'),
            'mapJson' => $request->post('mapJson'),
            'deleteMap' => $request->post('deleteMap'),
            'deleteFile' => $request->post('deleteFile'),
            'deleteExtend' => $request->post('deleteExtend'),
        ];
        $response = ApiHelper::make()->put("/api/v2/editor/{$type}/{$draftId}", [
            'json' => array_filter($params, fn ($val) => isset($val)),
        ]);

        return \response()->json($response);
    }

    // editor publish
    public function editorPublish(string $type, string $draftId)
    {
        $type = match ($type) {
            'posts' => 'post',
            'comments' => 'comment',
            'post' => 'post',
            'comment' => 'comment',
            default => 'post',
        };

        $response = ApiHelper::make()->post("/api/v2/editor/{$type}/{$draftId}");

        return \response()->json($response);
    }

    // editor recall
    public function editorRecall(string $type, string $draftId)
    {
        $type = match ($type) {
            'posts' => 'post',
            'comments' => 'comment',
            'post' => 'post',
            'comment' => 'comment',
            default => 'post',
        };

        $response = ApiHelper::make()->patch("/api/v2/editor/{$type}/{$draftId}");

        return \response()->json($response);
    }

    // editor delete
    public function editorDelete(string $type, string $draftId)
    {
        $type = match ($type) {
            'posts' => 'post',
            'comments' => 'comment',
            'post' => 'post',
            'comment' => 'comment',
            default => 'post',
        };

        $response = ApiHelper::make()->delete("/api/v2/editor/{$type}/{$draftId}");

        return \response()->json($response);
    }
}
