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
            <label for="backend_url" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.setting_backend_domain') }}:</label>
            <div class="col-lg-6"><input type="url" class="form-control update-backend-url" id="backend_url" name="domain" value="{{ $domain }}" placeholder="https://"></div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.setting_backend_domain_desc') }}</div>
        </div>
        <div class="row mb-3">
            <label for="panel_path" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.setting_panel_path') }}: </label>
            <div class="col-lg-6"><input type="text" class="form-control update-backend-url" id="panel_path" name="path" value="{{ $path }}" placeholder="admin"></div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.setting_panel_path_desc') }}</div>
        </div>
        <div class="row mb-3">
            <label for="backend_url" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.setting_panel_url') }}:</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <span class="form-control bg-light" id="backendUrl">{{ $domain.'/fresns/'.$path }}</span>
                    <button class="btn btn-outline-secondary" onclick="copyToClipboard('#backendUrl')" type="button">{{ __('FsLang::panel.setting_panel_url_copy') }}</button>
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