<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsAccounts;

use App\Fresns\Api\Base\Services\BaseAdminService;
use App\Fresns\Api\Helpers\DateHelper;
use App\Fresns\Api\Helpers\StrHelper;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Helpers\ApiFileHelper;
use App\Fresns\Api\Helpers\ApiLanguageHelper;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Fresns\Api\FsDb\FresnsLanguages\FresnsLanguagesService;
use App\Fresns\Api\FsDb\FresnsUserRoles\FresnsUserRoles;
use App\Fresns\Api\FsDb\FresnsUserRoles\FresnsUserRolesService;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRoles;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRolesConfig;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsersConfig;
use App\Fresns\Api\FsDb\FresnsPluginBadges\FresnsPluginBadgesService;
use App\Fresns\Api\FsDb\FresnsPlugins\FresnsPluginsService;
use App\Fresns\Api\FsDb\FresnsAccountConnects\FresnsAccountConnectsConfig;
use App\Fresns\Api\FsDb\FresnsAccountWallets\FresnsAccountWallets;
use Illuminate\Support\Facades\DB;

class FsService extends BaseAdminService
{
    public function __construct()
    {
        $this->model = new FsModel();
        $this->resource = FsResource::class;
        $this->resourceDetail = FsResourceDetail::class;
    }

    public function common()
    {
        $common = parent::common();

        return $common;
    }

