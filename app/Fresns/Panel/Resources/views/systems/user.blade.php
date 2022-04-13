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
            <div class="col-lg-6 connect-box">
                <div class="input-group">
                    <button type="button" class="btn btn-outline-primary btn-sm mt-2 me-3" id="addConnect">{{ __('FsLang::panel.button_add_account_connect') }}</button>
                </div>
                @foreach ($params['account_connect_services'] ?? [] as $connectService)
                    <div class="input-group mt-3">
                        <label class="input-group-text">{{ __('FsLang::panel.table_platform') }}</label>
                        <select class="form-select" name="connects[]">
                            @foreach ($params['connects'] as $connect)
                                <option value="{{ $connect['id'] }}" @if ($connectService['code'] == $connect['id']) selected @endif>{{ $connect['name'] }}</option>
                            @endforeach
                        </select>
                        <label class="input-group-text">{{ __('FsLang::panel.table_plugin') }}</label>
                        <select class="form-select" name="connect_plugins[]">
                            @foreach ($pluginParams['connect'] as $plugin)
                                <option value="{{ $plugin->unikey }}" {{ $connectService['unikey'] == $plugin->unikey ? 'selected' : '' }}> {{ $plugin->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-outline-secondary delete-connect" type="button">{{ __('FsLang::panel.button_delete') }}</button>
                    </div>
                @endforeach
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_account_connect_services_desc') }}</div>
        </div>
        <!--user_account_prove_services-->
        <div class="row mb-4">
            <label for="user_account_prove_services" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.user_account_prove_services') }}:</label>
            <div class="col-lg-6">
                <select class="form-select" name="account_prove_service">
                    <option value="" selected>🚫 {{ __('FsLang::panel.option_deactivate') }}</option>
                    @foreach ($pluginParams['prove'] as $plugin)
                        <option value="{{ $plugin->unikey }}" {{ $params['account_prove_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_account_prove_services_desc') }}</div>
        </div>
        <!--user_multiple-->
        <div class="row mb-4">
            <label for="user_multiple" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.user_multiple') }}:</label>
            <div class="col-lg-6 pt-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="user_multiple" id="user_multiple_false" value="false" data-bs-toggle="collapse" data-bs-target="#user_multiple_setting.show" aria-expanded="false" aria-controls="user_multiple_setting" {{ !$params['user_multiple'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="user_multiple_false">{{ __('FsLang::panel.option_close') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="user_multiple" id="user_multiple_true" value="true" data-bs-toggle="collapse" data-bs-target="#user_multiple_setting:not(.show)" aria-expanded="false" aria-controls="user_multiple_setting" {{ $params['user_multiple'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="user_multiple_true">{{ __('FsLang::panel.option_open') }}</label>
                </div>
                <!--user_multiple_config-->
                <div class="collapse {{ $params['user_multiple'] == 'true' ? 'show' : '' }}" id="user_multiple_setting">
                    <div class="card mt-2">
                        <div class="card-header">{{ __('FsLang::panel.user_multiple_config') }}</div>
                        <div class="card-body">
                            <!--config-->
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="multi_user_service">{{ __('FsLang::panel.user_multiple_service') }}</label>
                                <select class="form-select" name="multi_user_service">
                                    <option value="">🚫 {{ __('FsLang::panel.user_multiple_service_none') }}</option>
                                    @foreach ($pluginParams['multiple'] as $plugin)
                                        <option value="{{ $plugin->unikey }}" {{ $params['multi_user_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
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
                <!--user_multiple_config end-->
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
                    <ul class="dropdown-menu selectImageType">
                        <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                    </ul>
                    <!--Input-->
                    <input type="file" class="form-control inputFile" name="default_avatar_file" @if ($configImageInfo['defaultAvatarType'] == 'URL') style="display:none;" @endif>
                    <input type="url" class="form-control inputUrl" name="default_avatar_url" @if ($configImageInfo['defaultAvatarType'] == 'ID') style="display:none;" @endif  @if ($configImageInfo['defaultAvatarType'] == 'URL') value="{{ $params['default_avatar'] }}" @endif>
                    <!--Hidden item-->
                    <input type="hidden" name="default_avatar" value="{{ $params['default_avatar'] }}">
                    <!--Preview-->
                    @if ($params['default_avatar'])
                        <input type="hidden" class="imageUrl" value="{{ $configImageInfo['defaultAvatarUrl'] }}">
                        <button class="btn btn-outline-secondary preview-image" type="button">{{ __('FsLang::panel.button_image_view') }}</button>
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
                    <ul class="dropdown-menu selectImageType">
                        <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                    </ul>
                    <!--Input-->
                    <input type="file" class="form-control inputFile" name="anonymous_avatar_file" @if ($configImageInfo['anonymousAvatarType'] == 'URL') style="display:none;" @endif>
                    <input type="url" class="form-control inputUrl" name="anonymous_avatar_url" @if ($configImageInfo['anonymousAvatarType'] == 'ID') style="display:none;" @endif  @if ($configImageInfo['anonymousAvatarType'] == 'URL') value="{{ $params['anonymous_avatar'] }}" @endif>
                    <!--Hidden item-->
                    <input type="hidden" name="anonymous_avatar" value="{{ $params['anonymous_avatar'] }}">
                    <!--Preview-->
                    @if ($params['anonymous_avatar'])
                        <input type="hidden" class="imageUrl" value="{{ $configImageInfo['anonymousAvatarUrl'] }}">
                        <button class="btn btn-outline-secondary preview-image" type="button">{{ __('FsLang::panel.button_image_view') }}</button>
                    @endif
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_default_anonymous_avatar_desc') }}</div>
        </div>
        <div class="row mb-4">
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
                    <ul class="dropdown-menu selectImageType">
                        <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                    </ul>
                    <!--Input-->
                    <input type="file" class="form-control inputFile" name="deactivate_avatar_file" @if ($configImageInfo['deactivateAvatarType'] == 'URL') style="display:none;" @endif>
                    <input type="url" class="form-control inputUrl" name="deactivate_avatar_url" @if ($configImageInfo['deactivateAvatarType'] == 'ID') style="display:none;" @endif  @if ($configImageInfo['deactivateAvatarType'] == 'URL') value="{{ $params['deactivate_avatar'] }}" @endif>
                    <!--Hidden item-->
                    <input type="hidden" name="deactivate_avatar" value="{{ $params['deactivate_avatar'] }}">
                    <!--Preview-->
                    @if ($params['deactivate_avatar'])
                        <input type="hidden" class="imageUrl" value="{{ $configImageInfo['deactivateAvatarUrl'] }}">
                        <button class="btn btn-outline-secondary preview-image" type="button">{{ __('FsLang::panel.button_image_view') }}</button>
                    @endif
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_default_deactivate_avatar_desc') }}</div>
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
                            <input class="form-check-input" type="checkbox" id="digital" name="password_strength[]" value="1" {{ in_array(1, $params['password_strength']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="digital">{{ __('FsLang::panel.user_password_strength_digital') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="lower_letter" name="password_strength[]" value="2" {{ in_array(2, $params['password_strength']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="lower_letter">{{ __('FsLang::panel.user_password_strength_lowerLetters') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="upper_letter" value="3" name="password_strength[]" {{ in_array(3, $params['password_strength']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="upper_letter">{{ __('FsLang::panel.user_password_strength_upperLetters') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="symbol" value="4" name="password_strength[]" {{ in_array(4, $params['password_strength']) ? 'checked' : '' }}>
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
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_edit_nickname_periodicity') }}</label>
                    <input type="number" class="form-control input-number" name="nickname_edit" value="{{ $params['nickname_edit'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_day') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_edit_username_length_desc') }}</div>
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
            <select class="form-select" name="connects[]">
                @foreach ($params['connects'] as $connect)
                    <option value="{{ $connect['id'] }}">{{ $connect['name'] }}</option>
                @endforeach
            </select>
            <label class="input-group-text">{{ __('FsLang::panel.table_plugin') }}</label>
            <select class="form-select" name="connect_plugins[]">
                @foreach ($pluginParams['connect'] as $plugin)
                    <option value="{{ $plugin->unikey }}">{{ $plugin->name }}</option>
                @endforeach
            </select>
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
@endsection
