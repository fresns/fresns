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

    'createSuccess' => 'Create Success',
    'deleteSuccess' => 'Delete Success',
    'updateSuccess' => 'Update Success',
    'upgradeSuccess' => 'Upgrade Success',
    'installSuccess' => 'Install Success',
    'installFailure' => 'Install Failure',
    'uninstallSuccess' => 'Uninstall Success',
    'uninstallFailure' => 'Uninstall Failure',
    'copySuccess' => 'Copy Success',
    // request
    'request_in_progress' => 'Request in progress...',
    'requestSuccess' => 'Request Success',
    'requestFailure' => 'Request Failure',
    // install
    'install_not_entered_key' => 'Please enter the fresns key',
    'install_not_entered_dir' => 'Please enter a directory',
    'install_not_upload_zip' => 'Please select the installation package',
    'install_in_progress' => 'Install in progress...',
    // upgrade
    'upgrade_none' => 'No Upgrade',
    'upgrade_fresns' => 'A new version of Fresns is available for upgrade',
    'upgrade_fresns_tip' => 'You can upgrade to',
    'upgrade_fresns_warning' => 'Please backup the database before upgrading to avoid data loss due to improper upgrade.',
    'upgrade_confirm_tip' => 'Sure to upgrade?',
    'physical_upgrade_tip' => 'This update does not support automatic upgrade, please use the "physical upgrade" method.',
    'physical_upgrade_version_guide' => 'Click to read the description of this version update',
    'physical_upgrade_guide' => 'Upgrade Guide',
    'physical_upgrade_file_error' => 'Physical upgrade file mismatch',
    'physical_upgrade_confirm_tip' => 'Please make sure you have read the "Upgrade Guide" and processed the new version of the file according to the guide.',
    'upgrade_in_progress' => 'Upgrade in progress...',
    'upgrade_step_1' => 'Initialization verification',
    'upgrade_step_2' => 'Download application packages',
    'upgrade_step_3' => 'Unzip the application package',
    'upgrade_step_4' => 'Upgrade application',
    'upgrade_step_5' => 'Clear cache',
    'upgrade_step_6' => 'Done',
    // uninstall
    'uninstall_in_progress' => 'Uninstall in progress...',
    'uninstall_step_1' => 'Initialization Verification',
    'uninstall_step_2' => 'Data Processing',
    'uninstall_step_3' => 'Delete files',
    'uninstall_step_4' => 'Clear cache',
    'uninstall_step_5' => 'Done',
    // website
    'website_path_empty_error' => 'Failed to save, path parameter is not allowed to be empty',
    'website_path_format_error' => 'Failed to save, path parameters only support plain English letters',
    'website_path_unique_error' => 'Failed to save, duplicate path parameters, path parameter names are not allowed to duplicate each other',
    // theme
    'theme_error' => 'The theme is incorrect or does not exist',
    'theme_functions_file_error' => 'The theme config view file is incorrect or does not exist',
    'theme_json_file_error' => 'Theme config file is incorrect or does not exist',
    'theme_json_format_error' => 'The theme config file is in the wrong format',
    // others
    'account_not_found' => 'Account number does not exist or was entered incorrectly',
    'account_login_limit' => 'The error has exceeded the system limit. Please log in again 1 hour later',
    'timezone_error' => 'The database timezone does not match the timezone in the .env config file',
    'timezone_env_edit_tip' => 'Please modify the timezone identifier config item in the .env file',
    'secure_entry_route_conflicts' => 'Secure entry route conflicts',
    'language_exists' => 'Language already exists',
    'language_not_exists' => 'Language not exists',
    'plugin_not_exists' => 'Plugin not exists',
    'map_exists' => 'This map service provider has already been used and cannot be recreated',
    'map_not_exists' => 'Map not exists',
    'required_user_role_name' => 'Please fill in the role name',
    'required_sticker_category_name' => 'Please fill in the sticker category name',
    'required_group_category_name' => 'Please fill in the group category name',
    'required_group_name' => 'Please fill in the group name',
    'delete_group_category_error' => 'Groups exist under categories and are not allowed to be deleted',
    'delete_default_language_error' => 'Default language cannot be deleted',
    'account_connect_services_error' => 'Duplicate interconnection platforms in third-party interconnection support',
    'post_datetime_select_error' => 'The date range set for post cannot be empty',
    'post_datetime_select_range_error' => 'The end datetime of the post setting cannot be less than the start date',
    'comment_datetime_select_error' => 'The date range set for comment cannot be empty',
    'comment_datetime_select_range_error' => 'The end datetime of the comment setting cannot be less than the start date',
];
