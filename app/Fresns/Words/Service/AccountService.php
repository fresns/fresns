<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Service;

use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\PluginHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\StrHelper;
use App\Models\Account;
use App\Models\AccountConnect;
use App\Models\AccountWallet;
use App\Models\PluginUsage;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;

class AccountService
{
    /**
     * @param $accountId
     * @param $langTag
     * @param  null  $mid
     * @return mixed
     *
     * @throws \Exception
     */
    public function getAccountDetail($accountId, $langTag, $timezone)
    {
        $account = Account::where('id', $accountId)->first();
        $phone = $account->phone ?? null;
        $email = $account->email ?? null;
        $data['aid'] = $account->aid ?? null;
        $data['countryCode'] = $account->country_code ?? null;
        $data['purePhone'] = StrHelper::encryptNumber($account->pure_phone) ?? null;
        $data['phone'] = StrHelper::encryptNumber($account->phone) ?? null;
        $data['email'] = StrHelper::encryptEmail($email) ?? null;
        $isPassword = false;
        if (! empty($account->password)) {
            $isPassword = true;
        }
        $data['password'] = $isPassword;
        $proveSupportUnikey = ConfigHelper::fresnsConfigByItemKey('account_prove_service');
        $proveSupportUrl = PluginHelper::fresnsPluginUrlByUnikey($proveSupportUnikey);
        $data['proveSupport'] = $proveSupportUrl;
        $data['verifyStatus'] = $account->prove_verify ?? null;
        $data['realname'] = StrHelper::encryptName($account->prove_realname) ?? null;
        $data['gender'] = $account->prove_gender ?? null;
        $data['idType'] = $account->prove_type ?? null;
        $data['idNumber'] = StrHelper::encryptNumber($account->prove_number) ?? null;
        $data['registerTime'] = ($account->created_at)->toDateTimeString();
        $data['registerTime'] = DateHelper::fresnsOutputFormattingTime($account->created_at, $timezone, $langTag);
        $data['status'] = $account->is_enable ?? null;
        $data['deactivate'] = boolval($account->deleted_at) ?? null;
        $data['deactivateTime'] = $account->deleted_at ?? null;

        $connectsArr = AccountConnect::where('account_id', $accountId)->get([
            'connect_id',
            'connect_name',
            'connect_nickname',
            'connect_avatar',
            'is_enable',
        ])->toArray();
        $itemArr = [];
        if ($connectsArr) {
            foreach ($connectsArr as $user) {
                $item = [];
                $item['id'] = $user['connect_id'];
                $item['name'] = $user['connect_name'];
                $item['nickname'] = $user['connect_nickname'];
                $item['avatar'] = $user['connect_avatar'];
                $item['status'] = $user['is_enable'];
                $itemArr[] = $item;
            }
        }
        $data['connects'] = $itemArr;
        // Wallet
        $userWallets = AccountWallet::where('account_id', $accountId)->first();
        $wallet['status'] = $userWallets['is_enable'] ?? null;
        $isPassword = false;
        if (! empty($userWallets['password'])) {
            $isPassword = true;
        }
        $wallet['password'] = $isPassword;
        $wallet['balance'] = $userWallets['balance'] ?? null;
        $wallet['freezeAmount'] = $userWallets['freeze_amount'] ?? null;
        $wallet['bankName'] = $userWallets['bank_name'] ?? null;
        $wallet['swiftCode'] = $userWallets['swift_code'] ?? null;
        $wallet['bankAddress'] = $userWallets['bank_address'] ?? null;
        $wallet['bankAccount'] = null;
        if (! empty($userWallets)) {
            $wallet['bankAccount'] = StrHelper::encryptNumber($userWallets['bank_account']);
        }
        $wallet['bankStatus'] = $userWallets['bank_status'] ?? '';
        $wallet['rechargeExpands'] = self::getWalletPluginExpands(1, $langTag);
        $wallet['withdrawExpands'] = self::getWalletPluginExpands(2, $langTag);
        $data['wallet'] = $wallet;

        $userArr = User::where('account_id', $accountId)->get()->toArray();
        $itemArr = [];
        foreach ($userArr as $user) {
            $item = [];
            $item['uid'] = $user['uid'];
            $item['username'] = $user['username'];
            $item['nickname'] = $user['nickname'];
            $userRole = UserRole::where('user_id', $user['id'])->where('type', 2)->first();
            $role = Role::where('id', $userRole['role_id'])->first();
            $item['rid'] = null;
            $item['roleName'] = null;
            $item['nicknameColor'] = null;
            $item['roleNameDisplay'] = null;
            $item['roleIcon'] = null;
            $item['roleIconDisplay'] = null;
            if ($role) {
                $item['rid'] = $role['id'];
                $item['nicknameColor'] = $role['nickname_color'] ?? null;
                $item['roleName'] = LanguageHelper::fresnsLanguageByTableColumn('roles', 'name', $role['id'], $langTag) ?? null;
                $item['roleNameDisplay'] = $role['is_display_name'] ?? null;
                $item['roleIcon'] = FileHelper::fresnsFileImageUrlByColumn($role['icon_file_id'], $role['icon_file_url'], 'imageConfigUrl') ?? null;
                $item['roleIconDisplay'] = $role['is_display_icon'] ?? null;
            }
            $isPassword = false;
            if (! empty($user->password)) {
                $isPassword = true;
            }
            $item['password'] = $isPassword;

            if (empty($user->deleted_at)) {
                if (empty($user->avatar_file_url) && empty($user->avatar_file_id)) {
                    //默认头像
                    if (ConfigHelper::fresnsConfigFileByItemKey('default_avatar') === 2) {
                        $userAvatar = ConfigHelper::fresnsConfigByItemKey('default_avatar');
                    } else {
                        $fresnsResult = FileHelper::fresnsFileUrlById('default_avatar');
                        $userAvatar = $fresnsResult->imageAvatarUrl;
                    }
                } else {
                    //用户头像
                    $userAvatar = FileHelper::fresnsFileImageUrlByColumn($user->avatar_file_id, $user->avatar_file_url, 'imageAvatarUrl') ?? null;
                }
            } else {
                //停用用户的头像
                if (ConfigHelper::fresnsConfigFileByItemKey('deactivate_avatar') === 2) {
                    $userAvatar = ConfigHelper::fresnsConfigByItemKey('deactivate_avatar');
                } else {
                    $fresnsResult = FileHelper::fresnsFileUrlById('deactivate_avatar');
                    $userAvatar = $fresnsResult->imageAvatarUrl;
                }
            }
            $item['avatar'] = $userAvatar;

            $item['verifiedStatus'] = $user['verified_status'];
            $item['verifiedIcon'] = $user['verified_file_url'] ?? '';
            $item['verifiedDesc'] = $user['verified_desc'] ?? '';
            $item['status'] = $user['is_enable'];
            $isset = false;
            if (! empty($user['deleted_at'])) {
                $isset = true;
            }
            $item['deactivate'] = $isset;
            $item['deactivateTime'] = DateHelper::fresnsOutputTimeToTimezone($user['deleted_at']);
            // Determine if all roles of the member are in the "entitled roles" list
            $memberRoleIdArr = UserRole::where('user_id', $user['id'])->where('type', 1)->pluck('role_id')->toArray();
            $memberRoleIdArr[] = $role['id'];
            $permissionsRoleIdJson = ConfigHelper::fresnsConfigByItemKey('multi_member_roles');
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
                    $multiMemberServiceUrl = PluginHelper::fresnsPluginUrlByUnikey($multiMemberServiceUnikey);
                }
            }

