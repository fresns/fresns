<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Providers;

use App\Fresns\Words\Account\Account;
use App\Fresns\Words\Account\Wallet;
use App\Fresns\Words\Basic\Basic;
use App\Fresns\Words\Content\Content;
use App\Fresns\Words\Crontab\Crontab;
use App\Fresns\Words\File\File;
use App\Fresns\Words\Send\Send;
use App\Fresns\Words\User\User;
use Illuminate\Support\ServiceProvider;

class CmdWordServiceProvider extends ServiceProvider implements \Fresns\CmdWordManager\Contracts\CmdWordProviderContract
{
    use \Fresns\CmdWordManager\Traits\CmdWordProviderTrait;

    protected $unikeyName = 'Fresns';

    /**
     * Fresns official developed command word.
     *
     * @var array[]
     */
    protected $cmdWordsMap = [
        // Basic
        ['word' => 'verifySign', 'provider' => [Basic::class, 'verifySign']],
        ['word' => 'verifyUrlSign', 'provider' => [Basic::class, 'verifyUrlSign']],
        ['word' => 'uploadSessionLog', 'provider' => [Basic::class, 'uploadSessionLog']],
        ['word' => 'sendCode', 'provider' => [Basic::class, 'sendCode']],
        ['word' => 'checkCode', 'provider' => [Basic::class, 'checkCode']],

        // Send
        ['word' => 'sendEmail', 'provider' => [Send::class, 'sendEmail']],
        ['word' => 'sendSms', 'provider' => [Send::class, 'sendSms']],
        ['word' => 'sendAppNotification', 'provider' => [Send::class, 'sendAppNotification']],
        ['word' => 'sendWechatMessage', 'provider' => [Send::class, 'sendWechatMessage']],

        // Account
        ['word' => 'addAccount', 'provider' => [Account::class, 'addAccount']],
        ['word' => 'verifyAccount', 'provider' => [Account::class, 'verifyAccount']],
        ['word' => 'createSessionToken', 'provider' => [Account::class, 'createSessionToken']],
        ['word' => 'verifySessionToken', 'provider' => [Account::class, 'verifySessionToken']],
        ['word' => 'logicalDeletionAccount', 'provider' => [Account::class, 'logicalDeletionAccount']],

        // Wallet
        ['word' => 'walletIncrease', 'provider' => [Wallet::class, 'walletIncrease']],
        ['word' => 'walletDecrease', 'provider' => [Wallet::class, 'walletDecrease']],

        // User
        ['word' => 'addUser', 'provider' => [User::class, 'addUser']],
        ['word' => 'verifyUser', 'provider' => [User::class, 'verifyUser']],
        ['word' => 'deactivateUserDialog', 'provider' => [User::class, 'deactivateUserDialog']],
        ['word' => 'logicalDeletionUser', 'provider' => [User::class, 'logicalDeletionUser']],

        // File
        ['word' => 'getUploadToken', 'provider' => [File::class, 'getUploadToken']],
        ['word' => 'uploadFile', 'provider' => [File::class, 'uploadFile']],
        ['word' => 'uploadFileInfo', 'provider' => [File::class, 'uploadFileInfo']],
        ['word' => 'getAntiLinkFileInfo', 'provider' => [File::class, 'getAntiLinkFileInfo']],
        ['word' => 'getAntiLinkFileInfoList', 'provider' => [File::class, 'getAntiLinkFileInfoList']],
        ['word' => 'getAntiLinkFileOriginalUrl', 'provider' => [File::class, 'getAntiLinkFileOriginalUrl']],
        ['word' => 'logicalDeletionFiles', 'provider' => [File::class, 'logicalDeletionFiles']],
        ['word' => 'physicalDeletionFiles', 'provider' => [File::class, 'physicalDeletionFiles']],

        // Content
        ['word' => 'createDraft', 'provider' => [Content::class, 'createDraft']],
        ['word' => 'generateDraft', 'provider' => [Content::class, 'generateDraft']],
        ['word' => 'contentPublishByDraft', 'provider' => [Content::class, 'contentPublishByDraft']],
        ['word' => 'contentDirectPublish', 'provider' => [Content::class, 'contentDirectPublish']],
        ['word' => 'logicalDeletionContent', 'provider' => [Content::class, 'logicalDeletionContent']],
        ['word' => 'physicalDeletionContent', 'provider' => [Content::class, 'physicalDeletionContent']],

        // Crontab
        ['word' => 'addCrontabItem', 'provider' => [Crontab::class, 'addCrontabItem']],
        ['word' => 'deleteCrontabItem', 'provider' => [Crontab::class, 'deleteCrontabItem']],

        // Fresns Crontab List
        ['word' => 'checkUserRoleExpired', 'provider' => [Crontab::class, 'checkUserRoleExpired']],
        ['word' => 'checkDeleteAccount', 'provider' => [Crontab::class, 'checkDeleteAccount']],
        ['word' => 'checkExtensionsVersion', 'provider' => [Crontab::class, 'checkExtensionsVersion']],
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCmdWordProvider();
    }
}
