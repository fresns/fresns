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
    'title' => 'Установка',
    'desc' => 'Настройка файла конфигурации',
    'btn_check' => 'Попробовать ещё раз',
    'btn_next' => 'Следующий шаг',
    'btn_submit' => 'Отправить',
    // intro
    'intro_title' => 'Добро пожаловать.',
    'intro_desc' => 'Прежде чем мы начнём, потребуется информация о базе данных. Вот что вам необходимо знать до начала процедуры установки.',
    'intro_database_name' => 'Имя БД',
    'intro_database_username' => 'Имя пользователя базы данных',
    'intro_database_password' => 'Пароль к базе данных',
    'intro_database_host' => 'Хост БД',
    'intro_database_table_prefix' => 'Префикс таблиц (если вы хотите запустить более чем один Fresns на одной базе)',
    'intro_database_desc' => 'Скорее всего, эти данные были предоставлены вашим хостинг-провайдером. Если у вас нет этой информации, свяжитесь с их службой поддержки. А если есть, то можно продолжать…',
    'intro_next_btn' => 'Вперёд!',
    // server
    'server_title' => 'Требования к серверу',
    'server_check_php_version' => 'PHP 8.0.2+',
    'server_check_composer_version' => 'Composer 2.3+',
    'server_check_https' => 'Для сайтов рекомендуется HTTPS',
    'server_check_folder_ownership' => 'Владение папками',
    'server_check_php_extensions' => 'Расширения PHP',
    'server_check_php_functions' => 'Функции PHP',
    'server_check_error' => 'Сбой обнаружения серверного окружения.',
    'server_status_success' => 'ok',
    'server_status_failure' => 'Ошибка',
    'server_status_warning' => 'Предупреждение',
    'server_status_not_writable' => 'Не пишется',
    'server_status_not_installed' => 'Не установлено',
    'server_status_not_enabled' => 'Не включено',
    // database
    'database_title' => 'Информация о базе данных',
    'database_desc' => 'Введите здесь информацию о подключении к базе данных. Если вы в ней не уверены, свяжитесь с хостинг-провайдером.',
    'database_name' => 'Имя базы данных',
    'database_name_desc' => 'Имя базы данных, в которую вы хотите установить Fresns.',
    'database_username' => 'Имя пользователя',
    'database_username_desc' => 'Имя пользователя базы данных.',
    'database_password' => 'Пароль',
    'database_password_desc' => 'Пароль пользователя базы данных.',
    'database_host' => 'Сервер базы данных',
    'database_host_desc' => 'Если localhost не работает, нужно узнать правильный адрес в службе поддержки хостинг-провайдера.',
    'database_port' => 'Порт базы данных',
    'database_port_desc' => 'По умолчанию 3306',
    'database_timezone' => 'Часовой пояс базы данных',
    'database_timezone_desc' => 'Правильная конфигурация обеспечит точность данных в разы.',
    'database_table_prefix' => 'Префикс таблиц',
    'database_table_prefix_desc' => 'Если вы хотите запустить несколько копий Fresns в одной базе, измените это значение.',
    'database_config_invalid' => 'Неверная конфигурация базы данных',
    'database_import_log' => 'Журнал импорта данных',
    // register
    'register_welcome' => 'Добро пожаловать в процесс установки Fresns! Просто заполните приведенную ниже информацию, и вы будете на пути к использованию самого расширяемого и кросс-платформенного программного обеспечения для обслуживания социальных сетей в мире.',
    'register_title' => 'Требуется информация',
    'register_desc' => 'Пожалуйста, укажите следующую информацию. Не переживайте, потом вы всегда сможете изменить эти настройки.',
    'register_account_email' => 'Ваш e-mail',
    'register_account_password' => 'Пароль',
    'register_account_password_confirm' => 'Подтвердить пароль',
    // done
    'done_title' => 'Поздравляем!',
    'done_desc' => 'Fresns установлен. Желаем успешной работы!',
    'done_account' => 'Имя пользователя',
    'done_password' => 'Пароль',
    'done_password_desc' => 'Выбранный вами пароль.',
    'done_btn' => 'Войти',
];
