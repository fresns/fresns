@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::systems.sidebar')
@endsection

@section('content')
    @include('FsView::systems.storage-header')
    <!--storage config-->
    <form action="{{ route('panel.storage.substitution.update') }}" method="post">
        @csrf
        @method('put')
        <!--storage_service_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.storage_function_substitution_config') }}:</label>
            <div class="col-lg-8">

                <div class="input-group mb-3">
                    <div class="input-group">
                        <label class="input-group-text">{{ __('FsLang::panel.storage_function_substitution_image') }}</label>
                        <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_image_input') }}</button>
                        <ul class="dropdown-menu selectImageType">
                            <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                            <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        </ul>
                        <input type="file" class="form-control inputFile hidden" name="substitution_image_id" style="display:none">
                        <input type="url" class="form-control inputUrl" name="substitution_image" value="{{ $params['substitution_image'] }}">
                        <button class="btn btn-outline-secondary preview-image" type="button">{{ __('FsLang::panel.button_image_view') }}</button>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <div class="input-group">
                        <label class="input-group-text">{{ __('FsLang::panel.storage_function_substitution_video') }}</label>
                        <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_image_input') }}</button>
                        <ul class="dropdown-menu selectImageType">
                            <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                            <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        </ul>
                        <input type="file" class="form-control inputFile" name="substitution_video_id" style="display:none;">
                        <input type="url" class="form-control inputUrl" name="substitution_video" value="{{ $params['substitution_video'] }}">
                        <button class="btn btn-outline-secondary preview-image" type="button">{{ __('FsLang::panel.button_image_view') }}</button>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <div class="input-group">
                        <label class="input-group-text">{{ __('FsLang::panel.storage_function_substitution_audio') }}</label>
                        <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_image_input') }}</button>
                        <ul class="dropdown-menu selectImageType">
                            <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                            <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        </ul>
                        <input type="file" class="form-control inputFile" name="substitution_audio_id" style="display:none;">
                        <input type="url" class="form-control inputUrl" name="substitution_audio" value="{{ $params['substitution_audio'] }}">
                        <button class="btn btn-outline-secondary preview-image" type="button">{{ __('FsLang::panel.button_image_view') }}</button>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <div class="input-group">
                        <label class="input-group-text">{{ __('FsLang::panel.storage_function_substitution_document') }}</label>
                        <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_image_input') }}</button>
                        <ul class="dropdown-menu selectImageType">
                            <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                            <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        </ul>
                        <input type="file" class="form-control inputFile" name="substitution_document_id" style="display:none;">
                        <input type="url" class="form-control inputUrl" name="substitution_document" value="{{ $params['substitution_document'] }}">
                        <button class="btn btn-outline-secondary preview-image" type="button">{{ __('FsLang::panel.button_image_view') }}</button>
                    </div>
                </div>
                <div class="form-text"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.storage_function_substitution_config_desc') }}</div>
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

    <div class="modal fade image-zoom" id="imageZoom" tabindex="-1" aria-labelledby="imageZoomLabel"
        style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="position-relative image-box">
                <img class="img-fluid" src="">
            </div>
        </div>
    </div>
@endsection
