@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--apps header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_apps') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_apps_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#installModal" data-type="plugin" data-name="{{ __('FsLang::panel.sidebar_apps') }}">
                    <i class="bi bi-phone"></i> {{ __('FsLang::panel.button_install') }}
                </button>
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <!--app list-->
    <div class="row">
        @foreach ($apps as $app)
            <div class="col-sm-6 col-xl-3 mb-4">
                <div class="card">
                    <div class="position-relative">
                        <img src="/assets/{{ $app->unikey }}/fresns.png" class="card-img-top">
                        @if ($app->is_upgrade)
                            <div class="position-absolute top-0 start-100 translate-middle">
                                <a href="{{ route('panel.upgrades') }}"><span class="badge rounded-pill bg-danger">{{ __('FsLang::panel.new_version') }}</span></a>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <h5 class="text-nowrap overflow-hidden">{{ $app->name }} <span class="badge bg-secondary align-middle fs-9">{{ $app->version }}</span></h5>
                        <p class="card-text text-height">{{ $app->description }}</p>
                        <div>
                            @if ($app->is_enable)
                                <button type="button" class="btn btn-outline-secondary btn-sm plugin-manage" data-action="{{ route('panel.plugin.update', ['plugin' => $app->id]) }}" data-enable="0">{{ __('FsLang::panel.button_deactivate') }}</button>
                                @if ($app->settings_path)
                                    <a href="{{ route('panel.iframe.client', ['url' => $app->settings_path]) }}" class="btn btn-primary btn-sm">{{ __('FsLang::panel.button_setting') }}</a>
                                @endif
                            @else
                                <button type="button" class="btn btn-outline-success btn-sm plugin-manage" data-action="{{ route('panel.plugin.update', ['plugin' => $app->id]) }}" data-enable="1">{{ __('FsLang::panel.button_activate') }}</button>
                                <button type="button" class="btn btn-link btn-sm ms-2 text-danger fresns-link plugin-uninstall-button" data-action="{{ route('panel.plugin.uninstall', ['plugin' => $app->unikey]) }}" data-name="{{ $app->name }}" data-clear_data_desc="{{ __('FsLang::panel.option_uninstall_plugin_data') }}" data-bs-toggle="modal" data-bs-target="#uninstallConfirm">{{ __('FsLang::panel.button_uninstall') }}</button>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer fs-8">{{ __('FsLang::panel.author') }}: <a href="{{ $app->author_link }}" target="_blank" target="_blank" class="link-info fresns-link">{{ $app->author }}</a></div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
