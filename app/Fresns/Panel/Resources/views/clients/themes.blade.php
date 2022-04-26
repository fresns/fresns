@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--website header-->
    <div class="row mb-4">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_website') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_website_themes_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <button class="btn btn-primary install-dialog" type="button" data-bs-toggle="modal" data-bs-target="#installModal" data-type="theme" data-name="{{ __('FsLang::panel.sidebar_website_tab_themes') }}">
                    <i class="bi bi-brush"></i> {{ __('FsLang::panel.button_install') }}
                </button>
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
        <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link" href="{{ route('panel.engines.index') }}">{{ __('FsLang::panel.sidebar_website_tab_engines') }}</a></li>
            <li class="nav-item"><a class="nav-link active" href="{{ route('panel.themes.index') }}">{{ __('FsLang::panel.sidebar_website_tab_themes') }}</a></li>
        </ul>
    </div>
    <!--theme list-->
    <div class="row">
        @foreach ($themes as $theme)
            <div class="col-sm-6 col-xl-3 mb-4">
                <div class="card">
                    <div class="position-relative">
                        <img src="/assets/{{ $theme->unikey }}/fresns.png" class="card-img-top">
                        @if ($theme->is_upgrade)
                            <div class="position-absolute top-0 start-100 translate-middle">
                                <a href="{{ route('panel.upgrades') }}"><span class="badge rounded-pill bg-danger">{{ __('FsLang::panel.new_version') }}</span></a>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <h5 class="text-nowrap overflow-hidden">{{ $theme->name }} <span class="badge bg-secondary align-middle fs-9">{{ $theme->version }}</span></h5>
                        <p class="card-text text-height">{{ $theme->description }}</p>
                        <div>
                            @if ($theme->is_enable)
                                <button type="button" class="btn btn-outline-secondary btn-sm plugin-manage" data-action="{{ route('panel.plugin.updateTheme', ['theme' => $theme->unikey]) }}" data-enable="0">{{ __('FsLang::panel.button_deactivate') }}</button>
                                @if ($theme->theme_functions)
                                    <a href="{{ route('panel.themes.index', ['theme' => $theme->unikey]) }}" class="btn btn-primary btn-sm">{{ __('FsLang::panel.button_setting') }}</a>
                                @endif
                            @else
                                <button type="button" class="btn btn-outline-success btn-sm plugin-manage" data-action="{{ route('panel.plugin.updateTheme', ['theme' => $theme->unikey]) }}" data-enable="1">{{ __('FsLang::panel.button_activate') }}</button>
                                <button type="button" class="btn btn-link btn-sm ms-2 text-danger fresns-link plugin-uninstall-button" data-action="{{ route('panel.plugin.uninstallTheme', ['theme' => $theme->unikey]) }}" data-name="{{ $theme->name }}" data-clear_data_desc="{{ __('FsLang::panel.option_uninstall_theme_data') }}" data-bs-toggle="modal" data-bs-target="#uninstallConfirm">{{ __('FsLang::panel.button_uninstall') }}</button>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer fs-8">{{ __('FsLang::panel.author') }}: <a href="{{ $theme->author_link }}" target="_blank" class="link-info fresns-link">{{ $theme->author }}</a></div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
