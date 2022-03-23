@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::systems.sidebar')
@endsection

@section('content')
    @include('FsView::systems.storage-header')
    <!--storage config-->
    <form action="{{ route('panel.storage.document.update') }}" method="post">
        @csrf
        @method('put')
        <!--storage_service_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.storage_service_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_service_provider') }}</label>
                    <select class="form-select" id="document_service" name="document_service">
                        <option value="">🚫 {{ __('FsLang::panel.option_deactivate') }}</option>
                        @foreach ($pluginParams['storage'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['document_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_secret_id') }}</label>
                    <input type="text" class="form-control" id="document_secret_id" name="document_secret_id" value="{{ $params['document_secret_id'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_secret_key') }}</label>
                    <input type="text" class="form-control" id="document_secret_key" name="document_secret_key" value="{{ $params['document_secret_key'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_bucket_name') }}</label>
                    <input type="text" class="form-control" id="document_bucket_name" name="document_bucket_name" value="{{ $params['document_bucket_name'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_bucket_area') }}</label>
                    <input type="text" class="form-control" id="document_bucket_area" name="document_bucket_area" value="{{ $params['document_bucket_area'] }}">
                </div>
                <div class="input-group">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_bucket_domain') }}</label>
                    <input type="url" class="form-control" id="document_bucket_domain" name="document_bucket_domain" value="{{ $params['document_bucket_domain'] }}">
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
                    <input type="text" class="form-control" id="document_ext" placeholder="doc,docx,xls,xlsx,csv,ppt,pptx,pdf,md,zip,epub,mobi,7z,rar,markdown,pps,ppts,txt" name="document_ext" value="{{ $params['document_ext'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_max_size') }}</label>
                    <input type="number" class="form-control" id="document_max_size" name="document_max_size" value="{{ $params['document_max_size'] }}">
                    <label class="input-group-text">MB</label>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_url_status') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="document_url_status" id="document_url_status_false" value="false" data-bs-toggle="collapse" data-bs-target="#document_url_status_setting.show" aria-expanded="false" aria-controls="document_url_status_setting" {{ !$params['document_url_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="document_url_status_false">{{ __('FsLang::panel.option_close') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="document_url_status" id="document_url_status_true" value="true" data-bs-toggle="collapse" data-bs-target="#document_url_status_setting:not(.show)" aria-expanded="false" aria-controls="document_url_status_setting" {{ $params['document_url_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="document_url_status_true">{{ __('FsLang::panel.option_open') }}</label>
                        </div>
                    </div>
                </div>
                <!--AntiLink-->
                <div class="collapse  {{ $params['document_url_status'] ? 'show' : '' }}" id="document_url_status_setting">
                    <div class="input-group mb-3">
                        <label class="input-group-text w-25">{{ __('FsLang::panel.storage_url_key') }}</label>
                        <input type="text" class="form-control" id="document_url_key" name="document_url_key" value="{{ $params['document_url_key'] }}">
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.storage_url_expire') }}</label>
                        <input type="number" class="form-control" id="document_url_expire" name="document_url_expire" value="{{ $params['document_url_expire'] }}">
                        <span class="input-group-text">{{ __('FsLang::panel.unit_minute') }}</span>
                    </div>
                </div>
                <!--AntiLink end-->
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.storage_url_status_desc') }}</div>
        </div>
        <!--storage_document_online_preview-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.storage_function_document_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_document_online_preview') }}</label>
                    <input type="text" class="form-control" id="document_online_preview" name="document_online_preview" value="{{ $params['document_online_preview'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.storage_document_preview_ext') }}</label>
                    <input type="text" class="form-control" id="document_preview_ext" placeholder="doc,docx,xls,xlsx,csv,ppt,pptx,pps,ppts,pdf" name="document_preview_ext" value="{{ $params['document_preview_ext'] }}">
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1">
                <i class="bi bi-info-circle"></i> {{ __('FsLang::panel.storage_function_document_config_desc') }}
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
