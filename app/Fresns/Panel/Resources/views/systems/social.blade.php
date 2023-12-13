@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::systems.sidebar')
@endsection

@section('content')
    <!--header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_social') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_social_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                {{-- <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a> --}}
            </div>
        </div>
    </div>

    <!--config-->
    <form action="{{ route('panel.social.update') }}" method="post">
        @csrf
        @method('put')
        <!-- mention -->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.social_mention_status') }}:</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="mention_status" id="mention_status_true" value="true" {{ $params['mention_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="mention_status_true">{{ __('FsLang::panel.option_open') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="mention_status" id="mention_status_false" value="false" {{ ! $params['mention_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="mention_status_false">{{ __('FsLang::panel.option_close') }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- hashtag -->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.social_hashtag_status') }}</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="hashtag_status" id="hashtag_status_true" value="true" {{ $params['hashtag_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="hashtag_status_true">{{ __('FsLang::panel.option_open') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="hashtag_status" id="hashtag_status_false" value="false" {{ ! $params['hashtag_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="hashtag_status_false">{{ __('FsLang::panel.option_close') }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.social_hashtag_format') }}</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="hashtag_format" id="hashtag_format_1" value="1" {{ $params['hashtag_format'] == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="hashtag_format_1">{{ __('FsLang::panel.social_hashtag_format_1') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="hashtag_format" id="hashtag_format_2" value="2" {{ $params['hashtag_format'] == 2 ? 'checked' : '' }}>
                            <label class="form-check-label" for="hashtag_format_2">{{ __('FsLang::panel.social_hashtag_format_2') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {!! __('FsLang::panel.social_hashtag_format_desc') !!}</div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.social_hashtag_length') }}</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <input type="number" class="form-control input-number" name="hashtag_length" value="{{ $params['hashtag_length'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_character') }}</span>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.social_hashtag_regexp') }}</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <button class="btn btn-outline-secondary" style="flex: 1 1 auto;" type="button" data-bs-toggle="modal" data-bs-target="#hashtagRegexpModal">{{ __('FsLang::panel.button_config') }}</button>
                </div>
            </div>
        </div>

        <!--interaction_conversation_config-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.social_conversation_status') }}:</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="conversation_status" id="conversation_status_true" value="true" data-bs-toggle="collapse" data-bs-target=".conversation_setting:not(.show)" aria-expanded="false" aria-controls="conversation_setting" {{ $params['conversation_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="conversation_status_true">{{ __('FsLang::panel.option_open') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="conversation_status" id="conversation_status_false" value="false" data-bs-toggle="collapse" data-bs-target=".conversation_setting.show" aria-expanded="false" aria-controls="conversation_setting" {{ !$params['conversation_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="conversation_status_false">{{ __('FsLang::panel.option_close') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.social_conversation_status_desc') }}</div>
        </div>

        <div class="collapse conversation_setting {{ $params['conversation_status'] ? 'show' : '' }}">
            <div class="row mb-4">
                <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.social_conversation_files') }}</label>
                <div class="col-lg-6">
                    <div class="input-group mb-2">
                        <div class="input-group-text">
                            <input class="form-check-input" type="checkbox" id="conversation_file_image" name="conversation_files[]" value="image" {{ in_array('image', $params['conversation_files']) ? 'checked' : '' }}>
                            <label class="form-check-label ms-1" for="conversation_file_image">{{ __('FsLang::panel.editor_image') }}</label>
                        </div>
                        <select class="form-select" name="conversation_file_upload_type[image]">
                            <option disabled>{{ __('FsLang::panel.editor_upload_image_form') }}</option>
                            <option value="api" {{ ($params['conversation_file_upload_type']['image'] == 'api') ? 'selected' : '' }}>Fresns API</option>
                            <option value="page" {{ ($params['conversation_file_upload_type']['image'] == 'page') ? 'selected' : '' }}>Plugin Page</option>
                            <option value="sdk" {{ ($params['conversation_file_upload_type']['image'] == 'sdk') ? 'selected' : '' }}>S3 SDK</option>
                        </select>
                    </div>
                    <div class="input-group mb-2">
                        <div class="input-group-text">
                            <input class="form-check-input" type="checkbox" id="conversation_file_video" name="conversation_files[]" value="video" {{ in_array('video', $params['conversation_files']) ? 'checked' : '' }}>
                            <label class="form-check-label ms-1" for="conversation_file_video">{{ __('FsLang::panel.editor_video') }}</label>
                        </div>
                        <select class="form-select" name="conversation_file_upload_type[video]">
                            <option disabled>{{ __('FsLang::panel.editor_upload_video_form') }}</option>
                            <option value="api" {{ ($params['conversation_file_upload_type']['video'] == 'api') ? 'selected' : '' }}>Fresns API</option>
                            <option value="page" {{ ($params['conversation_file_upload_type']['video'] == 'page') ? 'selected' : '' }}>Plugin Page</option>
                            <option value="sdk" {{ ($params['conversation_file_upload_type']['video'] == 'sdk') ? 'selected' : '' }}>S3 SDK</option>
                        </select>
                    </div>
                    <div class="input-group mb-2">
                        <div class="input-group-text">
                            <input class="form-check-input" type="checkbox" id="conversation_file_audio" name="conversation_files[]" value="audio" {{ in_array('audio', $params['conversation_files']) ? 'checked' : '' }}>
                            <label class="form-check-label ms-1" for="conversation_file_audio">{{ __('FsLang::panel.editor_audio') }}</label>
                        </div>
                        <select class="form-select" name="conversation_file_upload_type[audio]">
                            <option disabled>{{ __('FsLang::panel.editor_upload_audio_form') }}</option>
                            <option value="api" {{ ($params['conversation_file_upload_type']['audio'] == 'api') ? 'selected' : '' }}>Fresns API</option>
                            <option value="page" {{ ($params['conversation_file_upload_type']['audio'] == 'page') ? 'selected' : '' }}>Plugin Page</option>
                            <option value="sdk" {{ ($params['conversation_file_upload_type']['audio'] == 'sdk') ? 'selected' : '' }}>S3 SDK</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <div class="input-group-text">
                            <input class="form-check-input" type="checkbox" id="conversation_file_document" name="conversation_files[]" value="document" {{ in_array('document', $params['conversation_files']) ? 'checked' : '' }}>
                            <label class="form-check-label ms-1" for="conversation_file_document">{{ __('FsLang::panel.editor_document') }}</label>
                        </div>
                        <select class="form-select" name="conversation_file_upload_type[document]">
                            <option disabled>{{ __('FsLang::panel.editor_upload_document_form') }}</option>
                            <option value="api" {{ ($params['conversation_file_upload_type']['document'] == 'api') ? 'selected' : '' }}>Fresns API</option>
                            <option value="page" {{ ($params['conversation_file_upload_type']['document'] == 'page') ? 'selected' : '' }}>Plugin Page</option>
                            <option value="sdk" {{ ($params['conversation_file_upload_type']['document'] == 'sdk') ? 'selected' : '' }}>S3 SDK</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.social_conversation_files_desc') }}</div>
            </div>
        </div>

        <!--social_view_config-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.social_view_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-2">
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="view_posts_by_timelines" name="view_posts_by_timelines" value="true" class="form-check-input" {{ $params['view_posts_by_timelines'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="view_posts_by_timelines">{{ __('FsLang::panel.social_view_posts_by_timelines') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="view_comments_by_timelines" name="view_comments_by_timelines" value="true" class="form-check-input" {{ $params['view_comments_by_timelines'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="view_comments_by_timelines">{{ __('FsLang::panel.social_view_comments_by_timelines') }}</label>
                        </div>
                    </div>
                </div>
                <div class="input-group">
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="view_posts_by_nearby" name="view_posts_by_nearby" value="true" class="form-check-input" {{ $params['view_posts_by_nearby'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="view_posts_by_nearby">{{ __('FsLang::panel.social_view_posts_by_nearby') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="view_comments_by_nearby" name="view_comments_by_nearby" value="true" class="form-check-input" {{ $params['view_comments_by_nearby'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="view_comments_by_nearby">{{ __('FsLang::panel.social_view_comments_by_nearby') }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--social_nearby_length-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.social_nearby_length') }}</label>
            <div class="col-lg-6">
                <div class="input-group mb-2">
                    <input type="number" class="form-control input-number" name="nearby_length_km" value="{{ $params['nearby_length_km'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_kilometer') }}</span>
                </div>
                <div class="input-group">
                    <input type="number" class="form-control input-number" name="nearby_length_mi" value="{{ $params['nearby_length_mi'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_mile') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.social_nearby_length_desc') }}</div>
        </div>

        <!--button_save-->
        <div class="row my-3">
            <div class="col-lg-2"></div>
            <div class="col-lg-8">
                <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
            </div>
        </div>
    </form>

    <!-- Hashtag Regexp Modal -->
    <div class="modal fade" id="hashtagRegexpModal" tabindex="-1" aria-labelledby="hashtagRegexpModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">{{ __('FsLang::panel.social_hashtag_regexp') }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('panel.social.update.hashtag-regexp') }}" method="post">
                        @csrf
                        @method('patch')

                        <div class="input-group">
                            <span class="input-group-text">{{ __('FsLang::panel.social_hashtag_format_1') }}</span>
                            <input type="text" class="form-control" name="hashtagRegexp[space]" value="{{ $params['hashtag_regexp']['space'] ?? '' }}">
                        </div>
                        <div class="form-text mb-3 ps-1">{{ __('FsLang::panel.option_default') }}: <code>/#[\p{L}\p{N}\p{M}]+[^\n\p{P}\s]/u</code></div>

                        <div class="input-group">
                            <span class="input-group-text">{{ __('FsLang::panel.social_hashtag_format_2') }}</span>
                            <input type="text" class="form-control" name="hashtagRegexp[hash]" value="{{ $params['hashtag_regexp']['hash'] ?? '' }}">
                        </div>
                        <div class="form-text mb-3 ps-1">{{ __('FsLang::panel.option_default') }}: <code>/#[\p{L}\p{N}\p{M}]+[^\n\p{P}]#/u</code></div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
