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

    'createSuccess' => 'Creare il successo',
    'deleteSuccess' => 'Cancellare il successo',
    'updateSuccess' => 'Aggiornare con successo',
    'upgradeSuccess' => 'Aggiornamento riuscito',
    'installSuccess' => 'Installare con successo',
    'uninstallSuccess' => 'Disinstallazione riuscita',

    'createFailure' => 'Creazione fallita',
    'deleteFailure' => 'Eliminazione fallita',
    'updateFailure' => 'Aggiornamento fallito',
    'upgradeFailure' => 'Aggiornamento fallito',
    'installFailure' => 'Installazione fallita',
    'downloadFailure' => 'Scarica il fallimento',
    'uninstallFailure' => 'Disinstallazione fallita',

    'copySuccess' => 'Copia il successo',
    'viewLog' => 'Si è verificato un problema con l\'implementazione; per maggiori dettagli, consultare il log di sistema di Fresns.',
    // auth empty
    'auth_empty_title' => 'Si prega di utilizzare il portale corretto per accedere al pannello',
    'auth_empty_description' => 'L\'accesso è stato interrotto o il login è scaduto, si prega di visitare il portale di login per accedere nuovamente.',
    // request
    'request_in_progress' => 'richiesta in corso...',
    'requestSuccess' => 'Richiesta di successo',
    'requestFailure' => 'Richiesta di fallimento',
    // install
    'install_not_entered_key' => 'Inserire la chiave fresns',
    'install_not_entered_directory' => 'Inserire una directory',
    'install_not_upload_zip' => 'Selezionare il pacchetto di installazione',
    'install_in_progress' => 'Installazione in corso...',
    'install_end' => 'Fine dell\'installazione',
    // upgrade
    'upgrade_none' => 'Nessun aggiornamento',
    'upgrade_fresns' => 'C\'è una nuova versione di Fresns disponibile per l\'aggiornamento',
    'upgrade_fresns_tip' => 'Puoi aggiornare a',
    'upgrade_fresns_warning' => 'Si prega di eseguire il backup del database prima di effettuare l\'aggiornamento per evitare la perdita di dati a causa di un aggiornamento improprio.',
    'upgrade_confirm_tip' => 'Determina l\'aggiornamento?',
    'manual_upgrade_tip' => 'Questo aggiornamento non supporta l\'aggiornamento automatico, si prega di utilizzare il metodo di "aggiornamento fisico".',
    'manual_upgrade_version_guide' => 'Clicca per leggere le istruzioni per questo aggiornamento',
    'manual_upgrade_guide' => 'Guida all\'aggiornamento',
    'manual_upgrade_file_error' => 'Mancata corrispondenza del file di aggiornamento fisico',
    'manual_upgrade_confirm_tip' => 'Assicurati di aver letto la "Guida all\'aggiornamento" e di aver elaborato la nuova versione del file secondo la guida.',
    'upgrade_in_progress' => 'Aggiornamento in corso...',
    'auto_upgrade_step_1' => 'Verifica di inizializzazione',
    'auto_upgrade_step_2' => 'Scarica pacchetto di applicazioni',
    'auto_upgrade_step_3' => 'Pacchetto di applicazione decompresso',
    'auto_upgrade_step_4' => 'Aggiorna applicazione',
    'auto_upgrade_step_5' => 'Svuotare la cache',
    'auto_upgrade_step_6' => 'Fine',
    'manualUpgrade_step_1' => 'Verifica di inizializzazione',
    'manualUpgrade_step_2' => 'Aggiornare i dati',
    'manualUpgrade_step_3' => 'Installare tutti i pacchetti di dipendenza dei plugin (questo passaggio è un processo lento, si prega di essere pazienti)',
    'manualUpgrade_step_4' => 'Pubblicare e ripristinare l\'attivazione delle estensioni',
    'manualUpgrade_step_5' => 'Aggiornare le informazioni sulla versione di Fresns',
    'manualUpgrade_step_6' => 'Svuotare la cache',
    'manualUpgrade_step_7' => 'Fine',
    // uninstall
    'uninstall_in_progress' => 'Disinstallazione in corso...',
    'uninstall_step_1' => 'Verifica di inizializzazione',
    'uninstall_step_2' => 'Elaborazione dati',
    'uninstall_step_3' => 'Cancella file',
    'uninstall_step_4' => 'Cancella cache',
    'uninstall_step_5' => 'Fatto',
    // select
    'select_box_tip_plugin' => 'Seleziona Plugin',
    'select_box_tip_role' => 'Seleziona un ruolo',
    'select_box_tip_group' => 'Seleziona un gruppo',
    'post_datetime_select_error' => 'L\'intervallo di data delle impostazioni post non può essere vuoto',
    'post_datetime_select_range_error' => 'La data di fine dell\'impostazione del post non può essere inferiore alla data di inizio',
    'comment_datetime_select_error' => 'L\'intervallo di date impostato dal commento non può essere vuoto',
    'comment_datetime_select_range_error' => 'La data di fine dell\'impostazione del commento non può essere inferiore alla data di inizio',
    // delete app
    'delete_app_warning' => 'Se non si desidera visualizzare un avviso di aggiornamento per l\'applicazione, è possibile eliminare l\'applicazione. Dopo l\'eliminazione, l\'utente non verrà più avvisato quando sarà disponibile una nuova versione.',
    // dashboard
    'panel_config' => 'Dopo aver modificato la configurazione, è necessario cancellare la cache prima che la nuova configurazione diventi effettiva.',
    'plugin_install_or_upgrade' => 'Dopo l\'installazione o l\'aggiornamento, il plugin è disattivato per impostazione predefinita e deve essere attivato manualmente per evitare problemi di sistema causati da errori nel plugin.',
    // website
    'website_path_empty_error' => 'Salvataggio fallito, il parametro percorso non può essere vuoto',
    'website_path_format_error' => 'non è riuscito a salvare, i parametri di percorso sono supportati solo in lettere semplici.',
    'website_path_reserved_error' => 'Salvataggio fallito, il parametro percorso contiene un nome di parametro riservato al sistema',
    'website_path_unique_error' => 'Salvataggio fallito, parametri di percorso duplicati, i nomi dei parametri di percorso non possono ripetersi.',
    // others
    'markdown_editor' => 'Il contenuto supporta la sintassi Markdown, ma la casella di input non supporta l\'anteprima; si prega di salvarla sul client per vedere l\'effetto.',
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
    'required_group_name' => 'Si prega di compilare il nome del gruppo',
    'delete_default_language_error' => 'La lingua predefinita non può essere cancellata',
    'account_connect_services_error' => 'Il supporto di interconnessione di terze parti ha una piattaforma interconnessa ripetitiva',
];
