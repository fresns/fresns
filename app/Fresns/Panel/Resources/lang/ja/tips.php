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

    'createSuccess' => '成功を生み出す',
    'deleteSuccess' => '削除されました',
    'updateSuccess' => '正常に変更されました',
    'upgradeSuccess' => 'アップグレード成功',
    'installSuccess' => 'インストール成功',
    'installFailure' => 'インストールに失敗しました',
    'uninstallSuccess' => 'アンインストールの成功',
    'uninstallFailure' => 'アンインストールに失敗しました',
    'copySuccess' => '成功をコピーする',
    'viewLog' => '実装に問題がありました。詳細はFresnsのシステムログをご覧ください。',
    // auth empty
    'auth_empty_title' => 'パネルにログインする際は、正しいポータルをご利用ください。',
    'auth_empty_description' => 'ログアウトした、またはログインがタイムアウトした場合は、ログインポータルで再ログインしてください。',
    // request
    'request_in_progress' => 'リクエスト中...',
    'requestSuccess' => 'リクエストサクセス',
    'requestFailure' => 'リクエストの失敗',
    // install
    'install_not_entered_key' => 'fresnsキーを入力してください。',
    'install_not_entered_directory' => 'ディレクトリを入力してください',
    'install_not_upload_zip' => 'インストールパッケージを選択してください',
    'install_in_progress' => 'インストール中...',
    'install_end' => 'インストール終了',
    // upgrade
    'upgrade_none' => '更新はありません',
    'upgrade_fresns' => 'Fresnsの新バージョンのアップグレードが可能です。',
    'upgrade_fresns_tip' => 'あなたはアップグレードすることができます',
    'upgrade_fresns_warning' => '不適切なアップグレードによるデータ損失を避けるため、アップグレード前にデータベースをバックアップしてください。',
    'upgrade_confirm_tip' => 'アップグレードを決定しますか？',
    'physical_upgrade_tip' => 'このアップデートは自動アップグレードに対応していませんので、「物理的なアップグレード」方法をご利用ください。',
    'physical_upgrade_version_guide' => 'クリックすると、このアップデートに関する説明が表示されます。',
    'physical_upgrade_guide' => 'アップグレードガイド',
    'physical_upgrade_file_error' => '物理アップグレードファイルの不一致',
    'physical_upgrade_confirm_tip' => '必ず「アップグレードガイド」をお読みいただき、ガイドに従って新しいバージョンのファイルを処理するようにしてください。',
    'upgrade_in_progress' => 'アップグレード中...',
    'upgrade_step_1' => '初期化検証',
    'upgrade_step_2' => 'アプリケーションパッケージをダウンロードします',
    'upgrade_step_3' => 'アプリケーションパッケージを解凍します',
    'upgrade_step_4' => 'アプリケーションをアップグレードします',
    'upgrade_step_5' => 'キャッシュを空にしてください',
    'upgrade_step_6' => '終了',
    // uninstall
    'uninstall_in_progress' => 'アンインストール中...',
    'uninstall_step_1' => '初期化検証',
    'uninstall_step_2' => '情報処理',
    'uninstall_step_3' => 'ファイルを削除します',
    'uninstall_step_4' => 'キャッシュの消去',
    'uninstall_step_5' => '終わり',
    // website
    'website_path_empty_error' => '保存に失敗しました。pathパラメータは空であってはいけません。',
    'website_path_format_error' => '保存に失敗しました。パスパラメータは、プレーンな英字でのみサポートされています。',
    'website_path_reserved_error' => '保存に失敗しました。path パラメータにシステム予約パラメータ名が含まれています。',
    'website_path_unique_error' => '保存に失敗しました、パスパラメータが重複しています、パスパラメータ名は互いに繰り返すことはできません。',
    // theme
    'theme_error' => 'テーマが正しくないか、存在しない',
    'theme_functions_file_error' => 'テーマ設定ビューファイルが正しくない、または存在しない',
    'theme_json_file_error' => 'テーマ設定ファイルが正しくない、または存在しない',
    'theme_json_format_error' => 'テーマ設定ファイルのフォーマットが間違っている',
    // others
    'account_not_found' => 'アカウントが存在しないか、エラーを入力します',
    'account_login_limit' => 'エラーはシステムの制限を超えました。1時間後に再ログインしてください。',
    'timezone_error' => 'データベースのタイムゾーンと .env 設定ファイルのタイムゾーンが一致しない',
    'timezone_env_edit_tip' => '.envファイルのタイムゾーン識別子の設定項目を変更してください',
    'secure_entry_route_conflicts' => '安全入り口ルーティングの競合',
    'language_exists' => '言語はすでに存在します',
    'language_not_exists' => '言語が存在しない',
    'plugin_not_exists' => 'プラグインが存在しない',
    'map_exists' => 'この地図サービスプロバイダはすでに使用されており、再作成することはできません。',
    'map_not_exists' => 'マップが存在しない',
    'required_user_role_name' => '役割の名前を記入してください',
    'required_sticker_category_name' => '式グループの名前を入力してください',
    'required_group_category_name' => 'グループ分類名を入力してください',
    'required_group_name' => 'グループ名を入力してください',
    'delete_group_category_error' => '分類にはグループがあり、削除を許可しない',
    'delete_default_language_error' => 'デフォルトの言語は削除できません',
    'account_connect_services_error' => 'サードパーティの相互接続サポートには、繰り返し相互接続されたプラットフォームがあります',
    'post_datetime_select_error' => '投稿設定の日付範囲を空にすることはできません',
    'post_datetime_select_range_error' => 'POST設定の終了日は開始日よりも小さいことはできません',
    'comment_datetime_select_error' => 'コメントによって設定された日付範囲を空にすることはできません',
    'comment_datetime_select_range_error' => 'コメント設定の終了日を開始日以内にすることはできません',
];
