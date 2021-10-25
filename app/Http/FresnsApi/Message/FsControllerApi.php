<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Message;

use App\Http\Center\Common\ErrorCodeService;
use App\Http\Center\Common\GlobalService;
use App\Http\Center\Common\ValidateService;
use App\Http\FresnsApi\Base\FresnsBaseApiController;
use App\Http\FresnsApi\Content\FsConfig as ContentConfig;
use App\Http\FresnsApi\Helpers\ApiCommonHelper;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsApi\Helpers\ApiFileHelper;
use App\Http\FresnsDb\FresnsDialogMessages\FresnsDialogMessages;
use App\Http\FresnsDb\FresnsDialogMessages\FresnsDialogMessagesConfig;
use App\Http\FresnsDb\FresnsDialogMessages\FresnsDialogMessagesService;
use App\Http\FresnsDb\FresnsDialogs\FresnsDialogs;
use App\Http\FresnsDb\FresnsDialogs\FresnsDialogsConfig;
use App\Http\FresnsDb\FresnsDialogs\FresnsDialogsService;
use App\Http\FresnsDb\FresnsFiles\FresnsFiles;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollows;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRels;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRoles;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsMembers\FresnsMembersConfig;
use App\Http\FresnsDb\FresnsNotifies\FresnsNotifies;
use App\Http\FresnsDb\FresnsNotifies\FresnsNotifiesConfig;
use App\Http\FresnsDb\FresnsNotifies\FresnsNotifiesService;
use App\Http\FresnsDb\FresnsSessionLogs\FresnsSessionLogs;
use App\Http\FresnsDb\FresnsSessionLogs\FresnsSessionLogsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FsControllerApi extends FresnsBaseApiController
{
    // Get Notify List
    public function notifyLists(Request $request)
    {
        $uid = $this->uid;
        $member_id = $this->mid;
        $uid = $this->uid;
        if (empty($uid)) {
            $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
        }
        if (empty($member_id)) {
            $this->error(ErrorCodeService::MID_REQUIRED_ERROR);
        }
        $type = $request->input('type');
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 30);
        $uid = $this->uid;
        $member_id = GlobalService::getGlobalKey('member_id');
        $FresnsNotifiesService = new FresnsNotifiesService();
        $request->offsetSet('currentPage', $page);
        $request->offsetSet('pageSize', $pageSize);
        $request->offsetSet('member_id', $member_id);
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
        $uid = $this->uid;
        $member_id = $this->mid;
        $uid = $this->uid;
        if (empty($uid)) {
            $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
        }
        if (empty($member_id)) {
            $this->error(ErrorCodeService::MID_REQUIRED_ERROR);
        }
        $member_id = GlobalService::getGlobalKey('member_id');
        $type = $request->input('type');
        // Set all the notifications I received under this type to read.
        $system_count = FresnsNotifies::where('member_id', $member_id)->where('source_type', $type)->update(['status' => FsConfig::READED]);
        $this->success();
    }

    // Delete Notify
    public function notifyDelete(Request $request)
    {
        $rule = [
            'notifyId' => 'required|array',
        ];
        ValidateService::validateRule($request, $rule);
        $uid = $this->uid;
        $member_id = $this->mid;
        $uid = $this->uid;
        if (empty($uid)) {
            $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
        }
        if (empty($member_id)) {
            $this->error(ErrorCodeService::MID_REQUIRED_ERROR);
        }
        $member_id = GlobalService::getGlobalKey('member_id');
        $idArr = $request->input('notifyId');
        $result = self::isExsitMember($idArr, FresnsNotifiesConfig::CFG_TABLE, 'member_id', $member_id);
        if (! $result) {
            $this->error(ErrorCodeService::DELETE_NOTIFY_ERROR);
        }
        FresnsNotifies::whereIn('id', $idArr)->delete();
        $this->success();
    }

    // Get Dialog List
    public function dialogLists(Request $request)
    {
        $uid = $this->uid;
        $member_id = $this->mid;
        if (empty($uid)) {
            $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
        }
        if (empty($member_id)) {
            $this->error(ErrorCodeService::MID_REQUIRED_ERROR);
        }
        $member_id = GlobalService::getGlobalKey('member_id');
        // Query the set of dialog ids that the member is in
        $idArr_A = FresnsDialogs::where('a_member_id', $member_id)->where('a_is_display', 1)->pluck('id')->toArray();
        $idArr_B = FresnsDialogs::where('b_member_id', $member_id)->where('b_is_display', 1)->pluck('id')->toArray();
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
        $uid = $this->uid;
        $member_id = $this->mid;
        if (empty($uid)) {
            $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
        }
        if (empty($member_id)) {
            $this->error(ErrorCodeService::MID_REQUIRED_ERROR);
        }
        $mid = GlobalService::getGlobalKey('member_id');
        $dialogId = $request->input('dialogId');
        // Query the set of dialog ids that the member is in
        $send_member_idArr = FresnsDialogMessages::where('dialog_id', $dialogId)->where('send_member_id', $mid)->where('send_deleted_at', null)->pluck('id')->toArray();
        $recv_member_idArr = FresnsDialogMessages::where('dialog_id', $dialogId)->where('recv_member_id', $mid)->where('recv_deleted_at', null)->pluck('id')->toArray();
        $idArr = array_merge($send_member_idArr, $recv_member_idArr);
        $ids = implode(',', $idArr);
        // Get whether the membership is A or B
        $dialogsInfo = FresnsDialogs::where('id', $dialogId)->first();
        if ($dialogsInfo['a_member_id'] == $mid) {
            $member_id = $dialogsInfo['b_member_id'];
        } else {
            if ($dialogsInfo['b_member_id'] != $mid) {
                $this->error(ErrorCodeService::DIALOG_ERROR);
            }
            $member_id = $dialogsInfo['a_member_id'];
        }
        $memberInfo = DB::table(FresnsMembersConfig::CFG_TABLE)->where('id', $member_id)->first();
        $member = [];
        $member['deactivate'] = false;
        $member['mid'] = '';
        $member['mname'] = '';
        $member['nickname'] = '';
        $member['avatar'] = $memberInfo->avatar_file_url ?? '';
        // Default Avatar
        if (empty($member['avatar'])) {
            $defaultIcon = ApiConfigHelper::getConfigByItemKey(ContentConfig::DEFAULT_AVATAR);
            $member['avatar'] = $defaultIcon;
        }
        // Deactivate Avatar
        if ($memberInfo->deleted_at != null) {
            $deactivateAvatar = ApiConfigHelper::getConfigByItemKey(ContentConfig::DEACTIVATE_AVATAR);
            $member['avatar'] = $deactivateAvatar;
        }
        $member['avatar'] = ApiFileHelper::getImageAvatarUrl($member['avatar']);
        $member['decorate'] = '';
        $member['verifiedStatus'] = '';
        $member['verifiedIcon'] = '';
        $member['verifiedDesc'] = '';
        if ($memberInfo) {
            if ($memberInfo->deleted_at == null) {
                $member['deactivate'] = true;
                $member['mid'] = $memberInfo->uuid;
                $member['mname'] = $memberInfo->name;
                $member['nickname'] = $memberInfo->nickname;
                $member['decorate'] = ApiFileHelper::getImageSignUrlByFileIdUrl($memberInfo->decorate_file_id, $memberInfo->decorate_file_url);
                $member['verifiedStatus'] = $memberInfo->verified_status;
                $member['verifiedIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($memberInfo->verified_file_id, $memberInfo->verified_file_url);
                $member['verifiedDesc'] = $memberInfo->verified_desc;
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
            'member' => $member,
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
        $mid = GlobalService::getGlobalKey('member_id');
        $dialogId = $request->input('dialogId');
        // Whether the dialog is owned by the member
        $aCount = FresnsDialogs::where('a_member_id', $mid)->where('id', $dialogId)->count();
        $bCount = FresnsDialogs::where('b_member_id', $mid)->where('id', $dialogId)->count();
        if ($aCount == 0 && $bCount == 0) {
            $this->error(ErrorCodeService::DIALOG_ERROR);
        }
        // Recipients-Reading time update
        FresnsDialogMessages::where('dialog_id', $dialogId)->where('recv_member_id', $mid)->update(['recv_read_at' => date('Y-m-d H:i:s')]);
        // dialogs status update
        $is_member_A = FresnsDialogs::where('a_member_id', $mid)->where('id', $dialogId)->count();
        if ($is_member_A > 0) {
            FresnsDialogs::where('id', $dialogId)->update(['a_status' => 2]);
        } else {
            FresnsDialogs::where('id', $dialogId)->update(['b_status' => 2]);
        }
        $this->success();
    }

    // Send Dialog Message
    public function sendMessage(Request $request)
    {
        $table = FresnsMembersConfig::CFG_TABLE;
        $mid = GlobalService::getGlobalKey('member_id');
        $rule = [
            'recvMid' => "required|exists:{$table},uuid|not_in:{$mid}",
            'message' => 'required_without:fid',
            'fid' => 'required_without:message',
        ];
        ValidateService::validateRule($request, $rule);
        // Validate submission parameters
        $checkInfo = FsChecker::checkSendMessage($mid);
        if (is_array($checkInfo)) {
            return $this->errorCheckInfo($checkInfo);
        }
        // Send
        $recvMid = $request->input('recvMid');
        $message = $request->input('message', null);
        $fid = $request->input('fid', null);
        if ($fid) {
            $filesInfo = FresnsFiles::Where('uuid', $fid)->first();
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
                    $file_type = 'doc';
                    break;
                default:
                    $file_type = 'image';
                    break;
            }
        }
        if ($message) {
            $message = ApiCommonHelper::messageStopWords($message);
            if (! $message) {
                $this->error(ErrorCodeService::DIALOG_WORD_ERROR);
            }
        }
        $recvMemberInfo = FresnsMembers::where('uuid', $recvMid)->first();
        $recvMid = $recvMemberInfo['id'];
        // Query the dialog id, if not, create a new one
        $input1 = [
            'a_member_id' => $mid,
            'b_member_id' => $recvMid,
        ];
        $dialogs = FresnsDialogs::where($input1)->first();
        if (! $dialogs) {
            $input2 = [
                'b_member_id' => $mid,
                'a_member_id' => $recvMid,
            ];
            $dialogs = FresnsDialogs::where($input2)->first();
            if (! $dialogs) {
                $input_dialogs = [
                    'a_member_id' => $mid,
                    'b_member_id' => $recvMid,
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
            'send_member_id' => $mid,
            'message_text' => $message,
            'file_id' => $fileId,
            'recv_member_id' => $recvMid,
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
        $count = FresnsDialogs::where('id', $dialogsId)->where('a_member_id', $mid)->count();
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
        $mid = GlobalService::getGlobalKey('member_id');
        $dialogId = $request->input('dialogId', '');
        $messageIdArr = $request->input('messageId', '');
        if ($dialogId) {
            if ($messageIdArr) {
                $this->error(ErrorCodeService::DIALOG_OR_MESSAGE_ERROR);
            }
            // Whether the dialog is owned by the member
            $aCount = FresnsDialogs::where('a_member_id', $mid)->where('id', $dialogId)->count();
            $bCount = FresnsDialogs::where('b_member_id', $mid)->where('id', $dialogId)->count();
            if ($aCount == 0 && $bCount == 0) {
                $this->error(ErrorCodeService::DIALOG_ERROR);
            }
            // Dialog Hide
            $count = FresnsDialogs::where('id', $dialogId)->where('a_member_id', $mid)->count();
            if ($count > 0) {
                FresnsDialogs::where('id', $dialogId)->update(['a_is_display' => 0]);
            } else {
                FresnsDialogs::where('id', $dialogId)->update(['b_is_display' => 0]);
            }
            // Delete Message List
            FresnsDialogMessages::where('dialog_id', $dialogId)->where('send_member_id', $mid)->update(['send_deleted_at' => date('Y-m-d H:i:s')]);
            FresnsDialogMessages::where('dialog_id', $dialogId)->where('recv_member_id', $mid)->update(['recv_deleted_at' => date('Y-m-d H:i:s')]);
            $this->success();
        }
        if ($messageIdArr) {
            foreach ($messageIdArr as $messageId) {
                // Determining whether a member is a sender or a recipient
                $count = FresnsDialogMessages::where('id', $messageId)->where('send_member_id', $mid)->count();
                $recvCount = FresnsDialogMessages::where('id', $messageId)->where('recv_member_id', $mid)->count();
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

    // Whether the data is owned by the member
    public static function isExsitMember($idArr, $table, $field, $field_value)
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