            $item['multiple'] = $multiMemberServiceUrl;
            $itemArr[] = $item;
        }
        $data['users'] = $itemArr;

        $data['userName'] = ConfigHelper::fresnsConfigByItemKey('user_name', $langTag);
        $data['userUidName'] = ConfigHelper::fresnsConfigByItemKey('user_uid_name', $langTag);
        $data['userUsername'] = ConfigHelper::fresnsConfigByItemKey('user_username_name', $langTag);
        $data['userNicknameName'] = ConfigHelper::fresnsConfigByItemKey('user_nickname_name', $langTag);
        $data['userRoleName'] = ConfigHelper::fresnsConfigByItemKey('user_role_name', $langTag);

        return $data;
    }

    // Get wallet plugin
    public static function getWalletPluginExpands($type, $langTag)
    {
        $walletArr = PluginUsage::where('type', $type)->get()->toArray();
        $expandsArr = [];
        foreach ($walletArr as $expand) {
            $item = [];
            $item['plugin'] = $expand['plugin_unikey'];
            $item['name'] = LanguageHelper::fresnsLanguageByTableColumn('plugin_usages', 'name', $expand['id'], $langTag);
            $item['icon'] = FileHelper::fresnsFileImageUrlByColumn($expand['icon_file_id'], $expand['icon_file_url'], 'imageConfigUrl') ?? null;
            $item['url'] = PluginHelper::fresnsPluginUsageUrl($expand['plugin_unikey'], $expand['id']);
            $expandsArr[] = $item;
        }

        return $expandsArr;
    }
}
