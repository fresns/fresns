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

                <!--substitution_image-->
                <div class="input-group mb-3">
                    <div class="input-group">
                        <label class="input-group-text">{{ __('FsLang::panel.storage_function_substitution_image') }}</label>
                        <!--Options-->
                        <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @if ($configImageInfo['imageConfigType'] == 'ID')
                                {{ __('FsLang::panel.button_image_upload') }}
                            @else
                                {{ __('FsLang::panel.button_image_input') }}
                            @endif
                        </button>
                        <ul class="dropdown-menu selectImageType">
                            <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                            <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        </ul>
                        <!--Input-->
                        <input type="file" class="form-control inputFile" name="substitution_image_file" @if ($configImageInfo['imageConfigType'] == 'URL') style="display:none;" @endif>
                        <input type="url" class="form-control inputUrl" name="substitution_image_url" @if ($configImageInfo['imageConfigType'] == 'ID') style="display:none;" @endif  @if ($configImageInfo['imageConfigType'] == 'URL') value="{{ $params['substitution_image'] }}" @endif>
                        <!--Hidden item-->
                        <input type="hidden" class="substitution_image" value="{{ $params['substitution_image'] }}">
                        <!--Preview-->
                        @if ($params['substitution_image'])
                            <input type="hidden" class="imageUrl" value="{{ $configImageInfo['imageConfigUrl'] }}">
                            <button class="btn btn-outline-secondary preview-image" type="button">{{ __('FsLang::panel.button_image_view') }}</button>
                        @endif
                    </div>
                </div>

                <!--substitution_video-->
                <div class="input-group mb-3">
                    <div class="input-group">
                        <label class="input-group-text">{{ __('FsLang::panel.storage_function_substitution_video') }}</label>
                        <!--Options-->
                        <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @if ($configImageInfo['videoConfigType'] == 'ID')
                                {{ __('FsLang::panel.button_image_upload') }}
                            @else
                                {{ __('FsLang::panel.button_image_input') }}
                            @endif
                        </button>
                        <ul class="dropdown-menu selectImageType">
                            <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                            <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        </ul>
                        <!--Input-->
                        <input type="file" class="form-control inputFile" name="substitution_video_file" @if ($configImageInfo['videoConfigType'] == 'URL') style="display:none;" @endif>
                        <input type="url" class="form-control inputUrl" name="substitution_video_url" @if ($configImageInfo['videoConfigType'] == 'ID') style="display:none;" @endif  @if ($configImageInfo['videoConfigType'] == 'URL') value="{{ $params['substitution_video'] }}" @endif>
                        <!--Hidden item-->
                        <input type="hidden" class="substitution_video" value="{{ $params['substitution_video'] }}">
                        <!--Preview-->
                        @if ($params['substitution_video'])
                            <input type="hidden" class="imageUrl" value="{{ $configImageInfo['videoConfigUrl'] }}">
                            <button class="btn btn-outline-secondary preview-image" type="button">{{ __('FsLang::panel.button_image_view') }}</button>
                        @endif
                    </div>
                </div>

                <!--substitution_audio-->
                <div class="input-group mb-3">
                    <div class="input-group">
                        <label class="input-group-text">{{ __('FsLang::panel.storage_function_substitution_audio') }}</label>
                        <!--Options-->
                        <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @if ($configImageInfo['audioConfigType'] == 'ID')
                                {{ __('FsLang::panel.button_image_upload') }}
                            @else
                                {{ __('FsLang::panel.button_image_input') }}
                            @endif
                        </button>
                        <ul class="dropdown-menu selectImageType">
                            <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                            <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        </ul>
                        <!--Input-->
                        <input type="file" class="form-control inputFile" name="substitution_audio_file" @if ($configImageInfo['audioConfigType'] == 'URL') style="display:none;" @endif>
                        <input type="url" class="form-control inputUrl" name="substitution_audio_url" @if ($configImageInfo['audioConfigType'] == 'ID') style="display:none;" @endif  @if ($configImageInfo['audioConfigType'] == 'URL') value="{{ $params['substitution_audio'] }}" @endif>
                        <!--Hidden item-->
                        <input type="hidden" class="substitution_audio" value="{{ $params['substitution_audio'] }}">
                        <!--Preview-->
                        @if ($params['substitution_audio'])
                            <input type="hidden" class="imageUrl" value="{{ $configImageInfo['audioConfigUrl'] }}">
                            <button class="btn btn-outline-secondary preview-image" type="button">{{ __('FsLang::panel.button_image_view') }}</button>
                        @endif
                    </div>
                </div>

                <!--substitution_document-->
                <div class="input-group mb-3">
                    <div class="input-group">
                        <label class="input-group-text">{{ __('FsLang::panel.storage_function_substitution_document') }}</label>
                        <!--Options-->
                        <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @if ($configImageInfo['documentConfigType'] == 'ID')
                                {{ __('FsLang::panel.button_image_upload') }}
                            @else
                                {{ __('FsLang::panel.button_image_input') }}
                            @endif
                        </button>
                        <ul class="dropdown-menu selectImageType">
                            <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                            <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        </ul>
                        <!--Input-->
                        <input type="file" class="form-control inputFile" name="substitution_document_file" @if ($configImageInfo['documentConfigType'] == 'URL') style="display:none;" @endif>
                        <input type="url" class="form-control inputUrl" name="substitution_document_url" @if ($configImageInfo['documentConfigType'] == 'ID') style="display:none;" @endif  @if ($configImageInfo['documentConfigType'] == 'URL') value="{{ $params['substitution_document'] }}" @endif>
                        <!--Hidden item-->
                        <input type="hidden" class="substitution_document" value="{{ $params['substitution_document'] }}">
                        <!--Preview-->
                        @if ($params['substitution_document'])
                            <input type="hidden" class="imageUrl" value="{{ $configImageInfo['documentConfigUrl'] }}">
                            <button class="btn btn-outline-secondary preview-image" type="button">{{ __('FsLang::panel.button_image_view') }}</button>
                        @endif
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
