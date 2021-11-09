<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Fresns Installation Language Lines
    |--------------------------------------------------------------------------
    */

    // header
    'title' => '安装',
    'desc' => '安装向导',
    'checkBtn' => '重新检测',
    'nextBtn' => '下一步',
    'statusSuccess' => '成功',
    'statusFailure' => '失败',
    'statusWarning' => '警告',
    'statusNotEnabled' => '未启用',
    // step 1
    'step1Title' => '基础环境检查',
    'step1CheckPhpVersion' => 'PHP 版本要求 8.0.0 或以上',
    'step1CheckHttps' => '站点推荐使用 HTTPS',
    'step1CheckFolderOwnership' => '目录权限',
    'step1CheckPhpExtensions' => 'PHP 扩展检查',
    'step1CheckPhpFunctions' => 'PHP 函数检查',
    // step 2
    'step2Title' => '数据库环境检查',
    'step2CheckMySqlVersion' => 'MySQL 版本要求 5.7 或以上',
    'step2CheckTablePrefix' => '数据库表前缀',
    'step2CheckMigrations' => '数据结构状态',
    'step2CheckSeeders' => '初始化数据状态',
    // step 3
    'step3Title' => '填写管理信息',
    'step3Desc' => '您需要填写一些基本信息。无需担心填错，这些信息以后可以再次修改。管理员邮箱和手机号可以二选一，也可以全部填写。',
    'step3BackendHost' => '后端地址',
    'step3MemberNickname' => '管理员昵称',
    'step3AccountEmail' => '管理员邮箱',
    'step3AccountPhoneNumber' => '管理员手机号',
    'step3AccountPassword' => '登录密码',
    'step3CheckAccount' => '邮箱和手机号必须填一个',
    'step3Btn' => '确认注册',
    // done
    'doneTitle' => '成功！',
    'doneDesc' => 'Fresns 安装完成。谢谢！',
    'doneAccount' => '登录账号：您填写的邮箱或手机号（带国际区号的手机号）',
    'donePassword' => '登录密码：您填写的密码',
    'doneBtn' => '前往登录',
];
