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
                        @foreach ($storagePlugins as $plugin)
                            <option value="{{ $plugin->fskey }}" {{ $params['document_service'] == $plugin->fskey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">Secret ID</label>
                    <input type="text" class="form-control" id="document_secret_id" name="document_secret_id" value="{{ $params['document_secret_id'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">Secret Key</label>
                    <input type="text" class="form-control" id="document_secret_key" name="document_secret_key" value="{{ $params['document_secret_key'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">Secret App</label>
                    <input type="text" class="form-control" name="document_secret_app" value="{{ $params['document_secret_app'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_bucket_name') }}</label>
                    <input type="text" class="form-control" id="document_bucket_name" name="document_bucket_name" value="{{ $params['document_bucket_name'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_bucket_region') }}</label>
                    <input type="text" class="form-control" id="document_bucket_region" name="document_bucket_region" value="{{ $params['document_bucket_region'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_bucket_domain') }}</label>
                    <input type="text" class="form-control" id="document_bucket_domain" name="document_bucket_domain" value="{{ $params['document_bucket_domain'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_filesystem_disk') }}</label>
                    <div class="form-control">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="document_filesystem_disk" id="document_filesystem_disk_local" value="local" {{ ($params['document_filesystem_disk'] == 'local') ? 'checked' : '' }}>
                            <label class="form-check-label" for="document_filesystem_disk_local">{{ __('FsLang::panel.option_local').' (local)' }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="document_filesystem_disk" id="document_filesystem_disk_remote" value="remote" {{ ($params['document_filesystem_disk'] == 'remote') ? 'checked' : '' }}>
                            <label class="form-check-label" for="document_filesystem_disk_remote">{{ __('FsLang::panel.option_remote').' (remote)' }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1">
                <i class="bi bi-info-circle"></i> {{ __('FsLang::panel.storage_service_config_desc') }}<br>
                <i class="bi bi-info-circle"></i> {{ __('FsLang::panel.storage_bucket_region_desc') }}<br>
                <i class="bi bi-info-circle"></i> {{ __('FsLang::panel.storage_bucket_domain_desc') }}
            </div>
        </div>
        <!--storage_function_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.storage_function_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_extension_names') }}</label>
                    <input type="text" class="form-control" id="document_extension_names" placeholder="doc,docx,xls,xlsx,csv,ppt,pptx,pdf,md,zip,epub,mobi,7z,rar,markdown,pps,ppts,txt" name="document_extension_names" value="{{ $params['document_extension_names'] }}">
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_max_size') }}</label>
                    <input type="number" class="form-control" id="document_max_size" name="document_max_size" value="{{ $params['document_max_size'] }}">
                    <span class="input-group-text">MB</span>
                    <span class="form-control text-end"><a href="{{ route('panel.roles.index') }}" target="_blank">{{ __('FsLang::panel.sidebar_roles') }} ({{ __('FsLang::panel.button_config_permission') }})</a></span>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_url_status') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="document_url_status" id="document_url_status_false" value="false" data-bs-toggle="collapse" data-bs-target=".document_url_status_setting.show" aria-expanded="false" aria-controls="document_url_status_setting" {{ !$params['document_url_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="document_url_status_false">{{ __('FsLang::panel.option_close') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="document_url_status" id="document_url_status_true" value="true" data-bs-toggle="collapse" data-bs-target=".document_url_status_setting:not(.show)" aria-expanded="false" aria-controls="document_url_status_setting" {{ $params['document_url_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="document_url_status_true">{{ __('FsLang::panel.option_open') }}</label>
                        </div>
                    </div>
                </div>
                <!--AntiLink-->
                <div class="collapse document_url_status_setting {{ $params['document_url_status'] ? 'show' : '' }}">
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
        <!--storage_document_preview_service-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.storage_function_document_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.storage_document_preview_service') }}</label>
                    <select class="form-select" id="document_preview_service" name="document_preview_service">
                        <option value="">🚫 {{ __('FsLang::panel.option_deactivate') }}</option>
                        @foreach ($documentPreviewPlugins as $previewPlugin)
                            <option value="{{ $previewPlugin->fskey }}" {{ $params['document_preview_service'] == $previewPlugin->fskey ? 'selected' : '' }}>{{ $previewPlugin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.storage_document_preview_extension_names') }}</label>
                    <input type="text" class="form-control" id="document_preview_extension_names" placeholder="doc,docx,xls,xlsx,csv,ppt,pptx,pps,ppts,pdf" name="document_preview_extension_names" value="{{ $params['document_preview_extension_names'] }}">
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
