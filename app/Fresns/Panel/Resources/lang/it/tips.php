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

    'createSuccess' => 'Creare successo',
    'deleteSuccess' => 'cancellato con successo',
    'updateSuccess' => 'Modificato con successo',
    'upgradeSuccess' => 'Aggiornamento completato',
    'installSuccess' => 'Installare con successo',
    'installFailure' => 'Installazione fallita',
    'uninstallSuccess' => 'Disinstallazione riuscita',
    'uninstallFailure' => 'Disinstallazione fallita',
    'copySuccess' => 'Copia il successo',
    // request
    'request_in_progress' => 'richiesta in corso...',
    'requestSuccess' => 'Richiesta di successo',
    'requestFailure' => 'Richiesta di fallimento',
    // install
    'install_not_entered_key' => 'Inserire la chiave fresns',
    'install_not_entered_dir' => 'Inserire una directory',
    'install_not_upload_zip' => 'Selezionare il pacchetto di installazione',
    'install_in_progress' => 'Installazione in corso...',
    'install_end' => 'Fine dell\'installazione',
    // upgrade
    'upgrade_none' => 'Nessun aggiornamento',
    'upgrade_fresns' => 'C\'è una nuova versione di Fresns disponibile per l\'aggiornamento',
    'upgrade_fresns_tip' => 'Puoi aggiornare a',
    'upgrade_fresns_warning' => 'Si prega di eseguire il backup del database prima di effettuare l\'aggiornamento per evitare la perdita di dati a causa di un aggiornamento improprio.',
    'upgrade_confirm_tip' => 'Determina l\'aggiornamento?',
    'physical_upgrade_tip' => 'Questo aggiornamento non supporta l\'aggiornamento automatico, si prega di utilizzare il metodo di "aggiornamento fisico".',
    'physical_upgrade_version_guide' => 'Clicca per leggere le istruzioni per questo aggiornamento',
    'physical_upgrade_guide' => 'Guida all\'aggiornamento',
    'physical_upgrade_file_error' => 'Mancata corrispondenza del file di aggiornamento fisico',
    'physical_upgrade_confirm_tip' => 'Assicurati di aver letto la "Guida all\'aggiornamento" e di aver elaborato la nuova versione del file secondo la guida.',
    'upgrade_in_progress' => 'Aggiornamento in corso...',
    'upgrade_step_1' => 'Verifica di inizializzazione',
    'upgrade_step_2' => 'Scarica pacchetto di applicazioni',
    'upgrade_step_3' => 'Pacchetto di applicazione decompresso',
    'upgrade_step_4' => 'Aggiorna applicazione',
    'upgrade_step_5' => 'Svuotare la cache',
    'upgrade_step_6' => 'Fine',
    // uninstall
    'uninstall_in_progress' => 'Disinstallazione in corso...',
    'uninstall_step_1' => 'Verifica di inizializzazione',
    'uninstall_step_2' => 'Elaborazione dati',
    'uninstall_step_3' => 'Cancella file',
    'uninstall_step_4' => 'Cancella cache',
    'uninstall_step_5' => 'Fatto',
    // website
    'website_path_empty_error' => 'Salvataggio fallito, il parametro percorso non può essere vuoto',
    'website_path_format_error' => 'non è riuscito a salvare, i parametri di percorso sono supportati solo in lettere semplici.',
    'website_path_unique_error' => 'Salvataggio fallito, parametri di percorso duplicati, i nomi dei parametri di percorso non possono ripetersi.',
    // theme
    'theme_error' => 'Il tema non è corretto o non esiste',
    'theme_functions_file_error' => 'Il file di configurazione della vista del tema non è corretto o non esiste.',
    'theme_json_file_error' => 'Il file di configurazione del tema non è corretto o non esiste',
    'theme_json_format_error' => 'Il file di configurazione del tema è nel formato sbagliato',
    // others
    'account_not_found' => 'L\'account non esiste o inserisci errori',
    'account_login_limit' => 'L\'errore ha superato il limite del sistema. Per favore, accedi di nuovo 1 ora dopo',
    'timezone_error' => 'Il fuso orario del database non corrisponde al fuso orario nel file di configurazione di .env',
    'timezone_env_edit_tip' => 'Modifica la voce di configurazione dell\'identificatore del fuso orario nel file .env',
    'secure_entry_route_conflicts' => 'Conflitto di routing di ingresso di sicurezza',
    'language_exists' => 'La lingua esiste già',
    'language_not_exists' => 'lingua non esiste',
    'plugin_not_exists' => 'plugin non esiste',
    'map_exists' => 'Questo fornitore di servizi cartografici è già stato utilizzato e non può essere ricreato.',
    'map_not_exists' => 'mappa non esiste',
    'required_user_role_name' => 'Si prega di compilare il nome del ruolo',
    'required_sticker_category_name' => 'Si prega di compilare il nome del gruppo di espressioni',
    'required_group_category_name' => 'Si prega di compilare il nome della classificazione del gruppo',
    'required_group_name' => 'Si prega di compilare il nome del gruppo',
    'delete_group_category_error' => 'C\'è un gruppo in classificazione, non permettendo la cancellazione',
    'delete_default_language_error' => 'La lingua predefinita non può essere cancellata',
    'account_connect_services_error' => 'Il supporto di interconnessione di terze parti ha una piattaforma interconnessa ripetitiva',
    'post_datetime_select_error' => 'L\'intervallo di data delle impostazioni post non può essere vuoto',
    'post_datetime_select_range_error' => 'La data di fine dell\'impostazione del post non può essere inferiore alla data di inizio',
    'comment_datetime_select_error' => 'L\'intervallo di date impostato dal commento non può essere vuoto',
    'comment_datetime_select_range_error' => 'La data di fine dell\'impostazione del commento non può essere inferiore alla data di inizio',
];
