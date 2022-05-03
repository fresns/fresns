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
    'title' => 'Installation',
    'desc' => 'Fichier de configuration',
    'btn_check' => 'Recommencer',
    'btn_next' => 'Étape suivante',
    'btn_submit' => 'Envoyer',
    // intro
    'intro_title' => 'Bienvenue sur Fresns',
    'intro_desc' => 'Avant de nous lancer, nous avons besoin de certaines informations sur votre base de données. Il va vous falloir réunir les informations suivantes pour continuer.',
    'intro_database_name' => 'Nom de la base de données',
    'intro_database_username' => 'Identifiant MySQL',
    'intro_database_password' => 'Mot de passe de base de données',
    'intro_database_host' => 'Hôte de base de données',
    'intro_database_table_prefix' => 'Préfixe de table (si vous souhaitez avoir plusieurs Fresns sur une même base de données)',
    'intro_database_desc' => 'Vous devriez normalement avoir reçu ces informations de la part de votre hébergeur. Si vous ne les avez pas, il vous faudra contacter votre hébergeur afin de continuer. Si vous avez tout le nécessaire, alors…',
    'intro_next_btn' => 'C’est parti !',
    // server
    'server_title' => 'Exigences relatives au serveur',
    'server_check_php_version' => 'PHP 8.0.2+',
    'server_check_composer_version' => 'Composer 2.3+',
    'server_check_https' => 'HTTPS est recommandé pour les sites',
    'server_check_folder_ownership' => 'Propriété des dossiers',
    'server_check_php_extensions' => 'Extensions PHP',
    'server_check_php_functions' => 'Fonctions PHP',
    'server_check_error' => 'Échec de la détection de l\'environnement du serveur.',
    'server_check_self' => 'Autocontrôle',
    'server_status_success' => 'ok',
    'server_status_failure' => 'Erreur',
    'server_status_warning' => 'Avertissement',
    'server_status_not_writable' => 'Ne peut être écrit',
    'server_status_not_installed' => 'Non installé',
    'server_status_not_enabled' => 'Non activé',
    // database
    'database_title' => 'Informations sur la base de données',
    'database_desc' => 'Vous devez saisir ci-dessous les détails de connexion à votre base de données. Si vous ne les connaissez pas, contactez votre hébergeur.',
    'database_name' => 'Nom de la base de données',
    'database_name_desc' => 'Le nom de la base de données avec laquelle vous souhaitez utiliser Fresns.',
    'database_username' => 'Identifiant',
    'database_username_desc' => 'Votre identifiant MySQL.',
    'database_password' => 'Mot de passe',
    'database_password_desc' => 'Votre mot de passe de base de données.',
    'database_host' => 'Adresse de la base de données',
    'database_host_desc' => 'Si localhost ne fonctionne pas, demandez cette information à l’hébergeur de votre site.',
    'database_port' => 'Port de la base de données',
    'database_port_desc' => 'Par défaut, 3306',
    'database_timezone' => 'Fuseau horaire de la base de données',
    'database_timezone_desc' => 'Une configuration correcte permet de s\'assurer que les temps des données sont exacts afin que Fresns puisse traiter les temps correctement.',
    'database_table_prefix' => 'Préfixe des tables',
    'database_table_prefix_desc' => 'Si vous souhaitez faire tourner plusieurs installations de Fresns sur une même base de données, modifiez ce réglage.',
    'database_config_invalid' => 'Configuration de la base de données invalide',
    'database_import_log' => 'Journal d\'importation des données',
    // register
    'register_welcome' => 'Bienvenue dans le processus d\'installation de Fresns ! Remplissez simplement les informations ci-dessous et vous serez sur le chemin de l\'utilisation du logiciel de service de réseau social le plus extensible et multiplateforme au monde.',
    'register_title' => 'Informations nécessaires',
    'register_desc' => 'Veuillez renseigner les informations suivantes. Ne vous inquiétez pas, vous pourrez les modifier plus tard.',
    'register_account_email' => 'Votre e-mail',
    'register_account_password' => 'Mot de passe',
    'register_account_password_confirm' => 'Confirmer le mot de passe',
    // done
    'done_title' => 'Quel succès !',
    'done_desc' => 'Fresns est installé. Merci et profitez bien !',
    'done_account' => 'Identifiant',
    'done_password' => 'Mot de passe',
    'done_password_desc' => 'Le mot de passe que vous avez choisi.',
    'done_btn' => 'Se connecter',
];
