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

    'createSuccess' => 'Erfolg schaffen',
    'deleteSuccess' => 'erfolgreich gelöscht',
    'updateSuccess' => 'Erfolgreich modifiziert',
    'upgradeSuccess' => 'Update abgeschlossen',
    'installSuccess' => 'Erfolgreiche Installation',
    'installFailure' => 'Installation fehlgeschlagen',
    'uninstallSuccess' => 'Erfolgreiche Deinstallation',
    'uninstallFailure' => 'Deinstallation fehlgeschlagen',
    'copySuccess' => 'Erfolg kopieren',
    // request
    'request_in_progress' => 'Anfrage in Bearbeitung...',
    'requestSuccess' => 'Erfolg anfordern',
    'requestFailure' => 'Anfrage Fehlschlag',
    // install
    'install_in_progress' => 'Installation im Gange...',
    // upgrade
    'upgrade_none' => 'Kein Update',
    'upgrade_fresns' => 'Für das Upgrade steht eine neue FRESNS-Version zur Verfügung',
    'upgrade_fresns_tip' => 'Sie können ein Upgrade auf ein Upgrade verwenden',
    'upgrade_fresns_warning' => 'Bitte erstellen Sie vor dem Upgrade eine Sicherungskopie Ihrer Datenbank, um Datenverluste aufgrund eines unsachgemäßen Upgrades zu vermeiden.',
    'upgrade_confirm_tip' => 'Upgrade bestimmen?',
    'physical_upgrade_tip' => 'Dieses Update unterstützt kein automatisches Upgrade, bitte verwenden Sie die Methode des "physischen Upgrades".',
    'physical_upgrade_version_guide' => 'Klicken Sie hier, um die Anweisungen für dieses Update zu lesen',
    'physical_upgrade_guide' => 'Upgrade-Leitfaden',
    'physical_upgrade_file_error' => 'Unstimmigkeit der physischen Upgrade-Datei',
    'physical_upgrade_confirm_tip' => 'Bitte vergewissern Sie sich, dass Sie die "Upgrade-Leitfaden" gelesen und die neue Version der Datei entsprechend der Anleitung verarbeitet haben.',
    'upgrade_in_progress' => 'Upgrade im Gange...',
    'upgrade_step_1' => 'Initialisierungsüberprüfung',
    'upgrade_step_2' => 'Anwendungspaket herunterladen',
    'upgrade_step_3' => 'UNZIP-Anwendungspaket',
    'upgrade_step_4' => 'Anwendung ein Upgrade',
    'upgrade_step_5' => 'Den Cache leeren',
    'upgrade_step_6' => 'Ziel',
    // uninstall
    'uninstall_in_progress' => 'Deinstallation im Gange...',
    'uninstall_step_1' => 'Initialisierungsüberprüfung',
    'uninstall_step_2' => 'Datenverarbeitung',
    'uninstall_step_3' => 'Dateien löschen',
    'uninstall_step_4' => 'Cache leeren',
    'uninstall_step_5' => 'Getan',
    // others
    'account_not_found' => 'Konto ist nicht vorhanden oder geben Fehler ein',
    'account_login_limit' => 'Der Fehler hat das Systemlimit überschritten. Bitte melden Sie sich 1 Stunde später erneut an',
    'timezone_error' => 'Die Zeitzone der Datenbank stimmt nicht mit der Zeitzone in der Konfigurationsdatei .env überein',
    'timezone_env_edit_tip' => 'Bitte ändern Sie den Konfigurationseintrag timezone identifier in der .env-Datei',
    'secure_entry_route_conflicts' => 'Sicherheitseingang-Routing-Konflikt',
    'language_exists' => 'Sprache existiert bereits',
    'language_not_exists' => 'Sprache nicht vorhanden',
    'plugin_not_exists' => 'plugin nicht vorhanden',
    'map_not_exists' => 'Karte nicht vorhanden',
    'required_user_role_name' => 'Bitte füllen Sie den Namen der Rolle aus',
    'required_sticker_category_name' => 'Bitte füllen Sie den Namen der Expression-Gruppe aus',
    'required_group_category_name' => 'Bitte füllen Sie den Gruppenklassifizierungsnamen aus',
    'required_group_name' => 'Bitte füllen Sie den Gruppennamen aus',
    'delete_group_category_error' => 'Es gibt eine Gruppe in der Klassifizierung, die nicht löschbar ist',
    'delete_default_language_error' => 'Die Standardsprache kann nicht gelöscht werden',
    'account_connect_services_error' => 'Die Unterstützung von Drittanbietern verfügt über eine sich wiederholende miteinander verbundene Plattform',
    'post_datetime_select_error' => 'Der Datumsbereich der Posteinstellungen kann nicht leer sein',
    'post_datetime_select_range_error' => 'Das Enddatum der Posteinstellung kann nicht weniger als das Startdatum sein',
    'comment_datetime_select_error' => 'Der vom Kommentar festgelegte Datumsbereich kann nicht leer sein',
    'comment_datetime_select_range_error' => 'Das Enddatum der Kommentareinstellung kann nicht weniger als das Startdatum sein',
];
