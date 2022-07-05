@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--app header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_app') }}</h3>
            <p class="text-secondary"><i class="bi bi-phone"></i> {{ __('FsLang::panel.sidebar_app_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>

    <!--app conifg-->
    <form action="{{ route('panel.app.update') }}" method="post">
        @csrf
        @method('put')

        <!--ios_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.app_ios_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.app_notify') }}</label>
                    <select class="form-select" name="ios_notify_service">
                        <option value="" {{ !$params['ios_notify_service'] ? 'selected' : '' }}>🚫 {{ __('FsLang::panel.option_deactivate') }}</option>
                        @foreach ($pluginParams['appNotify'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['ios_notify_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.app_ios_notify_desc') }}</div>
        </div>

        <!--android_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.app_android_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.app_notify') }}</label>
                    <select class="form-select" name="android_notify_service">
                        <option value="" {{ !$params['android_notify_service'] ? 'selected' : '' }}>🚫 {{ __('FsLang::panel.option_deactivate') }}</option>
                        @foreach ($pluginParams['appNotify'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['android_notify_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.app_android_notify_desc') }}</div>
        </div>

        <!--wechat_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.app_wechat_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.app_notify') }}</label>
                    <select class="form-select" name="wechat_notify_service">
                        <option value="" {{ !$params['wechat_notify_service'] ? 'selected' : '' }}>🚫 {{ __('FsLang::panel.option_deactivate') }}</option>
                        @foreach ($pluginParams['appNotify'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['wechat_notify_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.app_wechat_notify_desc') }}</div>
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
