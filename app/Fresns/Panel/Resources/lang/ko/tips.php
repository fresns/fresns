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

    'createSuccess' => '성공을 창출하십시오',
    'deleteSuccess' => '성공적으로 삭제되었습니다',
    'updateSuccess' => '성공적으로 수정되었습니다',
    'upgradeSuccess' => '업데이트 완료',
    'installSuccess' => '설치 성공',
    'installFailure' => '설치 실패',
    'uninstallSuccess' => '제거 성공',
    'uninstallFailure' => '제거 실패',
    'copySuccess' => '성공을 복사하십시오',
    'viewLog' => '실행에 문제가 발생했습니다. 자세한 내용은 Fresns 시스템 로그를 확인하십시오.',
    // auth empty
    'auth_empty_title' => '올바른 항목을 사용하여 패널에 로그인하십시오.',
    'auth_empty_description' => '로그아웃했거나 로그인 시간이 초과되었습니다. 로그인 포털을 방문하여 다시 로그인하십시오.',
    // request
    'request_in_progress' => '요청 진행 중...',
    'requestSuccess' => '요청 성공',
    'requestFailure' => '요청 실패',
    // install
    'install_not_entered_key' => 'fresns 키를 입력하세요.',
    'install_not_entered_directory' => '디렉토리를 입력하세요',
    'install_not_upload_zip' => '설치 패키지를 선택하십시오',
    'install_in_progress' => '설치 진행 중...',
    'install_end' => '설치 종료',
    // upgrade
    'upgrade_none' => '업데이트 없음',
    'upgrade_fresns' => '업그레이드를 위해 새로운 Fresns 버전이 있습니다',
    'upgrade_fresns_tip' => '업그레이드 할 수 있습니다',
    'upgrade_fresns_warning' => '부적절한 업그레이드로 인한 데이터 손실을 방지하려면 업그레이드하기 전에 데이터베이스를 백업하십시오.',
    'upgrade_confirm_tip' => '업그레이드 결정?',
    'physical_upgrade_tip' => '이 업그레이드는 자동 업그레이드를 지원하지 않으므로 "물리적 업그레이드" 방법을 사용하십시오.',
    'physical_upgrade_version_guide' => '이 버전의 업데이트 설명을 읽으려면 클릭하십시오.',
    'physical_upgrade_guide' => '업그레이드 가이드',
    'physical_upgrade_file_error' => '물리적 업그레이드 파일 불일치',
    'physical_upgrade_confirm_tip' => '"업그레이드 가이드"를 읽고 가이드에 따라 파일의 새 버전을 처리했는지 확인하십시오.',
    'upgrade_in_progress' => '업그레이드 진행 중...',
    'auto_upgrade_step_1' => '초기화 확인',
    'auto_upgrade_step_2' => '응용 프로그램 패키지를 다운로드하십시오',
    'auto_upgrade_step_3' => '압축 해제 응용 프로그램 패키지',
    'auto_upgrade_step_4' => '업그레이드 응용 프로그램',
    'auto_upgrade_step_5' => '캐시를 비 웁니다',
    'auto_upgrade_step_6' => '마치다',
    'physicalUpgrade_step_1' => '초기화 확인',
    'physicalUpgrade_step_2' => '데이터 업데이트',
    'physicalUpgrade_step_3' => '모든 플러그인 종속성 패키지를 설치합니다(이 단계는 느린 프로세스이므로 잠시만 기다려 주십시오).',
    'physicalUpgrade_step_4' => '확장 게시 및 복원 활성화',
    'physicalUpgrade_step_5' => 'Fresns 버전 정보 업데이트',
    'physicalUpgrade_step_6' => '캐시를 비 웁니다',
    'physicalUpgrade_step_7' => '마치다',
    // uninstall
    'uninstall_in_progress' => '제거 진행 중...',
    'uninstall_step_1' => '초기화 확인',
    'uninstall_step_2' => '데이터 처리',
    'uninstall_step_3' => '파일 삭제',
    'uninstall_step_4' => '캐시 지우기',
    'uninstall_step_5' => '완료',
    // website
    'website_path_empty_error' => '저장하지 못했습니다. 경로 매개변수는 비워둘 수 없습니다.',
    'website_path_format_error' => '저장에 실패했습니다. 경로 매개변수는 순수 영문자만 지원합니다.',
    'website_path_reserved_error' => '저장하지 못했습니다. 경로 매개변수에 시스템 예약 매개변수 이름이 포함되어 있습니다.',
    'website_path_unique_error' => '저장에 실패했습니다. 경로 매개변수를 복제했습니다. 경로 매개변수 이름은 서로 복제할 수 없습니다.',
    // theme
    'theme_error' => '테마가 잘못되었거나 존재하지 않습니다.',
    'theme_functions_file_error' => '테마 구성에 대한 보기 파일이 잘못되었거나 존재하지 않습니다.',
    'theme_json_file_error' => '테마 구성 파일이 잘못되었거나 존재하지 않습니다.',
    'theme_json_format_error' => '테마 구성 파일 형식 오류',
    // others
    'account_not_found' => '계정이 없거나 오류를 입력하지 않습니다',
    'account_login_limit' => '오류가 시스템 제한을 초과했습니다. 1시간 후에 다시 로그인하십시오',
    'timezone_error' => '데이터베이스 시간대가 .env 구성 파일의 시간대와 일치하지 않습니다.',
    'timezone_env_edit_tip' => '.env 파일에서 시간대 식별자 구성 항목을 수정하십시오',
    'secure_entry_route_conflicts' => '안전 입구 라우팅 충돌',
    'language_exists' => '언어가 이미 있습니다',
    'language_not_exists' => '언어가 존재하지 않습니다',
    'plugin_not_exists' => '플러그인이 존재하지 않습니다',
    'map_exists' => '지도 서비스 공급자는 이미 사용되었으며 다시 만들 수 없습니다.',
    'map_not_exists' => '지도가 존재하지 않습니다',
    'required_user_role_name' => '역할의 이름을 기입하십시오',
    'required_sticker_category_name' => '표현식 그룹의 이름을 기입하십시오',
    'required_group_category_name' => '그룹 분류 이름을 기입하십시오',
    'required_group_name' => '그룹 이름을 기입하십시오',
    'delete_group_category_error' => '삭제를 허용하지 않는 분류에 그룹이 있습니다',
    'delete_default_language_error' => '기본 언어는 삭제할 수 없습니다',
    'account_connect_services_error' => '타사 상호 연결 지원에는 반복적 인 상호 연결된 플랫폼이 있습니다',
    'post_datetime_select_error' => '게시물 설정의 날짜 범위는 비어있을 수 없습니다',
    'post_datetime_select_range_error' => '게시물 설정의 종료 날짜는 시작일보다 작을 수 없습니다',
    'comment_datetime_select_error' => '주석이 설정 한 날짜 범위는 비어있을 수 없습니다',
    'comment_datetime_select_range_error' => '주석 설정의 종료 날짜는 시작일보다 작을 수 없습니다',
];
