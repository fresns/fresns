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

    'createSuccess' => 'Crear éxito',
    'deleteSuccess' => 'borrado con éxito',
    'updateSuccess' => 'Modificado con éxito',
    'upgradeSuccess' => 'actualización completada',
    'installSuccess' => 'Instalar con éxito',
    'installFailure' => 'Fallo de instalación',
    'uninstallSuccess' => 'Desinstalación exitosa',
    'uninstallFailure' => 'Fallo de desinstalación',
    'copySuccess' => 'Copiar el éxito',
    'viewLog' => 'Hubo un problema con la implementación, por favor vea el registro del sistema Fresns para más detalles',
    // auth empty
    'auth_empty_title' => 'Por favor, utilice el portal correcto para acceder al panel',
    'auth_empty_description' => 'Se ha cerrado la sesión o se ha agotado el tiempo de acceso, por favor visite el portal de acceso para volver a entrar.',
    // request
    'request_in_progress' => 'solicitud en curso...',
    'requestSuccess' => 'Solicitud de éxito',
    'requestFailure' => 'Solicitud de fracaso',
    // install
    'install_not_entered_key' => 'Por favor, introduzca la clave de fresns',
    'install_not_entered_dir' => 'Por favor, introduzca un directorio',
    'install_not_upload_zip' => 'Por favor, seleccione el paquete de instalación',
    'install_in_progress' => 'Instalación en curso...',
    'install_end' => 'Fin de la instalación',
    // upgrade
    'upgrade_none' => 'Ninguna actualización',
    'upgrade_fresns' => 'Hay una nueva versión de Fresns disponible para la actualización',
    'upgrade_fresns_tip' => 'Puedes actualizar a',
    'upgrade_fresns_warning' => 'Por favor, haga una copia de seguridad de su base de datos antes de actualizar para evitar la pérdida de datos debido a una actualización incorrecta.',
    'upgrade_confirm_tip' => '¿Determinar la actualización?',
    'physical_upgrade_tip' => 'Esta actualización no admite la actualización automática, por favor, utilice el método de "actualización física".',
    'physical_upgrade_version_guide' => 'Haga clic para leer las instrucciones de esta actualización',
    'physical_upgrade_guide' => 'Guía de actualización',
    'physical_upgrade_file_error' => 'Fichero de actualización física erróneo',
    'physical_upgrade_confirm_tip' => 'Asegúrese de haber leído la "Guía de actualización" y de haber procesado la nueva versión del archivo de acuerdo con la guía.',
    'upgrade_in_progress' => 'Actualización en curso...',
    'upgrade_step_1' => 'Verificación de inicialización',
    'upgrade_step_2' => 'Descargar el paquete de la aplicación',
    'upgrade_step_3' => 'Paquete de aplicación de descompuesto',
    'upgrade_step_4' => 'Aplicación de actualización',
    'upgrade_step_5' => 'Vaciar el caché',
    'upgrade_step_6' => 'Finalizar',
    // uninstall
    'uninstall_in_progress' => 'Desinstalación en curso...',
    'uninstall_step_1' => 'Verificación de inicialización',
    'uninstall_step_2' => 'Procesamiento de datos',
    'uninstall_step_3' => 'Borrar archivos',
    'uninstall_step_4' => 'Limpiar cache',
    'uninstall_step_5' => 'Hecho',
    // website
    'website_path_empty_error' => 'Fallo al guardar, el parámetro de la ruta no puede estar vacío',
    'website_path_format_error' => 'no se ha podido guardar, los parámetros de la ruta sólo se admiten en letras inglesas simples',
    'website_path_reserved_error' => 'Guardar falló, el parámetro de la ruta contiene el nombre del parámetro reservado del sistema',
    'website_path_unique_error' => 'fallo al guardar, parámetros de ruta duplicados, los nombres de los parámetros de ruta no pueden repetirse',
    // theme
    'theme_error' => 'El tema es incorrecto o no existe',
    'theme_functions_file_error' => 'El archivo de la vista de configuración del tema es incorrecto o no existe',
    'theme_json_file_error' => 'El archivo de configuración del tema es incorrecto o no existe',
    'theme_json_format_error' => 'El archivo de configuración del tema tiene un formato incorrecto',
    // others
    'account_not_found' => 'La cuenta no existe o ingresa errores',
    'account_login_limit' => 'El error ha superado el límite del sistema. Por favor, vuelva a conectarse 1 hora más tarde',
    'timezone_error' => 'La zona horaria de la base de datos no coincide con la zona horaria del archivo .env config.',
    'timezone_env_edit_tip' => 'Modifique el elemento de configuración del identificador de zona horaria en el archivo .env',
    'secure_entry_route_conflicts' => 'Conflicto de enrutamiento de entrada de seguridad',
    'language_exists' => 'El lenguaje ya existe',
    'language_not_exists' => 'el idioma no existe',
    'plugin_not_exists' => 'el plugin no existe',
    'map_exists' => 'Este proveedor de servicios de mapas ya ha sido utilizado y no se puede volver a crear',
    'map_not_exists' => 'el mapa no existe',
    'required_user_role_name' => 'Por favor complete el nombre del rol',
    'required_sticker_category_name' => 'Por favor, complete el nombre del grupo de expresión',
    'required_group_category_name' => 'Por favor, complete el nombre de clasificación del grupo',
    'required_group_name' => 'Por favor, rellene el nombre del grupo',
    'delete_group_category_error' => 'Hay un grupo en clasificación, no permitiendo la eliminación',
    'delete_default_language_error' => 'El idioma predeterminado no se puede eliminar',
    'account_connect_services_error' => 'El soporte de interconexión de terceros tiene una plataforma interconectada repetitiva',
    'post_datetime_select_error' => 'El rango de fecha de configuración de publicación no puede estar vacío',
    'post_datetime_select_range_error' => 'La fecha de finalización de la configuración de POST no puede ser menor que la fecha de inicio',
    'comment_datetime_select_error' => 'El rango de fecha establecido por el comentario no puede estar vacío',
    'comment_datetime_select_range_error' => 'La fecha de finalización de la configuración de comentarios no puede ser menor que la fecha de inicio',
];
