<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsCmd;

use App\Fresns\Api\Center\Base\BasePlugin;
use App\Fresns\Api\Center\Common\ErrorCodeService;
use App\Fresns\Api\Center\Common\GlobalService;
use App\Fresns\Api\Center\Common\LogService;
use App\Fresns\Api\Center\Helper\CmdRpcHelper;
use App\Fresns\Api\FsDb\FresnsAccountConnects\FresnsAccountConnects;
use App\Fresns\Api\FsDb\FresnsAccountConnects\FresnsAccountConnectsConfig;
use App\Fresns\Api\FsDb\FresnsAccounts\FresnsAccounts;
use App\Fresns\Api\FsDb\FresnsAccounts\FresnsAccountsConfig;
use App\Fresns\Api\FsDb\FresnsAccounts\FresnsAccountsService;
use App\Fresns\Api\FsDb\FresnsAccountWallets\FresnsAccountWallets;
use App\Fresns\Api\FsDb\FresnsCommentAppends\FresnsCommentAppendsConfig;
use App\Fresns\Api\FsDb\FresnsCommentLogs\FresnsCommentLogsConfig;
use App\Fresns\Api\FsDb\FresnsComments\FresnsComments;
use App\Fresns\Api\FsDb\FresnsComments\FresnsCommentsConfig;
use App\Fresns\Api\FsDb\FresnsComments\FresnsCommentsService;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigs;
use App\Fresns\Api\FsDb\FresnsDomainLinks\FresnsDomainLinksConfig;
use App\Fresns\Api\FsDb\FresnsDomains\FresnsDomains;
use App\Fresns\Api\FsDb\FresnsExtendLinkeds\FresnsExtendLinkedsConfig;
use App\Fresns\Api\FsDb\FresnsExtends\FresnsExtendsConfig;
use App\Fresns\Api\FsDb\FresnsFileAppends\FresnsFileAppendsConfig;
use App\Fresns\Api\FsDb\FresnsFiles\FresnsFiles;
use App\Fresns\Api\FsDb\FresnsFiles\FresnsFilesConfig;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroups;
use App\Fresns\Api\FsDb\FresnsHashtagLinkeds\FresnsHashtagLinkedsConfig;
use App\Fresns\Api\FsDb\FresnsHashtags\FresnsHashtags;
use App\Fresns\Api\FsDb\FresnsLanguages\FresnsLanguagesConfig;
use App\Fresns\Api\FsDb\FresnsMentions\FresnsMentionsConfig;
use App\Fresns\Api\FsDb\FresnsPostAllows\FresnsPostAllowsConfig;
use App\Fresns\Api\FsDb\FresnsPostAppends\FresnsPostAppendsConfig;
use App\Fresns\Api\FsDb\FresnsPostLogs\FresnsPostLogsConfig;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPosts;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPostsConfig;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPostsService;
use App\Fresns\Api\FsDb\FresnsSessionKeys\FresnsSessionKeys;
use App\Fresns\Api\FsDb\FresnsSessionLogs\FresnsSessionLogs;
use App\Fresns\Api\FsDb\FresnsSessionLogs\FresnsSessionLogsConfig;
use App\Fresns\Api\FsDb\FresnsSessionLogs\FresnsSessionLogsService;
use App\Fresns\Api\FsDb\FresnsSessionTokens\FresnsSessionTokensConfig;
use App\Fresns\Api\FsDb\FresnsUserRoles\FresnsUserRoles;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsersConfig;
use App\Fresns\Api\FsDb\FresnsUserStats\FresnsUserStats;
use App\Fresns\Api\FsDb\FresnsVerifyCodes\FresnsVerifyCodes;
use App\Fresns\Api\Helpers\ApiCommonHelper;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Helpers\SignHelper;
use App\Fresns\Api\Helpers\StrHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

class FresnsCmdWords extends BasePlugin
{
    // Constructors
    public function __construct()
    {
        $this->pluginConfig = new FresnsCmdWordsConfig();
        $this->pluginCmdHandlerMap = FresnsCmdWordsConfig::FRESNS_CMD_HANDLE_MAP;
    }

    // Get Status Code
    public function getCodeMap()
    {
        return FresnsCmdWordsConfig::CODE_MAP;
    }

