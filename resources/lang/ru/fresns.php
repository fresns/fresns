<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fresns Console Language Lines
    |--------------------------------------------------------------------------
    */

    //Login page
    'language' => 'Язык',
    'login' => 'Вход в систему',
    'account' => 'Номер счета',
    'password' => 'Пароль',
    'enter' => 'Введите',
    //Menu
    'logout' => 'Выйти из системы',
    'menuDashboard' => 'Dashboard',
    'menuSettings' => 'Настройки',
    'menuKeys' => 'Ключи',
    'menuAdmins' => 'Администраторы',
    'menuWebsites' => 'Сайты',
    'menuApps' => 'Приложения',
    'menuPlugins' => 'Плагины',
    'menuAppStore' => 'App Store',
    //Dashboard
    'welcome' => 'Добро пожаловать в Fresns',
    'currentVersion' => 'Текущая версия',
    'overview' => 'Обзор',
    'userCounts' => 'Количество аккаунтов',
    'memberCounts' => 'Количество пользователей',
    'groupCounts' => 'Количество групп',
    'hashtagCounts' => 'Количество хэштегов',
    'postCounts' => 'Количество постов',
    'commentCounts' => 'Количество комментариев',
    'extensions' => 'Расширения',
    'keys' => 'Ключи',
    'controlPanel' => 'Панель управления',
    'engines' => 'Двигатели',
    'themes' => 'Темы',
    'apps' => 'Приложения',
    'plugins' => 'Плагины',
    'support' => 'Поддержка',
    'fresnsSite' => 'Официальный сайт',
    'fresnsTeam' => 'Команда',
    'fresnsPartners' => 'Партнеры',
    'fresnsJoin' => 'Присоединяйтесь к',
    'fresnsAppStore' => 'App Store',
    'fresnsCommunity' => 'Форум',
    'news' => 'События и новости Fresns',
    'installs' => 'Установки',
    'installIntro' => 'Если файлы приложения были размещены в указанном каталоге, выберите Локальная установка. Если вы приобрели приложение в магазине приложений Fresns и получили код установки, используйте Remote Install.',
    'localInstall' => 'Локальная установка',
    'localInstallBtn' => 'Подтвердите',
    'localInstallInfo' => 'Файлы приложений уже размещены в указанном каталоге, введите имя папки для установки',
    'codeInstall' => 'Установить код',
    'codeInstallBtn' => 'Отправить',
    'codeInstallInfo' => 'Приложение приобретено в Fresns App Store, введите код установки для установки',
    'folderName' => 'Имя папки',
    'fresnsCode' => 'Fresns Code',
    'updates' => 'Обновления',
    'updatesNull' => 'На данный момент обновлений нет',
    'updateBtn' => 'Обновление',
    //Settings
    'consoleTitle' => 'Настройки консоли',
    'consoleIntro' => 'Настройка системы бэкэнд-консоли Fresns',
    'backendDomain' => 'Домен бэкенда',
    'backendDomainInfo' => 'Адрес доступа по умолчанию для основного API приложения и плагинов, без / в конце',
    'backendPath' => 'Путь к бэкенду',
    'backendPathInfo' => 'Установка входа в консоль только через указанный безопасный портал',
    'consoleUrlName' => 'URL консоли',
    'copyConsoleUrl' => 'Копировать',
    'copyConsoleUrlSuccess' => 'Успех размножения',
    'copyConsoleUrlWarning' => 'Сбой копирования',
    'siteDomain' => 'Домен сайта',
    'siteDomainInfo' => 'Адрес запуска основного сайта (интегрированное или автономное развертывание) без / в конце',
    'consoleSettingBtn' => 'Сохранить настройки',
    'systemAdminTitle' => 'Администратор системы',
    'systemAdminIntro' => 'Администратор с доступом к консоли',
    'systemAdminUserId' => 'Пользователь ID',
    'systemAdminAccount' => 'Учетная запись',
    'systemAdminOptions' => 'Параметры',
    'deleteSystemAdmin' => 'Удалить',
    'addSystemAdmin' => 'Добавить администратора',
    'addSystemAdminTitle' => 'Новый системный администратор',
    'addSystemAdminAccount' => 'Учетная запись',
    'addSystemAdminAccountDesc' => 'Электронная почта или номер телефона',
    'addSystemAdminAccountInfo' => 'Номер мобильного телефона должен быть полным номером с международным кодом города',
    'addSystemAdminBtn' => 'Поиск и добавление',
    //Keys
    'keysTitle' => 'Ключ API',
    'keysIntro' => 'Ключевые учетные данные очень важны и не должны легко раскрываться другим.',
    'keysNull' => 'Нет ключа',
    'keysTablePlatform' => 'Платформа',
    'keysTableName' => 'Название',
    'keysTableAppId' => 'App ID',
    'keysTableAppSecret' => 'App Secret',
    'keysTableType' => 'Тип',
    'keysTableEnableStatus' => 'Включить Статус',
    'keysTableOptions' => 'Параметры',
    'keysTableOptionEdit' => 'Редактировать',
    'keysTableOptionReset' => 'Сброс ключа',
    'keysTableOptionDelete' => 'Удалить',
    'addKey' => 'Добавить ключ',
    'addKeyTitle' => 'Создать новый ключ',
    'addKeyBtn' => 'Отправить для создания',
    'editKeyTitle' => 'Клавиша редактирования',
    'editKeyBtn' => 'Отправить правки',
    'keyFormPlatform' => 'Платформа',
    'keyFormPlatformChooseOption' => 'Выберите ключевую платформу приложения',
    'keyFormName' => 'Название',
    'keyFormType' => 'Тип',
    'keyFormTypePlugin' => 'Назначенный плагин',
    'keyFormTypePluginInfo' => 'Ключ не позволяет запросить основной API приложения',
    'keyFormTypePluginChooseOption' => 'Выберите, для какого плагина будет использоваться ключ',
    'keyFormStatus' => 'Статус',
    'keyTypeFresns' => 'Fresns API',
    'keyTypePlugin' => 'API плагина',
    'keyStatusActivate' => 'Активировать',
    'keyStatusDeactivate' => 'Деактивировать',
    //Admins
    'adminsTitle' => 'Панель управления',
    'adminsIntro' => 'По желанию можно установить различные панели управления, чтобы испытать различные настройки функций и методы управления.',
    'adminsNull' => 'Панель управления еще не установлена',
    //Website
    'enginesTitle' => 'Двигатели',
    'enginesIntro' => 'Выберите другой двигатель для получения более персонализированных функций и услуг',
    'enginesNull' => 'Движок сайта пока не установлен',
    'enginesTableName' => 'Двигатель',
    'enginesTableNameInfo' => 'Если вы хотите развернуть отдельный веб-сайт или мобильное приложение без веб-сайта. Просто "отключите" или "удалите" движок сайта, чтобы Fresns был просто back-end системой с работающими API и плагинами.',
    'enginesTableAuthor' => 'Автор',
    'enginesTableTheme' => 'Тема',
    'enginesTableThemePcNull' => 'Не установлено',
    'enginesTableThemeMobileNull' => 'Не установлено',
    'enginesTableOptions' => 'Опции',
    'enginesTableOptionsInfo' => 'Поддерживаются несколько движков, если они не конфликтуют с путями друг друга, за подробностями обращайтесь к разработчику движка.',
    'enginesTableOptionsTheme' => 'Назначенная тема',
    'engineThemeTitle' => 'Назначенный шаблон темы',
    'engineThemeNoOption' => 'Нет темы',
    'engineThemePc' => 'Компьютерные темы',
    'engineThemeMobile' => 'Мобильные темы',
    'engineThemeBtn' => 'Сохранить',
    'themesTitle' => 'Тема',
    'themesIntro' => 'Выберите другую тему для более индивидуального стиля и взаимодействия.',
    'themesNull' => 'На данный момент шаблон темы не доступен',
    //Apps
    'appsTitle' => 'Приложения',
    'appsIntro' => 'По желанию можно установить различные приложения для создания различных сценариев работы и режимов применения.',
    'appsNull' => 'Мобильное приложение еще не установлено',
    //Plugins
    'pluginsTitle' => 'Плагины',
    'pluginsIntro' => 'Гибкие функции, мощные расширения и свобода делать то, что вы хотите.',
    'pluginsNull' => 'Плагин не установлен',
    'pluginsTabAll' => 'Все',
    'pluginsTabActive' => 'Активные',
    'pluginsTabInactive' => 'Неактивные',
    'pluginsTableName' => 'Имя',
    'pluginsTableDesc' => 'Описание',
    'pluginsTableAuthor' => 'Автор',
    'pluginsTableOptions' => 'Опции',
    //Controls
    'author' => 'Автор',
    'activate' => 'Активировать',
    'activateInfo' => 'Нажмите, чтобы активировать',
    'uninstall' => 'Удалить',
    'uninstallInfo' => 'Нажмите для деинсталляции',
    'deactivate' => 'Деактивировать',
    'deactivateInfo' => 'Нажмите для деактивации',
    'setting' => 'Настройка',
    'settingInfo' => 'Перейдите на страницу настроек',
    'newVersion' => 'Новый',
    'newVersionInfo' => 'Нажмите на приборную панель для обновления',
    'cancel' => 'Отмена',
    'confirmDelete' => 'Подтвердить удаление',
    'confirmUninstall' => 'Подтвердите деинсталляцию',
    'inputNull' => 'Не может быть пустым',
    'inputError' => 'Неправильный',
    //Local Install Step
    'localInstallStep1' => 'Найти папку',
    'localInstallStep2' => 'Проверка инициализации',
    'localInstallStep3' => 'Установка расширения',
    'localInstallStep4' => 'Опустошение кэша',
    'localInstallStep5' => 'Отделка',
    //Code Install Step
    'codeInstallStep1' => 'Проверка инициализации',
    'codeInstallStep2' => 'Загрузить пакет расширения',
    'codeInstallStep3' => 'Распакуйте пакет расширения',
    'codeInstallStep4' => 'Установка расширения',
    'codeInstallStep5' => 'Опустошение кэша',
    'codeInstallStep6' => 'Отделка',
    //Update Step
    'updateStep1' => 'Проверка инициализации',
    'updateStep2' => 'Загрузить пакет расширения',
    'updateStep3' => 'Распакуйте пакет расширения',
    'updateStep4' => 'Обновление расширения',
    'updateStep5' => 'Опустошение кэша',
    'updateStep6' => 'Отделка',
    //Uninstall Step
    'uninstallOption' => 'Одновременное удаление данных из этого плагина',
    'uninstallStep1' => 'Первоначальная валидация',
    'uninstallStep2' => 'Обработка данных',
    'uninstallStep3' => 'Удаление файлов',
    'uninstallStep4' => 'Опустошение кэша',
    'uninstallStep5' => 'Отделка',
];
