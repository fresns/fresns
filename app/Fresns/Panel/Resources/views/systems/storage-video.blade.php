@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::systems.sidebar')
@endsection

@section('content')
    @include('FsView::systems.storage-header')
    <!--storage config-->
    <form action="{{ route('panel.storage.video.update') }}" method="post">
        @csrf
        @method('put')
        <!--storage_service_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.storage_service_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_service_provider') }}</label>
                    <select class="form-select" id="video_service" name="video_service">
                        <option value="">🚫 {{ __('FsLang::panel.option_deactivate') }}</option>
                        @foreach ($pluginParams['storage'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['video_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_secret_id') }}</label>
                    <input type="text" class="form-control" id="video_secret_id" name="video_secret_id" value="{{ $params['video_secret_id'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_secret_key') }}</label>
                    <input type="text" class="form-control" id="video_secret_key" name="video_secret_key" value="{{ $params['video_secret_key'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_bucket_name') }}</label>
                    <input type="text" class="form-control" id="video_bucket_name" name="video_bucket_name" value="{{ $params['video_bucket_name'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_bucket_area') }}</label>
                    <input type="text" class="form-control" id="video_bucket_area" name="video_bucket_area" value="{{ $params['video_bucket_area'] }}">
                </div>
                <div class="input-group">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_bucket_domain') }}</label>
                    <input type="url" class="form-control" id="video_bucket_domain" name="video_bucket_domain" value="{{ $params['video_bucket_domain'] }}">
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
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_ext') }}</label>
                    <input type="text" class="form-control" id="video_ext" placeholder="wmv,rm,mov,mpeg,mp4,3gp,flv,avi,rmvb" name="video_ext" value="{{ $params['video_ext'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_max_size') }}</label>
                    <input type="number" class="form-control" id="video_max_size" name="video_max_size" value="{{ $params['video_max_size'] }}">
                    <span class="input-group-text">MB</span>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_max_time') }}</label>
                    <input type="number" class="form-control" id="video_max_time" name="video_max_time" value="{{ $params['video_max_time'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_second') }}</span>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_url_status') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="video_url_status" id="video_url_status_false" value="false" data-bs-toggle="collapse" data-bs-target="#video_url_status_setting.show" aria-expanded="false" aria-controls="video_url_status_setting" {{ !$params['video_url_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="video_url_status_false">{{ __('FsLang::panel.option_close') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="video_url_status" id="video_url_status_true" value="true" data-bs-toggle="collapse" data-bs-target="#video_url_status_setting:not(.show)" aria-expanded="false" aria-controls="video_url_status_setting" {{ $params['video_url_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="video_url_status_true">{{ __('FsLang::panel.option_open') }}</label>
                        </div>
                    </div>
                </div>
                <!--AntiLink-->
                <div class="collapse {{ $params['video_url_status'] == 'true' ? 'show' : '' }}" id="video_url_status_setting">
                    <div class="input-group mb-3">
                        <label class="input-group-text w-25">{{ __('FsLang::panel.storage_url_key') }}</label>
                        <input type="text" class="form-control" id="video_url_key" name="video_url_key" value="{{ $params['video_url_key'] }}">
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.storage_url_expire') }}</label>
                        <input type="number" class="form-control" id="video_url_expire" name="video_url_expire" value="{{ $params['video_url_expire'] }}">
                        <span class="input-group-text">{{ __('FsLang::panel.unit_minute') }}</span>
                    </div>
                </div>
                <!--AntiLink end-->
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.storage_url_status_desc') }}</div>
        </div>
        <!--storage_video_transcode-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.storage_function_video_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_video_transcode') }}</label>
                    <input type="text" class="form-control" id="video_transcode" name="video_transcode" value="{{ $params['video_transcode'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_video_watermark') }}</label>
                    <input type="text" class="form-control" id="video_watermark" name="video_watermark" value="{{ $params['video_watermark'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_video_screenshot') }}</label>
                    <input type="text" class="form-control" id="video_screenshot" name="video_screenshot" value="{{ $params['video_screenshot'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_video_gift') }}</label>
                    <input type="text" class="form-control" id="video_gift" name="video_gift" value="{{ $params['video_gift'] }}">
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.storage_function_video_config_desc') }}</div>
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