    // Verify the verification code
    public function checkCodeHandler($input)
    {
        $type = $input['type'];
        $account = $input['account'];
        $verifyCode = $input['verifyCode'];
        $countryCode = $input['countryCode'];
        // type: 1.email / 2.sms
        if ($type == 1) {
            $where = [
                'type' => $type,
                'account' => $account,
                'code' => $verifyCode,
                'is_enable' => 1,
            ];
        } else {
            $where = [
                'type' => $type,
                'account' => $countryCode.$account,
                'code' => $verifyCode,
                'is_enable' => 1,
            ];
        }
        // Is the verification code valid
        $verifyInfo = FresnsVerifyCodes::where($where)->where('expired_at', '>', date('Y-m-d H:i:s'))->first();
        if ($verifyInfo) {
            FresnsVerifyCodes::where('id', $verifyInfo['id'])->update(['is_enable' => 0]);

            return $this->pluginSuccess();
        } else {
            return $this->pluginError(ErrorCodeService::VERIFY_CODE_CHECK_ERROR);
        }
    }

    // Submit content into the main form (post and comment)
    public function directReleaseContentHandler($input)
    {
        $type = $input['type'];
        $logId = $input['logId'];
        $sessionLogsId = $input['sessionLogsId'];
        $commentCid = $input['commentCid'] ?? 0;
        $FresnsPostsService = new FresnsPostsService();
        $fresnsCommentService = new FresnsCommentsService();
        switch ($type) {
            case 1:
                $result = $FresnsPostsService->releaseByDraft($logId, $sessionLogsId);
                // $postId = FresnsPostLogs::find($logId);
                // $cmd = FresnsSubPluginConfig::FRESNS_CMD_SUB_ACTIVE_COMMAND_WORD;
                // $input = [
                //     'tableName' => 'posts',
                //     'insertId' => $postId['post_id'],
                //     'commandWord' => 'fresns_cmd_direct_release_content',
                // ];
                // $resp = CmdRpcHelper::call(FresnsSubPlugin::class, $cmd, $input);
                break;
            case 2:
                $result = $fresnsCommentService->releaseByDraft($logId, $commentCid, $sessionLogsId);
                // $commentInfo = FresnsCommentLogs::find($logId);
                // $cmd = FresnsSubPluginConfig::FRESNS_CMD_SUB_ACTIVE_COMMAND_WORD;
                // $input = [
                //     'tableName' => 'comments',
                //     'insertId' => $commentInfo['comment_id'],
                //     'commandWord' => 'fresns_cmd_direct_release_content',
                // ];
                // $resp = CmdRpcHelper::call(FresnsSubPlugin::class, $cmd, $input);
                break;
        }

        return $this->pluginSuccess();
    }

    // Creating Token
    public function createSessionTokenHandler($input)
    {
        $uri = Request::getRequestUri();

        $accountId = $input['aid'];
        $userId = $input['uid'] ?? null;
        $platform = $input['platform'];

        $expiredTime = $input['expiredTime'] ?? null;
        if ($accountId) {
            $accountId = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('aid', $accountId)->value('id');
        }
        if ($userId) {
            $userId = DB::table(FresnsUsersConfig::CFG_TABLE)->where('uid', $userId)->value('id');
        }
        if (empty($userId)) {
            $tokenCount = DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('account_id', $accountId)->where('user_id', null)->where('platform_id', $platform)->count();
            $token = StrHelper::createToken();

            if ($tokenCount > 0) {
                DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('account_id', $accountId)->where('user_id', null)->where('platform_id', $platform)->delete();
            }
            $input = [];
            $input['platform_id'] = $platform;
            $input['account_id'] = $accountId;
            $input['token'] = $token;
            if ($expiredTime) {
                $input['expired_at'] = $expiredTime ?? null;
            }
            DB::table(FresnsSessionTokensConfig::CFG_TABLE)->insert($input);
        } else {
            $sessionToken = DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('account_id', $accountId)->where('user_id', $userId)->where('platform_id', $platform)->first();
            $token = StrHelper::createToken();
            if ($sessionToken) {
                DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('account_id', $accountId)->where('user_id', $userId)->where('platform_id', $platform)->delete();
            }
            $input = [];
            $input['token'] = $token;
            $input['platform_id'] = $platform;
            $input['account_id'] = $accountId;
            $input['user_id'] = $userId;
            if ($expiredTime) {
                $input['expired_at'] = $expiredTime ?? null;
            }

            DB::table(FresnsSessionTokensConfig::CFG_TABLE)->insert($input);
        }

        $data = [];
        $data['token'] = $token;
        $data['tokenExpiredTime'] = $expiredTime;

        return $this->pluginSuccess($data);
    }

