@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::systems.sidebar')
@endsection

@section('content')
    <!--user header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_user') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_user_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <!--user config-->
    <form action="{{ route('panel.user.update') }}" id="userConfigForm" method="post" enctype="multipart/form-data">
        @csrf
        @method('put')
        <!--account_connect_services-->
        <div class="row mb-4">
            <label for="user_account_connect_services" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.user_account_connect_services') }}:</label>
            <div class="col-lg-10 connect-box">
                <div class="d-flex justify-content-start pt-1">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addConnect">{{ __('FsLang::panel.button_add_account_connect') }}</button>
                    <div class="form-text ms-3 pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_account_connect_services_desc') }}</div>
                </div>
                @foreach ($params['account_connect_services'] ?? [] as $connectService)
                    <div class="input-group mt-3">
                        <label class="input-group-text">{{ __('FsLang::panel.table_platform') }}</label>
                        <select class="form-select" name="connectId[]">
                            @foreach ($params['connects'] as $connect)
                                @if ($connect['id'] == 23 || $connect['id'] == 26)
                                    @continue
                                @endif
                                <option value="{{ $connect['id'] }}" @if ($connectService['code'] == $connect['id']) selected @endif>{{ $connect['name'] }}</option>
                            @endforeach
                        </select>
                        <label class="input-group-text">{{ __('FsLang::panel.table_plugin') }}</label>
                        <select class="form-select" name="connectPlugin[]">
                            @foreach ($pluginParams['connect'] as $plugin)
                                <option value="{{ $plugin->fskey }}" {{ $connectService['fskey'] == $plugin->fskey ? 'selected' : '' }}> {{ $plugin->name }}</option>
                            @endforeach
                        </select>
                        <label class="input-group-text">{{ __('FsLang::panel.table_order') }}</label>
                        <input type="number" class="form-control input-number" name="connectOrder[]" value="{{ $connectService['order'] ?? '' }}">
                        <button class="btn btn-outline-secondary delete-connect" type="button">{{ __('FsLang::panel.button_delete') }}</button>
                    </div>
                @endforeach
            </div>
        </div>
        <!--user_account_real_name_services-->
        <div class="row mb-4">
            <label for="user_account_real_name_services" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.user_account_real_name_services') }}:</label>
            <div class="col-lg-6">
                <select class="form-select" name="account_real_name_service">
                    <option value="" selected>🚫 {{ __('FsLang::panel.option_deactivate') }}</option>
                    @foreach ($pluginParams['realName'] as $plugin)
                        <option value="{{ $plugin->fskey }}" {{ $params['account_real_name_service'] == $plugin->fskey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_account_real_name_services_desc') }}</div>
        </div>
        <!--user_multiple-->
        <div class="row mb-4">
            <label for="user_multiple" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.user_multiple') }}:</label>
            <div class="col-lg-6 pt-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="multi_user_status" id="multi_user_status_false" value="false" data-bs-toggle="collapse" data-bs-target=".multi_user_setting.show" aria-expanded="false" aria-controls="multi_user_setting" {{ !$params['multi_user_status'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="multi_user_status_false">{{ __('FsLang::panel.option_close') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="multi_user_status" id="multi_user_status_true" value="true" data-bs-toggle="collapse" data-bs-target=".multi_user_setting:not(.show)" aria-expanded="false" aria-controls="multi_user_setting" {{ $params['multi_user_status'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="multi_user_status_true">{{ __('FsLang::panel.option_open') }}</label>
                </div>
                <!--multi_user_config-->
                <div class="collapse multi_user_setting {{ $params['multi_user_status'] == 'true' ? 'show' : '' }}">
                    <div class="card mt-2">
                        <div class="card-header">{{ __('FsLang::panel.user_multiple_config') }}</div>
                        <div class="card-body">
                            <!--config-->
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="multi_user_service">{{ __('FsLang::panel.user_multiple_service') }}</label>
                                <select class="form-select" name="multi_user_service">
                                    <option value="">🚫 {{ __('FsLang::panel.user_multiple_service_none') }}</option>
                                    @foreach ($pluginParams['multiUser'] as $plugin)
                                        <option value="{{ $plugin->fskey }}" {{ $params['multi_user_service'] == $plugin->fskey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="input-group mb-1">
                                <label class="input-group-text" for="multi_user_roles">{{ __('FsLang::panel.user_multiple_roles') }}</label>
                                <select class="form-select select2" multiple name="multi_user_roles[]">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" {{ in_array($role->id, $params['multi_user_roles']) ? 'selected' : '' }}>{{ $role->getLangName($defaultLanguage) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!--config end-->
                        </div>
                    </div>
                </div>
                <!--multi_user_config end-->
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_multiple_desc') }}</div>
        </div>
        <!--user_default_config-->
        <div class="row mb-2">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.user_default_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_default_role') }}</label>
                    <select class="form-select select2" name="default_role">
                        @foreach ($roles as $role)
                            @if ($role->type != 1)
                                <option value="{{ $role->id }}" {{ $params['default_role'] == $role->id ? 'selected' : '' }}>{{ $role->getLangName($defaultLanguage) }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_default_role_desc') }}</div>
        </div>
        <!--user_default_avatar-->
        <div class="row mb-2">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_default_avatar') }}</label>
                    <!--Options-->
                    <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if ($configImageInfo['defaultAvatarType'] == 'ID')
                            {{ __('FsLang::panel.button_image_upload') }}
                        @else
                            {{ __('FsLang::panel.button_image_input') }}
                        @endif
                    </button>
                    <ul class="dropdown-menu selectInputType">
                        <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                    </ul>
                    <!--Input-->
                    <input type="file" class="form-control inputFile" name="default_avatar_file" @if ($configImageInfo['defaultAvatarType'] == 'URL') style="display:none;" @endif>
                    <input type="text" class="form-control inputUrl" name="default_avatar_url" @if ($configImageInfo['defaultAvatarType'] == 'ID') style="display:none;" @endif  @if ($configImageInfo['defaultAvatarType'] == 'URL') value="{{ $params['default_avatar'] }}" @endif>
                    <!--Hidden item-->
                    <input type="hidden" name="default_avatar" value="{{ $params['default_avatar'] }}">
                    <!--Preview-->
                    @if ($params['default_avatar'])
                        <button class="btn btn-outline-secondary preview-image" type="button" data-url="{{ $configImageInfo['defaultAvatarUrl'] }}">{{ __('FsLang::panel.button_view') }}</button>
                    @endif
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_default_avatar_desc') }}</div>
        </div>
        <div class="row mb-2">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_default_anonymous_avatar') }}</label>
                    <!--Options-->
                    <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if ($configImageInfo['anonymousAvatarType'] == 'ID')
                            {{ __('FsLang::panel.button_image_upload') }}
                        @else
                            {{ __('FsLang::panel.button_image_input') }}
                        @endif
                    </button>
                    <ul class="dropdown-menu selectInputType">
                        <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                    </ul>
                    <!--Input-->
                    <input type="file" class="form-control inputFile" name="anonymous_avatar_file" @if ($configImageInfo['anonymousAvatarType'] == 'URL') style="display:none;" @endif>
                    <input type="text" class="form-control inputUrl" name="anonymous_avatar_url" @if ($configImageInfo['anonymousAvatarType'] == 'ID') style="display:none;" @endif  @if ($configImageInfo['anonymousAvatarType'] == 'URL') value="{{ $params['anonymous_avatar'] }}" @endif>
                    <!--Hidden item-->
                    <input type="hidden" name="anonymous_avatar" value="{{ $params['anonymous_avatar'] }}">
                    <!--Preview-->
                    @if ($params['anonymous_avatar'])
                        <button class="btn btn-outline-secondary preview-image" type="button" data-url="{{ $configImageInfo['anonymousAvatarUrl'] }}">{{ __('FsLang::panel.button_view') }}</button>
                    @endif
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_default_anonymous_avatar_desc') }}</div>
        </div>
        <div class="row mb-2">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_default_deactivate_avatar') }}</label>
                    <!--Options-->
                    <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if ($configImageInfo['deactivateAvatarType'] == 'ID')
                            {{ __('FsLang::panel.button_image_upload') }}
                        @else
                            {{ __('FsLang::panel.button_image_input') }}
                        @endif
                    </button>
                    <ul class="dropdown-menu selectInputType">
                        <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                    </ul>
                    <!--Input-->
                    <input type="file" class="form-control inputFile" name="deactivate_avatar_file" @if ($configImageInfo['deactivateAvatarType'] == 'URL') style="display:none;" @endif>
                    <input type="text" class="form-control inputUrl" name="deactivate_avatar_url" @if ($configImageInfo['deactivateAvatarType'] == 'ID') style="display:none;" @endif  @if ($configImageInfo['deactivateAvatarType'] == 'URL') value="{{ $params['deactivate_avatar'] }}" @endif>
                    <!--Hidden item-->
                    <input type="hidden" name="deactivate_avatar" value="{{ $params['deactivate_avatar'] }}">
                    <!--Preview-->
                    @if ($params['deactivate_avatar'])
                        <button class="btn btn-outline-secondary preview-image" type="button" data-url="{{ $configImageInfo['deactivateAvatarUrl'] }}">{{ __('FsLang::panel.button_view') }}</button>
                    @endif
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_default_deactivate_avatar_desc') }}</div>
        </div>
        <!--user_identifier-->
        <div class="row mb-2">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_identifier') }}</label>
                    <select class="form-select" name="user_identifier">
                        <option value="uid" {{ $params['user_identifier'] == 'uid' ? 'selected' : '' }}>uid</option>
                        <option value="username" {{ $params['user_identifier'] == 'username' ? 'selected' : '' }}>username</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_identifier_desc') }}</div>
        </div>
        <!--user_uid_digit-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_uid_digit') }}</label>
                    <input type="number" class="form-control input-number" name="user_uid_digit" min="5" max="16" value="{{ $params['user_uid_digit'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_length') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_uid_digit_desc') }}</div>
        </div>
        <!--user_password_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.user_password_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-2">
                    <label class="input-group-text">{{ __('FsLang::panel.user_password_length') }}</label>
                    <input type="number" class="form-control input-number" name="password_length" value="{{ $params['password_length'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_length') }}</span>
                </div>
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_password_strength') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="digital" name="password_strength[]" value="number" {{ in_array('number', $params['password_strength']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="digital">{{ __('FsLang::panel.user_password_strength_digital') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="lower_letter" name="password_strength[]" value="lowercase" {{ in_array('lowercase', $params['password_strength']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="lower_letter">{{ __('FsLang::panel.user_password_strength_lowerLetters') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="upper_letter" name="password_strength[]" value="uppercase" {{ in_array('uppercase', $params['password_strength']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="upper_letter">{{ __('FsLang::panel.user_password_strength_upperLetters') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="symbol" name="password_strength[]" value="symbols" {{ in_array('symbols', $params['password_strength']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="symbol">{{ __('FsLang::panel.user_password_strength_symbols') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_password_length_desc') }}<br><i
                    class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_password_strength_desc') }}</div>
        </div>
        <!--user_edit_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.user_edit_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-2">
                    <label class="input-group-text">{{ __('FsLang::panel.user_edit_username_length') }}</label>
                    <input type="number" class="form-control input-number" name="username_min" value="{{ $params['username_min'] }}" placeholder="{{ __('FsLang::panel.user_edit_username_length_min') }}">
                    <input type="number" class="form-control input-number" name="username_max" value="{{ $params['username_max'] }}" placeholder="{{ __('FsLang::panel.user_edit_username_length_max') }}">
                </div>
                <div class="input-group mb-2">
                    <label class="input-group-text">{{ __('FsLang::panel.user_edit_username_periodicity') }}</label>
                    <input type="number" class="form-control input-number" name="username_edit" value="{{ $params['username_edit'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_day') }}</span>
                </div>
                <div class="input-group mb-2">
                    <label class="input-group-text">{{ __('FsLang::panel.user_edit_nickname_length') }}</label>
                    <input type="number" class="form-control input-number" name="nickname_min" value="{{ $params['nickname_min'] }}" placeholder="{{ __('FsLang::panel.user_edit_username_length_min') }}">
                    <input type="number" class="form-control input-number" name="nickname_max" value="{{ $params['nickname_max'] }}" placeholder="{{ __('FsLang::panel.user_edit_username_length_max') }}">
                </div>
                <div class="input-group mb-2">
                    <label class="input-group-text">{{ __('FsLang::panel.user_edit_nickname_periodicity') }}</label>
                    <input type="number" class="form-control input-number" name="nickname_edit" value="{{ $params['nickname_edit'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_day') }}</span>
                </div>
                <div class="input-group mb-2">
                    <label class="input-group-text">{{ __('FsLang::panel.user_edit_nickname_unique') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="nickname_unique" id="nickname_unique_false" value="false" {{ ! $params['nickname_unique'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="nickname_unique_false">{{ __('FsLang::panel.option_no') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="nickname_unique" id="nickname_unique_true" value="true" {{ $params['nickname_unique'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="nickname_unique_true">{{ __('FsLang::panel.option_yes') }}</label>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-2">
                    <label class="input-group-text">{{ __('FsLang::panel.user_edit_bio_length') }}</label>
                    <input type="number" class="form-control input-number" name="bio_length" value="{{ $params['bio_length'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_character') }}</span>
                </div>
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_edit_bio_support') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="bio_support_mention" name="bio_support_mention" value="true" {{ $params['bio_support_mention'] == 'true' ? 'checked' : '' }}>
                            <label class="form-check-label" for="bio_support_mention">{{ __('FsLang::panel.user_bio_support_mention') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="bio_support_link" name="bio_support_link" value="true" {{ $params['bio_support_link'] == 'true' ? 'checked' : '' }}>
                            <label class="form-check-label" for="bio_support_link">{{ __('FsLang::panel.user_bio_support_link') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="bio_support_hashtag" name="bio_support_hashtag" value="true" {{ $params['bio_support_hashtag'] == 'true' ? 'checked' : '' }}>
                            <label class="form-check-label" for="bio_support_hashtag">{{ __('FsLang::panel.user_bio_support_hashtag') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_edit_username_length_desc') }}</div>
        </div>
        <!--user_extcredits_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.user_extcredits_config') }}:</label>
            <div class="col-lg-6">
                @foreach ([1, 2, 3, 4, 5] as $extcreditsId)
                    <div class="input-group mb-2">
                        <label class="input-group-text">{{ 'extcredits'.$extcreditsId }}</label>
                        <button type="button" class="btn btn-outline-secondary btn-modal form-control text-start"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-action="{{ route('panel.languages.batch.update', ['itemKey' => "extcredits{$extcreditsId}_name"]) }}"
                            data-languages="{{ optional($langConfigs["extcredits{$extcreditsId}_name"])->languages->toJson() }}"
                            data-item_key="{{ "extcredits{$extcreditsId}_name" }}">
                            {{ $defaultLangParams["extcredits{$extcreditsId}_name"] ??  __('FsLang::panel.user_extcredits_name') }}
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-modal form-control text-start"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-action="{{ route('panel.languages.batch.update', ['itemKey' => "extcredits{$extcreditsId}_unit"]) }}"
                            data-languages="{{ optional($langConfigs["extcredits{$extcreditsId}_unit"])->languages->toJson() }}"
                            data-item_key="{{ "extcredits{$extcreditsId}_unit" }}">
                            {{ $defaultLangParams["extcredits{$extcreditsId}_unit"] ??  __('FsLang::panel.user_extcredits_unit') }}
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-modal form-control"
                            data-bs-toggle="modal"
                            data-bs-target="#extcreditsSetting"
                            data-id="{{ $extcreditsId }}"
                            data-state="{{ $params["extcredits{$extcreditsId}_state"] }}">
                            @if ($params["extcredits{$extcreditsId}_state"] == 2)
                                {{ __('FsLang::panel.user_extcredits_state_private') }}
                            @elseif ($params["extcredits{$extcreditsId}_state"] == 3)
                                {{ __('FsLang::panel.user_extcredits_state_public') }}
                            @else
                                {{ __('FsLang::panel.user_extcredits_state_not_enabled') }}
                            @endif
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
        <!--button_save-->
        <div class="row my-3">
            <div class="col-lg-2"></div>
            <div class="col-lg-6">
                <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
            </div>
        </div>
    </form>

    <!--user_account_connect_services template-->
    <template id="connectTemplate">
        <div class="input-group mt-3">
            <label class="input-group-text">{{ __('FsLang::panel.table_platform') }}</label>
            <select class="form-select" name="connectId[]">
                @foreach ($params['connects'] as $connect)
                    @if ($connect['id'] == 23 || $connect['id'] == 26)
                        @continue
                    @endif
                    <option value="{{ $connect['id'] }}">{{ $connect['name'] }}</option>
                @endforeach
            </select>
            <label class="input-group-text">{{ __('FsLang::panel.table_plugin') }}</label>
            <select class="form-select" name="connectPlugin[]">
                @foreach ($pluginParams['connect'] as $plugin)
                    <option value="{{ $plugin->fskey }}">{{ $plugin->name }}</option>
                @endforeach
            </select>
            <label class="input-group-text">{{ __('FsLang::panel.table_order') }}</label>
            <input type="number" class="form-control input-number" name="connectOrder[]">
            <button class="btn btn-outline-secondary delete-connect" type="button">{{ __('FsLang::panel.button_delete') }}</button>
        </div>
    </template>

    <!--imageZoom-->
    <div class="modal fade image-zoom" id="imageZoom" tabindex="-1" aria-labelledby="imageZoomLabel" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="position-relative image-box">
                <img class="img-fluid" src="">
            </div>
        </div>
    </div>

    <!-- Language Modal (input) -->
    <div class="modal fade" id="configLangModal" tabindex="-1" aria-labelledby="configLangModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_setting') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        @csrf
                        @method('put')
                        <input type="hidden" name="sync_config" value="1">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-nowrap">
                                <thead>
                                    <tr class="table-info">
                                        <th scope="col" class="w-25">{{ __('FsLang::panel.table_lang_tag') }}</th>
                                        <th scope="col" class="w-25">{{ __('FsLang::panel.table_lang_name') }}</th>
                                        <th scope="col" class="w-50">{{ __('FsLang::panel.table_content') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($optionalLanguages as $lang)
                                        <tr>
                                            <td>
                                                {{ $lang['langTag'] }}
                                                @if ($lang['langTag'] == $defaultLanguage)
                                                    <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.default_language') }}"></i>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $lang['langName'] }}
                                                @if ($lang['areaName'])
                                                    {{ '('.$lang['areaName'].')' }}
                                                @endif
                                            </td>
                                            <td><input class="form-control" name="languages[{{ $lang['langTag'] }}]"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Setting -->
    <div class="modal fade" id="extcreditsSetting" tabindex="-1" aria-labelledby="extcreditsSetting" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_setting') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('panel.user.update.extcredits') }}" method="post">
                        @csrf
                        @method('put')
                        <input type="hidden" name="extcreditsId" value="1">
                        <!--state-->
                        <div class="mb-3 row extcredits_state">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_status') }}</label>
                            <div class="col-sm-9 pt-2">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="extcredits_state" id="extcredits_state_1" value="1" checked>
                                    <label class="form-check-label" for="extcredits_state_1">{{ __('FsLang::panel.user_extcredits_state_not_enabled') }}</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="extcredits_state" id="extcredits_state_2" value="2">
                                    <label class="form-check-label" for="extcredits_state_2">{{ __('FsLang::panel.user_extcredits_state_private') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="extcredits_state" id="extcredits_state_3" value="3">
                                    <label class="form-check-label" for="extcredits_state_3">{{ __('FsLang::panel.user_extcredits_state_public') }}</label>
                                </div>
                            </div>
                        </div>

                        <!--button_save-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label"></label>
                            <div class="col-sm-9"><button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
