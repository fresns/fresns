@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::systems.sidebar')
@endsection

@section('content')
    @include('FsView::systems.storage-header')
    <!--storage config-->
    <form action="{{ route('panel.storage.substitution.update') }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('put')
        <!--storage_service_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.storage_function_substitution_config') }}:</label>
            <div class="col-lg-8">

                <!--image_substitution-->
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
                        <ul class="dropdown-menu selectInputType">
                            <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                            <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        </ul>
                        <!--Input-->
                        <input type="file" class="form-control inputFile" name="image_substitution_file" @if ($configImageInfo['imageConfigType'] == 'URL') style="display:none;" @endif>
                        <input type="url" class="form-control inputUrl" name="image_substitution_url" @if ($configImageInfo['imageConfigType'] == 'ID') style="display:none;" @endif  @if ($configImageInfo['imageConfigType'] == 'URL') value="{{ $params['image_substitution'] }}" @endif>
                        <!--Hidden item-->
                        <input type="hidden" name="image_substitution" value="{{ $params['image_substitution'] }}">
                        <!--Preview-->
                        @if ($params['image_substitution'])
                            <button class="btn btn-outline-secondary preview-image" type="button" data-url="{{ $configImageInfo['imageConfigUrl'] }}">{{ __('FsLang::panel.button_view') }}</button>
                        @endif
                    </div>
                </div>

                <!--video_substitution-->
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
                        <ul class="dropdown-menu selectInputType">
                            <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                            <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        </ul>
                        <!--Input-->
                        <input type="file" class="form-control inputFile" name="video_substitution_file" @if ($configImageInfo['videoConfigType'] == 'URL') style="display:none;" @endif>
                        <input type="url" class="form-control inputUrl" name="video_substitution_url" @if ($configImageInfo['videoConfigType'] == 'ID') style="display:none;" @endif  @if ($configImageInfo['videoConfigType'] == 'URL') value="{{ $params['video_substitution'] }}" @endif>
                        <!--Hidden item-->
                        <input type="hidden" name="video_substitution" value="{{ $params['video_substitution'] }}">
                        <!--Preview-->
                        @if ($params['video_substitution'])
                            <button class="btn btn-outline-secondary preview-image" type="button" data-url="{{ $configImageInfo['videoConfigUrl'] }}">{{ __('FsLang::panel.button_view') }}</button>
                        @endif
                    </div>
                </div>

                <!--audio_substitution-->
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
                        <ul class="dropdown-menu selectInputType">
                            <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                            <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        </ul>
                        <!--Input-->
                        <input type="file" class="form-control inputFile" name="audio_substitution_file" @if ($configImageInfo['audioConfigType'] == 'URL') style="display:none;" @endif>
                        <input type="url" class="form-control inputUrl" name="audio_substitution_url" @if ($configImageInfo['audioConfigType'] == 'ID') style="display:none;" @endif  @if ($configImageInfo['audioConfigType'] == 'URL') value="{{ $params['audio_substitution'] }}" @endif>
                        <!--Hidden item-->
                        <input type="hidden" name="audio_substitution" value="{{ $params['audio_substitution'] }}">
                        <!--Preview-->
                        @if ($params['audio_substitution'])
                            <button class="btn btn-outline-secondary preview-image" type="button" data-url="{{ $configImageInfo['audioConfigUrl'] }}">{{ __('FsLang::panel.button_view') }}</button>
                        @endif
                    </div>
                </div>

                <!--document_substitution-->
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
                        <ul class="dropdown-menu selectInputType">
                            <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                            <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        </ul>
                        <!--Input-->
                        <input type="file" class="form-control inputFile" name="document_substitution_file" @if ($configImageInfo['documentConfigType'] == 'URL') style="display:none;" @endif>
                        <input type="url" class="form-control inputUrl" name="document_substitution_url" @if ($configImageInfo['documentConfigType'] == 'ID') style="display:none;" @endif  @if ($configImageInfo['documentConfigType'] == 'URL') value="{{ $params['document_substitution'] }}" @endif>
                        <!--Hidden item-->
                        <input type="hidden" name="document_substitution" value="{{ $params['document_substitution'] }}">
                        <!--Preview-->
                        @if ($params['document_substitution'])
                            <button class="btn btn-outline-secondary preview-image" type="button" data-url="{{ $configImageInfo['documentConfigUrl'] }}">{{ __('FsLang::panel.button_view') }}</button>
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
