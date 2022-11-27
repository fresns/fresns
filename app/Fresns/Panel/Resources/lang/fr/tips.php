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

    'createSuccess' => 'Créer un succès',
    'deleteSuccess' => 'supprimé avec succès',
    'updateSuccess' => 'Modifié avec succès',
    'upgradeSuccess' => 'Mise à jour terminée',
    'installSuccess' => 'Installation réussie',
    'installFailure' => 'Échec de l\'installation',
    'uninstallSuccess' => 'Succès de la désinstallation',
    'uninstallFailure' => 'Échec de la désinstallation',
    'copySuccess' => 'Copier le succès',
    'viewLog' => 'Il y a eu un problème avec l\'implémentation, veuillez consulter le journal système de Fresns pour plus de détails.',
    // auth empty
    'auth_empty_title' => 'Veuillez utiliser le bon portail pour vous connecter au panneau.',
    'auth_empty_description' => 'Vous vous êtes déconnecté ou votre connexion a expiré, veuillez visiter le portail de connexion pour vous reconnecter.',
    // request
    'request_in_progress' => 'demande en cours...',
    'requestSuccess' => 'Demande de succès',
    'requestFailure' => 'Échec de la demande',
    // install
    'install_not_entered_key' => 'Veuillez entrer la clé de la fresque',
    'install_not_entered_directory' => 'Veuillez entrer un répertoire',
    'install_not_upload_zip' => 'Veuillez sélectionner le paquet d\'installation',
    'install_in_progress' => 'Installation en cours...',
    'install_end' => 'Fin de l\'installation',
    // upgrade
    'upgrade_none' => 'Pas de mise à jour',
    'upgrade_fresns' => 'Il existe une nouvelle version Fresns disponible pour la mise à niveau',
    'upgrade_fresns_tip' => 'Vous pouvez passer à',
    'upgrade_fresns_warning' => 'Veuillez sauvegarder votre base de données avant de procéder à la mise à niveau afin d\'éviter toute perte de données due à une mise à niveau incorrecte.',
    'upgrade_confirm_tip' => 'Déterminer la mise à niveau?',
    'physical_upgrade_tip' => 'Cette mise à jour ne prend pas en charge la mise à niveau automatique, veuillez utiliser la méthode de "mise à niveau physique".',
    'physical_upgrade_version_guide' => 'Cliquez pour lire les instructions de cette mise à jour',
    'physical_upgrade_guide' => 'Guide de mise à niveau',
    'physical_upgrade_file_error' => 'Mauvaise concordance du fichier de mise à niveau physique',
    'physical_upgrade_confirm_tip' => 'Veuillez vous assurer que vous avez lu le "Guide de mise à niveau" et que vous avez traité la nouvelle version du fichier conformément au guide.',
    'upgrade_in_progress' => 'Mise à niveau en cours...',
    'auto_upgrade_step_1' => 'Vérification d\'initialisation',
    'auto_upgrade_step_2' => 'Télécharger le package d\'applications',
    'auto_upgrade_step_3' => 'Paquet d\'application UNZIP',
    'auto_upgrade_step_4' => 'Application de mise à niveau',
    'auto_upgrade_step_5' => 'Vider le cache',
    'auto_upgrade_step_6' => 'Finir',
    'physicalUpgrade_step_1' => 'Vérification d\'initialisation',
    'physicalUpgrade_step_2' => 'Mise à jour des données',
    'physicalUpgrade_step_3' => 'Installer tous les paquets de dépendance des plugins (cette étape est un processus lent, veuillez être patient)',
    'physicalUpgrade_step_4' => 'Publier et restaurer l\'activation des extensions',
    'physicalUpgrade_step_5' => 'Mettre à jour les informations sur la version de Fresns',
    'physicalUpgrade_step_6' => 'Vider le cache',
    'physicalUpgrade_step_7' => 'Finir',
    // uninstall
    'uninstall_in_progress' => 'Désinstallation en cours...',
    'uninstall_step_1' => 'Vérification d\'initialisation',
    'uninstall_step_2' => 'Traitement de l\'information',
    'uninstall_step_3' => 'Supprimer les fichiers',
    'uninstall_step_4' => 'Vider le cache',
    'uninstall_step_5' => 'Terminé',
    // website
    'website_path_empty_error' => 'Échec de la sauvegarde, le paramètre "path" ne doit pas être vide.',
    'website_path_format_error' => 'a échoué à enregistrer, les paramètres de chemin d\'accès ne sont supportés qu\'en lettres anglaises simples.',
    'website_path_reserved_error' => 'L\'enregistrement a échoué, le paramètre path contient un nom de paramètre réservé au système.',
    'website_path_unique_error' => 'Échec de l\'enregistrement, paramètres de chemin en double, les noms des paramètres de chemin ne sont pas autorisés à se répéter les uns les autres.',
    // theme
    'theme_error' => 'Le thème est incorrect ou n\'existe pas',
    'theme_functions_file_error' => 'Le fichier de vue de la configuration du thème est incorrect ou n\'existe pas.',
    'theme_json_file_error' => 'Le fichier de configuration du thème est incorrect ou n\'existe pas',
    'theme_json_format_error' => 'Le fichier de configuration du thème n\'a pas le bon format',
    // others
    'account_not_found' => 'Compte n\'existe pas ou n\'entre pas d\'erreur',
    'account_login_limit' => 'L\'erreur a dépassé la limite du système. Veuillez vous reconnecter 1 heure plus tard',
    'timezone_error' => 'Le fuseau horaire de la base de données ne correspond pas au fuseau horaire du fichier de configuration .env',
    'timezone_env_edit_tip' => 'Veuillez modifier l\'élément de configuration de l\'identifiant du fuseau horaire dans le fichier .env',
    'secure_entry_route_conflicts' => 'Conflit de routage d\'entrée de sécurité',
    'language_exists' => 'La langue existe déjà',
    'language_not_exists' => 'la langue n\'existe pas',
    'plugin_not_exists' => 'plugin n\'existe pas',
    'map_exists' => 'Ce fournisseur de services cartographiques a déjà été utilisé et ne peut être recréé.',
    'map_not_exists' => 'carte inexistante',
    'required_user_role_name' => 'S\'il vous plaît remplir le nom du rôle',
    'required_sticker_category_name' => 'S\'il vous plaît remplir le nom du groupe d\'expression',
    'required_group_category_name' => 'Veuillez remplir le nom de la classification du groupe',
    'required_group_name' => 'S\'il vous plaît remplir le nom du groupe',
    'delete_group_category_error' => 'Il y a un groupe de classification, ne permettant pas la suppression',
    'delete_default_language_error' => 'La langue par défaut ne peut pas être supprimée',
    'account_connect_services_error' => 'Le support d\'interconnexion tierce a une plate-forme interconnectée répétitive',
    'post_datetime_select_error' => 'La plage de dates des paramètres de poste ne peut pas être vide',
    'post_datetime_select_range_error' => 'La date de fin de la post-réglage ne peut pas être inférieure à la date de début',
    'comment_datetime_select_error' => 'La plage de date définie par le commentaire ne peut pas être vide',
    'comment_datetime_select_range_error' => 'La date de fin du paramètre de commentaire ne peut être inférieure à la date de début',
];
