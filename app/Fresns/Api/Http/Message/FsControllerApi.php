<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Message;

use App\Fresns\Api\Center\Common\ErrorCodeService;
use App\Fresns\Api\Center\Common\GlobalService;
use App\Fresns\Api\Center\Common\ValidateService;
use App\Fresns\Api\FsDb\FresnsDialogMessages\FresnsDialogMessages;
use App\Fresns\Api\FsDb\FresnsDialogMessages\FresnsDialogMessagesConfig;
use App\Fresns\Api\FsDb\FresnsDialogMessages\FresnsDialogMessagesService;
use App\Fresns\Api\FsDb\FresnsDialogs\FresnsDialogs;
use App\Fresns\Api\FsDb\FresnsDialogs\FresnsDialogsConfig;
use App\Fresns\Api\FsDb\FresnsDialogs\FresnsDialogsService;
use App\Fresns\Api\FsDb\FresnsFiles\FresnsFiles;
use App\Fresns\Api\FsDb\FresnsNotifies\FresnsNotifies;
use App\Fresns\Api\FsDb\FresnsNotifies\FresnsNotifiesConfig;
use App\Fresns\Api\FsDb\FresnsNotifies\FresnsNotifiesService;
use App\Fresns\Api\FsDb\FresnsSessionLogs\FresnsSessionLogs;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsersConfig;
use App\Fresns\Api\Helpers\ApiCommonHelper;
use App\Fresns\Api\Helpers\ApiFileHelper;
use App\Fresns\Api\Http\Base\FsApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FsControllerApi extends FsApiController
{
    // Get Notify List
    public function notifyLists(Request $request)
    {
        $aid = $this->aid;
        $user_id = $this->uid;
        if (empty($aid)) {
            $this->error(ErrorCodeService::AID_REQUIRED_ERROR);
        }
        if (empty($user_id)) {
            $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
        }
        $type = $request->input('type');
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 30);
        $user_id = GlobalService::getGlobalKey('user_id');
        $FresnsNotifiesService = new FresnsNotifiesService();
        $request->offsetSet('currentPage', $page);
        $request->offsetSet('pageSize', $pageSize);
        $request->offsetSet('user_id', $user_id);
        $FresnsNotifiesService->setResource(FresnsNotifiesResource::class);
        $list = $FresnsNotifiesService->searchData();
        $data = [
            'pagination' => $list['pagination'],
            'list' => $list['list'],
        ];
        $this->success($data);
    }

    // Update Notify Reading
    public function notifyRead(Request $request)
    {
        $rule = [
            'type' => 'required|in:1,2,3,4,5,6',
        ];
        ValidateService::validateRule($request, $rule);
        $aid = $this->aid;
        $user_id = $this->uid;
        if (empty($aid)) {
            $this->error(ErrorCodeService::AID_REQUIRED_ERROR);
        }
        if (empty($user_id)) {
            $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
        }
        $user_id = GlobalService::getGlobalKey('user_id');
        $type = $request->input('type');
        // Set all the notifications I received under this type to read.
        $system_count = FresnsNotifies::where('user_id', $user_id)->where('source_type', $type)->update(['status' => FsConfig::READED]);
        $this->success();
    }

    // Delete Notify
    public function notifyDelete(Request $request)
    {
        $rule = [
            'notifyId' => 'required|array',
        ];
        ValidateService::validateRule($request, $rule);
        $aid = $this->aid;
        $user_id = $this->uid;
        if (empty($aid)) {
            $this->error(ErrorCodeService::AID_REQUIRED_ERROR);
        }
        if (empty($user_id)) {
            $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
        }
        $user_id = GlobalService::getGlobalKey('user_id');
        $idArr = $request->input('notifyId');
        $result = self::isExsitUser($idArr, FresnsNotifiesConfig::CFG_TABLE, 'user_id', $user_id);
        if (! $result) {
            $this->error(ErrorCodeService::DELETE_NOTIFY_ERROR);
        }
        FresnsNotifies::whereIn('id', $idArr)->delete();
        $this->success();
    }

    // Get Dialog List
    public function dialogLists(Request $request)
    {
        $aid = $this->aid;
        $user_id = $this->uid;
        if (empty($aid)) {
            $this->error(ErrorCodeService::AID_REQUIRED_ERROR);
        }
        if (empty($user_id)) {
            $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
        }
        $user_id = GlobalService::getGlobalKey('user_id');
        // Query the set of dialog ids that the user is in
        $idArr_A = FresnsDialogs::where('a_user_id', $user_id)->where('a_is_display', 1)->pluck('id')->toArray();
        $idArr_B = FresnsDialogs::where('b_user_id', $user_id)->where('b_is_display', 1)->pluck('id')->toArray();
        $idArr = array_merge($idArr_A, $idArr_B);
        $ids = implode(',', $idArr);
        $page = $request->input('page', 1) ?? 1;
        $pageSize = $request->input('pageSize', 30) ?? 30;
        $FresnsDialogsService = new FresnsDialogsService();
        $request->offsetSet('currentPage', $page);
        $request->offsetSet('ids', $ids);
        $request->offsetSet('pageSize', $pageSize);
        $FresnsDialogsService->setResource(FresnsDialogsResource::class);
        $list = $FresnsDialogsService->searchData();
        $data = [
            'pagination' => $list['pagination'],
            'list' => $list['list'],
        ];
        $this->success($data);
    }

    // Get Dialog Message List
    public function dialogMessages(Request $request)
    {
        $table = FresnsDialogsConfig::CFG_TABLE;
        $rule = [
            'dialogId' => [
                'required',
                'numeric',
                "exists:{$table},id",
            ],
        ];
        ValidateService::validateRule($request, $rule);
        $aid = $this->aid;
        $user_id = $this->uid;
        if (empty($aid)) {
            $this->error(ErrorCodeService::AID_REQUIRED_ERROR);
        }
        if (empty($user_id)) {
            $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
        }
        $uid = GlobalService::getGlobalKey('user_id');
        $dialogId = $request->input('dialogId');
        // Query the set of dialog ids that the user is in
        $send_user_idArr = FresnsDialogMessages::where('dialog_id', $dialogId)->where('send_user_id', $uid)->where('send_deleted_at', null)->pluck('id')->toArray();
        $recv_user_idArr = FresnsDialogMessages::where('dialog_id', $dialogId)->where('recv_user_id', $uid)->where('recv_deleted_at', null)->pluck('id')->toArray();
        $idArr = array_merge($send_user_idArr, $recv_user_idArr);
        $ids = implode(',', $idArr);
        // Get whether the usership is A or B
        $dialogsInfo = FresnsDialogs::where('id', $dialogId)->first();
        if ($dialogsInfo['a_user_id'] == $uid) {
            $user_id = $dialogsInfo['b_user_id'];
        } else {
            if ($dialogsInfo['b_user_id'] != $uid) {
                $this->error(ErrorCodeService::DIALOG_ERROR);
            }
            $user_id = $dialogsInfo['a_user_id'];
        }
        $userInfo = DB::table(FresnsUsersConfig::CFG_TABLE)->where('id', $user_id)->first();
        $user = [];
        $user['deactivate'] = false;
        $user['uid'] = null;
        $user['username'] = null;
        $user['nickname'] = null;
        $user['avatar'] = ApiFileHelper::getUserAvatar($userInfo->uid);
        $user['decorate'] = null;
        $user['verifiedStatus'] = null;
        $user['verifiedIcon'] = null;
        $user['verifiedDesc'] = null;
        if ($userInfo) {
            if ($userInfo->deleted_at == null) {
                $user['deactivate'] = true;
                $user['uid'] = $userInfo->uid;
                $user['username'] = $userInfo->username;
                $user['nickname'] = $userInfo->nickname;
                $user['decorate'] = ApiFileHelper::getImageSignUrlByFileIdUrl($userInfo->decorate_file_id, $userInfo->decorate_file_url);
                $user['verifiedStatus'] = $userInfo->verified_status;
                $user['verifiedIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($userInfo->verified_file_id, $userInfo->verified_file_url);
                $user['verifiedDesc'] = $userInfo->verified_desc;
            }
        }

        $dialogId = $request->input('dialogId');
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 50);
        $FresnsDialogsService = new FresnsDialogMessagesService();
        $request->offsetSet('currentPage', $page);
        $request->offsetSet('ids', $ids);
        $request->offsetSet('pageSize', $pageSize);
        $FresnsDialogsService->setResource(FresnsDialogMessagesResource::class);
        $list = $FresnsDialogsService->searchData();
        $data = [
            'pagination' => $list['pagination'],
            'dialogId' => $dialogId,
            'user' => $user,
            'list' => $list['list'],
        ];
        $this->success($data);
    }

    // Update Dialog Message Reading
    public function readMessage(Request $request)
    {
        $table = FresnsDialogsConfig::CFG_TABLE;
        $rule = [
            'dialogId' => [
                'required',
                'numeric',
                "exists:{$table},id",
            ],
        ];
        ValidateService::validateRule($request, $rule);
        $uid = GlobalService::getGlobalKey('user_id');
        $dialogId = $request->input('dialogId');
        // Whether the dialog is owned by the user
        $aCount = FresnsDialogs::where('a_user_id', $uid)->where('id', $dialogId)->count();
        $bCount = FresnsDialogs::where('b_user_id', $uid)->where('id', $dialogId)->count();
        if ($aCount == 0 && $bCount == 0) {
            $this->error(ErrorCodeService::DIALOG_ERROR);
        }
        // Recipients-Reading time update
        FresnsDialogMessages::where('dialog_id', $dialogId)->where('recv_user_id', $uid)->update(['recv_read_at' => date('Y-m-d H:i:s')]);
        // dialogs status update
        $is_user_A = FresnsDialogs::where('a_user_id', $uid)->where('id', $dialogId)->count();
        if ($is_user_A > 0) {
            FresnsDialogs::where('id', $dialogId)->update(['a_status' => 2]);
        } else {
            FresnsDialogs::where('id', $dialogId)->update(['b_status' => 2]);
        }
        $this->success();
    }

    // Send Dialog Message
    public function sendMessage(Request $request)
    {
        $table = FresnsUsersConfig::CFG_TABLE;
        $uid = GlobalService::getGlobalKey('user_id');
        $rule = [
            'recvUid' => "required|exists:{$table},uid|not_in:{$uid}",
            'message' => 'required_without:fid',
            'fid' => 'required_without:message',
        ];
        ValidateService::validateRule($request, $rule);
        // Validate submission parameters
        $checkInfo = FsChecker::checkSendMessage($uid);
        if (is_array($checkInfo)) {
            return $this->errorCheckInfo($checkInfo);
        }
        // Send
        $recvUid = $request->input('recvUid');
        $message = $request->input('message', null);
        $fid = $request->input('fid', null);
        if ($fid) {
            $filesInfo = FresnsFiles::Where('fid', $fid)->first();
            if (! $filesInfo) {
                $this->error(ErrorCodeService::FILE_EXIST_ERROR);
            }
            $fileId = $filesInfo->id;
            $fileType = $filesInfo->type;
            $file_type = 'image';
            switch ($fileType) {
                case '2':
                    $file_type = 'video';
                    break;
                case '3':
                    $file_type = 'audio';
                    break;
                case '4':
                    $file_type = 'document';
                    break;
                default:
                    $file_type = 'image';
                    break;
            }
        }
        if ($message) {
            $message = ApiCommonHelper::messageBlockWords($message);
            if (! $message) {
                $this->error(ErrorCodeService::DIALOG_WORD_ERROR);
            }
        }
        $recvUserInfo = FresnsUsers::where('uid', $recvUid)->first();
        $recvUid = $recvUserInfo['id'];
        // Query the dialog id, if not, create a new one
        $input1 = [
            'a_user_id' => $uid,
            'b_user_id' => $recvUid,
        ];
        $dialogs = FresnsDialogs::where($input1)->first();
        if (! $dialogs) {
            $input2 = [
                'b_user_id' => $uid,
                'a_user_id' => $recvUid,
            ];
            $dialogs = FresnsDialogs::where($input2)->first();
            if (! $dialogs) {
                $input_dialogs = [
                    'a_user_id' => $uid,
                    'b_user_id' => $recvUid,
                ];
                $dialogsId = (new FresnsDialogs())->store($input_dialogs);
            } else {
                $dialogsId = $dialogs['id'];
            }
        } else {
            $dialogsId = $dialogs['id'];
        }
        // Insert dialog_messages table
        $fileId = $fileId ?? null;
        $input_message = [
            'dialog_id' => $dialogsId,
            'send_user_id' => $uid,
            'message_text' => $message,
            'file_id' => $fileId,
            'recv_user_id' => $recvUid,
        ];
        $messageId = (new FresnsDialogMessages())->store($input_message);
        $sessionLogId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionLogId) {
            FresnsSessionLogs::where('id', $sessionLogId)->update([
                'object_result' => FsConfig::OBJECT_SUCCESS,
                'object_order_id' => $messageId,
            ]);
        }
        // Update dialogs table
        $count = FresnsDialogs::where('id', $dialogsId)->where('a_user_id', $uid)->count();
        if ($count > 0) {
            if ($fid) {
                $update_input = [
                    'latest_message_id' => $messageId,
                    'latest_message_time' => date('Y-m-d H:i:s'),
                    'latest_message_brief' => "[{$file_type}]",
                    'b_status' => 1,
                    'b_is_display' => 1,
                    'a_is_display' => 1,
                ];
            } else {
                $update_input = [
                    'latest_message_id' => $messageId,
                    'latest_message_time' => date('Y-m-d H:i:s'),
                    'latest_message_brief' => $message,
                    'b_status' => 1,
                    'b_is_display' => 1,
                    'a_is_display' => 1,
                ];
            }
        } else {
            if ($fid) {
                $update_input = [
                    'latest_message_id' => $messageId,
                    'latest_message_time' => date('Y-m-d H:i:s'),
                    'latest_message_brief' => "[{$file_type}]",
                    'a_status' => 1,
                    'a_is_display' => 1,
                    'b_is_display' => 1,
                ];
            } else {
                $update_input = [
                    'latest_message_id' => $messageId,
                    'latest_message_time' => date('Y-m-d H:i:s'),
                    'latest_message_brief' => $message,
                    'a_status' => 1,
                    'a_is_display' => 1,
                    'b_is_display' => 1,
                ];
            }
        }
        FresnsDialogs::where('id', $dialogsId)->update($update_input);
        $this->success();
    }

    // Delete Dialog Message
    public function dialogDelete(Request $request)
    {
        $table = FresnsDialogsConfig::CFG_TABLE;
        $messageTable = FresnsDialogMessagesConfig::CFG_TABLE;
        $rule = [
            'dialogId' => [
                "exists:{$table},id",
                'required_without:messageId',
            ],
            'messageId' => [
                'array',
                'required_without:dialogId',
            ],
        ];
        ValidateService::validateRule($request, $rule);
        $uid = GlobalService::getGlobalKey('user_id');
        $dialogId = $request->input('dialogId', '');
        $messageIdArr = $request->input('messageId', '');
        if ($dialogId) {
            if ($messageIdArr) {
                $this->error(ErrorCodeService::DIALOG_OR_MESSAGE_ERROR);
            }
            // Whether the dialog is owned by the user
            $aCount = FresnsDialogs::where('a_user_id', $uid)->where('id', $dialogId)->count();
            $bCount = FresnsDialogs::where('b_user_id', $uid)->where('id', $dialogId)->count();
            if ($aCount == 0 && $bCount == 0) {
                $this->error(ErrorCodeService::DIALOG_ERROR);
            }
            // Dialog Hide
            $count = FresnsDialogs::where('id', $dialogId)->where('a_user_id', $uid)->count();
            if ($count > 0) {
                FresnsDialogs::where('id', $dialogId)->update(['a_is_display' => 0]);
            } else {
                FresnsDialogs::where('id', $dialogId)->update(['b_is_display' => 0]);
            }
            // Delete Message List
            FresnsDialogMessages::where('dialog_id', $dialogId)->where('send_user_id', $uid)->update(['send_deleted_at' => date('Y-m-d H:i:s')]);
            FresnsDialogMessages::where('dialog_id', $dialogId)->where('recv_user_id', $uid)->update(['recv_deleted_at' => date('Y-m-d H:i:s')]);
            $this->success();
        }
        if ($messageIdArr) {
            foreach ($messageIdArr as $messageId) {
                // Determining whether a user is a sender or a recipient
                $count = FresnsDialogMessages::where('id', $messageId)->where('send_user_id', $uid)->count();
                $recvCount = FresnsDialogMessages::where('id', $messageId)->where('recv_user_id', $uid)->count();
                if ($count == 0 && $recvCount == 0) {
                    $this->error(ErrorCodeService::DELETE_NOTIFY_ERROR);
                }
                if ($count > 0) {
                    $dialogMessageCount = FresnsDialogMessages::where('id', $messageId)->where('send_deleted_at', '!=',
                        null)->count();
                    if ($dialogMessageCount > 0) {
                        $this->error(ErrorCodeService::DIALOG_MESSAGE_ERROR);
                    }
                    FresnsDialogMessages::where('id', $messageId)->update(['send_deleted_at' => date('Y-m-d H:i:s')]);
                } else {
                    $dialogMessageCount = FresnsDialogMessages::where('id', $messageId)->where('recv_deleted_at', '!=',
                        null)->count();
                    if ($dialogMessageCount > 0) {
                        $this->error(ErrorCodeService::DIALOG_MESSAGE_ERROR);
                    }
                    FresnsDialogMessages::where('id', $messageId)->update(['recv_deleted_at' => date('Y-m-d H:i:s')]);
                }
            }
            $this->success();
        }
    }

    // Whether the data is owned by the user
    public static function isExsitUser($idArr, $table, $field, $field_value)
    {
        if (! is_array($idArr)) {
            return false;
        }

        if (count($idArr) == 0) {
            return false;
        }
        foreach ($idArr as $id) {
            $queryCount = DB::table($table)->where('id', $id)->where($field, $field_value)->count();
            if ($queryCount == 0) {
                return false;
            }
        }

        return true;
    }
}
