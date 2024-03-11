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

    'createSuccess' => 'Criar o Sucesso',
    'deleteSuccess' => 'Eliminar o sucesso',
    'updateSuccess' => 'Actualizar o sucesso',
    'upgradeSuccess' => 'Actualizar Sucesso',
    'installSuccess' => 'Instalar o Sucesso',
    'uninstallSuccess' => 'Desinstalar o sucesso',

    'createFailure' => 'Criar fracasso',
    'deleteFailure' => 'Eliminar falhas',
    'updateFailure' => 'Actualização Falha',
    'upgradeFailure' => 'Actualização Falha',
    'installFailure' => 'Falha de Instalação',
    'downloadFailure' => 'Falha na transferência',
    'uninstallFailure' => 'Falha na desinstalação',

    'copySuccess' => 'Copie o sucesso',
    'viewLog' => 'Houve um problema com a implementação, por favor consulte o registo do sistema Fresns para mais detalhes',
    // auth empty
    'auth_empty_title' => 'Favor utilizar o portal correcto para entrar no painel',
    'auth_empty_description' => 'Terminou a sessão ou o seu login está desactivado, por favor visite o portal de login para iniciar novamente a sessão.',
    // request
    'request_in_progress' => 'pedido em curso...',
    'requestSuccess' => 'Pedir sucesso',
    'requestFailure' => 'Pedir falha',
    // install
    'install_not_entered_key' => 'Por favor introduza a chave fresns',
    'install_not_entered_directory' => 'Por favor, introduza um directório',
    'install_not_upload_zip' => 'Por favor, seleccione o pacote de instalação',
    'install_in_progress' => 'Instalação em curso...',
    'install_end' => 'Fim da instalação',
    // upgrade
    'upgrade_none' => 'Sem atualização',
    'upgrade_fresns' => 'Existe uma nova versão Fresns disponível para atualização',
    'upgrade_fresns_tip' => 'Você pode atualizar para',
    'upgrade_fresns_warning' => 'Por favor, faça uma cópia de segurança da sua base de dados antes de actualizar para evitar a perda de dados devido a uma actualização inadequada.',
    'upgrade_confirm_tip' => 'Determinar a atualização?',
    'manual_upgrade_tip' => 'Esta actualização não suporta a actualização automática, por favor use o método de "actualização física".',
    'manual_upgrade_version_guide' => 'Clique para ler as instruções para esta actualização',
    'manual_upgrade_guide' => 'Guia de Actualização',
    'manual_upgrade_file_error' => 'Descoordenação física do ficheiro de actualização',
    'manual_upgrade_confirm_tip' => 'Certifique-se de que leu o "Guia de Actualização" e que processou a nova versão do ficheiro de acordo com o guia.',
    'upgrade_in_progress' => 'Actualização em curso...',
    'auto_upgrade_step_1' => 'Verificação de inicialização',
    'auto_upgrade_step_2' => 'Baixe o pacote do aplicativo',
    'auto_upgrade_step_3' => 'UNZIP Pacote de Aplicativos',
    'auto_upgrade_step_4' => 'Aplicação de atualização',
    'auto_upgrade_step_5' => 'Esvaziar o cache',
    'auto_upgrade_step_6' => 'Terminar',
    'manualUpgrade_step_1' => 'Verificação de inicialização',
    'manualUpgrade_step_2' => 'Actualização de dados',
    'manualUpgrade_step_3' => 'Instalar todos os pacotes de dependência de plugin (este passo é um processo lento, por favor seja paciente)',
    'manualUpgrade_step_4' => 'Publicar e restaurar extensões activadas',
    'manualUpgrade_step_5' => 'Actualização de informação da versão Fresns',
    'manualUpgrade_step_6' => 'Esvaziar o cache',
    'manualUpgrade_step_7' => 'Terminar',
    // uninstall
    'uninstall_in_progress' => 'Desinstalar em curso...',
    'uninstall_step_1' => 'Verificação de inicialização',
    'uninstall_step_2' => 'Processamento de dados',
    'uninstall_step_3' => 'Deletar arquivos',
    'uninstall_step_4' => 'Limpar cache',
    'uninstall_step_5' => 'Feito',
    // select
    'select_box_tip_plugin' => 'Selecione Plugin',
    'select_box_tip_role' => 'Selecione uma função',
    'select_box_tip_group' => 'Selecione um grupo',
    'post_datetime_select_error' => 'O intervalo de data de configurações postais não pode estar vazio',
    'post_datetime_select_range_error' => 'A data final da postagem não pode ser menor que a data de início',
    'comment_datetime_select_error' => 'O intervalo de data definido pelo comentário não pode estar vazio',
    'comment_datetime_select_range_error' => 'A data final da configuração de comentários não pode ser menor que a data de início',
    // delete app
    'delete_app_warning' => 'Se não pretender apresentar um alerta de atualização para a aplicação, pode eliminar a aplicação. Após a eliminação, deixará de ser alertado quando estiver disponível uma nova versão.',
    // dashboard
    'panel_config' => 'Após a modificação da configuração, a cache precisa de ser limpa antes que a nova configuração possa ter efeito.',
    'plugin_install_or_upgrade' => 'Depois de instalado ou actualizado, o plugin é desligado por defeito e precisa de ser activado manualmente para evitar problemas no sistema causados por erros no plugin.',
    // website
    'website_path_empty_error' => 'Falha no salvamento, o parâmetro do caminho não pode estar vazio',
    'website_path_format_error' => 'não conseguiu salvar, os parâmetros do caminho só são suportados em letras simples em inglês',
    'website_path_reserved_error' => 'Salvar falha, o parâmetro do caminho contém o nome do parâmetro reservado do sistema',
    'website_path_unique_error' => 'não salvou, parâmetros de caminho duplicados, não é permitido que os nomes dos parâmetros de caminho se repitam um ao outro',
    // theme
    'website_engine_error' => 'Motor do sítio Web não instalado',
    'theme_error' => 'O tema é incorrecto ou não existe',
    'theme_functions_file_error' => 'O ficheiro de visualização da configuração do tema está incorrecto ou não existe',
    'theme_json_file_error' => 'O ficheiro de configuração do tema está incorrecto ou não existe',
    'theme_json_format_error' => 'O ficheiro de configuração do tema está no formato errado',
    // others
    'markdown_editor' => 'O conteúdo suporta a sintaxe Markdown, mas a caixa de entrada não suporta a pré-visualização.',
    'account_not_found' => 'Conta não existe ou inserir erros',
    'account_login_limit' => 'O erro excedeu o limite do sistema. Por favor, faça novamente o log in 1 hora mais tarde',
    'timezone_error' => 'O fuso horário da base de dados não corresponde ao fuso horário no ficheiro de configuração .env',
    'timezone_env_edit_tip' => 'Por favor modifique o item de configuração do identificador de fuso horário no ficheiro .env',
    'secure_entry_route_conflicts' => 'Conflito de roteamento de entrada de segurança',
    'language_exists' => 'A linguagem já existe',
    'language_not_exists' => 'a língua não existe',
    'plugin_not_exists' => 'o plugin não existe',
    'map_exists' => 'Este prestador de serviços de mapas já foi utilizado e não pode ser recriado',
    'map_not_exists' => 'o mapa não existe',
    'required_user_role_name' => 'Por favor, preencha o nome do papel',
    'required_sticker_category_name' => 'Por favor, preencha o nome do grupo de expressão',
    'required_group_name' => 'Por favor, preencha o nome do grupo',
    'delete_default_language_error' => 'O idioma padrão não pode ser excluído',
    'account_connect_services_error' => 'Suporte de interconexão de terceiros tem uma plataforma interconectada repetitiva',
];
