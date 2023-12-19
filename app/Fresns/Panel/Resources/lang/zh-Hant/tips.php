<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
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
    'uninstallSuccess' => '卸載成功',

    'createFailure' => '創建失敗',
    'deleteFailure' => '刪除失敗',
    'updateFailure' => '更改失敗',
    'upgradeFailure' => '更新失敗',
    'installFailure' => '安裝失敗',
    'downloadFailure' => '下載失敗',
    'uninstallFailure' => '卸載失敗',

    'copySuccess' => '複製成功',
    'viewLog' => '執行遇到了問題，詳細信息請查看 Fresns 系統日誌',
    // auth empty
    'auth_empty_title' => '請使用正確的入口登入面板',
    'auth_empty_description' => '您已退出登入或者登入超時，請訪問登入入口重新登入。',
    // request
    'request_in_progress' => '正在請求中...',
    'requestSuccess' => '請求成功',
    'requestFailure' => '請求失敗',
    // install
    'install_not_entered_key' => '請輸入標識名',
    'install_not_entered_directory' => '请输入目錄',
    'install_not_upload_zip' => '請選擇安裝包',
    'install_in_progress' => '正在安裝中...',
    'install_end' => '安裝結束',
    // upgrade
    'upgrade_none' => '暫無更新',
    'upgrade_fresns' => '有新的 Fresns 版本可供升級。',
    'upgrade_fresns_tip' => '您可以升級到',
    'upgrade_fresns_warning' => '升級前請先備份資料庫，避免升級不當導致資料丟失。',
    'upgrade_confirm_tip' => '確定升級嗎？',
    'manual_upgrade_tip' => '本次升級不支持自動升級，請使用「手動升級」方法。',
    'manual_upgrade_version_guide' => '點擊閱讀本次版本更新說明',
    'manual_upgrade_guide' => '升級指南',
    'manual_upgrade_file_error' => '手動升級文件不匹配',
    'manual_upgrade_confirm_tip' => '請確認已經閱讀了「幫助說明」，並且按指南處理好了新版文件。',
    'upgrade_in_progress' => '正在更新中...',
    'auto_upgrade_step_1' => '初始化驗證',
    'auto_upgrade_step_2' => '下載應用包',
    'auto_upgrade_step_3' => '解壓應用包',
    'auto_upgrade_step_4' => '升級應用',
    'auto_upgrade_step_5' => '清理緩存',
    'auto_upgrade_step_6' => '完成',
    'manualUpgrade_step_1' => '初始化驗證',
    'manualUpgrade_step_2' => '更新數據',
    'manualUpgrade_step_3' => '安裝所有外掛依賴（該步驟流程較慢，請耐心等待）',
    'manualUpgrade_step_4' => '發布並恢復擴展啟用',
    'manualUpgrade_step_5' => '更新 Fresns 版本信息',
    'manualUpgrade_step_6' => '清理緩存',
    'manualUpgrade_step_7' => '完成',
    // uninstall
    'uninstall_in_progress' => '正在卸載中...',
    'uninstall_step_1' => '初始化驗證',
    'uninstall_step_2' => '數據處理',
    'uninstall_step_3' => '刪除文件',
    'uninstall_step_4' => '清理緩存',
    'uninstall_step_5' => '完成',
    // select
    'select_box_tip_plugin' => '選擇外掛關聯',
    'select_box_tip_role' => '選擇角色',
    'select_box_tip_group' => '選擇社團',
    'post_datetime_select_error' => '貼文設置的日期範圍不能為空',
    'post_datetime_select_range_error' => '貼文設置的結束日期不能小於開始日期',
    'comment_datetime_select_error' => '留言設置的日期範圍不能為空',
    'comment_datetime_select_range_error' => '留言設置的結束日期不能小於開始日期',
    // delete app
    'delete_app_warning' => '如果你不希望顯示該應用的升級提醒，可以刪除該應用。刪除後，有新版本將不再提示。',
    // dashboard
    'panel_config' => '修改配置後，需要清空緩存才能生效新配置。',
    'plugin_install_or_upgrade' => '外掛安裝或升級後，為避免外掛的錯誤導致系統問題，所以默認為關閉狀態，需手動啟用。',
    // website
    'website_path_empty_error' => '保存失敗，路徑參數不允許為空',
    'website_path_format_error' => '保存失敗，路徑參數僅支持純英文字母',
    'website_path_reserved_error' => '保存失敗，路徑參數含有系統保留參數名',
    'website_path_unique_error' => '保存失敗，路徑參數重複，路徑參數名不允許彼此重複',
    // others
    'markdown_editor' => '內容支援 Markdown 語法，但輸入框不支援預覽，請儲存後到用戶端查看效果。',
    'account_not_found' => '賬號不存在或者輸入錯誤',
    'account_login_limit' => '錯誤已超系統限制，請 1 小時後再登入',
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
    'required_group_name' => '請填寫小組名稱',
    'delete_default_language_error' => '默認語言不能刪除',
    'account_connect_services_error' => '第三方互聯支持中有重複的互聯平台',
];
