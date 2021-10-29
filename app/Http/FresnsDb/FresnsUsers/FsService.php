<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsUsers;

use App\Base\Services\BaseAdminService;
use App\Helpers\DateHelper;
use App\Helpers\StrHelper;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsApi\Helpers\ApiFileHelper;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Http\FresnsDb\FresnsLanguages\FresnsLanguagesService;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRels;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRelsService;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRoles;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRolesConfig;
use App\Http\FresnsDb\FresnsMembers\FresnsMembersConfig;
use App\Http\FresnsDb\FresnsPluginBadges\FresnsPluginBadgesService;
use App\Http\FresnsDb\FresnsPlugins\FresnsPluginsService;
use App\Http\FresnsDb\FresnsUserConnects\FresnsUserConnectsConfig;
use App\Http\FresnsDb\FresnsUserWallets\FresnsUserWallets;
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

    // Get User Detail
    public function getUserDetail($uid, $langTag, $mid = null)
    {
        $langTag = ApiLanguageHelper::getLangTagByHeader();

        if (empty($mid)) {
            $mid = Db::table(FresnsMembersConfig::CFG_TABLE)->where('user_id', $uid)->value('id');
        }

        $users = DB::table(FresnsUsersConfig::CFG_TABLE)->where('id', $uid)->first();
        $phone = $users->phone ?? '';
        $email = $users->email ?? '';

        $data['uid'] = $users->uuid ?? '';
        $data['countryCode'] = $users->country_code ?? '';
        $data['purePhone'] = StrHelper::encryptPhone($users->pure_phone ?? '');
        $data['phone'] = StrHelper::encryptPhone($phone, 5, 6) ?? '';
        $data['email'] = StrHelper::encryptEmail($email) ?? '';
        $isPassword = false;
        if (! empty($users->password)) {
            $isPassword = true;
        }
        $data['password'] = $isPassword;
        // Configs the plugin associated with the table account_prove_service and output the plugin URL
        $proveSupportUnikey = ApiConfigHelper::getConfigByItemKey('account_prove_service');
        $proveSupportUrl = FresnsPluginsService::getPluginUrlByUnikey($proveSupportUnikey);
        $data['proveSupport'] = $proveSupportUrl;
        $data['verifyStatus'] = $users->prove_verify ?? '';
        $data['realname'] = StrHelper::encryptName($users->prove_realname) ?? '';
        $data['gender'] = $users->prove_gender ?? '';
        $data['idType'] = $users->prove_type ?? '';
        $data['idNumber'] = StrHelper::encryptIdNumber($users->prove_number, 1, -1) ?? '';
        $data['registerTime'] = DateHelper::fresnsOutputTimeToTimezone($users->created_at ?? '');
        $data['status'] = $users->is_enable ?? '';
        $data['deactivate'] = boolval($users->deleted_at ?? '');
        $data['deactivateTime'] = DateHelper::fresnsOutputTimeToTimezone($users->deleted_at ?? '');

        $connectsArr = DB::table(FresnsUserConnectsConfig::CFG_TABLE)->where('user_id', $uid)->get([
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
        $userWallets = FresnsUserWallets::where('user_id', $uid)->first();
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
        $wallet['payExpands'] = FresnsPluginBadgesService::getWalletPluginExpands($mid, FsConfig::PLUGIN_USAGERS_TYPE_1, $langTag);
        $wallet['withdrawExpands'] = FresnsPluginBadgesService::getWalletPluginExpands($mid, FsConfig::PLUGIN_USAGERS_TYPE_2, $langTag);
        $data['wallet'] = $wallet;

        $memberArr = DB::table('members')->where('user_id', $uid)->get()->toArray();
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
                $item['roleName'] = FresnsLanguagesService::getLanguageByTableId(FresnsMemberRolesConfig::CFG_TABLE, 'name', $memberRole['id'], $langTag);
                $item['roleNameDisplay'] = $memberRole['is_display_name'];
                $item['roleIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($memberRole['icon_file_id'], $memberRole['icon_file_url']);
                $item['roleIconDisplay'] = $memberRole['is_display_icon'];
            }

            $isPassword = false;
            if (! empty($v->password)) {
                $isPassword = true;
            }
            $item['password'] = $isPassword;

            if (empty($users->deleted_at)) {
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
            $memberRoleIdArr = FresnsMemberRoleRels::where('member_id', $v->id)->where('type', 1)->pluck('role_id')->toArray();
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

        $data['memberName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'member_name', $langTag);
        $data['memberIdName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'member_id_name', $langTag);
        $data['memberNameName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'member_name_name', $langTag);
        $data['memberNicknameName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'member_nickname_name', $langTag);
        $data['memberRoleName'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', 'member_role_name', $langTag);

        return $data;
    }
}
