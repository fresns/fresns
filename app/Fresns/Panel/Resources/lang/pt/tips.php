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

    'createSuccess' => 'Criar sucesso',
    'deleteSuccess' => 'deletado com sucesso',
    'updateSuccess' => 'Modificado com sucesso',
    'upgradeSuccess' => 'atualização completa',
    'installSuccess' => 'Instalar o Sucesso',
    'installFailure' => 'Falha de Instalação',
    'uninstallSuccess' => 'Desinstalar o sucesso',
    'uninstallFailure' => 'Falha na desinstalação',
    'copySuccess' => 'Copie o sucesso',
    'viewLog' => 'Houve um problema com a implementação, por favor consulte o registo do sistema Fresns para mais detalhes',
    // request
    'request_in_progress' => 'pedido em curso...',
    'requestSuccess' => 'Pedir sucesso',
    'requestFailure' => 'Pedir falha',
    // install
    'install_not_entered_key' => 'Por favor introduza a chave fresns',
    'install_not_entered_dir' => 'Por favor, introduza um directório',
    'install_not_upload_zip' => 'Por favor, seleccione o pacote de instalação',
    'install_in_progress' => 'Instalação em curso...',
    'install_end' => 'Fim da instalação',
    // upgrade
    'upgrade_none' => 'Sem atualização',
    'upgrade_fresns' => 'Existe uma nova versão Fresns disponível para atualização',
    'upgrade_fresns_tip' => 'Você pode atualizar para',
    'upgrade_fresns_warning' => 'Por favor, faça uma cópia de segurança da sua base de dados antes de actualizar para evitar a perda de dados devido a uma actualização inadequada.',
    'upgrade_confirm_tip' => 'Determinar a atualização?',
    'physical_upgrade_tip' => 'Esta actualização não suporta a actualização automática, por favor use o método de "actualização física".',
    'physical_upgrade_version_guide' => 'Clique para ler as instruções para esta actualização',
    'physical_upgrade_guide' => 'Guia de Actualização',
    'physical_upgrade_file_error' => 'Descoordenação física do ficheiro de actualização',
    'physical_upgrade_confirm_tip' => 'Certifique-se de que leu o "Guia de Actualização" e que processou a nova versão do ficheiro de acordo com o guia.',
    'upgrade_in_progress' => 'Actualização em curso...',
    'upgrade_step_1' => 'Verificação de inicialização',
    'upgrade_step_2' => 'Baixe o pacote do aplicativo',
    'upgrade_step_3' => 'UNZIP Pacote de Aplicativos',
    'upgrade_step_4' => 'Aplicação de atualização',
    'upgrade_step_5' => 'Esvaziar o cache',
    'upgrade_step_6' => 'Terminar',
    // uninstall
    'uninstall_in_progress' => 'Desinstalar em curso...',
    'uninstall_step_1' => 'Verificação de inicialização',
    'uninstall_step_2' => 'Processamento de dados',
    'uninstall_step_3' => 'Deletar arquivos',
    'uninstall_step_4' => 'Limpar cache',
    'uninstall_step_5' => 'Feito',
    // website
    'website_path_empty_error' => 'Falha no salvamento, o parâmetro do caminho não pode estar vazio',
    'website_path_format_error' => 'não conseguiu salvar, os parâmetros do caminho só são suportados em letras simples em inglês',
    'website_path_unique_error' => 'não salvou, parâmetros de caminho duplicados, não é permitido que os nomes dos parâmetros de caminho se repitam um ao outro',
    // theme
    'theme_error' => 'O tema é incorrecto ou não existe',
    'theme_functions_file_error' => 'O ficheiro de visualização da configuração do tema está incorrecto ou não existe',
    'theme_json_file_error' => 'O ficheiro de configuração do tema está incorrecto ou não existe',
    'theme_json_format_error' => 'O ficheiro de configuração do tema está no formato errado',
    // others
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
    'required_group_category_name' => 'Por favor, preencha o nome de classificação do grupo',
    'required_group_name' => 'Por favor, preencha o nome do grupo',
    'delete_group_category_error' => 'Existe um grupo em classificação, não permitindo a exclusão',
    'delete_default_language_error' => 'O idioma padrão não pode ser excluído',
    'account_connect_services_error' => 'Suporte de interconexão de terceiros tem uma plataforma interconectada repetitiva',
    'post_datetime_select_error' => 'O intervalo de data de configurações postais não pode estar vazio',
    'post_datetime_select_range_error' => 'A data final da postagem não pode ser menor que a data de início',
    'comment_datetime_select_error' => 'O intervalo de data definido pelo comentário não pode estar vazio',
    'comment_datetime_select_range_error' => 'A data final da configuração de comentários não pode ser menor que a data de início',
];
