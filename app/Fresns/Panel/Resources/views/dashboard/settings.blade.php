@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::dashboard.sidebar')
@endsection

@section('content')
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_settings') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_settings_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <!--form-->
    <form action="{{ route('panel.settings.update') }}" method="post" id="adminConfig">
        @csrf
        @method('put')
        <div class="row mb-3">
            <label for="backend_url" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.setting_build_type') }}:</label>
            <div class="col-lg-6">
                <select class="form-select" name="build_type" required>
                    <option disabled value="">{{ __('FsLang::panel.setting_build_select_tip') }}</option>
                    <option value="1" @if($params['build_type'] == 1) selected @endif>{{ __('FsLang::panel.setting_build_option_stable') }}</option>
                    <option value="2" @if($params['build_type'] == 2) selected @endif>{{ __('FsLang::panel.setting_build_option_beta') }}</option>
                </select>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.setting_build_type_desc') }}</div>
        </div>
        <div class="row mb-3">
            <label for="system_url" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.setting_system_url') }}:</label>
            <div class="col-lg-6"><input type="url" class="form-control update-panel-url" id="system_url" name="systemUrl" value="{{ $params['system_url'] }}" placeholder="https://" required></div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.setting_system_url_desc') }}</div>
        </div>
        <div class="row mb-3">
            <label for="panel_path" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.setting_panel_path') }}: </label>
            <div class="col-lg-6"><input type="text" class="form-control update-panel-url" id="panel_path" name="panelPath" value="{{ $params['panel_path'] }}" placeholder="admin" required></div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.setting_panel_path_desc') }}</div>
        </div>
        <div class="row mb-3">
            <label for="panel_url" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.setting_panel_url') }}:</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <span class="form-control bg-light" id="panelUrl">{{ $params['system_url'].'/fresns/'.$params['panel_path'] }}</span>
                    <button class="btn btn-outline-secondary" onclick="copyToClipboard('#panelUrl')" type="button">{{ __('FsLang::panel.setting_panel_url_copy') }}</button>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.setting_panel_url_desc') }}</div>
        </div>
        <!--Save-->
        <div class="row my-3">
            <div class="col-lg-2"></div>
            <div class="col-lg-6">
                <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
            </div>
        </div>
    </form>
@endsection
