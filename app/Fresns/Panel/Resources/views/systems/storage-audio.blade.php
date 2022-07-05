@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::systems.sidebar')
@endsection

@section('content')
    @include('FsView::systems.storage-header')
    <!--storage config-->
    <form action="{{ route('panel.storage.audio.update') }}" method="post">
        @csrf
        @method('put')
        <!--storage_service_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.storage_service_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <span class="input-group-text w-25">{{ __('FsLang::panel.storage_service_provider') }}</span>
                    <select class="form-select" id="audio_service" name="audio_service">
                        <option value="">🚫 {{ __('FsLang::panel.option_deactivate') }}</option>
                        @foreach ($pluginParams['storage'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['audio_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_secret_id') }}</label>
                    <input type="text" class="form-control" id="audio_secret_id" name="audio_secret_id" value="{{ $params['audio_secret_id'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_secret_key') }}</label>
                    <input type="text" class="form-control" id="audio_secret_key" name="audio_secret_key" value="{{ $params['audio_secret_key'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_bucket_name') }}</label>
                    <input type="text" class="form-control" id="audio_bucket_name" name="audio_bucket_name" value="{{ $params['audio_bucket_name'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_bucket_area') }}</label>
                    <input type="text" class="form-control" id="audio_bucket_area" name="audio_bucket_area" value="{{ $params['audio_bucket_area'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_bucket_domain') }}</label>
                    <input type="url" class="form-control" id="audio_bucket_domain" name="audio_bucket_domain" value="{{ $params['audio_bucket_domain'] }}">
                </div>
                <div class="input-group">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_filesystem_disk') }}</label>
                    <select class="form-select" name="audio_filesystem_disk">
                        <option value="local" {{ $params['audio_filesystem_disk'] == 'local' ? 'selected' : '' }}>local</option>
                        <option value="remote" {{ $params['audio_filesystem_disk'] == 'remote' ? 'selected' : '' }}>remote</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1">
                <i class="bi bi-info-circle"></i> {{ __('FsLang::panel.storage_service_config_desc') }}<br>
                <i class="bi bi-info-circle"></i> {{ __('FsLang::panel.storage_bucket_area_desc') }}<br>
                <i class="bi bi-info-circle"></i> {{ __('FsLang::panel.storage_bucket_domain_desc') }}
            </div>
        </div>
        <!--storage_function_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.storage_function_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_extension_names') }}</label>
                    <input type="text" class="form-control" id="audio_extension_names" placeholder="mp3,wav,m4a" name="audio_extension_names" value="{{ $params['audio_extension_names'] ?? '' }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_max_size') }}</label>
                    <input type="number" class="form-control" id="audio_max_size" name="audio_max_size" value="{{ $params['audio_max_size'] }}">
                    <span class="input-group-text">MB</span>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_max_time') }}</label>
                    <input type="number" class="form-control" id="audio_max_time" name="audio_max_time" value="{{ $params['audio_max_time'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_second') }}</span>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_url_status') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="audio_url_status" id="audio_url_status_false" value="false" data-bs-toggle="collapse" data-bs-target="#audio_url_status_setting.show" aria-expanded="false" aria-controls="audio_url_status_setting" {{ !$params['audio_url_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="audio_url_status_false">{{ __('FsLang::panel.option_close') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="audio_url_status" id="audio_url_status_true" value="true" data-bs-toggle="collapse" data-bs-target="#audio_url_status_setting:not(.show)" aria-expanded="false" aria-controls="audio_url_status_setting" {{ $params['audio_url_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="audio_url_status_true">{{ __('FsLang::panel.option_open') }}</label>
                        </div>
                    </div>
                </div>
                <!--AntiLink-->
                <div class="collapse  {{ $params['audio_url_status'] ? 'show' : '' }}" id="audio_url_status_setting">
                    <div class="input-group mb-3">
                        <label class="input-group-text w-25">{{ __('FsLang::panel.storage_url_key') }}</label>
                        <input type="text" class="form-control" id="audio_url_key" name="audio_url_key" value="{{ $params['audio_url_key'] }}">
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.storage_url_expire') }}</label>
                        <input type="number" class="form-control" id="audio_url_expire" name="audio_url_expire" value="{{ $params['audio_url_expire'] }}">
                        <span class="input-group-text">{{ __('FsLang::panel.unit_minute') }}</span>
                    </div>
                </div>
                <!--AntiLink end-->
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.storage_url_status_desc') }}</div>
        </div>
        <!--storage_audio_transcode-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.storage_function_audio_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_audio_transcode') }}</label>
                    <input type="text" class="form-control" id="audio_transcode" name="audio_transcode" value="{{ $params['audio_transcode'] }}">
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.storage_function_audio_config_desc') }}</div>
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
