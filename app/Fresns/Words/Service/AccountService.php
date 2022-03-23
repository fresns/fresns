<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Service;

use App\Fresns\Api\Helpers\StrHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Models\Account;
use App\Models\AccountConnect;
use App\Models\AccountWallet;
use App\Models\Config;
use App\Models\Language;
use App\Models\Plugin;
use App\Models\PluginBadge;
use App\Models\PluginUsage;
use App\Models\User;

class AccountService
{
    // Get User Detail
    public function getUserDetail($aid, $langTag, $mid = null)
    {
        $langTag = self::getLangTagByHeader();

        if (empty($mid)) {
            $mid = Account::where('id', $aid)->value('id');
        }

        $account = Account::where('id', $aid)->first();
        $phone = $account->phone ?? '';
        $email = $account->email ?? '';

        $data['aid'] = $account->aid ?? '';
        $data['countryCode'] = $account->country_code ?? '';
        $data['purePhone'] = StrHelper::encryptPhone($account->pure_phone ?? '');
        $data['phone'] = StrHelper::encryptPhone($phone, 5, 6) ?? '';
        $data['email'] = StrHelper::encryptEmail($email) ?? '';
        $isPassword = false;
        if (! empty($account->password)) {
            $isPassword = true;
        }
        $data['password'] = $isPassword;
        // Configs the plugin associated with the table account_prove_service and output the plugin URL
        $proveSupportUnikey = ConfigHelper::fresnsConfigByItemKey('account_prove_service');
        $proveSupportUrl = self::getPluginUrlByUnikey($proveSupportUnikey);
        $data['proveSupport'] = $proveSupportUrl;
        $data['verifyStatus'] = $account->prove_verify ?? '';
        $data['realname'] = StrHelper::encryptName($account->prove_realname) ?? '';
        $data['gender'] = $account->prove_gender ?? '';
        $data['idType'] = $account->prove_type ?? '';
        $data['idNumber'] = StrHelper::encryptIdNumber($account->prove_number, 1, -1) ?? '';
        $data['registerTime'] = DateHelper::fresnsOutputTimeToTimezone($account->created_at ?? '');
        $data['status'] = $account->is_enable ?? '';
        $data['deactivate'] = boolval($account->deleted_at ?? '');
        $data['deactivateTime'] = DateHelper::fresnsOutputTimeToTimezone($account->deleted_at ?? '');

        $connectsArr = AccountConnect::where('account_id', $aid)->get([
            'connect_id',
            'connect_name',
            'connect_nickname',
            'connect_avatar',
            'is_enable',
        ])->toArray();
        $itemArr = [];
        if ($connectsArr) {
            foreach ($connectsArr as $v) {
                $item = [];
                $item['id'] = $v->connect_id;
                $item['name'] = $v->connect_name;
                $item['nickname'] = $v->connect_nickname;
                $item['avatar'] = $v->connect_avatar;
                $item['status'] = $v->is_enable;
                $itemArr[] = $item;
            }
        }
        $data['connects'] = $itemArr;

        // Wallet
        $userWallets = AccountWallet::where('account_id', $aid)->first();
        $wallet['status'] = $userWallets['is_enable'] ?? '';
        $isPassword = false;
        if (! empty($userWallets['password'])) {
            $isPassword = true;
        }
        $wallet['password'] = $isPassword;
        $wallet['balance'] = $userWallets['balance'] ?? '';
        $wallet['freezeAmount'] = $userWallets['freeze_amount'] ?? '';
        $wallet['bankName'] = $userWallets['bank_name'] ?? '';
        $wallet['swiftCode'] = $userWallets['swift_code'] ?? '';
        $wallet['bankAddress'] = $userWallets['bank_address'] ?? '';
        $wallet['bankAccount'] = '';
        if (! empty($userWallets)) {
            $wallet['bankAccount'] = StrHelper::encryptIdNumber($userWallets['bank_account'], 4, -2);
        }
        $wallet['bankStatus'] = $userWallets['bank_status'] ?? '';
        $wallet['payExpands'] = self::getWalletPluginExpands($mid, 1, $langTag);
        $wallet['withdrawExpands'] = self::getWalletPluginExpands($mid, 2, $langTag);
        $data['wallet'] = $wallet;

        $memberArr = User::where('account_id', $aid)->get()->toArray();
        $itemArr = [];
        foreach ($memberArr as $v) {
            $item = [];
            $item['mid'] = $v->uuid;
            $item['mname'] = $v->name;
            $item['nickname'] = $v->nickname;
            $roleId = FresnsMemberRoleRelsService::getMemberRoleRels($v->id);
            $memberRole = FresnsMemberRoles::where('id', $roleId)->first();
            $item['rid'] = '';
            $item['nicknameColor'] = '';
            $item['roleName'] = '';
            $item['roleNameDisplay'] = '';
            $item['roleIcon'] = '';
            $item['roleIconDisplay'] = '';
            if ($memberRole) {
                $item['rid'] = $memberRole['id'];
                $item['nicknameColor'] = $memberRole['nickname_color'];
                $item['roleName'] = self::getLanguageByTableId(FresnsMemberRolesConfig::CFG_TABLE, 'name', $memberRole['id'], $langTag);
                $item['roleNameDisplay'] = $memberRole['is_display_name'];
                $item['roleIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($memberRole['icon_file_id'], $memberRole['icon_file_url']);
                $item['roleIconDisplay'] = $memberRole['is_display_icon'];
            }

            $isPassword = false;
            if (! empty($v->password)) {
                $isPassword = true;
            }
            $item['password'] = $isPassword;

            if (empty($account->deleted_at)) {
                if (empty($v->avatar_file_url) && empty($v->avatar_file_id)) {
                    $defaultAvatar = ApiConfigHelper::getConfigByItemKey('default_avatar');
                    $memberAvatar = ApiFileHelper::getImageAvatarUrl($defaultAvatar);
                } else {
                    $memberAvatar = ApiFileHelper::getImageAvatarUrlByFileIdUrl($v->avatar_file_id, $v->avatar_file_url);
                }
            } else {
                $deactivateAvatar = ApiConfigHelper::getConfigByItemKey('deactivate_avatar');
                $memberAvatar = ApiFileHelper::getImageAvatarUrl($deactivateAvatar);
            }
            $item['avatar'] = $memberAvatar;
            $item['verifiedStatus'] = $v->verified_status;
            $item['verifiedIcon'] = $v->verified_file_url;
            $item['verifiedDesc'] = $v->verified_desc;
            $item['status'] = $v->is_enable;
            $item['deactivate'] = DateHelper::fresnsOutputTimeToTimezone($v->deleted_at);
            $item['deactivateTime'] = DateHelper::fresnsOutputTimeToTimezone($v->deleted_at);

            // Determine if all roles of the member are in the "entitled roles" list
            $memberRoleIdArr = FresnsMemberRoleRels::where('user_id', $v->id)->where('type', 1)->pluck('role_id')->toArray();
            $memberRoleIdArr[] = $roleId;
            $permissionsRoleIdJson = ApiConfigHelper::getConfigByItemKey('multi_member_roles');
            $permissionsRoleIdArr = json_decode($permissionsRoleIdJson, true) ?? [];
            $multiMemberServiceUrl = '';
            if (! empty($permissionsRoleIdArr)) {
                $isPermissions = false;
                foreach ($memberRoleIdArr as $memberRoleId) {
                    if (in_array($memberRoleId, $permissionsRoleIdArr)) {
                        $isPermissions = true;
                        break;
                    }
                }
                if ($isPermissions === true) {
                    $multiMemberServiceUnikey = ApiConfigHelper::getConfigByItemKey('multi_member_service');
                    $multiMemberServiceUrl = FresnsPluginsService::getPluginUrlByUnikey($multiMemberServiceUnikey);
                }
            }

            $item['multiple'] = $multiMemberServiceUrl;
            $itemArr[] = $item;
        }
        $data['members'] = $itemArr;

        $data['userName'] = self::getLanguageByTableKey('configs', 'item_value', 'user_name', $langTag);
        $data['userIdName'] = self::getLanguageByTableKey('configs', 'item_value', 'user_name', $langTag);
        $data['userNameName'] = self::getLanguageByTableKey('configs', 'item_value', 'user_name_name', $langTag);
        $data['userNicknameName'] = self::getLanguageByTableKey('configs', 'item_value', 'user_nickname_name', $langTag);
        $data['userRoleName'] = self::getLanguageByTableKey('configs', 'item_value', 'user_role_name', $langTag);

        return $data;
    }

    public static function getLanguageByTableKey($table, $field, $tableKey, $langTag)
    {
        $lang_content = Language::where('table_name', $table)->where('table_column', $field)->where('table_key', $tableKey)->where('lang_tag', $langTag)->value('lang_content');
        if (empty($lang_content)) {
            $langTag = Config::where('item_key', 'default_language')->value('item_value');
            $lang_content = Language::where('table_name', $table)->where('table_column', $field)->where('table_key',
                $tableKey)->where('lang_tag', $langTag)->value('lang_content');
        }

        return $lang_content;
    }

    // Get langTag
    public static function getLangTagByHeader()
    {
        $langTagHeader = request()->header('langTag');
        $langTag = null;
        if (! empty($langTagHeader)) {
            // If it is not empty, check if the language exists
            $langSetting = Config::where('item_key', 'language_menus')->value('item_value');
            if (! empty($langSetting)) {
                $langSettingArr = json_decode($langSetting, true);
                foreach ($langSettingArr as $v) {
                    if ($v['langTag'] == $langTagHeader) {
                        $langTag = $langTagHeader;
                    }
                }
            }
        }

        // If no multiple languages are passed or not queried, the default language is queried
        if (empty($langTag)) {
            $langTag = ConfigHelper::fresnsConfigByItemKey('default_language');
        }

        return $langTag;
    }

    // Get plugin url via unikey
    public static function getPluginUrlByUnikey($unikey)
    {
        $plugin = Plugin::where('unikey', $unikey)->first();
        if (empty($plugin)) {
            return '';
        }

        $uri = $plugin['access_path'];
        if (empty($plugin['plugin_domain'])) {
            $domain = ConfigHelper::fresnsConfigByItemKey('backend_domain');
        } else {
            $domain = $plugin['plugin_domain'];
        }
        $url = $domain.$uri;

        return $url;
    }

    // Get Plugin
    public static function getWalletPluginExpands($user_id, $type, $langTag)
    {
        $unikeyArr = PluginBadge::where('user_id', $user_id)->pluck('plugin_unikey')->toArray();
        $payArr = PluginUsage::whereIn('plugin_unikey', $unikeyArr)->where('type', $type)->get()->toArray();
        $expandsArr = [];
        foreach ($payArr as $v) {
            $item = [];
            $item['plugin'] = $v['plugin_unikey'];
            $item['name'] = self::getLanguageByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $v['id'], $langTag);
            $item['icon'] = F::getImageSignUrlByFileIdUrl($v['icon_file_id'], $v['icon_file_url']);
            $item['url'] = FresnsPluginsService::getPluginUsagesUrl($pluginUsages['plugin_unikey'], $v['id']);
            $badges = FresnsPluginBadges::where('user_id', $user_id)->where('plugin_unikey', $v['plugin_unikey'])->first();
            $item['badgesType'] = $badges['display_type'];
            $item['badgesValue'] = $badges['value_text'];
            $expandsArr[] = $item;
        }

        return $expandsArr;
    }

