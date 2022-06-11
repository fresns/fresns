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

    'createSuccess' => '創建成功',
    'deleteSuccess' => '刪除成功',
    'updateSuccess' => '更改成功',
    'upgradeSuccess' => '更新成功',
    'installSuccess' => '安裝成功',
    'installFailure' => '安裝失敗',
    'uninstallSuccess' => '卸載成功',
    'uninstallFailure' => '卸載失敗',
    'copySuccess' => '複製成功',
    // request
    'request_in_progress' => '正在請求中...',
    'requestSuccess' => '請求成功',
    'requestFailure' => '請求失敗',
    // install
    'install_not_entered_key' => '請輸入標識名',
    'install_not_entered_dir' => '请输入目錄',
    'install_not_upload_zip' => '請選擇安裝包',
    'install_in_progress' => '正在安裝中...',
    // upgrade
    'upgrade_none' => '暫無更新',
    'upgrade_fresns' => '有新的 Fresns 版本可供升級。',
    'upgrade_fresns_tip' => '您可以升級到',
    'upgrade_fresns_warning' => '升級前請先備份資料庫，避免升級不當導致資料丟失。',
    'upgrade_confirm_tip' => '確定升級嗎？',
    'physical_upgrade_tip' => '本次升級不支持自動升級，請使用「物理升級」方法。',
    'physical_upgrade_version_guide' => '點擊閱讀本次版本更新說明',
    'physical_upgrade_guide' => '升級指南',
    'physical_upgrade_file_error' => '物理升級文件不匹配',
    'physical_upgrade_confirm_tip' => '請確認已經閱讀了「幫助說明」，並且按指南處理好了新版文件。',
    'upgrade_in_progress' => '正在更新中...',
    'upgrade_step_1' => '初始化驗證',
    'upgrade_step_2' => '下載應用包',
    'upgrade_step_3' => '解壓應用包',
    'upgrade_step_4' => '升級應用',
    'upgrade_step_5' => '清空緩存',
    'upgrade_step_6' => '完成',
    // uninstall
    'uninstall_in_progress' => '正在卸載中...',
    'uninstall_step_1' => '初始化驗證',
    'uninstall_step_2' => '數據處理',
    'uninstall_step_3' => '刪除文件',
    'uninstall_step_4' => '清空緩存',
    'uninstall_step_5' => '完成',
    // theme
    'theme_error' => '主題錯誤或者不存在',
    'theme_functions_file_error' => '主題配置的視圖文件錯誤或者不存在',
    'theme_json_file_error' => '主題配置文件錯誤或者不存在',
    'theme_json_format_error' => '主題配置文件格式錯誤',
    // others
    'account_not_found' => '賬號不存在或者輸入錯誤',
    'account_login_limit' => '錯誤已超系統限制，請 1 小時後再登錄',
    'timezone_error' => '資料庫時區和 .env 配置文件中時區不一致',
    'timezone_env_edit_tip' => '請修改根目錄 .env 配置文件中時區地名配置項',
    'secure_entry_route_conflicts' => '安全入口路由衝突',
    'language_exists' => '語言已存在',
    'language_not_exists' => '語言不存在',
    'plugin_not_exists' => '外掛不存在',
    'map_exists' => '該地圖服務商已被使用，不可重複創建',
    'map_not_exists' => '地圖不存在',
    'required_user_role_name' => '請填寫角色名稱',
    'required_sticker_category_name' => '請填寫表情組名稱',
    'required_group_category_name' => '請填寫小組分類名稱',
    'required_group_name' => '請填寫小組名稱',
    'delete_group_category_error' => '分類下存在社團，不允許刪除',
    'delete_default_language_error' => '默認語言不能刪除',
    'account_connect_services_error' => '第三方互聯支持中有重複的互聯平台',
    'post_datetime_select_error' => '貼文設置的日期範圍不能為空',
    'post_datetime_select_range_error' => '貼文設置的結束日期不能小於開始日期',
    'comment_datetime_select_error' => '留言設置的日期範圍不能為空',
    'comment_datetime_select_range_error' => '留言設置的結束日期不能小於開始日期',
];
