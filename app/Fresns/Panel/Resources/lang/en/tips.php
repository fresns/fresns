<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
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
    'uninstallSuccess' => 'Uninstall Success',

    'createFailure' => 'Create Failure',
    'deleteFailure' => 'Delete Failure',
    'updateFailure' => 'Update Failure',
    'upgradeFailure' => 'Upgrade Failure',
    'installFailure' => 'Install Failure',
    'downloadFailure' => 'Download Failure',
    'uninstallFailure' => 'Uninstall Failure',

    'copySuccess' => 'Copy Success',
    'viewLog' => 'There was a problem with the implementation, please see the Fresns system log for details',
    // auth empty
    'auth_empty_title' => 'Please use the correct portal to login to the panel',
    'auth_empty_description' => 'You have logged out or your login has timed out, please visit the login portal to log in again.',
    // request
    'request_in_progress' => 'Request in progress...',
    'requestSuccess' => 'Request Success',
    'requestFailure' => 'Request Failure',
    // install
    'install_not_entered_key' => 'Please enter the fresns key',
    'install_not_entered_directory' => 'Please enter a directory',
    'install_not_upload_zip' => 'Please select the installation package',
    'install_in_progress' => 'Install in progress...',
    'install_end' => 'End of installation',
    // upgrade
    'upgrade_none' => 'No Upgrade',
    'upgrade_fresns' => 'A new version of Fresns is available for upgrade',
    'upgrade_fresns_tip' => 'You can upgrade to',
    'upgrade_fresns_warning' => 'Please backup the database before upgrading to avoid data loss due to improper upgrade.',
    'upgrade_confirm_tip' => 'Sure to upgrade?',
    'manual_upgrade_tip' => 'This update does not support automatic upgrade, please use the "manual upgrade" method.',
    'manual_upgrade_version_guide' => 'Click to read the description of this version update',
    'manual_upgrade_guide' => 'Upgrade Guide',
    'manual_upgrade_file_error' => 'Manual upgrade file mismatch',
    'manual_upgrade_confirm_tip' => 'Please make sure you have read the "Upgrade Guide" and processed the new version of the file according to the guide.',
    'upgrade_in_progress' => 'Upgrade in progress...',
    'auto_upgrade_step_1' => 'Initialization verification',
    'auto_upgrade_step_2' => 'Download application packages',
    'auto_upgrade_step_3' => 'Unzip the application package',
    'auto_upgrade_step_4' => 'Upgrade application',
    'auto_upgrade_step_5' => 'Clear cache',
    'auto_upgrade_step_6' => 'Done',
    'manualUpgrade_step_1' => 'Initialization verification',
    'manualUpgrade_step_2' => 'Update data',
    'manualUpgrade_step_3' => 'Install all plugin dependency packages (this step is a slow process, please be patient)',
    'manualUpgrade_step_4' => 'Publish and restore extensions activate',
    'manualUpgrade_step_5' => 'Update Fresns version information',
    'manualUpgrade_step_6' => 'Clear cache',
    'manualUpgrade_step_7' => 'Done',
    // uninstall
    'uninstall_in_progress' => 'Uninstall in progress...',
    'uninstall_step_1' => 'Initialization Verification',
    'uninstall_step_2' => 'Data Processing',
    'uninstall_step_3' => 'Delete files',
    'uninstall_step_4' => 'Clear cache',
    'uninstall_step_5' => 'Done',
    // delete app
    'delete_app_warning' => 'If you don\'t want to see update alerts for the application, you can delete it. Once deleted, you will no longer receive alerts for new versions.',
    // dashboard
    'panel_config' => 'After modifying the configuration, the cache needs to be cleared before the new configuration can take effect.',
    'plugin_install_or_upgrade' => 'After the plug-in is installed or upgraded, the default state is off and needs to be enabled manually to avoid system problems caused by errors in the plug-in.',
    // website
    'website_path_empty_error' => 'Failed to save, path parameter is not allowed to be empty',
    'website_path_format_error' => 'Failed to save, path parameters only support plain English letters',
    'website_path_reserved_error' => 'Save failed, path parameter contains system reserved parameter name',
    'website_path_unique_error' => 'Failed to save, duplicate path parameters, path parameter names are not allowed to duplicate each other',
    // others
    'markdown_editor' => 'The content supports Markdown syntax, but the input box does not support preview, please save it to the client to see the effect.',
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