    // Get the corresponding multilingual
    public static function getLanguageByTableId($table, $field, $tableId, $langTag = null)
    {
        $lang_content = Language::where('table_name', $table)->where('table_column', $field)->where('table_id', $tableId)->where('lang_tag', $langTag)->value('lang_content');
        if (empty($lang_content)) {
            $langTag = ConfigHelper::fresnsConfigByItemKey('default_language');
            $lang_content = Language::where('table_name', $table)->where('table_column', $field)->where('table_id',
                $tableId)->where('lang_tag', $langTag)->value('lang_content');
        }

        return $lang_content;
    }

    public static function getPluginUsagesUrl($pluginUnikey, $pluginUsagesid)
    {
        $bucketDomain = ConfigHelper::fresnsConfigByItemKey('backend_domain');
        $pluginUsages = PluginUsage::find($pluginUsagesid);
        $plugin = Plugin::where('unikey', $pluginUnikey)->first();
        $url = '';
        if (! $plugin || ! $pluginUsages) {
            return $url;
        }
        $access_path = $plugin['access_path'];
        $str = strstr($access_path, '{parameter}');
        if ($str) {
            $uri = str_replace('{parameter}', $pluginUsages['parameter'], $access_path);
        } else {
            $uri = $access_path;
        }
        if (empty($plugin['plugin_url'])) {
            $url = $bucketDomain.$uri;
        } else {
            $url = $plugin['plugin_domain'].$uri;
        }

        return $url;
    }
}
