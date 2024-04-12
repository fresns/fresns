<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Providers;

use App\Fresns\Words\Account\Account;
use App\Fresns\Words\Account\Wallet;
use App\Fresns\Words\Basic\Basic;
use App\Fresns\Words\Content\Content;
use App\Fresns\Words\Feature\Crontab;
use App\Fresns\Words\Feature\Subscribe;
use App\Fresns\Words\File\File;
use App\Fresns\Words\Manage\Manage;
use App\Fresns\Words\Send\Send;
use App\Fresns\Words\User\User;
use Illuminate\Support\ServiceProvider;

class CmdWordServiceProvider extends ServiceProvider implements \Fresns\CmdWordManager\Contracts\CmdWordProviderContract
{
    use \Fresns\CmdWordManager\Traits\CmdWordProviderTrait;

    protected $fsKeyName = 'Fresns';

    /**
     * Fresns official developed command word.
     */
    protected $cmdWordsMap = [
        // Basic
        ['word' => 'checkHeaders', 'provider' => [Basic::class, 'checkHeaders']],
        ['word' => 'verifySign', 'provider' => [Basic::class, 'verifySign']],
        ['word' => 'verifyAccessToken', 'provider' => [Basic::class, 'verifyAccessToken']],
        ['word' => 'ipInfo', 'provider' => [Basic::class, 'ipInfo']],
        ['word' => 'createSessionLog', 'provider' => [Basic::class, 'createSessionLog']],
        ['word' => 'checkLoginToken', 'provider' => [Basic::class, 'checkLoginToken']],
        ['word' => 'updateLoginToken', 'provider' => [Basic::class, 'updateLoginToken']],
        ['word' => 'sendCode', 'provider' => [Basic::class, 'sendCode']],
        ['word' => 'checkCode', 'provider' => [Basic::class, 'checkCode']],
        ['word' => 'updateOrCreateCallbackContent', 'provider' => [Basic::class, 'updateOrCreateCallbackContent']],
        ['word' => 'getCallbackContent', 'provider' => [Basic::class, 'getCallbackContent']],

        // Send
        ['word' => 'sendEmail', 'provider' => [Send::class, 'sendEmail']],
        ['word' => 'sendSms', 'provider' => [Send::class, 'sendSms']],
        ['word' => 'sendNotification', 'provider' => [Send::class, 'sendNotification']],
        ['word' => 'sendAppNotification', 'provider' => [Send::class, 'sendAppNotification']],

        // Account
        ['word' => 'createAccount', 'provider' => [Account::class, 'createAccount']],
        ['word' => 'verifyAccount', 'provider' => [Account::class, 'verifyAccount']],
        ['word' => 'setAccountConnect', 'provider' => [Account::class, 'setAccountConnect']],
        ['word' => 'disconnectAccountConnect', 'provider' => [Account::class, 'disconnectAccountConnect']],
        ['word' => 'createAccountToken', 'provider' => [Account::class, 'createAccountToken']],
        ['word' => 'verifyAccountToken', 'provider' => [Account::class, 'verifyAccountToken']],
        ['word' => 'getAccountDeviceToken', 'provider' => [Account::class, 'getAccountDeviceToken']],
        ['word' => 'logicalDeletionAccount', 'provider' => [Account::class, 'logicalDeletionAccount']],
        ['word' => 'physicalDeletionAccount', 'provider' => [Account::class, 'physicalDeletionAccount']],

        // Wallet
        ['word' => 'walletCheckPassword', 'provider' => [Wallet::class, 'walletCheckPassword']],
        ['word' => 'walletRecharge', 'provider' => [Wallet::class, 'walletRecharge']],
        ['word' => 'walletWithdraw', 'provider' => [Wallet::class, 'walletWithdraw']],
        ['word' => 'walletUpdateState', 'provider' => [Wallet::class, 'walletUpdateState']],
        ['word' => 'walletFreeze', 'provider' => [Wallet::class, 'walletFreeze']],
        ['word' => 'walletUnfreeze', 'provider' => [Wallet::class, 'walletUnfreeze']],
        ['word' => 'walletIncrease', 'provider' => [Wallet::class, 'walletIncrease']],
        ['word' => 'walletDecrease', 'provider' => [Wallet::class, 'walletDecrease']],
        ['word' => 'walletReversal', 'provider' => [Wallet::class, 'walletReversal']],

        // User
        ['word' => 'createUser', 'provider' => [User::class, 'createUser']],
        ['word' => 'verifyUser', 'provider' => [User::class, 'verifyUser']],
        ['word' => 'createUserToken', 'provider' => [User::class, 'createUserToken']],
        ['word' => 'verifyUserToken', 'provider' => [User::class, 'verifyUserToken']],
        ['word' => 'getUserDeviceToken', 'provider' => [User::class, 'getUserDeviceToken']],
        ['word' => 'logicalDeletionUser', 'provider' => [User::class, 'logicalDeletionUser']],
        ['word' => 'physicalDeletionUser', 'provider' => [User::class, 'physicalDeletionUser']],
        ['word' => 'setUserExtcredits', 'provider' => [User::class, 'setUserExtcredits']],
        ['word' => 'setUserExpiryDatetime', 'provider' => [User::class, 'setUserExpiryDatetime']],
        ['word' => 'setUserGroupExpiryDatetime', 'provider' => [User::class, 'setUserGroupExpiryDatetime']],
        ['word' => 'setUserBadge', 'provider' => [User::class, 'setUserBadge']],
        ['word' => 'clearUserBadge', 'provider' => [User::class, 'clearUserBadge']],
        ['word' => 'clearUserAllBadges', 'provider' => [User::class, 'clearUserAllBadges']],

        // File
        ['word' => 'checkUploadPerm', 'provider' => [File::class, 'checkUploadPerm']],
        ['word' => 'getUploadToken', 'provider' => [File::class, 'getUploadToken']],
        ['word' => 'uploadFile', 'provider' => [File::class, 'uploadFile']],
        ['word' => 'getAntiLinkFileInfo', 'provider' => [File::class, 'getAntiLinkFileInfo']],
        ['word' => 'getAntiLinkFileInfoList', 'provider' => [File::class, 'getAntiLinkFileInfoList']],
        ['word' => 'getAntiLinkFileOriginalUrl', 'provider' => [File::class, 'getAntiLinkFileOriginalUrl']],
        ['word' => 'logicalDeletionFiles', 'provider' => [File::class, 'logicalDeletionFiles']],
        ['word' => 'physicalDeletionFiles', 'provider' => [File::class, 'physicalDeletionFiles']],

        // Content
        ['word' => 'createDraft', 'provider' => [Content::class, 'createDraft']],
        ['word' => 'generateDraft', 'provider' => [Content::class, 'generateDraft']],
        ['word' => 'contentPublishByDraft', 'provider' => [Content::class, 'contentPublishByDraft']],
        ['word' => 'contentQuickPublish', 'provider' => [Content::class, 'contentQuickPublish']],
        ['word' => 'logicalDeletionContent', 'provider' => [Content::class, 'logicalDeletionContent']],
        ['word' => 'physicalDeletionContent', 'provider' => [Content::class, 'physicalDeletionContent']],
        ['word' => 'addContentMoreInfo', 'provider' => [Content::class, 'addContentMoreInfo']],
        ['word' => 'setContentSticky', 'provider' => [Content::class, 'setContentSticky']],
        ['word' => 'setContentDigest', 'provider' => [Content::class, 'setContentDigest']],
        ['word' => 'setContentCloseDelete', 'provider' => [Content::class, 'setContentCloseDelete']],
        ['word' => 'setPostAuth', 'provider' => [Content::class, 'setPostAuth']],
        ['word' => 'setPostAffiliateUser', 'provider' => [Content::class, 'setPostAffiliateUser']],

        // Manage
        ['word' => 'getPortalContent', 'provider' => [Manage::class, 'getPortalContent']],
        ['word' => 'updatePortalContent', 'provider' => [Manage::class, 'updatePortalContent']],
        ['word' => 'checkExtendPerm', 'provider' => [Manage::class, 'checkExtendPerm']],

        // Crontab
        ['word' => 'addCrontabItem', 'provider' => [Crontab::class, 'addCrontabItem']],
        ['word' => 'removeCrontabItem', 'provider' => [Crontab::class, 'removeCrontabItem']],

        // Subscribe
        ['word' => 'addSubscribeItem', 'provider' => [Subscribe::class, 'addSubscribeItem']],
        ['word' => 'removeSubscribeItem', 'provider' => [Subscribe::class, 'removeSubscribeItem']],

        // Fresns Crontab
        ['word' => 'checkUserRoleExpired', 'provider' => [Crontab::class, 'checkUserRoleExpired']],
        ['word' => 'checkDeleteAccount', 'provider' => [Crontab::class, 'checkDeleteAccount']],
        ['word' => 'checkAppsVersions', 'provider' => [Crontab::class, 'checkAppsVersions']],
    ];

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->registerCmdWordProvider();
    }
}
