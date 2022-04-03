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
    'title' => 'インストール',
    'desc' => '構成ファイルのセットアップ',
    'btn_check' => 'もう一度お試しください',
    'btn_next' => '次のステップ',
    'btn_submit' => '送信',
    // intro
    'intro_title' => 'Fresns へようこそ。',
    'intro_desc' => '作業を始める前にデータベースに関するいくつかの情報が必要となります。以下の項目を準備してください。',
    'intro_database_name' => 'データベース名',
    'intro_database_username' => 'データベースのユーザー名',
    'intro_database_password' => 'データベースのパスワード',
    'intro_database_host' => 'データベースホスト',
    'intro_database_table_prefix' => 'テーブル接頭辞 (1つのデータベースに複数の Fresns を作動させる場合)',
    'intro_database_desc' => 'おそらく、これらのデータベース情報はホスティング先から提供されています。データベース情報がわからない場合、作業を続行する前にホスティング先と連絡を取ってください。すべての準備が整っているなら…',
    'intro_next_btn' => 'さあ、始めましょう !',
    // server
    'server_title' => 'サーバーの要件',
    'server_check_php_version' => 'PHP 8.0.2+',
    'server_check_composer_version' => 'Composer 2.3+',
    'server_check_https' => 'HTTPSを推奨するサイト',
    'server_check_folder_ownership' => 'フォルダの所有権',
    'server_check_php_extensions' => 'PHP拡張機能',
    'server_check_php_functions' => 'PHP 関数',
    'server_check_error' => 'サーバー環境検知に失敗しました。',
    'server_status_success' => 'OK',
    'server_status_failure' => 'エラー',
    'server_status_warning' => '警告',
    'server_status_not_enabled' => '有効でない',
    // database
    'database_title' => 'データベース情報',
    'database_desc' => '以下にデータベース接続のための詳細を入力してください。これらのデータについて分からない点があれば、ホストに連絡を取ってください。',
    'database_name' => 'データベース名',
    'database_name_desc' => 'Fresns で使用したいデータベース名。',
    'database_username' => 'ユーザー名',
    'database_username_desc' => 'データベースのユーザー名。',
    'database_password' => 'パスワード',
    'database_password_desc' => 'データベースのパスワード。',
    'database_host' => 'データベースのホスト名',
    'database_host_desc' => 'localhost が動作しない場合には Web ホストからこの情報を取得することができます。',
    'database_port' => 'データベースポート',
    'database_port_desc' => 'デフォルトは3306',
    'database_table_prefix' => 'テーブル接頭辞',
    'database_table_prefix_desc' => 'ひとつのデータベースに複数の Fresns をインストールしたい場合、これを変えてください。',
    'database_import_log' => 'データインポートログ',
    // register
    'register_welcome' => 'Fresnsのインストール手順へようこそ! 以下の情報を入力するだけで、世界で最も拡張性が高く、クロスプラットフォームなソーシャルネットワークサービスソフトウェアを使用することができます。',
    'register_title' => '必要情報',
    'register_desc' => '次の情報を入力してください。ご心配なく、これらの情報は後からいつでも変更できます。',
    'register_account_email' => 'メールアドレス',
    'register_account_password' => 'パスワード',
    'register_account_password_confirm' => 'パスワードの確認',
    // done
    'done_title' => '成功しました !',
    'done_desc' => 'Fresns をインストールしました。ありがとうございます。それではお楽しみください !',
    'done_account' => 'ユーザー名',
    'done_password' => 'パスワード',
    'done_password_desc' => '選択したパスワード。',
    'done_btn' => 'ログイン',
];
