<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Fresns Panel Tips Language Lines
    |--------------------------------------------------------------------------
    */

    'createSuccess' => '创建成功',
    'deleteSuccess' => '删除成功',
    'updateSuccess' => '修改成功',
    'upgradeSuccess' => '更新成功',
    'installSuccess' => '安装成功',
    'installFailure' => '安装失败',
    'uninstallSuccess' => '卸载成功',
    'uninstallFailure' => '卸载失败',
    'copySuccess' => '复制成功',
    // request
    'request_in_progress' => '正在请求中...',
    'requestSuccess' => '请求成功',
    'requestFailure' => '请求失败',
    // install
    'install_in_progress' => '正在安装中...',
    // upgrade
    'upgrade_none' => '暂无更新',
    'upgrade_fresns' => '有新的 Fresns 版本可供升级。',
    'upgrade_fresns_tip' => '您可以升级到',
    'upgrade_fresns_warning' => '升级前请先备份数据库，避免升级不当导致数据丢失。',
    'upgrade_confirm_tip' => '确定升级吗？',
    'physical_upgrade_guide' => '升级指南',
    'physical_upgrade_file_error' => '物理升级文件不匹配',
    'physical_upgrade_confirm_tip' => '请确认已经阅读了「升級指南」，并且按指南处理好了新版文件。',
    'upgrade_in_progress' => '正在更新中...',
    'upgrade_step_1' => '初始化验证',
    'upgrade_step_2' => '下载应用包',
    'upgrade_step_3' => '解压应用包',
    'upgrade_step_4' => '升级应用',
    'upgrade_step_5' => '清空缓存',
    'upgrade_step_6' => '完成',
    // uninstall
    'uninstall_in_progress' => '正在卸载中...',
    'uninstall_step_1' => '初始化验证',
    'uninstall_step_2' => '数据处理',
    'uninstall_step_3' => '删除文件',
    'uninstall_step_4' => '清空缓存',
    'uninstall_step_5' => '完成',
    // others
    'account_not_found' => '账号不存在或者输入错误',
    'account_login_limit' => '错误已超系统限制，请 1 小时后再登录',
    'timezone_error' => '数据库时区和 .env 配置文件中时区不一致',
    'timezone_env_edit_tip' => '请修改根目录 .env 配置文件中时区地名配置项',
    'secure_entry_route_conflicts' => '安全入口路由冲突',
    'language_exists' => '语言已存在',
    'language_not_exists' => '语言不存在',
    'plugin_not_exists' => '插件不存在',
    'map_not_exists' => '地图服务商不存在',
    'required_user_role_name' => '请填写角色名称',
    'required_sticker_category_name' => '请填写表情组名称',
    'required_group_category_name' => '请填写小组分类名称',
    'required_group_name' => '请填写小组名称',
    'delete_group_category_error' => '分类下存在小组，不允许删除',
    'delete_default_language_error' => '默认语言不能删除',
    'account_connect_services_error' => '第三方互联支持中有重复的互联平台',
    'post_datetime_select_error' => '帖子设置的日期范围不能为空',
    'post_datetime_select_range_error' => '帖子设置的结束日期不能小于开始日期',
    'comment_datetime_select_error' => '评论设置的日期范围不能为空',
    'comment_datetime_select_range_error' => '评论设置的结束日期不能小于开始日期',
];