    // Verify Token
    public function verifySessionTokenHandler($input)
    {
        $accountId = $input['aid'];
        $userId = $input['uid'] ?? null;
        $platform = $input['platform'];
        $token = $input['token'];
        $time = date('Y-m-d H:i:s', time());

        if ($accountId) {
            $accountId = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('aid', $accountId)->value('id');
        }
        if ($userId) {
            $userId = DB::table(FresnsUsersConfig::CFG_TABLE)->where('uid', $userId)->value('id');
        }

        if (empty($userId)) {
            // Verify Token
            $aidToken = DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('platform_id', $platform)->where('account_id', $accountId)->where('user_id', null)->first();

            if (empty($aidToken)) {
                return $this->pluginError(ErrorCodeService::ACCOUNT_TOKEN_ERROR);
            }

            if (! empty($aidToken->expired_at)) {
                if ($aidToken->expired_at < $time) {
                    return $this->pluginError(ErrorCodeService::ACCOUNT_TOKEN_ERROR);
                }
            }

            if ($aidToken->token != $token) {
                return $this->pluginError(ErrorCodeService::ACCOUNT_TOKEN_ERROR);
            }
        } else {
            // Verify Token
            $uidToken = DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('platform_id', $platform)->where('account_id', $accountId)->where('user_id', $userId)->first();
            if (empty($uidToken)) {
                return $this->pluginError(ErrorCodeService::USER_TOKEN_ERROR);
            }

            if (! empty($uidToken->expired_at)) {
                if ($uidToken->expired_at < $time) {
                    return $this->pluginError(ErrorCodeService::USER_TOKEN_ERROR);
                }
            }

            if ($uidToken->token != $token) {
                return $this->pluginError(ErrorCodeService::USER_TOKEN_ERROR);
            }
        }

        return $this->pluginSuccess();
    }

    // Upload log
    public function uploadSessionLogHandler($input)
    {
        $platform = $input['platform'];
        $version = $input['version'];
        $objectName = $input['objectName'];
        $objectAction = $input['objectAction'];
        $objectResult = $input['objectResult'];
        $objectType = $input['objectType'] ?? 1;
        $langTag = $input['langTag'] ?? null;
        $objectOrderId = $input['objectOrderId'] ?? null;
        $deviceInfo = $input['deviceInfo'] ?? null;
        $accountId = $input['aid'] ?? null;
        $userId = $input['uid'] ?? null;
        $moreJson = $input['moreJson'] ?? null;

        if ($accountId) {
            $accountId = FresnsAccounts::where('aid', $accountId)->value('id');
        }
        if ($userId) {
            $userId = FresnsUsers::where('uid', $userId)->value('id');
        }
        $input = [
            'platform_id' => $platform,
            'version' => $version,
            'lang_tag' => $langTag,
            'object_name' => $objectName,
            'object_action' => $objectAction,
            'object_result' => $objectResult,
            'object_order_id' => $objectOrderId,
            'device_info' => $deviceInfo,
            'account_id' => $accountId,
            'user_id' => $userId,
            'more_json' => $moreJson,
            'object_type' => $objectType,
        ];

        FresnsSessionLogs::insert($input);

        return $this->pluginSuccess();
    }

