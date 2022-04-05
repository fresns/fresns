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
    'title' => '安裝',
    'desc' => '安裝設定檔案',
    'btn_check' => '再試一次',
    'btn_next' => '下一步',
    'btn_submit' => '傳送',
    // intro
    'intro_title' => '歡迎使用 Fresns',
    'intro_desc' => '在開始安裝前，安裝程式必須取得資料庫的相關資訊；而進行安裝時，安裝人員必須能夠提供安裝程式下列資訊。',
    'intro_database_name' => '資料庫名稱',
    'intro_database_username' => '資料庫使用者名稱',
    'intro_database_password' => '資料庫密碼',
    'intro_database_host' => '資料庫主機位址',
    'intro_database_table_prefix' => '資料表前置詞 (用於在單一資料庫中安裝多個 Fresns)',
    'intro_database_desc' => '在多數的狀況中，這些資訊應由網站主機服務商提供。如果安裝人員並未獲得這些資訊，請在繼續安裝前先行聯絡廠商。當一切準備就緒後...',
    'intro_next_btn' => '開始安裝吧！',
    // server
    'server_title' => '基礎環境檢查',
    'server_check_php_version' => 'PHP 版本要求 8.0.2 或以上',
    'server_check_composer_version' => 'Composer 版本要求 2.3 或以上',
    'server_check_https' => '網站推薦使用 HTTPS',
    'server_check_folder_ownership' => '目錄權限',
    'server_check_php_extensions' => 'PHP 擴展檢查',
    'server_check_php_functions' => 'PHP 函數檢查',
    'server_check_error' => '伺服器環境檢測發生錯誤',
    'server_status_success' => '成功',
    'server_status_failure' => '失敗',
    'server_status_warning' => '警告',
    'server_status_not_writable' => '不可寫',
    'server_status_not_installed' => '未安裝',
    'server_status_not_enabled' => '未啟用',
    // database
    'database_title' => '填寫資料庫信息',
    'database_desc' => '安裝人員應於下方輸入資料庫連線詳細資料。如果不清楚以下欄位代表的意義，請洽詢網站主機服務商。',
    'database_name' => '資料庫名稱',
    'database_name_desc' => '使用於 Fresns 的網站資料庫名稱。',
    'database_username' => '使用者名稱',
    'database_username_desc' => 'Fresns 網站資料庫的使用者名稱。',
    'database_password' => '密碼',
    'database_password_desc' => 'Fresns 網站資料庫的密碼。',
    'database_host' => '資料庫主機位址',
    'database_host_desc' => '如果因故無法使用 localhost 進行連線，請要求網站主機服務商提供正確對應資訊。',
    'database_port' => '資料庫主機端口',
    'database_port_desc' => '默認為 3306',
    'database_table_prefix' => '資料表前置詞',
    'database_table_prefix_desc' => '	如需在同一個資料庫中安裝多個 Fresns，請修改這個欄位中的預設設定。',
    'database_config_invalid' => '資料庫配置無效',
    'database_import_log' => '資料匯入情況',
    // register
    'register_welcome' => '歡迎使用 Fresns 安裝程式！僅需填寫以下資訊，便能開始使用這個世界上最具擴充性、跨平台的社交網絡服務軟件了。',
    'register_title' => '安裝網站所需資訊',
    'register_desc' => '請提供下列資訊。不必擔心，這些設定均可於安裝完成後進行變更。',
    'register_account_email' => '電子郵件地址',
    'register_account_password' => '密碼',
    'register_account_password_confirm' => '再輸入一次密碼',
    // done
    'done_title' => '成功了！',
    'done_desc' => 'Fresns 已成功安裝。感謝你，享受吧！',
    'done_account' => '使用者',
    'done_password' => '登錄密碼',
    'done_password_desc' => '你所選擇的密碼。',
    'done_btn' => '登入',
];