    // Get Account Detail
    public function getAccountDetail($aid, $langTag, $uid = null)
    {
        $langTag = ApiLanguageHelper::getLangTagByHeader();

        if (empty($uid)) {
            $uid = Db::table(FresnsUsersConfig::CFG_TABLE)->where('account_id', $aid)->value('id');
        }

        $accounts = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('id', $aid)->first();
        $phone = $accounts->phone ?? null;
        $email = $accounts->email ?? null;

        $data['aid'] = $accounts->aid ?? null;
        $data['countryCode'] = $accounts->country_code ?? null;
        $data['purePhone'] = StrHelper::encryptPhone($accounts->pure_phone ?? null);
        $data['phone'] = StrHelper::encryptPhone($phone, 5, 6) ?? null;
        $data['email'] = StrHelper::encryptEmail($email) ?? null;
        $isPassword = false;
        if (! empty($accounts->password)) {
            $isPassword = true;
        }
        $data['password'] = $isPassword;
        // Configs the plugin associated with the table account_prove_service and output the plugin URL
        $proveSupportUnikey = ApiConfigHelper::getConfigByItemKey('account_prove_service');
        $proveSupportUrl = FresnsPluginsService::getPluginUrlByUnikey($proveSupportUnikey);
        $data['proveSupport'] = $proveSupportUrl;
        $data['verifyStatus'] = $accounts->prove_verify ?? null;
        $data['verifyType'] = $accounts->verify_type ?? null;
        $data['realname'] = StrHelper::encryptName($accounts->prove_realname) ?? null;
        $data['gender'] = $accounts->prove_gender ?? null;
        $data['idType'] = $accounts->prove_type ?? null;
        $data['idNumber'] = StrHelper::encryptIdNumber($accounts->prove_number, 1, -1) ?? null;
        $data['registerTime'] = DateHelper::fresnsOutputTimeToTimezone($accounts->created_at ?? null);
        $data['status'] = $accounts->is_enable ?? null;
        $data['deactivate'] = boolval($accounts->deleted_at ?? null);
        $data['deactivateTime'] = DateHelper::fresnsOutputTimeToTimezone($accounts->deleted_at ?? null);

        $connectsArr = DB::table(FresnsAccountConnectsConfig::CFG_TABLE)->where('account_id', $aid)->get([
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
        $accountWallets = FresnsAccountWallets::where('account_id', $aid)->first();
        $wallet['status'] = $accountWallets['is_enable'] ?? null;
        $isPassword = false;
        if (! empty($accountWallets['password'])) {
            $isPassword = true;
        }
        $wallet['password'] = $isPassword;
        $wallet['balance'] = $accountWallets['balance'] ?? null;
        $wallet['freezeAmount'] = $accountWallets['freeze_amount'] ??null;
        $wallet['bankName'] = $accountWallets['bank_name'] ?? null;
        $wallet['swiftCode'] = $accountWallets['swift_code'] ?? null;
        $wallet['bankAddress'] = $accountWallets['bank_address'] ?? null;
        $wallet['bankAccount'] = null;
        if (! empty($accountWallets)) {
            $wallet['bankAccount'] = StrHelper::encryptIdNumber($accountWallets['bank_account'], 4, -2);
        }
        $wallet['bankStatus'] = $accountWallets['bank_status'] ?? null;
        $wallet['rechargeExpands'] = FresnsPluginBadgesService::getWalletPluginExpands($uid, FsConfig::PLUGIN_USAGERS_TYPE_1, $langTag);
        $wallet['withdrawExpands'] = FresnsPluginBadgesService::getWalletPluginExpands($uid, FsConfig::PLUGIN_USAGERS_TYPE_2, $langTag);
        $data['wallet'] = $wallet;

        $userArr = DB::table('users')->where('account_id', $aid)->get()->toArray();
        $itemArr = [];
        foreach ($userArr as $v) {
            $item = [];
            $item['uid'] = $v->uid;
            $item['username'] = $v->username;
            $item['nickname'] = $v->nickname;
            $roleId = FresnsUserRolesService::getUserRoles($v->id);
            $userRole = FresnsRoles::where('id', $roleId)->first();
            $item['rid'] = null;
            $item['nicknameColor'] = null;
            $item['roleName'] = null;
            $item['roleNameDisplay'] = null;
            $item['roleIcon'] = null;
            $item['roleIconDisplay'] = null;
            if ($userRole) {
                $item['rid'] = $userRole['id'];
                $item['nicknameColor'] = $userRole['nickname_color'];
                $item['roleName'] = FresnsLanguagesService::getLanguageByTableId(FresnsRolesConfig::CFG_TABLE, 'name', $userRole['id'], $langTag);
                $item['roleNameDisplay'] = $userRole['is_display_name'];
                $item['roleIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($userRole['icon_file_id'], $userRole['icon_file_url']);
                $item['roleIconDisplay'] = $userRole['is_display_icon'];
            }

            $isPassword = false;
            if (! empty($v->password)) {
                $isPassword = true;
            }
            $item['password'] = $isPassword;

            if (empty($accounts->deleted_at)) {
                if (empty($v->avatar_file_url) && empty($v->avatar_file_id)) {
                    $defaultAvatar = ApiConfigHelper::getConfigByItemKey('default_avatar');
                    $userAvatar = ApiFileHelper::getImageAvatarUrl($defaultAvatar);
                } else {
                    $userAvatar = ApiFileHelper::getImageAvatarUrlByFileIdUrl($v->avatar_file_id, $v->avatar_file_url);
                }
            } else {
                $deactivateAvatar = ApiConfigHelper::getConfigByItemKey('deactivate_avatar');
                $userAvatar = ApiFileHelper::getImageAvatarUrl($deactivateAvatar);
            }
            $item['avatar'] = $userAvatar;
            $item['verifiedStatus'] = $v->verified_status;
            $item['verifiedIcon'] = $v->verified_file_url;
            $item['verifiedDesc'] = $v->verified_desc;
            $item['status'] = $v->is_enable;
            $item['deactivate'] = DateHelper::fresnsOutputTimeToTimezone($v->deleted_at);
            $item['deactivateTime'] = DateHelper::fresnsOutputTimeToTimezone($v->deleted_at);

            // Determine if all roles of the user are in the "entitled roles" list
            $userRoleIdArr = FresnsUserRoles::where('user_id', $v->id)->where('type', 1)->pluck('role_id')->toArray();
            $userRoleIdArr[] = $roleId;
            $permissionsRoleIdJson = ApiConfigHelper::getConfigByItemKey('multi_roles');
            $permissionsRoleIdArr = json_decode($permissionsRoleIdJson, true) ?? [];
            $multiUserServiceUrl = null;
            if (! empty($permissionsRoleIdArr)) {
                $isPermissions = false;
                foreach ($userRoleIdArr as $userRoleId) {
                    if (in_array($userRoleId, $permissionsRoleIdArr)) {
                        $isPermissions = true;
                        break;
                    }
                }
                if ($isPermissions === true) {
                    $multiUserServiceUnikey = ApiConfigHelper::getConfigByItemKey('multi_user_service');
                    $multiUserServiceUrl = FresnsPluginsService::getPluginUrlByUnikey($multiUserServiceUnikey);
                }
            }

            $item['multiple'] = $multiUserServiceUrl;
            $itemArr[] = $item;
        }
        $data['users'] = $itemArr;

        $data['userName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'user_name', $langTag);
        $data['userUidName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'user_uid_name', $langTag);
        $data['userUsernameName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'user_username_name', $langTag);
        $data['userNicknameName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'user_nickname_name', $langTag);
        $data['userRoleName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'user_role_name', $langTag);

        return $data;
    }
}