    /**
     * Delete official content (Logical Deletion)
     * type: 1.post / 2.comment
     * contentId: primary key ID
     * https://fresns.org/extensions/delete.html.
     */
    public function deleteContentHandler($input)
    {
        $type = $input['type'];
        $contentId = $input['content'];
        switch ($type) {
            case 1:
                /*
                 * post
                 * Step 1
                 * delete extend
                 */
                $input = ['linked_type' => 1, 'linked_id' => $contentId];
                $extendsLinksArr = DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where($input)->pluck('extend_id')->toArray();
                // Determine if an extend exists
                if (! empty($extendsLinksArr)) {
                    foreach ($extendsLinksArr as $e) {
                        $extendsLinksInfo = DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where('extend_id',
                            $e)->where('linked_type', 1)->where('linked_id', '!=', $contentId)->first();
                        // extend_linkeds: Whether the association is unique.
                        if (empty($extendsLinksInfo)) {
                            // Query whether the extension has attached files
                            $input = [
                                'table_type' => 10,
                                'table_name' => FresnsExtendsConfig::CFG_TABLE,
                                'table_column' => 'id',
                                'table_id' => $e,
                            ];
                            $extendFiles = FresnsFiles::where($input)->get(['id', 'fid', 'file_type'])->toArray();
                            // The queried file ID will be forwarded to the associated plugin with the file type, and the plugin will delete the physical files of the storage service provider.
                            if (! empty($extendFiles)) {
                                foreach ($extendFiles as $file) {
                                    $extendsFileId = $file['fid'];
                                    $extendsFileType = $file['file_type'];
                                    // Plugin handle logic.
                                    $cmd = FresnsCmdWordsConfig::FRESNS_CMD_PHYSICAL_DELETION_FILE;
                                    $input['fid'] = $extendsFileId;
                                    $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                                    // Delete file data records from both "files" + "file_appends" tables.
                                    DB::table(FresnsFileAppendsConfig::CFG_TABLE)->where('file_id', $file['id'])->delete();
                                }
                            }

                            // Delete the language table contents of the extend content
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'title')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'desc_primary')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'desc_secondary')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'btn_name')->where('table_id', $e)->delete();
                            // Delete the associated records in the "extend_linkeds" table
                            DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where('extend_id', $e)->where('linked_type', 1)->where('linked_id', '=', $contentId)->delete();
                            // Delete extends extended content records.
                            DB::table(FresnsExtendsConfig::CFG_TABLE)->where('id', $e)->delete();
                        } else {
                            DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where('linked_type', 1)->where('linked_id', $contentId)->where('extend_id', $e)->delete();
                        }
                    }
                }

                /*
                 * post
                 * Step 2
                 * Delete attached files
                 * Read the main table "posts > more_json > files" file list, plus all logs of the post "post_logs > files_json" file list, and perform bulk delete.
                 */
                $post = DB::table(FresnsPostsConfig::CFG_TABLE)->where('id', $contentId)->first();
                $postAppend = DB::table(FresnsPostAppendsConfig::CFG_TABLE)->where('post_id', $contentId)->first();
                // Get the post master form file
                $filesFidArr = [];
                if (! empty($post->more_json)) {
                    $postMoreJsonArr = json_decode($post->more_json, true);
                    if (! empty($postMoreJsonArr['files'])) {
                        foreach ($postMoreJsonArr['files'] as $v) {
                            $filesFidArr[] = $v['fid'];
                        }
                    }
                }
                // Get "post_logs" table file information
                $postLogsFiles = DB::table(FresnsPostLogsConfig::CFG_TABLE)->where('post_id',
                    $post->id)->pluck('files_json')->toArray();
                if (! empty($postLogsFiles)) {
                    foreach ($postLogsFiles as $v) {
                        $filesArr = json_decode($v, true);
                        if (! empty($filesArr)) {
                            foreach ($filesArr as $files) {
                                $filesFidArr[] = $files['fid'];
                            }
                        }
                    }
                }
                if ($filesFidArr) {
                    $filesFidArr = array_unique($filesFidArr);
                    $filesIdArr = DB::table(FresnsFilesConfig::CFG_TABLE)->whereIn('fid', $filesFidArr)->pluck('id')->toArray();
                    if ($filesIdArr) {
                        // Delete physical files
                        foreach ($filesIdArr as $v) {
                            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_PHYSICAL_DELETION_FILE;
                            $input['fid'] = $v;
                            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                        }
                        // Delete file data
                        DB::table(FresnsFilesConfig::CFG_TABLE)->whereIn('fid', $filesIdArr)->delete();
                        DB::table(FresnsFileAppendsConfig::CFG_TABLE)->whereIn('file_id', $filesIdArr)->delete();
                    }
                }

                /*
                 * post
                 * Step 3
                 * Remove parsing association
                 * Delete the mentions record.
                 */
                DB::table(FresnsMentionsConfig::CFG_TABLE)->where('linked_type', 1)->where('linked_id', $contentId)->delete();
                // Delete hashtag-related "hashtag_linkeds" records
                // Corresponding hashtag "hashtags > comment_count" field value -1
                $linkedArr = DB::table(FresnsHashtagLinkedsConfig::CFG_TABLE)->where('linked_id', $contentId)->where('linked_type', 1)->pluck('hashtag_id')->toArray();
                FresnsHashtags::whereIn('id', $linkedArr)->decrement('post_count');
                DB::table(FresnsHashtagLinkedsConfig::CFG_TABLE)->where('linked_id', $contentId)->where('linked_type', 1)->delete();
                // Delete hyperlinks "domain_links"
                // Corresponding domain "domains > post_count" field value -1
                $domainArr = DB::table(FresnsDomainLinksConfig::CFG_TABLE)->where('linked_id', $contentId)->where('linked_type', 1)->pluck('domain_id')->toArray();
                FresnsDomains::whereIn('id', $domainArr)->decrement('post_count');
                DB::table(FresnsDomainLinksConfig::CFG_TABLE)->where('linked_id', $contentId)->where('linked_type', 1)->delete();

                /*
                 * post
                 * Step 4
                 * Delete post affiliation form (language)
                 * Delete the fields "allow_btn_name", "comment_btn_name", and "user_list_name" from the posts table in the languages table.
                 */
                if ($postAppend) {
                    DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsPostsConfig::CFG_TABLE)->where('table_column', 'allow_btn_name')->where('table_id', $postAppend->id)->delete();
                    DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsPostsConfig::CFG_TABLE)->where('table_column', 'comment_btn_name')->where('table_id', $postAppend->id)->delete();
                    DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsPostsConfig::CFG_TABLE)->where('table_column', 'user_list_name')->where('table_id', $postAppend->id)->delete();
                }

                /*
                 * post
                 * Step 5
                 * Delete statistical values
                 */
                $groupPostCount = FresnsGroups::where('id', $post->group_id)->value('post_count');
                if ($groupPostCount > 0) {
                    FresnsGroups::where('id', $post->group_id)->decrement('post_count');
                }
                FresnsConfigs::where('item_key', 'posts_count')->decrement('item_value');

                /*
                 * post
                 * Step 6
                 * Delete all records of the "post_id" in the associated table "post_appends" + "post_allows" + "post_logs".
                 */
                DB::table(FresnsPostAppendsConfig::CFG_TABLE)->where('post_id', $contentId)->delete();
                DB::table(FresnsPostAllowsConfig::CFG_TABLE)->where('post_id', $contentId)->delete();
                DB::table(FresnsPostLogsConfig::CFG_TABLE)->where('post_id', $contentId)->delete();

                /*
                 * post
                 * Step 7
                 * Deletes the row from the "posts" table.
                 */
                DB::table(FresnsPostsConfig::CFG_TABLE)->where('id', $contentId)->delete();

                break;

            default:
                /*
                 * comment
                 * Step 1
                 * delete extend
                 */
                $input = ['linked_type' => 2, 'linked_id' => $contentId];
                $extendsLinksArr = DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where($input)->pluck('extend_id')->toArray();
                // Determine if an extend exists
                if (! empty($extendsLinksArr)) {
                    foreach ($extendsLinksArr as $e) {
                        $extendsLinksInfo = DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where('extend_id', $e)->where('linked_type', 2)->where('linked_id', '!=', $contentId)->first();
                        // extend_linkeds: Whether the association is unique.
                        if (empty($extendsLinksInfo)) {
                            // Query whether the extension has attached files
                            $input = [
                                'table_type' => 10,
                                'table_name' => FresnsExtendsConfig::CFG_TABLE,
                                'table_column' => 'id',
                                'table_id' => $e,
                            ];
                            $extendFiles = FresnsFiles::where($input)->get(['id', 'fid', 'file_type'])->toArray();
                            // The queried file ID will be forwarded to the associated plugin with the file type, and the plugin will delete the physical files of the storage service provider.
                            if (! empty($extendFiles)) {
                                foreach ($extendFiles as $file) {
                                    $extendsFileId = $file['fid'];
                                    $extendsFileType = $file['file_type'];
                                    // Plugin handle logic.
                                    $cmd = FresnsCmdWordsConfig::FRESNS_CMD_PHYSICAL_DELETION_FILE;
                                    $input['fid'] = $extendsFileId;
                                    $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);

                                    // Delete file data records from both "files" + "file_appends" tables.
                                    DB::table(FresnsFileAppendsConfig::CFG_TABLE)->where('file_id', $file['id'])->delete();
                                }
                            }

                            // Delete the language table contents of the extend content
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'title')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'desc_primary')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'desc_secondary')->where('table_id', $e)->delete();
                            DB::table(FresnsLanguagesConfig::CFG_TABLE)->where('table_name', FresnsExtendsConfig::CFG_TABLE)->where('table_column', 'btn_name')->where('table_id', $e)->delete();
                            // Delete the associated records in the "extend_linkeds" table
                            DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where('extend_id', $e)->where('linked_type', 2)->where('linked_id', '=', $contentId)->delete();
                            // Delete extends extended content records.
                            DB::table(FresnsExtendsConfig::CFG_TABLE)->where('id', $e)->delete();
                        } else {
                            DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where('linked_type', 2)->where('linked_id', $contentId)->where('extend_id', $e)->delete();
                        }
                    }
                }

                /*
                 * comment
                 * Step 2
                 * Delete attached files
                 */
                $comment = DB::table(FresnsCommentsConfig::CFG_TABLE)->where('id', $contentId)->first();
                $commentAppend = DB::table(FresnsCommentAppendsConfig::CFG_TABLE)->where('comment_id', $contentId)->first();
                // Get the comment master form file
                $filesFidArr = [];
                if (! empty($comment->more_json)) {
                    $commentMoreJsonArr = json_decode($comment->more_json, true);
                    if (! empty($commentMoreJsonArr['files'])) {
                        foreach ($commentMoreJsonArr['files'] as $v) {
                            $filesFidArr[] = $v['fid'];
                        }
                    }
                }
                // Get "comment_logs" table file information
                $commentLogsFiles = DB::table(FresnsCommentLogsConfig::CFG_TABLE)->where('comment_id',
                    $comment->id)->pluck('files_json')->toArray();
                if (! empty($commentLogsFiles)) {
                    foreach ($commentLogsFiles as $v) {
                        $filesArr = json_decode($v, true);
                        if (! empty($filesArr)) {
                            foreach ($filesArr as $files) {
                                $filesFidArr[] = $files['fid'];
                            }
                        }
                    }
                }
                if ($filesFidArr) {
                    $filesFidArr = array_unique($filesFidArr);
                    $filesIdArr = DB::table(FresnsFilesConfig::CFG_TABLE)->whereIn('fid', $filesFidArr)->pluck('id')->toArray();
                    if ($filesFidArr) {
                        // Delete physical files
                        foreach ($filesFidArr as $v) {
                            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_PHYSICAL_DELETION_FILE;
                            $input['fid'] = $v;
                            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                        }
                        // Delete files
                        DB::table(FresnsFilesConfig::CFG_TABLE)->whereIn('id', $filesIdArr)->delete();
                        DB::table(FresnsFileAppendsConfig::CFG_TABLE)->whereIn('file_id', $filesIdArr)->delete();
                    }
                }

                /*
                 * comment
                 * Step 3
                 * Remove parsing association
                 * Delete the mentions record.
                 */
                DB::table(FresnsMentionsConfig::CFG_TABLE)->where('linked_type', 2)->where('linked_id', $contentId)->delete();
                // Delete hashtag-related "hashtag_linkeds" records
                // Corresponding hashtag "hashtags > comment_count" field value -1
                $linkedArr = DB::table(FresnsHashtagLinkedsConfig::CFG_TABLE)->where('linked_id', $contentId)->where('linked_type', 2)->pluck('hashtag_id')->toArray();
                FresnsHashtags::whereIn('id', $linkedArr)->decrement('comment_count');
                DB::table(FresnsHashtagLinkedsConfig::CFG_TABLE)->where('linked_id', $contentId)->where('linked_type', 2)->delete();
                // Delete hyperlinks "domain_links"
                // Corresponding domain "domains > post_count" field value -1
                $domainArr = DB::table(FresnsDomainLinksConfig::CFG_TABLE)->where('linked_id', $contentId)->where('linked_type', 2)->pluck('domain_id')->toArray();
                FresnsDomains::whereIn('id', $domainArr)->decrement('comment_count');
                DB::table(FresnsDomainLinksConfig::CFG_TABLE)->where('linked_id', $contentId)->where('linked_type', 2)->delete();

                /*
                 * comment
                 * Step 4
                 * Delete post affiliation form (language)
                 */
                FresnsComments::where('id', $comment->parent_id)->decrement('comment_count');
                FresnsComments::where('id', $comment->parent_id)->decrement('comment_like_count', $comment->like_count);
                FresnsPosts::where('id', $comment->post_id)->decrement('comment_count');
                FresnsPosts::where('id', $comment->post_id)->decrement('comment_like_count', $comment->like_count);
                FresnsConfigs::where('item_key', 'comments_count')->decrement('item_value');

                /*
                 * comment
                 * Step 5
                 * Delete all records of the "comment_id" in the "comment_appends" + "comment_logs" table
                 */
                DB::table(FresnsCommentAppendsConfig::CFG_TABLE)->where('comment_id', $contentId)->delete();
                DB::table(FresnsCommentLogsConfig::CFG_TABLE)->where('comment_id', $contentId)->delete();

                /*
                 * comment
                 * Step 6
                 * Deletes the row from the "comments" table.
                 */
                DB::table(FresnsCommentsConfig::CFG_TABLE)->where('id', $contentId)->delete();

                break;
        }

        return $this->pluginSuccess();
    }

    // Verify Sign
    public function verifySignHandler($input)
    {
        $platform = $input['platform'];
        $version = $input['version'] ?? null;
        $appId = $input['appId'];
        $timestamp = $input['timestamp'];
        $sign = $input['sign'];
        $aid = $input['aid'] ?? null;
        $uid = $input['uid'] ?? null;
        $token = $input['token'] ?? null;

        $dataMap['platform'] = $platform;
        if ($version) {
            $dataMap['version'] = $version;
        }
        $dataMap['appId'] = $appId;
        $dataMap['timestamp'] = $timestamp;
        if ($aid) {
            $dataMap['aid'] = $aid;
        }
        if ($uid) {
            $dataMap['uid'] = $uid;
        }
        if ($token) {
            $dataMap['token'] = $token;
        }
        $dataMap['sign'] = $sign;

        // Header Signature Expiration Date
        $min = 5; //Expiration time limit (unit: minutes)
        //Determine the timestamp type
        $timestampNum = strlen($timestamp);
        if ($timestampNum == 10) {
            $now = time();
            $expiredMin = $min * 60;
        } else {
            $now = intval(microtime(true) * 1000);
            $expiredMin = $min * 60 * 1000;
        }

        if ($now - $timestamp > $expiredMin) {
            return $this->pluginError(ErrorCodeService::HEADER_SIGN_EXPIRED);
        }
        LogService::info('Tips: ', $dataMap);
        $signKey = FresnsSessionKeys::where('app_id', $appId)->value('app_secret');

        $checkSignRes = SignHelper::checkSign($dataMap, $signKey);
        if ($checkSignRes !== true) {
            $info = [
                'sign' => $checkSignRes,
            ];

            return $this->pluginError(ErrorCodeService::HEADER_SIGN_ERROR, $info);
        }

        return $this->pluginSuccess();
    }

    public function accountRegisterHandler($inputData)
    {
        $type = $inputData['type'];
        $account = $inputData['account'];
        $countryCode = $inputData['countryCode'] ?? null;
        $connectInfo = $inputData['connectInfo'] ?? null;
        $password = $inputData['password'] ?? null;
        $nickname = $inputData['nickname'];
        $avatarFid = $inputData['avatarFid'] ?? null;
        $avatarUrl = $inputData['avatarUrl'] ?? null;
        $gender = $inputData['gender'] ?? 0;
        $birthday = $inputData['birthday'] ?? null;
        $timezone = $inputData['timezone'] ?? null;
        $language = $inputData['language'] ?? null;

        // If the connectInfo parameter is passed, check if the connectToken exists
        $connectInfoArr = [];
        if ($connectInfo) {
            $connectInfoArr = json_decode($connectInfo, true);
            $connectTokenArr = [];
            foreach ($connectInfoArr as $v) {
                $connectTokenArr[] = $v['connectToken'];
            }

            $count = DB::table(FresnsAccountConnectsConfig::CFG_TABLE)->whereIn('connect_token', $connectTokenArr)->count();
            if ($count > 0) {
                return $this->pluginError(ErrorCodeService::CONNECT_TOKEN_ERROR);
            }
        }

        $input = [];
        // Verify successful account creation
        switch ($type) {
            case 1:
                $input = [
                    'email' => $account,
                ];
                break;
            case 2:
                $input = [
                    'country_code' => $countryCode,
                    'pure_phone' => $account,
                    'phone' => $countryCode.$account,
                ];
                break;
            default:
                // code...
                break;
        }
        $accountAid = StrHelper::createFsid();
        $input['aid'] = $accountAid;
        $input['last_login_at'] = date('Y-m-d H:i:s');
        if ($password) {
            $input['password'] = StrHelper::createPassword($password);
        }

        $aid = FresnsAccounts::insertGetId($input);
        // FresnsSubPluginService::addSubTablePluginItem(FresnsAccountsConfig::CFG_TABLE, $aid);

        $fileId = null;
        if ($avatarFid) {
            $fileId = FresnsFiles::where('fid', $avatarFid)->value('id');
        }

        $userInput = [
            'account_id' => $aid,
            'username' => StrHelper::createToken(rand(6, 8)),
            'nickname' => $nickname,
            'uid' => ApiCommonHelper::createUserUid(),
            'avatar_file_id' => $fileId,
            'avatar_file_url' => $avatarUrl,
            'gender' => $gender,
            'birthday' => $birthday,
            'timezone' => $timezone,
            'language' => $language,
        ];

        $uid = FresnsUsers::insertGetId($userInput);
        // FresnsSubPluginService::addSubTablePluginItem(FresnsUsersConfig::CFG_TABLE, $uid);

        $langTag = request()->header('langTag');

        if ($type == 1) {
            // Add Counts
            $accountCounts = ApiConfigHelper::getConfigByItemKey('accounts_count');
            if ($accountCounts === null) {
                $input = [
                    'item_key' => 'accounts_count',
                    'item_value' => 1,
                    'item_tag' => 'stats',
                    'item_type' => 'number',
                ];
                FresnsConfigs::insert($input);
            } else {
                FresnsConfigs::where('item_key', 'accounts_count')->update(['item_value' => $accountCounts + 1]);
            }
            $userCounts = ApiConfigHelper::getConfigByItemKey('users_count');
            if ($userCounts === null) {
                $input = [
                    'item_key' => 'users_count',
                    'item_value' => 1,
                    'item_tag' => 'stats',
                    'item_type' => 'number',
                ];
                FresnsConfigs::insert($input);
            } else {
                FresnsConfigs::where('item_key', 'users_count')->update(['item_value' => $userCounts + 1]);
            }
        }

        // Register successfully to add records to the table
        $userStatsInput = [
            'user_id' => $uid,
        ];
        FresnsUserStats::insert($userStatsInput);
        $accountWalletsInput = [
            'account_id' => $aid,
            'balance' => 0,
        ];
        FresnsAccountWallets::insert($accountWalletsInput);
        $defaultRoleId = ApiConfigHelper::getConfigByItemKey('default_role');
        $userRolesInput = [
            'user_id' => $uid,
            'role_id' => $defaultRoleId,
            'is_main' => 1,
        ];
        FresnsUserRoles::insert($userRolesInput);

        // If the connectInfo parameter is passed, add it to the account_connects table
        if ($connectInfoArr) {
            $itemArr = [];
            foreach ($connectInfoArr as $info) {
                $item = [];
                $item['account_id'] = $aid;
                $item['connect_id'] = $info['connectId'];
                $item['connect_token'] = $info['connectToken'];
                $item['connect_name'] = $info['connectName'];
                $item['connect_nickname'] = $info['connectNickname'];
                $item['connect_avatar'] = $info['connectAvatar'];
                $item['plugin_unikey'] = 'fresns_cmd_account_register';
                $itemArr[] = $item;
            }

            FresnsAccountConnects::insert($itemArr);
        }

        $sessionId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionId) {
            FresnsSessionLogsService::updateSessionLogs($sessionId, 2, $aid, $uid, $aid);
        }

        $service = new FresnsAccountsService();
        $data = $service->getAccountDetail($aid, $langTag, $uid);

        return $this->pluginSuccess($data);
    }

    public function accountLoginHandler($input)
    {
        $type = $input['type'];
        $account = $input['account'];
        $countryCode = $input['countryCode'];
        $verifyCode = $input['verifyCode'];
        $passwordBase64 = $input['password'];

        if ($passwordBase64) {
            $password = base64_decode($passwordBase64, true);
            if ($password == false) {
                $password = $passwordBase64;
            }
        } else {
            $password = null;
        }

        switch ($type) {
            case 1:
                $account = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('email', $account)->first();
                break;
            case 2:
                $account = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('phone', $countryCode.$account)->first();
                break;
            default:
                // code...
                break;
        }

        $sessionLogId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionLogId) {
            $sessionInput = [
                'object_order_id' => $account->id,
                'account_id' => $account->id,
            ];
            FresnsSessionLogs::where('id', $sessionLogId)->update($sessionInput);
        }

        // Check the account of login password errors in the last 1 hour for the account to whom the email or cell phone number belongs.
        // If it reaches 5 times, the login will be restricted.
        // session_logs > object_type=3
        $startTime = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $sessionCount = FresnsSessionLogs::where('created_at', '>=', $startTime)
        ->where('account_id', $account->id)
        ->where('object_result', FresnsSessionLogsConfig::OBJECT_RESULT_ERROR)
        ->where('object_type', FresnsSessionLogsConfig::OBJECT_TYPE_ACCOUNT_LOGIN)
        ->count();

        if ($sessionCount >= 5) {
            return $this->pluginError(ErrorCodeService::ACCOUNT_COUNT_ERROR);
        }
        // One of the password or verification code is required
        if (empty($password) && empty($verifyCode)) {
            return $this->pluginError(ErrorCodeService::ACCOUNT_VERIFY_ERROR);
        }

        $time = date('Y-m-d H:i:s', time());
        if ($type != 3) {
            if ($verifyCode) {
                switch ($type) {
                    case 1:
                        $codeArr = FresnsVerifyCodes::where('type', $type)->where('account',
                            $account)->where('expired_at', '>', $time)->pluck('code')->toArray();
                        break;
                    case 2:
                        $codeArr = FresnsVerifyCodes::where('type', $type)->where('account',
                            $countryCode.$account)->where('expired_at', '>', $time)->pluck('code')->toArray();
                        break;
                    default:
                        // code...
                        break;
                }

                if (! in_array($verifyCode, $codeArr)) {
                    return $this->pluginError(ErrorCodeService::VERIFY_CODE_CHECK_ERROR);
                }
            }

            if ($password) {
                if (! Hash::check($password, $account->password)) {
                    return $this->pluginError(ErrorCodeService::ACCOUNT_PASSWORD_INVALID);
                }
            }
        }

        if ($account->is_enable == 0) {
            return $this->pluginError(ErrorCodeService::ACCOUNT_IS_ENABLE_ERROR);
        }
        $langTag = request()->header('langTag');
        $service = new FresnsAccountsService();
        $data = $service->getAccountDetail($account->id, $langTag);
        // Update the last_login_at field in the accounts table
        FresnsAccounts::where('id', $account->id)->update(['last_login_at' => date('Y-m-d H:i:s', time())]);

        $sessionId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionId) {
            FresnsSessionLogsService::updateSessionLogs($sessionId, 2, $account->id, null, $account->id);
        }

        return $this->pluginSuccess($data);
    }

    public function accountDetailHandler($input)
    {
        $aid = $input['aid'];
        $aid = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('aid', $aid)->value('id');

        $langTag = request()->header('langTag');
        $service = new FresnsAccountsService();
        $data = $service->getAccountDetail($aid, $langTag);

        return $this->pluginSuccess($data);
    }
}
