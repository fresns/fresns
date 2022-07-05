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

    'createSuccess' => 'Создать успех',
    'deleteSuccess' => 'успешно удален',
    'updateSuccess' => 'Успешно изменен',
    'upgradeSuccess' => 'Обновление завершено',
    'installSuccess' => 'Успех установки',
    'installFailure' => 'Сбой установки',
    'uninstallSuccess' => 'Успех деинсталляции',
    'uninstallFailure' => 'Неудачная деинсталляция',
    'copySuccess' => 'Копировать успех',
    // request
    'request_in_progress' => 'запрос в процессе...',
    'requestSuccess' => 'Запросить успех',
    'requestFailure' => 'Отказ запроса',
    // install
    'install_not_entered_key' => 'Пожалуйста, введите ключ fresns',
    'install_not_entered_dir' => 'Пожалуйста, введите каталог',
    'install_not_upload_zip' => 'Пожалуйста, выберите пакет установки',
    'install_in_progress' => 'Выполняется установка...',
    // upgrade
    'upgrade_none' => 'Нет обновления',
    'upgrade_fresns' => 'Для обновления доступна новая версия Freesns',
    'upgrade_fresns_tip' => 'Вы можете обновить до',
    'upgrade_fresns_warning' => 'Пожалуйста, создайте резервную копию базы данных перед обновлением, чтобы избежать потери данных из-за неправильного обновления.',
    'upgrade_confirm_tip' => 'Определите обновление?',
    'physical_upgrade_tip' => 'Это обновление не поддерживает автоматическое обновление, пожалуйста, используйте метод "физического обновления".',
    'physical_upgrade_version_guide' => 'Нажмите, чтобы прочитать инструкции для этого обновления',
    'physical_upgrade_guide' => 'Руководство по модернизации',
    'physical_upgrade_file_error' => 'Несоответствие файлов физического обновления',
    'physical_upgrade_confirm_tip' => 'Пожалуйста, убедитесь, что вы прочитали "Руководство по модернизации" и что вы обработали новую версию файла в соответствии с руководством.',
    'upgrade_in_progress' => 'Выполняется обновление...',
    'upgrade_step_1' => 'Проверка инициализации',
    'upgrade_step_2' => 'Скачать пакет приложений',
    'upgrade_step_3' => 'Unzip Application Package',
    'upgrade_step_4' => 'Обновление заявки',
    'upgrade_step_5' => 'Очистить кэш',
    'upgrade_step_6' => 'Заканчивать',
    // uninstall
    'uninstall_in_progress' => 'Деинсталляция в процессе...',
    'uninstall_step_1' => 'Проверка инициализации',
    'uninstall_step_2' => 'Обработка данных',
    'uninstall_step_3' => 'Удалить файлы',
    'uninstall_step_4' => 'Очистить кэш',
    'uninstall_step_5' => 'Сделанный',
    // website
    'website_path_empty_error' => 'Не удалось сохранить, параметр пути не может быть пустым',
    'website_path_format_error' => 'не удалось сохранить, параметры пути поддерживаются только в виде простых английских букв',
    'website_path_unique_error' => 'не удалось сохранить, дублирование параметров пути, имена параметров пути не должны повторять друг друга',
    // theme
    'theme_error' => 'Тема неверна или не существует',
    'theme_functions_file_error' => 'Файл представления конфигурации темы неверен или не существует',
    'theme_json_file_error' => 'Файл конфигурации темы неверен или не существует',
    'theme_json_format_error' => 'Файл конфигурации темы имеет неправильный формат',
    // others
    'account_not_found' => 'Учетная запись не существует или ввода ошибок',
    'account_login_limit' => 'Ошибка превысила системный лимит. Пожалуйста, войдите в систему снова через 1 час',
    'timezone_error' => 'Часовой пояс базы данных не совпадает с часовым поясом в конфигурационном файле .env',
    'timezone_env_edit_tip' => 'Пожалуйста, измените элемент конфигурации идентификатора часового пояса в файле .env',
    'secure_entry_route_conflicts' => 'Безопасность входной маршрутизационный конфликт',
    'language_exists' => 'Язык уже существует',
    'language_not_exists' => 'язык не существует',
    'plugin_not_exists' => 'плагин не существует',
    'map_exists' => 'Этот поставщик услуг карты уже использовался и не может быть создан заново',
    'map_not_exists' => 'карта не существует',
    'required_user_role_name' => 'Пожалуйста, заполните имя роли',
    'required_sticker_category_name' => 'Пожалуйста, заполните имя группы выражений',
    'required_group_category_name' => 'Пожалуйста, заполните название классификации группы',
    'required_group_name' => 'Пожалуйста, заполните имя группы',
    'delete_group_category_error' => 'Есть группа в классификации, не позволяющая удалению',
    'delete_default_language_error' => 'Язык по умолчанию не может быть удален',
    'account_connect_services_error' => 'Сторонняя поддержка взаимосвязи имеет повторяющуюся взаимосвязанную платформу',
    'post_datetime_select_error' => 'Диапазон даты настроек посту не может быть пустым',
    'post_datetime_select_range_error' => 'Дата окончания установки сообщения не может быть меньше, чем дата начала',
    'comment_datetime_select_error' => 'Дата диапазона, установленного комментарием, не может быть пустым',
    'comment_datetime_select_range_error' => 'Дата окончания настройки комментариев не может быть меньше, чем дата начала',
];
