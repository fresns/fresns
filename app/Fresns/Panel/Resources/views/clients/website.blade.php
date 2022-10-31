@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--website header-->
    <div class="row mb-5 border-bottom">
        <div class="col-lg-9">
            <h3>{{ __('FsLang::panel.sidebar_website') }}</h3>
            <p class="text-secondary"><i class="bi bi-laptop"></i> {{ __('FsLang::panel.sidebar_website_intro') }}</p>
        </div>
        <div class="col-lg-3">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>

    <!--website config-->
    <form action="{{ route('panel.website.update') }}" method="post">
        @csrf
        @method('put')

        <!--service config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.website_engine_config') }}:</label>
            <div class="col-lg-5">
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.website_engine_service') }}</label>
                    <select class="form-select" name="engine_service">
                        <option value="FresnsEngine" {{ !$params['engine_service'] ? 'selected' : '' }}>
                            @if ($FresnsEngine)
                                🟢
                            @else
                                ⚪
                            @endif
                            {{ __('FsLang::panel.website_engine_default') }}
                        </option>
                        @foreach ($pluginParams['engine'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['engine_service'] == $plugin->unikey ? 'selected' : '' }}>
                                @if ($plugin->is_enable)
                                    🟢
                                @else
                                    ⚪
                                @endif
                                {{ $plugin->name }}
                            </option>
                        @endforeach
                    </select>
                    @if ($engineSettingsPath)
                        <a class="btn btn-outline-secondary" href="{{ route('panel.iframe.setting', ['url' => $engineSettingsPath]) }}" role="button">{{ __('FsLang::panel.button_setting') }}</a>
                    @endif
                </div>
                <!--engine_api_type-->
                <div id="accordionApiType">
                    <!--api_type-->
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.website_engine_api_type') }}</label>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="engine_api_type" id="api_local" value="local" data-bs-toggle="collapse" data-bs-target="#local_key_setting:not(.show)" aria-expanded="true" aria-controls="local_key_setting" @if($params['engine_api_type'] == 'local') checked @endif>
                                <label class="form-check-label" for="api_local">{{ __('FsLang::panel.option_local') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="engine_api_type" id="api_remote" value="remote" data-bs-toggle="collapse" data-bs-target="#remote_key_setting:not(.show)" aria-expanded="false" aria-controls="remote_key_setting" @if($params['engine_api_type'] == 'remote') checked @endif>
                                <label class="form-check-label" for="api_remote">{{ __('FsLang::panel.option_remote') }}</label>
                            </div>
                        </div>
                    </div>
                    <!--api_type config-->
                    <!--api_local-->
                    <div class="collapse {{ $params['engine_api_type'] == 'local' ? 'show' : '' }}" id="local_key_setting" aria-labelledby="api_local" data-bs-parent="#accordionApiType">
                        <div class="input-group mb-2">
                            <label class="input-group-text">{{ __('FsLang::panel.website_engine_key_id') }}</label>
                            <select class="form-select" name="engine_key_id">
                                <option value="" {{ !$params['engine_key_id'] ? 'selected' : '' }}>{{ __('FsLang::panel.option_not_set') }}</option>
                                @foreach ($keys as $key)
                                    <option value="{{ $key['id'] }}" {{ $params['engine_key_id'] == $key['id'] ? 'selected' : '' }}>{{ $key['appId'] }} - {{ $key['name'] }}</option>
                                @endforeach
                            </select>
                            <a class="btn btn-outline-secondary" href="{{ route('panel.keys.index') }}" role="button">{{ __('FsLang::panel.button_view') }}</a>
                        </div>
                    </div>
                    <!--api_remote-->
                    <div class="collapse {{ $params['engine_api_type'] == 'remote' ? 'show' : '' }}" id="remote_key_setting" aria-labelledby="api_remote" data-bs-parent="#accordionApiType">
                        <div class="input-group mb-3">
                            <label class="input-group-text">{{ __('FsLang::panel.table_platform') }}</label>
                            <select class="form-select" disabled>
                                <option value="4" selected>Responsive Web</option>
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <label class="input-group-text">{{ __('FsLang::panel.table_type') }}</label>
                            <input class="form-control" type="text" value="{{ __('FsLang::panel.key_option_main_api') }}" disabled>
                        </div>
                        <div class="input-group mb-3">
                            <label class="input-group-text">API Host</label>
                            <input type="url" class="form-control" name="engine_api_host" id="engine_api_host" value="{{ $params['engine_api_host'] }}" placeholder="https://">
                        </div>
                        <div class="input-group mb-3">
                            <label class="input-group-text">API ID</label>
                            <input type="text" class="form-control" name="engine_api_app_id" id="engine_api_app_id" value="{{ $params['engine_api_app_id'] }}">
                        </div>
                        <div class="input-group mb-2">
                            <label class="input-group-text">API Secret</label>
                            <input type="text" class="form-control" name="engine_api_app_secret" id="engine_api_app_secret" value="{{ $params['engine_api_app_secret'] }}">
                        </div>
                    </div>
                    <!--api_type config end-->
                </div>
                <!--engine_api_type end-->
            </div>
            <div class="col-lg-5 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.sidebar_engines_intro') }}</div>
        </div>

        <!--website_theme_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.website_theme_config') }}:</label>
            <div class="col-lg-5">
                <div class="input-group mb-3">
                    <div class="form-control bg-white" style="padding:0.28rem 0.75rem;">
                        @if ($themeUnikey['pc'])
                            <span class="badge bg-success fw-normal ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.engine_theme_pc') }}">
                                <i class="bi bi-laptop"></i> {{ $themeName['pc'] ?? $themeUnikey['pc'] }}
                            </span>
                        @else
                            <span class="badge bg-secondary fw-normal ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.engine_theme_pc') }}">
                                <i class="bi bi-laptop"></i> {{ __('FsLang::panel.option_not_set') }}
                            </span>
                        @endif

                        @if ($themeUnikey['mobile'])
                            <span class="badge bg-success fw-normal ms-3" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.engine_theme_mobile') }}">
                                <i class="bi bi-phone"></i> {{ $themeName['mobile'] ?? $themeUnikey['mobile'] }}
                            </span>
                        @else
                            <span class="badge bg-secondary fw-normal ms-3" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.engine_theme_mobile') }}">
                                <i class="bi bi-phone"></i> {{ __('FsLang::panel.option_not_set') }}
                            </span>
                        @endif
                    </div>
                    <a class="btn btn-outline-secondary" href="{{ route('panel.engines.index') }}" role="button">{{ __('FsLang::panel.button_config') }}</a>
                </div>
            </div>
            <div class="col-lg-5 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.sidebar_themes_intro') }}</div>
        </div>

        <!--website_stat_code-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.website_stat_code') }}:</label>
            <div class="col-lg-5 pt-2"><textarea class="form-control" name="website_stat_code" rows="4">{{ $params['website_stat_code'] }}</textarea></div>
            <div class="col-lg-5 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.website_stat_code_desc') }}</div>
        </div>

        <!--website_stat_position-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.website_stat_position') }}:</label>
            <div class="col-lg-5 pt-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="website_stat_position" id="website_stat_position_head" value="head" @if($params['website_stat_position'] == 'head') checked @endif>
                    <label class="form-check-label" for="website_stat_position_head">&lt;head&gt;</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="website_stat_position" id="website_stat_position_body" value="body" @if($params['website_stat_position'] == 'body') checked @endif>
                    <label class="form-check-label" for="website_stat_position_body">&lt;body&gt;</label>
                </div>
            </div>
            <div class="col-lg-5 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.website_stat_position_desc') }}</div>
        </div>

        <!--website_status-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.website_status') }}:</label>
            <div class="col-lg-5 pt-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="website_status" id="website_status_true" value=true data-bs-toggle="collapse" data-bs-target="#website_status_setting.show" aria-expanded="false" aria-controls="website_status_setting" {{ $params['website_status'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="website_status_true">{{ __('FsLang::panel.option_open') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="website_status" id="website_status_false" value=false data-bs-toggle="collapse" data-bs-target="#website_status_setting:not(.show)" aria-expanded="false" aria-controls="website_status_setting" {{ !$params['website_status'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="website_status_false">{{ __('FsLang::panel.option_close') }}</label>
                </div>
                <!--Web Status Config-->
                <div class="collapse {{ !$params['website_status'] ? 'show' : '' }}" id="website_status_setting">
                    <div class="card mt-1">
                        <div class="card-header text-success">{{ __('FsLang::panel.website_status_config') }}</div>
                        <div class="card-body">
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="website_number">{{ __('FsLang::panel.website_status_config_content_number') }}</label>
                                <input type="number" class="form-control" name="website_number" id="website_number" value="{{ $params['website_number'] }}">
                                <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                            </div>
                            <div class="input-group">
                                <label class="input-group-text" for="website_proportion">{{ __('FsLang::panel.website_status_config_content_proportion') }}</label>
                                <input type="number" class="form-control" name="website_proportion" id="website_proportion" value="{{ $params['website_proportion'] }}">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.website_status_config_desc') }}</div>
                        </div>
                    </div>
                </div>
                <!--Web Status Config end-->
            </div>
            <div class="col-lg-5 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.website_status_desc') }}</div>
        </div>

        <!--site_china_mode-->
        <div class="row">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.china_mode') }}:</label>
            <div class="col-lg-5 pt-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="site_china_mode" id="china_server_false" value=false data-bs-toggle="collapse" data-bs-target="#china_server_setting.show" aria-expanded="false" aria-controls="china_server_setting" {{ !$params['site_china_mode'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="china_server_false">{{ __('FsLang::panel.option_no') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="site_china_mode" id="china_server_true" value=true data-bs-toggle="collapse" data-bs-target="#china_server_setting:not(.show)" aria-expanded="false" aria-controls="china_server_setting"{{ $params['site_china_mode'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="china_server_true">{{ __('FsLang::panel.option_yes') }}</label>
                </div>
            </div>
            <div class="col-lg-5 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.china_mode_desc') }}</div>
        </div>
        <!--China Mode Config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-10">
                <div class="collapse {{ $params['site_china_mode'] ? 'show' : '' }}" id="china_server_setting">
                    <div class="card mt-1">
                        <div class="card-header">{{ __('FsLang::panel.china_mode_config') }}</div>
                        <div class="card-body">
                            <!--Config-->
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="china_icp_filing">{{ __('FsLang::panel.china_icp_filing') }}</label>
                                <input type="text" class="form-control" id="china_icp_filing" name="china_icp_filing" value="{{ $params['china_icp_filing'] }}">
                            </div>
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="china_icp_license">{{ __('FsLang::panel.china_icp_license') }}</label>
                                <input type="text" class="form-control" id="china_icp_license" name="china_icp_license" value="{{ $params['china_icp_license'] }}">
                            </div>
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="china_psb_filing">{{ __('FsLang::panel.china_psb_filing') }}</label>
                                <input type="text" class="form-control" id="china_psb_filing" name="china_psb_filing" value="{{ $params['china_psb_filing'] }}">
                            </div>
                            <div class="input-group mb-1">
                                <label class="input-group-text" for="china_broadcasting_license">{{ __('FsLang::panel.china_broadcasting_license') }}</label>
                                <input type="text" class="form-control" id="china_broadcasting_license" name="china_broadcasting_license" value="{{ $params['china_broadcasting_license'] }}">
                            </div>
                            <!--Config end-->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--button_save-->
        <div class="row my-3">
            <div class="col-lg-2"></div>
            <div class="col-lg-8">
                <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
            </div>
        </div>
    </form>
@endsection
