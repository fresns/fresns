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

    // commons
    'title' => '安装',
    'desc' => '安装向导',
    'btn_check' => '再试一次',
    'btn_next' => '下一步',
    'btn_submit' => '提交',
    // intro
    'intro_title' => '欢迎使用 Fresns',
    'intro_desc' => '在开始前，我们需要您数据库的一些信息。请准备好如下信息。',
    'intro_database_name' => '数据库名',
    'intro_database_username' => '数据库用户名',
    'intro_database_password' => '数据库密码',
    'intro_database_host' => '数据库主机',
    'intro_database_table_prefix' => '数据表前缀（table prefix，特别是当您要在一个数据库中安装多个 Fresns 时）',
    'intro_database_desc' => '绝大多数时候，您的主机服务提供商会给您这些信息。如果您没有这些信息，在继续之前您将需要联系他们。如果您准备好了…',
    'intro_next_btn' => '现在开始安装',
    // server
    'server_title' => '基础环境检查',
    'server_check_php_version' => 'PHP 版本要求 8.0.2 或以上',
    'server_check_composer_version' => 'Composer 版本要求 2.4 或以上',
    'server_check_https' => '站点推荐使用 HTTPS',
    'server_check_folder_ownership' => '目录权限',
    'server_check_php_extensions' => 'PHP 扩展检查',
    'server_check_php_functions' => 'PHP 函数检查',
    'server_check_error' => '服务器环境检测失败',
    'server_check_self' => '需自我确认',
    'server_status_success' => '成功',
    'server_status_failure' => '失败',
    'server_status_warning' => '警告',
    'server_status_not_writable' => '不可写',
    'server_status_not_installed' => '未安装',
    'server_status_not_enabled' => '未启用',
    // database
    'database_title' => '填写数据库信息',
    'database_desc' => '请在下方填写您的数据库连接信息。如果您不确定，请联系您的主机提供商。',
    'database_name' => '数据库名',
    'database_name_desc' => '希望将 Fresns 安装到的数据库名称。',
    'database_username' => '用户名',
    'database_username_desc' => '您的数据库用户名。',
    'database_password' => '密码',
    'database_password_desc' => '您的数据库密码。',
    'database_host' => '数据库主机',
    'database_host_desc' => '如果 localhost 不能用，您通常可以从主机提供商处得到正确的信息。',
    'database_port' => '数据库端口',
    'database_port_desc' => '默认为 3306',
    'database_timezone' => '数据库时区',
    'database_timezone_desc' => '配置正确可以保证数据时间的准确性，以便 Fresns 能正确的处理时间。',
    'database_table_prefix' => '表前缀',
    'database_table_prefix_desc' => '如果您希望在同一个数据库安装多个 Fresns，请修改前缀。',
    'database_config_invalid' => '数据库配置无效',
    'database_import_log' => '数据导入情况',
    // register
    'register_welcome' => '欢迎来到 Fresns 安装过程！只要填写下面的信息，你就可以开始使用灵活可扩展和跨平台的社交网络服务软件了。',
    'register_title' => '填写管理信息',
    'register_desc' => '您需要填写一些基本信息。无需担心填错，这些信息以后可以再次修改。',
    'register_account_email' => '管理员邮箱',
    'register_account_password' => '登录密码',
    'register_account_password_confirm' => '确认登录密码',
    // done
    'done_title' => '成功！',
    'done_desc' => 'Fresns 安装完成。谢谢！',
    'done_account' => '登录账号',
    'done_password' => '登录密码',
    'done_password_desc' => '您填写的密码',
    'done_btn' => '前往登录',
];
