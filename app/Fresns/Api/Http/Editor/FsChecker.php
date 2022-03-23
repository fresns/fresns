<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Editor;

use App\Fresns\Api\Base\Checkers\BaseChecker;
use App\Fresns\Api\Center\Common\ErrorCodeService;
use App\Fresns\Api\Center\Common\LogService;
use App\Fresns\Api\FsDb\FresnsAccounts\FresnsAccountsConfig;
use App\Fresns\Api\FsDb\FresnsBlockWords\FresnsBlockWords;
use App\Fresns\Api\FsDb\FresnsCommentLogs\FresnsCommentLogs;
use App\Fresns\Api\FsDb\FresnsComments\FresnsComments;
use App\Fresns\Api\FsDb\FresnsExtends\FresnsExtends;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroups;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroupsService;
use App\Fresns\Api\FsDb\FresnsPlugins\FresnsPlugins;
use App\Fresns\Api\FsDb\FresnsPostLogs\FresnsPostLogs;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPosts;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRoles;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRolesService;
use App\Fresns\Api\FsDb\FresnsUserRoles\FresnsUserRolesService;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsersConfig;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Helpers\StrHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class FsChecker extends BaseChecker
{
    /**
     * Verify post and comment permissions.
     *
     * @param [type] $type 1-post 2-comment
     * @param [update_type] $update_type 1-add 2-edit
     * @param [accountId] $accountId
     * @param [userId] $userId
     * @param [typeId] $typeId Post or comment id (update_type Must be passed when 2)
     * @return void
     */
    public static function checkPermission($type, $update_type, $accountId, $userId, $typeId = null)
    {
        $uri = Request::getRequestUri();

        $uriRuleArr = FsConfig::URI_NOT_IN_RULE;
        // Verify account and user status
        $account = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('id', $accountId)->first();
        $user = DB::table(FresnsUsersConfig::CFG_TABLE)->where('id', $userId)->first();
        $data = [];
        if (empty($account) || empty($user)) {
            return self::checkInfo(ErrorCodeService::ACCOUNT_ERROR);
        }
        switch ($type) {
            case 1:
                switch ($update_type) {
                    case 1:
                        // Verify user role permissions
                        $roleId = FresnsUserRolesService::getUserRoles($userId);

                        // Global checksum (post)
                        // Email, Phone number, Real name
                        $post_email_verify = ApiConfigHelper::getConfigByItemKey('post_email_verify');
                        if ($post_email_verify == true) {
                            if (empty($account->email)) {
                                return self::checkInfo(ErrorCodeService::PUBLISH_EMAIL_VERIFY_ERROR);
                            }
                        }
                        $post_phone_verify = ApiConfigHelper::getConfigByItemKey('post_phone_verify');
                        if ($post_phone_verify == true) {
                            if (empty($account->phone)) {
                                return self::checkInfo(ErrorCodeService::PUBLISH_PHONE_VERIFY_ERROR);
                            }
                        }
                        $post_prove_verify = ApiConfigHelper::getConfigByItemKey('post_prove_verify');
                        if ($post_prove_verify == true) {
                            if ($account->prove_verify == 1) {
                                return self::checkInfo(ErrorCodeService::PUBLISH_PROVE_VERIFY_ERROR);
                            }
                        }
                        $post_limit_status = ApiConfigHelper::getConfigByItemKey('post_limit_status');

                        // If the user master role is a whitelisted role, it is not subject to this permission requirement
                        if (in_array($uri, $uriRuleArr)) {
                            if ($post_limit_status == true) {
                                if (! empty($roleId)) {
                                    // Get a list of whitelisted roles
                                    $post_limit_whitelist = ApiConfigHelper::getConfigByItemKey('post_limit_whitelist');
                                    if (! empty($post_limit_whitelist)) {
                                        $post_limit_whitelist_arr = json_decode($post_limit_whitelist, true);
                                        if (in_array($roleId, $post_limit_whitelist_arr)) {
                                            $post_limit_status = false;
                                        }
                                    }
                                }
                            }
                        }

                        // Check Special Rules - Opening Hours
                        if ($post_limit_status === true) {
                            $post_limit_rule = ApiConfigHelper::getConfigByItemKey('post_limit_rule');
                            $post_limit_tip = ApiConfigHelper::getConfigByItemKey('post_limit_tip');
                            $post_limit_type = ApiConfigHelper::getConfigByItemKey('post_limit_type');
                            // 1.All-day limit on specified dates
                            if ($post_limit_type == 1) {
                                $post_limit_period_start = ApiConfigHelper::getConfigByItemKey('post_limit_period_start');
                                $post_limit_period_end = ApiConfigHelper::getConfigByItemKey('post_limit_period_end');

                                $time = date('Y-m-d H:i:s', time());

                                if ($post_limit_rule == 2) {
                                    if ($post_limit_period_start <= $time && $post_limit_period_end >= $time) {
                                        return self::checkInfo(ErrorCodeService::PUBLISH_LIMIT_ERROR);
                                    }
                                } else {
                                    if ($post_limit_period_start > $time || $post_limit_period_end < $time) {
                                        return self::checkInfo(ErrorCodeService::PUBLISH_LIMIT_ERROR);
                                    }
                                }
                            }
                            // 2.Specify a time period to set
                            if ($post_limit_type == 2) {
                                $post_limit_cycle_start = ApiConfigHelper::getConfigByItemKey('post_limit_cycle_start');
                                $post_limit_cycle_end = ApiConfigHelper::getConfigByItemKey('post_limit_cycle_end');
                                $post_limit_cycle_start = date('Y-m-d', time()).' '.$post_limit_cycle_start;
                                if ($post_limit_cycle_start < $post_limit_cycle_end) {
                                    $post_limit_cycle_end = date('Y-m-d', time()).' '.$post_limit_cycle_end;
                                } else {
                                    $post_limit_cycle_end = date('Y-m-d',
                                            strtotime('+1 day')).' '.$post_limit_cycle_end;
                                }

                                $time = date('Y-m-d H:i:s', time());

                                if ($post_limit_rule == 2) {
                                    if ($post_limit_cycle_start <= $time && $post_limit_cycle_end >= $time) {
                                        return self::checkInfo(ErrorCodeService::PUBLISH_LIMIT_ERROR);
                                    }
                                } else {
                                    if ($time < $post_limit_cycle_start || $time > $post_limit_cycle_end) {
                                        return self::checkInfo(ErrorCodeService::PUBLISH_LIMIT_ERROR);
                                    }
                                }
                            }
                        }

                        if (empty($roleId)) {
                            return self::checkInfo(ErrorCodeService::ROLE_NO_CONFIG_ERROR);
                        }

                        $permission = FresnsRoles::where('id', $roleId)->value('permission');
                        if ($permission) {
                            $permissionArr = json_decode($permission, true);
                            if ($permissionArr) {
                                $permissionMap = FresnsRolesService::getPermissionMap($permissionArr);

                                LogService::info('permissionMap-checkPermission', $permissionMap);
                                if (empty($permissionMap)) {
                                    return self::checkInfo(ErrorCodeService::ROLE_NO_CONFIG_ERROR);
                                }
                                // Publish Post Permissions
                                if ($permissionMap['post_publish'] == false) {
                                    return self::checkInfo(ErrorCodeService::ROLE_NO_PERMISSION_PUBLISH);
                                }
                                // Publish Post Request - Email
                                if ($permissionMap['post_email_verify'] == true) {
                                    if (empty($account->email)) {
                                        return self::checkInfo(ErrorCodeService::ROLE_PUBLISH_EMAIL_VERIFY);
                                    }
                                }
                                // Publish Post Request - Phone Number
                                if ($permissionMap['post_phone_verify'] == true) {
                                    if (empty($account->phone)) {
                                        return self::checkInfo(ErrorCodeService::ROLE_PUBLISH_PHONE_VERIFY);
                                    }
                                }
                                // Publish Post Request - Real name
                                if ($permissionMap['post_prove_verify'] == true) {
                                    if ($account->prove_verify == 1) {
                                        return self::checkInfo(ErrorCodeService::ROLE_PUBLISH_PROVE_VERIFY);
                                    }
                                }

                                if ($permissionMap['post_limit_status'] == true) {
                                    $post_limit_rule = $permissionMap['post_limit_rule'];
                                    if ($permissionMap['post_limit_type'] == 1) {
                                        $post_limit_period_start = $permissionMap['post_limit_period_start'];
                                        $post_limit_period_end = $permissionMap['post_limit_period_end'];
                                        $time = date('Y-m-d H:i:s', time());
                                        if ($post_limit_rule == 2) {
                                            if ($post_limit_period_start <= $time && $post_limit_period_end >= $time) {
                                                return self::checkInfo(ErrorCodeService::ROLE_PUBLISH_LIMIT);
                                            }
                                        } else {
                                            if ($post_limit_period_start > $time || $post_limit_period_end < $time) {
                                                return self::checkInfo(ErrorCodeService::ROLE_PUBLISH_LIMIT);
                                            }
                                        }
                                    }
                                    if ($permissionMap['post_limit_type'] == 2) {
                                        $post_limit_cycle_start = $permissionMap['post_limit_cycle_start'];
                                        $post_limit_cycle_end = $permissionMap['post_limit_cycle_end'];
                                        $post_limit_cycle_start = date('Y-m-d', time()).' '.$post_limit_cycle_start;
                                        if ($post_limit_cycle_start < $post_limit_cycle_end) {
                                            $post_limit_cycle_end = date('Y-m-d', time()).' '.$post_limit_cycle_end;
                                        } else {
                                            $post_limit_cycle_end = date('Y-m-d',
                                                    strtotime('+1 day')).' '.$post_limit_cycle_end;
                                        }
                                        $time = date('Y-m-d H:i:s', time());
                                        if ($post_limit_rule == 2) {
                                            if ($post_limit_cycle_start <= $time && $post_limit_cycle_end >= $time) {
                                                return self::checkInfo(ErrorCodeService::ROLE_PUBLISH_LIMIT);
                                            }
                                        } else {
                                            if ($time < $post_limit_cycle_start || $time > $post_limit_cycle_end) {
                                                return self::checkInfo(ErrorCodeService::ROLE_PUBLISH_LIMIT);
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            return self::checkInfo(ErrorCodeService::ROLE_NO_CONFIG_ERROR);
                        }

                        break;

                    default:
                        // How long to edit
                        $posts = FresnsPosts::where('id', $typeId)->first();
                        if (! $posts) {
                            return self::checkInfo(ErrorCodeService::POST_LOG_EXIST_ERROR);
                        }
                        // Whether the user_id and the editor of the post are the same person
                        if ($userId != $posts['user_id']) {
                            return self::checkInfo(ErrorCodeService::CONTENT_AUTHOR_ERROR);
                        }
                        $post_edit = ApiConfigHelper::getConfigByItemKey('post_edit');
                        if ($post_edit == false) {
                            return self::checkInfo(ErrorCodeService::POSTS_EDIT_ERROR);
                        }

                        $post_edit_timelimit = ApiConfigHelper::getConfigByItemKey('post_edit_timelimit');
                        $postTime = date('Y-m-d H:i:s', strtotime("+$post_edit_timelimit minutes", strtotime($posts['created_at'])));
                        $time = date('Y-m-d H:i:s', time());
                        if ($postTime < $time) {
                            return self::checkInfo(ErrorCodeService::EDIT_TIME_ERROR);
                        }
                        $post_edit_sticky = ApiConfigHelper::getConfigByItemKey('post_edit_sticky');
                        // Determine edit permissions after post topping
                        if ($posts['sticky_state'] != 1) {
                            if ($post_edit_sticky == false) {
                                return self::checkInfo(ErrorCodeService::EDIT_STICKY_ERROR);
                            }
                        }
                        $post_edit_digest = ApiConfigHelper::getConfigByItemKey('post_edit_digest');
                        // Determine the editing permissions after the post is refined
                        if ($posts['digest_state'] != 1) {
                            if ($post_edit_digest == false) {
                                return self::checkInfo(ErrorCodeService::EDIT_ESSENCE_ERROR);
                            }
                        }
                        break;
                }
                break;

            default:
                switch ($update_type) {
                    case 1:
                        $roleId = FresnsUserRolesService::getUserRoles($userId);
                        // Publish Comment Request - Email
                        $comment_email_verify = ApiConfigHelper::getConfigByItemKey('comment_email_verify');
                        if ($comment_email_verify == true) {
                            if (empty($account->email)) {
                                return self::checkInfo(ErrorCodeService::PUBLISH_EMAIL_VERIFY_ERROR);
                            }
                        }
                        // Publish Comment Request - Phone Number
                        $comment_phone_verify = ApiConfigHelper::getConfigByItemKey('comment_phone_verify');
                        if ($comment_phone_verify == true) {
                            if (empty($account->phone)) {
                                return self::checkInfo(ErrorCodeService::PUBLISH_PHONE_VERIFY_ERROR);
                            }
                        }
                        // Publish Comment Request - Real name
                        $comment_prove_verify = ApiConfigHelper::getConfigByItemKey('comment_prove_verify');
                        if ($comment_prove_verify == true) {
                            if ($account->prove_verify == 1) {
                                return self::checkInfo(ErrorCodeService::PUBLISH_PROVE_VERIFY_ERROR);
                            }
                        }

                        $comment_limit_status = ApiConfigHelper::getConfigByItemKey('comment_limit_status');
                        // If the user master role is a whitelisted role, it is not subject to this permission requirement
                        if (in_array($uri, $uriRuleArr)) {
                            if ($comment_limit_status == true) {
                                if (! empty($roleId)) {
                                    // Get a list of whitelisted roles
                                    $comment_limit_whitelist = ApiConfigHelper::getConfigByItemKey('comment_limit_whitelist');
                                    if (! empty($comment_limit_whitelist)) {
                                        $comment_limit_whitelist_arr = json_decode($comment_limit_whitelist, true);
                                        if (in_array($roleId, $comment_limit_whitelist_arr)) {
                                            $comment_limit_status = false;
                                        }
                                    }
                                }
                            }
                        }
                        // Check Special Rules - Opening Hours
                        if ($comment_limit_status == true) {
                            $comment_limit_rule = ApiConfigHelper::getConfigByItemKey('comment_limit_rule');
                            $comment_limit_tip = ApiConfigHelper::getConfigByItemKey('comment_limit_tip');
                            $comment_limit_type = ApiConfigHelper::getConfigByItemKey('comment_limit_type');
                            // 1.All-day limit on specified dates
                            if ($comment_limit_type == 1) {
                                $comment_limit_period_start = ApiConfigHelper::getConfigByItemKey('comment_limit_period_start');
                                $comment_limit_period_end = ApiConfigHelper::getConfigByItemKey('comment_limit_period_end');
                                $time = date('Y-m-d H:i:s', time());
                                if ($comment_limit_rule == 2) {
                                    if ($comment_limit_period_start <= $time && $comment_limit_period_end >= $time) {
                                        return self::checkInfo(ErrorCodeService::PUBLISH_LIMIT_ERROR);
                                    }
                                } else {
                                    if ($time < $comment_limit_period_start || $time > $comment_limit_period_end) {
                                        return self::checkInfo(ErrorCodeService::PUBLISH_LIMIT_ERROR);
                                    }
                                }
                            }
                            // 2.Specify a time period to set
                            if ($comment_limit_type == 2) {
                                $comment_limit_cycle_start = ApiConfigHelper::getConfigByItemKey('comment_limit_cycle_start');
                                $comment_limit_cycle_end = ApiConfigHelper::getConfigByItemKey('comment_limit_cycle_end');
                                $comment_limit_cycle_start = date('Y-m-d', time()).' '.$comment_limit_cycle_start;
                                if ($comment_limit_cycle_start < $comment_limit_cycle_end) {
                                    $post_limit_cycle_end = date('Y-m-d', time()).' '.$comment_limit_cycle_end;
                                } else {
                                    $post_limit_cycle_end = date('Y-m-d',
                                            strtotime('+1 day')).' '.$comment_limit_cycle_end;
                                }
                                $time = date('Y-m-d H:i:s', time());

                                if ($comment_limit_rule == 2) {
                                    if ($comment_limit_cycle_start <= $time && $comment_limit_cycle_end >= $time) {
                                        return self::checkInfo(ErrorCodeService::PUBLISH_LIMIT_ERROR);
                                    }
                                } else {
                                    if ($time < $comment_limit_cycle_start || $time > $comment_limit_cycle_end) {
                                        return self::checkInfo(ErrorCodeService::PUBLISH_LIMIT_ERROR);
                                    }
                                }
                            }
                        }
                        // Global checks pass, checks role permissions
                        if (empty($roleId)) {
                            return self::checkInfo(ErrorCodeService::ROLE_NO_CONFIG_ERROR);
                        }

                        $permission = FresnsRoles::where('id', $roleId)->value('permission');
                        if ($permission) {
                            $permissionArr = json_decode($permission, true);
                            $permissionMap = FresnsRolesService::getPermissionMap($permissionArr);
                            if (empty($permissionMap)) {
                                return self::checkInfo(ErrorCodeService::ROLE_NO_CONFIG_ERROR);
                            }

                            // Publish Comment Permissions
                            if ($permissionMap['comment_publish'] == false) {
                                return self::checkInfo(ErrorCodeService::ROLE_NO_PERMISSION_PUBLISH);
                            }

                            // Publish Comment Request - Email
                            if ($permissionMap['comment_email_verify'] == true) {
                                if (empty($account->email)) {
                                    return self::checkInfo(ErrorCodeService::ROLE_PUBLISH_EMAIL_VERIFY);
                                }
                            }
                            // Publish Comment Request - Phone Number
                            if ($permissionMap['comment_phone_verify'] == true) {
                                if (empty($account->phone)) {
                                    return self::checkInfo(ErrorCodeService::ROLE_PUBLISH_PHONE_VERIFY);
                                }
                            }
                            // Publish Comment Request - Real name
                            if ($permissionMap['comment_prove_verify'] == true) {
                                if ($account->prove_verify == 1) {
                                    return self::checkInfo(ErrorCodeService::ROLE_PUBLISH_PROVE_VERIFY);
                                }
                            }

                            // Check Special Rules - Opening Hours
                            if ($permissionMap['comment_limit_status'] == true) {
                                $comment_limit_rule = $permissionMap['comment_limit_rule'];
                                $comment_limit_type = $permissionMap['comment_limit_type'];
                                // 1.All-day limit on specified dates
                                if ($comment_limit_type == 1) {
                                    $comment_limit_period_start = $permissionMap['comment_limit_period_start'];
                                    $comment_limit_period_end = $permissionMap['comment_limit_period_end'];
                                    $time = date('Y-m-d H:i:s', time());
                                    if ($comment_limit_rule == 2) {
                                        if ($comment_limit_period_start <= $time && $comment_limit_period_end >= $time) {
                                            return self::checkInfo(ErrorCodeService::ROLE_PUBLISH_LIMIT);
                                        }
                                    } else {
                                        if ($time < $comment_limit_period_start || $time > $comment_limit_period_end) {
                                            return self::checkInfo(ErrorCodeService::ROLE_PUBLISH_LIMIT);
                                        }
                                    }
                                }
                                // 2.Specify a time period to set
                                if ($comment_limit_type == 2) {
                                    $comment_limit_cycle_start = $permissionMap['comment_limit_cycle_start'];
                                    $comment_limit_cycle_end = $permissionMap['comment_limit_cycle_end'];
                                    $comment_limit_cycle_start = date('Y-m-d', time()).' '.$comment_limit_cycle_start;
                                    if ($comment_limit_cycle_start < $comment_limit_cycle_end) {
                                        $post_limit_cycle_end = date('Y-m-d', time()).' '.$comment_limit_cycle_end;
                                    } else {
                                        $post_limit_cycle_end = date('Y-m-d',
                                                strtotime('+1 day')).' '.$comment_limit_cycle_end;
                                    }
                                    $time = date('Y-m-d H:i:s', time());

                                    if ($comment_limit_rule == 2) {
                                        if ($comment_limit_cycle_start <= $time && $comment_limit_cycle_end >= $time) {
                                            return self::checkInfo(ErrorCodeService::ROLE_PUBLISH_LIMIT);
                                        }
                                    } else {
                                        if ($time < $comment_limit_cycle_start || $time > $comment_limit_cycle_end) {
                                            return self::checkInfo(ErrorCodeService::ROLE_PUBLISH_LIMIT);
                                        }
                                    }
                                }
                            }
                        } else {
                            return self::checkInfo(ErrorCodeService::ROLE_PUBLISH_LIMIT);
                        }

                        break;

                    default:
                        // How long to edit
                        $comments = FresnsComments::where('id', $typeId)->first();
                        if (! $comments) {
                            return self::checkInfo(ErrorCodeService::COMMENT_LOG_EXIST_ERROR);
                        }
                        // Whether the user_id and the editor of the comment are the same person
                        if ($userId != $comments['user_id']) {
                            return self::checkInfo(ErrorCodeService::CONTENT_AUTHOR_ERROR);
                        }
                        $comment_edit = ApiConfigHelper::getConfigByItemKey('comment_edit');
                        if ($comment_edit == false) {
                            return self::checkInfo(ErrorCodeService::COMMENTS_EDIT_ERROR);
                        }

                        $comment_edit_timelimit = ApiConfigHelper::getConfigByItemKey('comment_edit_timelimit');
                        $commentsTime = date('Y-m-d H:i:s', strtotime("+$comment_edit_timelimit minutes", strtotime($comments['created_at'])));
                        $time = date('Y-m-d H:i:s', time());
                        if ($commentsTime < $time) {
                            return self::checkInfo(ErrorCodeService::EDIT_TIME_ERROR);
                        }
                        $comment_edit_sticky = ApiConfigHelper::getConfigByItemKey('comment_edit_sticky');
                        // Determine edit permissions after post topping
                        if ($comments['is_sticky'] == 1) {
                            if ($comment_edit_sticky == false) {
                                return self::checkInfo(ErrorCodeService::EDIT_STICKY_ERROR);
                            }
                        }
                        break;
                }
                break;
        }

        return true;
    }

    /**
     * Verify global config
     * 1.The posting or commenting requirement is false
     * 2.Special rules not turned on.
     *
     * @param [type] $type 1-post 2-comment
     * @return void
     */
    public static function checkGlobalSubmit($type)
    {
        switch ($type) {
            case 1:
                $post_email_verify = ApiConfigHelper::getConfigByItemKey('post_email_verify');
                $post_phone_verify = ApiConfigHelper::getConfigByItemKey('post_phone_verify');
                $post_prove_verify = ApiConfigHelper::getConfigByItemKey('post_prove_verify');
                $post_limit_status = ApiConfigHelper::getConfigByItemKey('post_limit_status');

                if ($post_email_verify == false && $post_phone_verify == false && $post_prove_verify == false && $post_limit_status == false) {
                    return false;
                }
                break;

            default:
                $comment_email_verify = ApiConfigHelper::getConfigByItemKey('comment_email_verify');
                $comment_phone_verify = ApiConfigHelper::getConfigByItemKey('comment_phone_verify');
                $comment_prove_verify = ApiConfigHelper::getConfigByItemKey('comment_prove_verify');
                $comment_limit_status = ApiConfigHelper::getConfigByItemKey('comment_limit_status');

                if ($comment_email_verify == false && $comment_phone_verify == false && $comment_prove_verify == false && $comment_limit_status == false) {
                    return false;
                }
                break;
        }

        return true;
    }

    /**
     * Check if the current post or comment needs to be moderated.
     *
     * @param [type] $type 1-post 2-comment
     * @param [uid] $uid
     */
    public static function checkAudit($type, $uid, $content)
    {
        $isCheck = false;
        $roleId = FresnsUserRolesService::getUserRoles($uid);
        // Global special rules configuration (Review requirements)
        if ($type == 1) {
            $post_limit_status = ApiConfigHelper::getConfigByItemKey('post_limit_status');
            if ($post_limit_status == true && $roleId > 0) {
                // Get a list of whitelisted roles
                $post_limit_whitelist = ApiConfigHelper::getConfigByItemKey('post_limit_whitelist');
                if (! empty($post_limit_whitelist)) {
                    $post_limit_whitelist_arr = json_decode($post_limit_whitelist, true);
                    if (in_array($roleId, $post_limit_whitelist_arr)) {
                        $post_limit_status = false;
                    }
                }
            }

            if ($post_limit_status == true) {
                $post_limit_rule = ApiConfigHelper::getConfigByItemKey('post_limit_rule');
                if ($post_limit_rule == 1) {
                    $isCheck = true;
                }
            }
            $contentBlockWord = self::blockWords($content);
            if ($contentBlockWord == 4) {
                $isCheck = true;
            }
        } else {
            $comment_limit_status = ApiConfigHelper::getConfigByItemKey('comment_limit_status');
            if ($comment_limit_status == true && $roleId > 0) {
                // Get a list of whitelisted roles
                $comment_limit_whitelist = ApiConfigHelper::getConfigByItemKey('comment_limit_whitelist');
                if (! empty($comment_limit_whitelist)) {
                    $comment_limit_whitelist_arr = json_decode($comment_limit_whitelist, true);
                    if (in_array($roleId, $comment_limit_whitelist_arr)) {
                        $comment_limit_status = false;
                    }
                }
            }

            if ($comment_limit_status == true) {
                $comment_limit_rule = ApiConfigHelper::getConfigByItemKey('comment_limit_rule');
                if ($comment_limit_rule == 1) {
                    $isCheck = true;
                }
            }
            $contentBlockWord = self::blockWords($content);
            if ($contentBlockWord == 4) {
                $isCheck = true;
            }
        }

        if ($roleId) {
            $permission = FresnsRoles::where('id', $roleId)->value('permission');
            if ($permission) {
                $permissionArr = json_decode($permission, true);
                if ($permissionArr) {
                    $permissionMap = FresnsRolesService::getPermissionMap($permissionArr);
                    if ($permissionMap) {
                        if ($type == 1) {
                            $postLimitReview = $permissionMap['post_review'];
                            if ($postLimitReview == true) {
                                $isCheck = true;
                            }
                        } else {
                            $commentLimitReview = $permissionMap['comment_review'];
                            if ($commentLimitReview == true) {
                                $isCheck = true;
                            }
                        }
                    }
                }
            }
        }

        return $isCheck;
    }

    /**
     * Update draft content validation.
     */
    public static function checkDrast($uid)
    {
        $request = request();
        $logType = $request->input('logType');
        $logId = $request->input('logId');
        $gid = $request->input('gid');
        $types = $request->input('types');
        $userListJson = $request->input('userListJson');
        $commentSetJson = $request->input('commentSetJson');
        $allowJson = $request->input('allowJson');
        $locationJson = $request->input('locationJson');
        $filesJson = $request->input('filesJson');
        $extendsJson = $request->input('extendsJson');
        if ($userListJson) {
            $userListJsonStatus = StrHelper::isJson($userListJson);
            if (! $userListJsonStatus) {
                return self::checkInfo(ErrorCodeService::USER_LIST_JSON_ERROR);
            }
        }
        if ($commentSetJson) {
            $commentSetJsonStatus = StrHelper::isJson($commentSetJson);
            if (! $commentSetJsonStatus) {
                return self::checkInfo(ErrorCodeService::COMMENT_SET_JSON_ERROR);
            }
        }
        if ($allowJson) {
            $allowJsonStatus = StrHelper::isJson($allowJson);
            if (! $allowJsonStatus) {
                return self::checkInfo(ErrorCodeService::ALLOW_JSON_ERROR);
            }
        }
        if ($locationJson) {
            $locationJsonStatus = StrHelper::isJson($locationJson);
            if (! $locationJsonStatus) {
                return self::checkInfo(ErrorCodeService::LOCATION_JSON_ERROR);
            }
        }
        if ($filesJson) {
            $filesJsonStatus = StrHelper::isJson($filesJson);
            if (! $filesJsonStatus) {
                return self::checkInfo(ErrorCodeService::FILES_JSON_ERROR);
            }
        }
        if ($extendsJson) {
            $extendsJsonStatus = StrHelper::isJson($extendsJson);
            if (! $extendsJsonStatus) {
                return self::checkInfo(ErrorCodeService::EXTENDS_JSON_ERROR);
            }
            $extends = json_decode($extendsJson, true);
            foreach ($extends as $e) {
                if (! isset($e['eid'])) {
                    return self::checkInfo(ErrorCodeService::EXTENDS_JSON_EID_ERROR);
                } else {
                    if (empty($e['eid'])) {
                        return self::checkInfo(ErrorCodeService::EXTENDS_JSON_EID_ERROR);
                    }
                }
            }
        }
        $title = $request->input('title');
        $content = $request->input('content');
        // pluginUnikey
        $pluginUnikey = $request->input('pluginUnikey');
        if ($pluginUnikey) {
            $pluginCount = FresnsPlugins::Where('unikey', $pluginUnikey)->where('is_enable', 1)->count();
            if ($pluginCount == 0) {
                return self::checkInfo(ErrorCodeService::PLUGINS_CLASS_ERROR);
            }
        }
        // Site mode verification
        $site_mode = ApiConfigHelper::getConfigByItemKey('site_mode');
        if ($site_mode == FsConfig::PRIVATE) {
            $userInfo = FresnsUsers::find($uid);
            if ($userInfo['expired_at'] && ($userInfo['expired_at'] <= date('Y-m-d H:i:s'))) {
                return self::checkInfo(ErrorCodeService::USER_EXPIRED_ERROR);
            }
        }
        switch ($logType) {
            case 1:
                $postLogs = FresnsPostLogs::where('id', $logId)->first();
                if (! $postLogs) {
                    return self::checkInfo(ErrorCodeService::POST_LOG_EXIST_ERROR);
                }
                // Editable or not
                if ($postLogs['state'] == 2) {
                    return self::checkInfo(ErrorCodeService::POST_STATE_2_ERROR);
                }
                if ($postLogs['state'] == 3) {
                    return self::checkInfo(ErrorCodeService::POST_STATE_3_ERROR);
                }
                // Check whether the gid is correct, including whether the right to post in the group, whether the group can post (group classification is not allowed)
                if (! empty($gid)) {
                    $group = FresnsGroups::where('gid', $gid)->first();
                    if (! ($group)) {
                        return self::checkInfo(ErrorCodeService::GROUP_EXIST_ERROR);
                    }
                    if ($group['type'] == 1) {
                        return self::checkInfo(ErrorCodeService::GROUP_TYPE_ERROR);
                    }
                }
                // Judgment word limit
                if ($content) {
                    // Get the maximum number of words in a post
                    $postEditorWordCount = ApiConfigHelper::getConfigByItemKey(FsConfig::POST_EDITOR_WORD_COUNT) ?? 1000;
                    if (mb_strlen(trim($content)) > $postEditorWordCount) {
                        return self::checkInfo(ErrorCodeService::CONTENT_COUNT_ERROR);
                    }
                }
                break;
            default:
                $commentLogs = FresnsCommentLogs::where('id', $logId)->first();
                if (! $commentLogs) {
                    return self::checkInfo(ErrorCodeService::COMMENT_LOG_EXIST_ERROR);
                }
                // Editable or not
                if ($commentLogs['state'] == 2) {
                    return self::checkInfo(ErrorCodeService::COMMENT_STATE_2_ERROR);
                }
                if ($commentLogs['state'] == 3) {
                    return self::checkInfo(ErrorCodeService::COMMENT_STATE_3_ERROR);
                }
                if ($content) {
                    // Get the maximum number of words in a comment
                    $commentEditorWordCount = ApiConfigHelper::getConfigByItemKey(FsConfig::COMMENT_EDITOR_WORD_COUNT) ?? 1000;
                    if (mb_strlen(trim($content)) > $commentEditorWordCount) {
                        return self::checkInfo(ErrorCodeService::CONTENT_COUNT_ERROR);
                    }
                }
                break;
        }
        // The title should not be too long
        if ($title) {
            $strlen = mb_strlen($title);
            if ($strlen > 255) {
                return self::checkInfo(ErrorCodeService::TITLE_ERROR);
            }
        }
        // type cannot be too long
        if ($types) {
            $strlen = mb_strlen($types);
            if ($strlen > 128) {
                return self::checkInfo(ErrorCodeService::CONTENT_TYPES_ERROR);
            }
        }
    }

    /*
     * Create Draft Check
     */
    public static function checkCreate($uid)
    {
        $request = request();
        $type = $request->input('type');
        $fsid = $request->Input('fsid');
        $pid = $request->input('pid');
        // In case of private mode, the feature cannot be requested when it expires (users > expired_at).
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $userInfo = FresnsUsers::find($uid);
            if ($userInfo['expired_at'] && ($userInfo['expired_at'] <= date('Y-m-d H:i:s'))) {
                return self::checkInfo(ErrorCodeService::USER_EXPIRED_ERROR);
            }
        }
        switch ($type) {
            case 1:
                if (! empty($fsid)) {
                    $postInfo = FresnsPosts::where('pid', $fsid)->first();
                    if (! $postInfo) {
                        return self::checkInfo(ErrorCodeService::POST_LOG_EXIST_ERROR);
                    }
                    $postCount = DB::table('posts as post')
                        ->join('post_appends as append', 'post.id', '=', 'append.post_id')
                        ->where('post.pid', $fsid)
                        ->where('post.deleted_at', null)
                        ->count();
                    if ($postCount == 0) {
                        return self::checkInfo(ErrorCodeService::POST_APPEND_ERROR);
                    }
                }
                break;

            default:
                if (empty($fsid)) {
                    if (empty($pid)) {
                        return self::checkInfo(ErrorCodeService::COMMENT_PID_ERROR);
                    }
                    $postInfo = FresnsPosts::where('pid', $pid)->first();
                    if (! $postInfo) {
                        return self::checkInfo(ErrorCodeService::POST_LOG_EXIST_ERROR);
                    }
                    $postCount = DB::table('posts as post')
                        ->join('post_appends as append', 'post.id', '=', 'append.post_id')
                        ->where('post.pid', $pid)
                        ->where('post.deleted_at', null)
                        ->count();
                    if ($postCount == 0) {
                        return self::checkInfo(ErrorCodeService::POST_APPEND_ERROR);
                    }
                } else {
                    $commentInfo = FresnsComments::where('cid', $fsid)->first();
                    if (! $commentInfo) {
                        return self::checkInfo(ErrorCodeService::COMMENT_LOG_EXIST_ERROR);
                    }
                    $commentCount = DB::table('comments as comment')
                        ->join('comment_appends as append', 'comment.id', '=', 'append.comment_id')
                        ->where('comment.cid', $fsid)
                        ->where('comment.deleted_at', null)
                        ->count();
                    if ($commentCount == 0) {
                        return self::checkInfo(ErrorCodeService::COMMENT_APPEND_ERROR);
                    }
                    if ($commentInfo['parent_id'] != 0) {
                        return self::checkInfo(ErrorCodeService::COMMENT_CREATE_ERROR);
                    }
                }
                break;
        }
    }

    /*
     * Submitted Content Check
     */
    public static function checkSubmit($uid)
    {
        $request = request();
        $type = $request->input('type');
        $logId = $request->input('logId');
        // In case of private mode, the feature cannot be requested when it expires (users > expired_at).
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $userInfo = FresnsUsers::find($uid);
            if ($userInfo['expired_at'] && ($userInfo['expired_at'] <= date('Y-m-d H:i:s'))) {
                return self::checkInfo(ErrorCodeService::USER_EXPIRED_ERROR);
            }
        }
        switch ($type) {
            case 1:
                $postLog = FresnsPostLogs::find($logId);
                if (! $postLog) {
                    return self::checkInfo(ErrorCodeService::POST_LOG_EXIST_ERROR);
                }
                if ($postLog['state'] == 2) {
                    return self::checkInfo(ErrorCodeService::POST_SUBMIT_STATE_2_ERROR);
                }
                if ($postLog['state'] == 3) {
                    return self::checkInfo(ErrorCodeService::POST_SUBMIT_STATE_3_ERROR);
                }
                if ($uid != $postLog['user_id']) {
                    return self::checkInfo(ErrorCodeService::CONTENT_AUTHOR_ERROR);
                }
                // Logs have group values to determine whether the group exists and whether current users have the right to post in the group
                if ($postLog['group_id']) {
                    $groupInfo = FresnsGroups::find($postLog['group_id']);
                    if (! $groupInfo) {
                        return self::checkInfo(ErrorCodeService::GROUP_EXIST_ERROR);
                    }
                    if ($groupInfo['type'] == 1) {
                        return self::checkInfo(ErrorCodeService::GROUP_TYPE_ERROR);
                    }
                    $publishRule = FresnsGroupsService::publishRule($uid, $groupInfo['permission'], $groupInfo['id']);
                    if (! $publishRule['allowPost']) {
                        return self::checkInfo(ErrorCodeService::GROUP_POST_ALLOW_ERROR);
                    }
                }
                if (empty($postLog['content']) && (empty($postLog['files_json']) || empty(json_decode($postLog['files_json'], true))) && (empty($postLog['extends_json']) || empty(json_decode($postLog['extends_json'], true)))) {
                    return self::checkInfo(ErrorCodeService::CONTENT_CHECK_PARAMS_ERROR);
                }
                // Block Word Rule Check
                if ($postLog['content']) {
                    $message = self::blockWords($postLog['content']);
                    if (! $message) {
                        return self::checkInfo(ErrorCodeService::CONTENT_STOP_WORDS_ERROR);
                    }
                }
                break;

            default:
                $commentLog = FresnsCommentLogs::find($logId);
                if (! $commentLog) {
                    return self::checkInfo(ErrorCodeService::COMMENT_LOG_EXIST_ERROR);
                }
                if ($commentLog['state'] == 2) {
                    return self::checkInfo(ErrorCodeService::COMMENT_SUBMIT_STATE_2_ERROR);
                }
                if ($commentLog['state'] == 3) {
                    return self::checkInfo(ErrorCodeService::COMMENT_SUBMIT_STATE_3_ERROR);
                }
                if ($uid != $commentLog['user_id']) {
                    return self::checkInfo(ErrorCodeService::CONTENT_AUTHOR_ERROR);
                }
                $postInfo = FresnsPosts::find($commentLog['post_id']);
                if (! $postInfo) {
                    return self::checkInfo(ErrorCodeService::COMMENT_PID_EXIST_ERROR);
                }
                if ($postInfo['group_id']) {
                    $groupInfo = FresnsGroups::find($postInfo['group_id']);
                    if (! $groupInfo) {
                        return self::checkInfo(ErrorCodeService::GROUP_EXIST_ERROR);
                    }
                    if ($groupInfo['type'] == 1) {
                        return self::checkInfo(ErrorCodeService::GROUP_TYPE_ERROR);
                    }
                    $publishRule = FresnsGroupsService::publishRule($uid, $groupInfo['permission'], $groupInfo['id']);
                    if (! $publishRule['allowComment']) {
                        return self::checkInfo(ErrorCodeService::GROUP_COMMENTS_ALLOW_ERROR);
                    }
                }
                if (empty($commentLog['content']) && (empty($commentLog['files_json']) || empty(json_decode($commentLog['files_json'], true))) && (empty($commentLog['extends_json']) || empty(json_decode($commentLog['extends_json'], true)))) {
                    return self::checkInfo(ErrorCodeService::CONTENT_CHECK_PARAMS_ERROR);
                }
                // Block Word Rule Check
                if ($commentLog['content']) {
                    $message = self::blockWords($commentLog['content']);
                    if (! $message) {
                        return self::checkInfo(ErrorCodeService::CONTENT_STOP_WORDS_ERROR);
                    }
                }

                break;
        }
    }

    /*
     * Fast Publishing Check
     */
    public static function checkPublish($uid)
    {
        $request = request();
        // In case of private mode, the feature cannot be requested when it expires (users > expired_at).
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $userInfo = FresnsUsers::find($uid);
            if ($userInfo['expired_at'] && ($userInfo['expired_at'] <= date('Y-m-d H:i:s'))) {
                return self::checkInfo(ErrorCodeService::USER_EXPIRED_ERROR);
            }
        }
        $commentPid = $request->input('commentPid');
        $commentCid = $request->input('commentCid');
        $postGid = $request->input('postGid');
        $type = $request->input('type');
        if ($commentCid) {
            $commentInfo = FresnsComments::where('cid', $commentCid)->first();
            if (! $commentInfo) {
                return self::checkInfo(ErrorCodeService::COMMENT_LOG_EXIST_ERROR);
            }
        }
        if ($commentPid) {
            // Post Info
            $postInfo = FresnsPosts::where('pid', $commentPid)->first();
            if (! $postInfo) {
                return self::checkInfo(ErrorCodeService::POST_LOG_EXIST_ERROR);
            }
        }
        if ($type == 2) {
            // Get the maximum number of words in a comment
            $commentEditorWordCount = ApiConfigHelper::getConfigByItemKey(FsConfig::COMMENT_EDITOR_WORD_COUNT) ?? 1000;
            $content = $request->input('content');
            if (mb_strlen(trim($content)) > $commentEditorWordCount) {
                return self::checkInfo(ErrorCodeService::CONTENT_COUNT_ERROR);
            }
            if (empty($commentPid)) {
                return self::checkInfo(ErrorCodeService::COMMENT_PID_ERROR);
            }
            // Whether to have group publish privileges
            $postInfo = FresnsPosts::where('pid', $commentPid)->first();
            if ($postInfo['group_id']) {
                $groupInfo = FresnsGroups::find($postInfo['group_id']);
                if (! $groupInfo) {
                    return self::checkInfo(ErrorCodeService::GROUP_EXIST_ERROR);
                }
                if ($groupInfo['type'] == 1) {
                    return self::checkInfo(ErrorCodeService::GROUP_TYPE_ERROR);
                }
                $publishRule = FresnsGroupsService::publishRule($uid, $groupInfo['permission'], $groupInfo['id']);
                if (! $publishRule['allowComment']) {
                    return self::checkInfo(ErrorCodeService::GROUP_COMMENTS_ALLOW_ERROR);
                }
            }
        } else {
            // Get the maximum number of words in a post
            $postEditorWordCount = ApiConfigHelper::getConfigByItemKey(FsConfig::POST_EDITOR_WORD_COUNT) ?? 1000;
            $content = $request->input('content');
            if (mb_strlen(trim($content)) > $postEditorWordCount) {
                return self::checkInfo(ErrorCodeService::CONTENT_COUNT_ERROR);
            }
            if ($postGid) {
                $groupInfo = FresnsGroups::where('gid', $postGid)->where('is_enable', 1)->first();
                if (! $groupInfo) {
                    return self::checkInfo(ErrorCodeService::GROUP_EXIST_ERROR);
                }
                if ($groupInfo['type'] == 1) {
                    return self::checkInfo(ErrorCodeService::GROUP_TYPE_ERROR);
                }
                // Whether to have group publish privileges
                $publishRule = FresnsGroupsService::publishRule($uid, $groupInfo['permission'], $groupInfo['id']);
                if (! $publishRule['allowPost']) {
                    return self::checkInfo(ErrorCodeService::GROUP_POST_ALLOW_ERROR);
                }
            }
        }
        // Block Word Rule Check
        $message = self::blockWords($request->input('content'));
        if (! $message) {
            return self::checkInfo(ErrorCodeService::CONTENT_STOP_WORDS_ERROR);
        }
        $file = $request->input('file');
        $fileInfo = $request->input('fileInfo');
        if ($fileInfo) {
            $filesJsonStatus = StrHelper::isJson($fileInfo);
            if (! $filesJsonStatus) {
                return self::checkInfo(ErrorCodeService::FILES_JSON_ERROR);
            }
            $fileInfo = json_decode($fileInfo, true);
            if (count($fileInfo) == count($fileInfo, 1)) {
                return self::checkInfo(ErrorCodeService::FILES_JSON_ERROR);
            }
        }
        if (! empty($file) && ! empty($fileInfo)) {
            return self::checkInfo(ErrorCodeService::FILE_OR_TEXT_ERROR);
        }
        $eid = $request->input('eid');
        if ($eid) {
            $eidJsonStatus = StrHelper::isJson($eid);
            if (! $eidJsonStatus) {
                return self::checkInfo(ErrorCodeService::EXTENDS_JSON_ERROR);
            }
            $extendsJson = json_decode($eid, true);
            foreach ($extendsJson as $e) {
                $extend = FresnsExtends::where('eid', $e)->first();
                if (! $extend) {
                    return self::checkInfo(ErrorCodeService::EXTEND_EXIST_ERROR);
                }
            }
        }
    }

    // Block Word Rules
    public static function blockWords($text)
    {
        $blockWordsArr = FresnsBlockWords::get()->toArray();

        foreach ($blockWordsArr as $v) {
            $str = strstr($text, $v['word']);
            if ($str != false) {
                if ($v['content_mode'] == 2) {
                    $text = str_replace($v['word'], $v['replace_word'], $text);

                    return $text;
                }
                if ($v['content_mode'] == 3) {
                    return false;
                }
                if ($v['content_mode'] == 4) {
                    return $v['content_mode'];
                }
            }
        }

        return $text;
    }

    // Calibrate the interface if it is in private mode, when the interface is not requestable after expiration (users > expired_at).
    public static function checkUploadPermission($userId, $type, $fileSize = null, $suffix = null)
    {
        $siteMode = ApiConfigHelper::getConfigByItemKey('site_mode');
        $user = FresnsUsers::where('id', $userId)->first();
        $time = date('Y-m-d H:i:s', time());
        if ($siteMode == 'private') {
            if (! empty($user['expired_at'])) {
                if ($time > $user['expired_at']) {
                    return ErrorCodeService::USER_EXPIRED_ERROR;
                }
            }
        }

        $roleId = FresnsUserRolesService::getUserRoles($userId);

        if (empty($roleId)) {
            return ErrorCodeService::ROLE_NO_PERMISSION;
        }

        // Change file extensions to lowercase letters
        if (! empty($suffix)) {
            $suffix = mb_strtolower($suffix);
        }

        $userRole = FresnsRoles::where('id', $roleId)->first();
        if (! empty($userRole)) {
            $permission = $userRole['permission'];
            $permissionArr = json_decode($permission, true);
            if (! empty($permissionArr)) {
                $permissionMap = FresnsRolesService::getPermissionMap($permissionArr);
                if (empty($permissionMap)) {
                    return ErrorCodeService::ROLE_NO_PERMISSION;
                }
                $mbFileSize = null;
                if ($fileSize) {
                    $mbFileSize = $fileSize;
                }
                switch ($type) {
                    case 1:
                        // Verify user upload permissions (image)
                        if ($permissionMap['post_editor_image'] == false) {
                            return ErrorCodeService::ROLE_NO_PERMISSION_UPLOAD_IMAGE;
                        }

                        if (! empty($mbFileSize)) {
                            // Check global configuration of uploadable image size
                            $image_max_size = ApiConfigHelper::getConfigByItemKey('image_max_size');
                            if ($mbFileSize > $image_max_size * 1024 * 1024) {
                                return ErrorCodeService::ROLE_UPLOAD_FILES_SIZE_ERROR;
                            }
                            if ($mbFileSize > $permissionMap['image_max_size'] * 1024 * 1024) {
                                return ErrorCodeService::ROLE_UPLOAD_FILES_SIZE_ERROR;
                            }
                        }

                        if (! empty($suffix)) {
                            $image_ext = ApiConfigHelper::getConfigByItemKey('image_ext');
                            $imagesExtArr = explode(',', $image_ext);
                            if (! in_array($suffix, $imagesExtArr)) {
                                return ErrorCodeService::UPLOAD_FILES_SUFFIX_ERROR;
                            }
                        }

                        break;
                    case 2:
                        // Verify user upload permissions (video)
                        if ($permissionMap['post_editor_video'] == false) {
                            return ErrorCodeService::ROLE_NO_PERMISSION_UPLOAD_VIDEO;
                        }
                        if (! empty($mbFileSize)) {
                            // Check global configuration of uploadable video size
                            $video_max_size = ApiConfigHelper::getConfigByItemKey('video_max_size');
                            if ($mbFileSize > $video_max_size * 1024 * 1024) {
                                return ErrorCodeService::ROLE_UPLOAD_FILES_SIZE_ERROR;
                            }
                            if ($mbFileSize > $permissionMap['video_max_size'] * 1024 * 1024) {
                                return ErrorCodeService::ROLE_UPLOAD_FILES_SIZE_ERROR;
                            }
                        }
                        if (! empty($suffix)) {
                            $video_ext = ApiConfigHelper::getConfigByItemKey('video_ext');
                            $videosExtArr = explode(',', $video_ext);
                            if (! in_array($suffix, $videosExtArr)) {
                                return ErrorCodeService::UPLOAD_FILES_SUFFIX_ERROR;
                            }
                        }

                        break;
                    case 3:
                        // Verify user upload permissions (audio)
                        if ($permissionMap['post_editor_audio'] == false) {
                            return ErrorCodeService::ROLE_NO_PERMISSION_UPLOAD_AUDIO;
                        }
                        if (! empty($mbFileSize)) {
                            // Check global configuration of uploadable audio size
                            $audio_max_size = ApiConfigHelper::getConfigByItemKey('audio_max_size');
                            if ($mbFileSize > $audio_max_size * 1024 * 1024) {
                                return ErrorCodeService::ROLE_UPLOAD_FILES_SIZE_ERROR;
                            }
                            if ($mbFileSize > $permissionMap['audio_max_size'] * 1024 * 1024) {
                                return ErrorCodeService::ROLE_UPLOAD_FILES_SIZE_ERROR;
                            }
                        }

                        if (! empty($suffix)) {
                            $audio_ext = ApiConfigHelper::getConfigByItemKey('audio_ext');
                            $audiosExtArr = explode(',', $audio_ext);
                            if (! in_array($suffix, $audiosExtArr)) {
                                return ErrorCodeService::UPLOAD_FILES_SUFFIX_ERROR;
                            }
                        }

                        break;
                    default:
                        // Verify user upload permissions (doc)
                        if ($permissionMap['post_editor_document'] == false) {
                            return ErrorCodeService::ROLE_NO_PERMISSION_UPLOAD_DOC;
                        }
                        if (! empty($mbFileSize)) {
                            // Check global configuration of uploadable document size
                            $doc_max_size = ApiConfigHelper::getConfigByItemKey('document_max_size');
                            if ($mbFileSize > $doc_max_size * 1024 * 1024) {
                                return ErrorCodeService::ROLE_UPLOAD_FILES_SIZE_ERROR;
                            }
                            if ($mbFileSize > $permissionMap['document_max_size'] * 1024 * 1024) {
                                return ErrorCodeService::ROLE_UPLOAD_FILES_SIZE_ERROR;
                            }
                        }

                        if (! empty($suffix)) {
                            $document_ext = ApiConfigHelper::getConfigByItemKey('document_ext');
                            $docsExtArr = explode(',', $document_ext);
                            if (! in_array($suffix, $docsExtArr)) {
                                return ErrorCodeService::UPLOAD_FILES_SUFFIX_ERROR;
                            }
                        }

                        break;
                }
            }
        }

        return true;
    }
}
