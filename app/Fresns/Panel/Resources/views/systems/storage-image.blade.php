@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::systems.sidebar')
@endsection

@section('content')
    @include('FsView::systems.storage-header')
    <!--storage config-->
    <form action="{{ route('panel.storage.image.update') }}" method="post">
        @csrf
        @method('put')
        <!--storage_service_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.storage_service_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_service_provider') }}</label>
                    <select class="form-select" id="image_service" name="image_service">
                        <option value="">🚫 {{ __('FsLang::panel.option_deactivate') }}</option>
                        @foreach ($pluginParams['storage'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['image_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_secret_id') }}</label>
                    <input type="text" class="form-control" name="image_secret_id" value="{{ $params['image_secret_id'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_secret_key') }}</label>
                    <input type="text" class="form-control" name="image_secret_key" value="{{ $params['image_secret_key'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_bucket_name') }}</label>
                    <input type="text" class="form-control" name="image_bucket_name" value="{{ $params['image_bucket_name'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_bucket_area') }}</label>
                    <input type="text" class="form-control" name="image_bucket_area" value="{{ $params['image_bucket_area'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_bucket_domain') }}</label>
                    <input type="url" class="form-control" name="image_bucket_domain" value="{{ $params['image_bucket_domain'] }}">
                </div>
                <div class="input-group">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_filesystem_disk') }}</label>
                    <select class="form-select" name="image_filesystem_disk">
                        <option value="local" {{ $params['image_filesystem_disk'] == 'local' ? 'selected' : '' }}>local</option>
                        <option value="remote" {{ $params['image_filesystem_disk'] == 'remote' ? 'selected' : '' }}>remote</option>
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
                    <input type="text" class="form-control" name="image_extension_names" value="{{ $params['image_extension_names'] }}" placeholder="png,gif,jpg,jpeg,bmp,heic">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_max_size') }}</label>
                    <input type="number" class="form-control" name="image_max_size" value="{{ $params['image_max_size'] }}">
                    <span class="input-group-text">MB</span>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_url_status') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="image_url_status" id="image_url_status_false" value="false" data-bs-toggle="collapse" data-bs-target="#image_url_status_setting.show" aria-expanded="false" aria-controls="image_url_status_setting" {{ !$params['image_url_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="image_url_status_false">{{ __('FsLang::panel.option_close') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="image_url_status" id="image_url_status_true" value="true" data-bs-toggle="collapse" data-bs-target="#image_url_status_setting:not(.show)" aria-expanded="false" aria-controls="image_url_status_setting" {{ $params['image_url_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="image_url_status_true">{{ __('FsLang::panel.option_open') }}</label>
                        </div>
                    </div>
                </div>
                <!--AntiLink-->
                <div class="collapse {{ $params['image_url_status'] == 'true' ? 'show' : '' }}" id="image_url_status_setting">
                    <div class="input-group mb-3">
                        <label class="input-group-text w-25">{{ __('FsLang::panel.storage_url_key') }}</label>
                        <input type="text" class="form-control" name="image_url_key" value="{{ $params['image_url_key'] }}">
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.storage_url_expire') }}</label>
                        <input type="number" class="form-control" name="image_url_expire" value="{{ $params['image_url_expire'] }}">
                        <span class="input-group-text">{{ __('FsLang::panel.unit_minute') }}</span>
                    </div>
                </div>
                <!--AntiLink end-->
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.storage_url_status_desc') }}</div>
        </div>
        <!--storage_image_thumb_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.storage_function_image_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_image_thumb_config') }}</label>
                    <input type="text" class="form-control" name="image_thumb_config" value="{{ $params['image_thumb_config'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_image_thumb_avatar') }}</label>
                    <input type="text" class="form-control" name="image_thumb_avatar" value="{{ $params['image_thumb_avatar'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_image_thumb_ratio') }}</label>
                    <input type="text" class="form-control" name="image_thumb_ratio" value="{{ $params['image_thumb_ratio'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_image_thumb_square') }}</label>
                    <input type="text" class="form-control" name="image_thumb_square" value="{{ $params['image_thumb_square'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_image_thumb_big') }}</label>
                    <input type="text" class="form-control" name="image_thumb_big" value="{{ $params['image_thumb_big'] }}">
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.storage_function_image_config_desc') }}</div>
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
