@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::operations.sidebar')
@endsection

@section('content')
    <!--header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_content') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_content_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                {{-- <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a> --}}
            </div>
        </div>
    </div>

    <!--config-->
    <form action="{{ route('panel.content.update') }}" method="post">
        @csrf
        @method('put')

        <!--config_name-->
        <div class="row mb-2">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.config_name') }}:</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text rename-label">{{ __('FsLang::panel.content_group_name') }}</label>
                    <button type="button" class="btn btn-outline-secondary text-start rename-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#configLangModal"
                        data-title="{{ __('FsLang::panel.content_group_name') }}"
                        data-description="{{ __('FsLang::panel.content_group_name_desc') }}"
                        data-action="{{ route('panel.update.languages', ['itemKey' => 'group_name']) }}"
                        data-languages="{{ json_encode($params['group_name']) }}">
                        {{ $defaultLangParams['group_name'] ?? '' }}
                    </button>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.content_group_name_desc') }}</div>
        </div>
        <div class="row mb-2">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text rename-label">{{ __('FsLang::panel.content_hashtag_name') }}</label>
                    <button type="button" class="btn btn-outline-secondary text-start rename-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#configLangModal"
                        data-title="{{ __('FsLang::panel.content_hashtag_name') }}"
                        data-description="{{ __('FsLang::panel.content_hashtag_name_desc') }}"
                        data-action="{{ route('panel.update.languages', ['itemKey' => 'hashtag_name']) }}"
                        data-languages="{{ json_encode($params['hashtag_name']) }}">
                        {{ $defaultLangParams['hashtag_name'] ?? '' }}
                    </button>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.content_hashtag_name_desc') }}</div>
        </div>
        <div class="row mb-2">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text rename-label">{{ __('FsLang::panel.content_post_name') }}</label>
                    <button type="button" class="btn btn-outline-secondary text-start rename-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#configLangModal"
                        data-title="{{ __('FsLang::panel.content_post_name') }}"
                        data-description="{{ __('FsLang::panel.content_post_name_desc') }}"
                        data-action="{{ route('panel.update.languages', ['itemKey' => 'post_name']) }}"
                        data-languages="{{ json_encode($params['post_name']) }}">
                        {{ $defaultLangParams['post_name'] ?? '' }}
                    </button>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.content_post_name_desc') }}</div>
        </div>
        <div class="row mb-2">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text rename-label">{{ __('FsLang::panel.content_comment_name') }}</label>
                    <button type="button" class="btn btn-outline-secondary text-start rename-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#configLangModal"
                        data-title="{{ __('FsLang::panel.content_comment_name') }}"
                        data-description="{{ __('FsLang::panel.content_comment_name_desc') }}"
                        data-action="{{ route('panel.update.languages', ['itemKey' => 'comment_name']) }}"
                        data-languages="{{ json_encode($params['comment_name']) }}">
                        {{ $defaultLangParams['comment_name'] ?? '' }}
                    </button>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.content_comment_name_desc') }}</div>
        </div>
        <div class="row mb-2">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text rename-label">{{ __('FsLang::panel.content_publish_post_name') }}</label>
                    <button type="button" class="btn btn-outline-secondary text-start rename-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#configLangModal"
                        data-title="{{ __('FsLang::panel.content_publish_post_name') }}"
                        data-description="{{ __('FsLang::panel.content_publish_post_name_desc') }}"
                        data-action="{{ route('panel.update.languages', ['itemKey' => 'publish_post_name']) }}"
                        data-languages="{{ json_encode($params['publish_post_name']) }}">
                        {{ $defaultLangParams['publish_post_name'] ?? '' }}
                    </button>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.content_publish_post_name_desc') }}</div>
        </div>
        <div class="row mb-5">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text rename-label">{{ __('FsLang::panel.content_publish_comment_name') }}</label>
                    <button type="button" class="btn btn-outline-secondary text-start rename-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#configLangModal"
                        data-title="{{ __('FsLang::panel.content_publish_comment_name') }}"
                        data-description="{{ __('FsLang::panel.content_publish_comment_name_desc') }}"
                        data-action="{{ route('panel.update.languages', ['itemKey' => 'publish_comment_name']) }}"
                        data-languages="{{ json_encode($params['publish_comment_name']) }}">
                        {{ $defaultLangParams['publish_comment_name'] ?? '' }}
                    </button>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.content_publish_comment_name_desc') }}</div>
        </div>

        <!-- config_interaction -->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.config_interaction') }}:</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.content_mention_status') }}</label>
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
        <div class="row mb-3">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.content_mention_number') }}</label>
                    <input type="number" class="form-control input-number" name="mention_number" value="{{ $params['mention_number'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {!! __('FsLang::panel.content_mention_number_desc') !!}</div>
        </div>
        <!-- hashtag -->
        <div class="row mb-3">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.content_hashtag_status') }}</label>
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
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.content_hashtag_format') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="hashtag_format" id="hashtag_format_1" value="1" {{ $params['hashtag_format'] == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="hashtag_format_1">{{ __('FsLang::panel.content_hashtag_format_1') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="hashtag_format" id="hashtag_format_2" value="2" {{ $params['hashtag_format'] == 2 ? 'checked' : '' }}>
                            <label class="form-check-label" for="hashtag_format_2">{{ __('FsLang::panel.content_hashtag_format_2') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {!! __('FsLang::panel.content_hashtag_format_desc') !!}</div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.content_hashtag_length') }}</label>
                    <input type="number" class="form-control input-number" name="hashtag_length" value="{{ $params['hashtag_length'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_character') }}</span>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.content_hashtag_number') }}</label>
                    <input type="number" class="form-control input-number" name="hashtag_number" value="{{ $params['hashtag_number'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {!! __('FsLang::panel.content_hashtag_number_desc') !!}</div>
        </div>
        <div class="row mb-5">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.content_hashtag_regexp') }}</label>
                    <button class="btn btn-outline-secondary" style="flex: 1 1 auto;" type="button" data-bs-toggle="modal" data-bs-target="#hashtagRegexpModal">{{ __('FsLang::panel.button_config') }}</button>
                </div>
            </div>
        </div>

        <!--content_nearby_length-->
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.content_nearby_length') }}:</label>
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
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.content_nearby_length_desc') }}</div>
        </div>

        <!--config_list-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.config_list') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-2">
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="view_posts_by_timelines" name="view_posts_by_timelines" value="true" class="form-check-input" {{ $params['view_posts_by_timelines'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="view_posts_by_timelines">{{ __('FsLang::panel.content_view_posts_by_timelines') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="view_comments_by_timelines" name="view_comments_by_timelines" value="true" class="form-check-input" {{ $params['view_comments_by_timelines'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="view_comments_by_timelines">{{ __('FsLang::panel.content_view_comments_by_timelines') }}</label>
                        </div>
                    </div>
                </div>
                <div class="input-group">
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="view_posts_by_nearby" name="view_posts_by_nearby" value="true" class="form-check-input" {{ $params['view_posts_by_nearby'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="view_posts_by_nearby">{{ __('FsLang::panel.content_view_posts_by_nearby') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="view_comments_by_nearby" name="view_comments_by_nearby" value="true" class="form-check-input" {{ $params['view_comments_by_nearby'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="view_comments_by_nearby">{{ __('FsLang::panel.content_view_comments_by_nearby') }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.content_post_brief_length') }}</label>
                    <input type="number" class="form-control input-number" id="post_brief_length" name="post_brief_length" value="{{ $params['post_brief_length'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_character') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.content_post_brief_length_desc') }}</div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.content_comment_brief_length') }}</label>
                    <input type="number" class="form-control input-number" id="comment_brief_length" name="comment_brief_length" value="{{ $params['comment_brief_length'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_character') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.content_comment_brief_length_desc') }}</div>
        </div>
        <div class="row mb-5">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.content_comment_visibility_rule') }}</label>
                    <input type="number" class="form-control input-number" name="comment_visibility_rule" value="{{ $params['comment_visibility_rule'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_day') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.content_comment_visibility_rule_desc') }}</div>
        </div>

        <!-- config_preview -->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.config_preview') }}:</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.content_preview_post_like_users') }}</label>
                    <select class="form-select" name="preview_post_like_users">
                        <option value="0" {{ $params['preview_post_like_users'] == 0 ? 'selected' : '' }}>{{ __('FsLang::panel.option_close') }}</option>
                        <option value="1" {{ $params['preview_post_like_users'] == 1 ? 'selected' : '' }}>1</option>
                        <option value="2" {{ $params['preview_post_like_users'] == 2 ? 'selected' : '' }}>2</option>
                        <option value="3" {{ $params['preview_post_like_users'] == 3 ? 'selected' : '' }}>3</option>
                        <option value="4" {{ $params['preview_post_like_users'] == 4 ? 'selected' : '' }}>4</option>
                        <option value="5" {{ $params['preview_post_like_users'] == 5 ? 'selected' : '' }}>5</option>
                        <option value="6" {{ $params['preview_post_like_users'] == 6 ? 'selected' : '' }}>6</option>
                        <option value="7" {{ $params['preview_post_like_users'] == 7 ? 'selected' : '' }}>7</option>
                        <option value="8" {{ $params['preview_post_like_users'] == 8 ? 'selected' : '' }}>8</option>
                        <option value="9" {{ $params['preview_post_like_users'] == 9 ? 'selected' : '' }}>9</option>
                        <option value="10" {{ $params['preview_post_like_users'] == 10 ? 'selected' : '' }}>10</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.content_preview_post_like_users_desc') }}</div>
        </div>
        <div class="row mb-1">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.content_preview_post_comments') }}</label>
                    <select class="form-select" name="preview_post_comments">
                        <option value="0" {{ $params['preview_post_comments'] == 0 ? 'selected' : '' }}>{{ __('FsLang::panel.option_close') }}</option>
                        <option value="1" {{ $params['preview_post_comments'] == 1 ? 'selected' : '' }}>1</option>
                        <option value="2" {{ $params['preview_post_comments'] == 2 ? 'selected' : '' }}>2</option>
                        <option value="3" {{ $params['preview_post_comments'] == 3 ? 'selected' : '' }}>3</option>
                        <option value="4" {{ $params['preview_post_comments'] == 4 ? 'selected' : '' }}>4</option>
                        <option value="5" {{ $params['preview_post_comments'] == 5 ? 'selected' : '' }}>5</option>
                    </select>
                    <select class="form-select" name="preview_post_comments_type">
                        <option disabled>{{ __('FsLang::panel.table_order') }}</option>
                        <option value="like" {{ $params['preview_post_comments_type'] == 'like' ? 'selected' : '' }}>Like Count</option>
                        <option value="comment" {{ $params['preview_post_comments_type'] == 'comment' ? 'selected' : '' }}>Comment Count</option>
                        <option value="oldest" {{ $params['preview_post_comments_type'] == 'oldest' ? 'selected' : '' }}>Oldest</option>
                        <option value="latest" {{ $params['preview_post_comments_type'] == 'latest' ? 'selected' : '' }}>Latest</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.content_preview_post_comments_desc') }}</div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.content_preview_post_comment_require') }}</label>
                    <input type="number" class="form-control input-number" name="preview_post_comments_threshold" value="{{ $params['preview_post_comments_threshold'] }}">
                    <span class="input-group-text">Like Count / Comment Count</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.content_preview_post_comment_require_desc') }}</div>
        </div>

        <div class="row mb-3">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.content_preview_comment_like_users') }}</label>
                    <select class="form-select" name="preview_comment_like_users">
                        <option value="0" {{ $params['preview_comment_like_users'] == 0 ? 'selected' : '' }}>{{ __('FsLang::panel.option_close') }}</option>
                        <option value="1" {{ $params['preview_comment_like_users'] == 1 ? 'selected' : '' }}>1</option>
                        <option value="2" {{ $params['preview_comment_like_users'] == 2 ? 'selected' : '' }}>2</option>
                        <option value="3" {{ $params['preview_comment_like_users'] == 3 ? 'selected' : '' }}>3</option>
                        <option value="4" {{ $params['preview_comment_like_users'] == 4 ? 'selected' : '' }}>4</option>
                        <option value="5" {{ $params['preview_comment_like_users'] == 5 ? 'selected' : '' }}>5</option>
                        <option value="6" {{ $params['preview_comment_like_users'] == 6 ? 'selected' : '' }}>6</option>
                        <option value="7" {{ $params['preview_comment_like_users'] == 7 ? 'selected' : '' }}>7</option>
                        <option value="8" {{ $params['preview_comment_like_users'] == 8 ? 'selected' : '' }}>8</option>
                        <option value="9" {{ $params['preview_comment_like_users'] == 9 ? 'selected' : '' }}>9</option>
                        <option value="10" {{ $params['preview_comment_like_users'] == 10 ? 'selected' : '' }}>10</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row mb-5">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.content_preview_comment_replies') }}</label>
                    <select class="form-select" name="preview_comment_replies">
                        <option value="0" {{ $params['preview_comment_replies'] == 0 ? 'selected' : '' }}>{{ __('FsLang::panel.option_close') }}</option>
                        <option value="1" {{ $params['preview_comment_replies'] == 1 ? 'selected' : '' }}>1</option>
                        <option value="2" {{ $params['preview_comment_replies'] == 2 ? 'selected' : '' }}>2</option>
                        <option value="3" {{ $params['preview_comment_replies'] == 3 ? 'selected' : '' }}>3</option>
                        <option value="4" {{ $params['preview_comment_replies'] == 4 ? 'selected' : '' }}>4</option>
                        <option value="5" {{ $params['preview_comment_replies'] == 5 ? 'selected' : '' }}>5</option>
                    </select>
                    <select class="form-select" name="preview_comment_replies_type">
                        <option disabled>{{ __('FsLang::panel.table_order') }}</option>
                        <option value="like" {{ $params['preview_comment_replies_type'] == 'like' ? 'selected' : '' }}>Like Count</option>
                        <option value="comment" {{ $params['preview_comment_replies_type'] == 'comment' ? 'selected' : '' }}>Comment Count</option>
                        <option value="oldest" {{ $params['preview_comment_replies_type'] == 'oldest' ? 'selected' : '' }}>Oldest</option>
                        <option value="latest" {{ $params['preview_comment_replies_type'] == 'latest' ? 'selected' : '' }}>Latest</option>
                    </select>
                </div>
            </div>
        </div>

        <!--content_post_edit_config-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.content_post_edit_config') }}:</label>
            <div class="col-lg-6 pt-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="post_edit" id="post_edit_status_0" data-bs-toggle="collapse" data-bs-target=".post_edit_setting.show" aria-expanded="false" aria-controls="post_edit_setting" value="false" {{ !$params['post_edit'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="post_edit_status_0">{{ __('FsLang::panel.permission_option_cannot_be_edited') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="post_edit" id="post_edit_status_1" data-bs-toggle="collapse" data-bs-target=".post_edit_setting:not(.show)" aria-expanded="true" aria-controls="post_edit_setting" value="true" {{ $params['post_edit'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="post_edit_status_1">{{ __('FsLang::panel.permission_option_can_be_edited') }}</label>
                </div>
                <!--post_edit_setting-->
                <div class="collapse post_edit_setting mt-3 {{ $params['post_edit'] ? 'show' : '' }}">
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_edit_time_limit') }}</label>
                        <input type="number" name="post_edit_time_limit" value="{{ $params['post_edit_time_limit'] }}" class="form-control input-number" id="post_edit_time_limit" value="30">
                        <span class="input-group-text">{{ __('FsLang::panel.unit_within_minute') }}</span>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_edit_sticky_limit') }}</label>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="post_edit_sticky_limit" id="post_edit_sticky_false" value="false" {{ !$params['post_edit_sticky_limit'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="post_edit_sticky_false">{{ __('FsLang::panel.permission_option_cannot_be_edited') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="post_edit_sticky_limit" id="post_edit_sticky_true" value="true" {{ $params['post_edit_sticky_limit'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="post_edit_sticky_true">{{ __('FsLang::panel.permission_option_can_be_edited') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_edit_digest_limit') }}</label>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="post_edit_digest_limit" id="post_edit_digest_false" value="false" {{ !$params['post_edit_digest_limit'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="post_edit_digest_false">{{ __('FsLang::panel.permission_option_cannot_be_edited') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="post_edit_digest_limit" id="post_edit_digest_true" value="true" {{ $params['post_edit_digest_limit'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="post_edit_digest_true">{{ __('FsLang::panel.permission_option_can_be_edited') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <!--post_edit_setting end-->
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.content_edit_desc') }}</div>
        </div>
        <!--content_post_delete_config-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.content_post_delete_config') }}:</label>
            <div class="col-lg-6 pt-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="post_delete" id="post_delete_status_0" data-bs-toggle="collapse" data-bs-target=".post_delete_setting.show" aria-expanded="false" aria-controls="post_delete_setting" value="false" {{ !$params['post_delete'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="post_delete_status_0">{{ __('FsLang::panel.permission_option_cannot_be_deleted') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="post_delete" id="post_delete_status_1" data-bs-toggle="collapse" data-bs-target=".post_delete_setting:not(.show)" aria-expanded="true" aria-controls="post_delete_setting" value="true" {{ $params['post_delete'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="post_delete_status_1">{{ __('FsLang::panel.permission_option_can_be_deleted') }}</label>
                </div>
                <!--post_delete_setting-->
                <div class="collapse post_delete_setting mt-3 {{ $params['post_delete'] ? 'show' : '' }}">
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_edit_sticky_limit') }}</label>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="post_delete_sticky_limit" id="post_delete_sticky_false" value="false" {{ !$params['post_delete_sticky_limit'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="post_delete_sticky_false">{{ __('FsLang::panel.permission_option_cannot_be_deleted') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="post_delete_sticky_limit" id="post_delete_sticky_true" value="true" {{ $params['post_delete_sticky_limit'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="post_delete_sticky_true">{{ __('FsLang::panel.permission_option_can_be_deleted') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_edit_digest_limit') }}</label>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="post_delete_digest_limit" id="post_delete_digest_false" value="false" {{ !$params['post_delete_digest_limit'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="post_delete_digest_false">{{ __('FsLang::panel.permission_option_cannot_be_deleted') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="post_delete_digest_limit" id="post_delete_digest_true" value="true" {{ $params['post_delete_digest_limit'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="post_delete_digest_true">{{ __('FsLang::panel.permission_option_can_be_deleted') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <!--post_edit_setting end-->
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.content_delete_desc') }}</div>
        </div>

        <!--content_comment_edit_config-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.content_comment_edit_config') }}:</label>
            <div class="col-lg-6 pt-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="comment_edit" id="comment_edit_status_0" value="false" data-bs-toggle="collapse" data-bs-target=".comment_edit_setting.show" aria-expanded="false" aria-controls="comment_edit_setting" {{ !$params['comment_edit'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="comment_edit_status_0">{{ __('FsLang::panel.permission_option_cannot_be_edited') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="comment_edit" id="comment_edit_status_1" value="true" data-bs-toggle="collapse" data-bs-target=".comment_edit_setting:not(.show)" aria-expanded="false" aria-controls="comment_edit_setting" {{ $params['comment_edit'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="comment_edit_status_1">{{ __('FsLang::panel.permission_option_can_be_edited') }}</label>
                </div>
                <!--comment_edit_setting-->
                <div class="collapse comment_edit_setting mt-3 {{ $params['comment_edit'] ? 'show' : '' }}">
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_edit_time_limit') }}</label>
                        <input type="number" class="form-control input-number" name="comment_edit_time_limit" value="{{ $params['comment_edit_time_limit'] }}" id="comment_edit_time_limit" value="30">
                        <span class="input-group-text">{{ __('FsLang::panel.unit_within_minute') }}</span>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_edit_sticky_limit') }}</label>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="comment_edit_sticky_limit" id="comment_edit_sticky_false" value="false" {{ !$params['comment_edit_sticky_limit'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="comment_edit_sticky_false">{{ __('FsLang::panel.permission_option_cannot_be_edited') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="comment_edit_sticky_limit" id="comment_edit_sticky_true" value="true" {{ $params['comment_edit_sticky_limit'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="comment_edit_sticky_true">{{ __('FsLang::panel.permission_option_can_be_edited') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_edit_digest_limit') }}</label>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="comment_edit_digest_limit" id="comment_edit_digest_false" value="false" {{ !$params['comment_edit_digest_limit'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="comment_edit_digest_false">{{ __('FsLang::panel.permission_option_cannot_be_edited') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="comment_edit_digest_limit" id="comment_edit_digest_true" value="true" {{ $params['comment_edit_digest_limit'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="comment_edit_digest_true">{{ __('FsLang::panel.permission_option_can_be_edited') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <!--comment_edit_setting end-->
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.content_edit_desc') }}</div>
        </div>
        <!--content_comment_delete_config-->
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.content_comment_delete_config') }}:</label>
            <div class="col-lg-6 pt-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="comment_delete" id="comment_delete_status_0" data-bs-toggle="collapse" data-bs-target=".comment_delete_setting.show" aria-expanded="false" aria-controls="comment_delete_setting" value="false" {{ !$params['comment_delete'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="comment_delete_status_0">{{ __('FsLang::panel.permission_option_cannot_be_deleted') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="comment_delete" id="comment_delete_status_1" data-bs-toggle="collapse" data-bs-target=".comment_delete_setting:not(.show)" aria-expanded="true" aria-controls="comment_delete_setting" value="true" {{ $params['comment_delete'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="comment_delete_status_1">{{ __('FsLang::panel.permission_option_can_be_deleted') }}</label>
                </div>
                <!--comment_delete_setting-->
                <div class="collapse comment_delete_setting mt-3 {{ $params['comment_delete'] ? 'show' : '' }}">
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_edit_sticky_limit') }}</label>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="comment_delete_sticky_limit" id="comment_delete_sticky_false" value="false" {{ !$params['comment_delete_sticky_limit'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="comment_delete_sticky_false">{{ __('FsLang::panel.permission_option_cannot_be_deleted') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="comment_delete_sticky_limit" id="comment_delete_sticky_true" value="true" {{ $params['comment_delete_sticky_limit'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="comment_delete_sticky_true">{{ __('FsLang::panel.permission_option_can_be_deleted') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_edit_digest_limit') }}</label>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="comment_delete_digest_limit" id="comment_delete_digest_false" value="false" {{ !$params['comment_delete_digest_limit'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="comment_delete_digest_false">{{ __('FsLang::panel.permission_option_cannot_be_deleted') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="comment_delete_digest_limit" id="comment_delete_digest_true" value="true" {{ $params['comment_delete_digest_limit'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="comment_delete_digest_true">{{ __('FsLang::panel.permission_option_can_be_deleted') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <!--comment_edit_setting end-->
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.content_delete_desc') }}</div>
        </div>

        <!--button_save-->
        <div class="row my-3">
            <div class="col-lg-2"></div>
            <div class="col-lg-8">
                <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
            </div>
        </div>
    </form>

    <!-- Language Modal (input) -->
    <div class="modal fade" id="configLangModal" tabindex="-1" aria-labelledby="configLangModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title lang-modal-title">{{ __('FsLang::panel.button_setting') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-text mb-3 lang-modal-description">
                        <i class="bi bi-info-circle"></i>
                        <span class="lang-modal-description-text"></span>
                    </div>
                    <form method="post">
                        @csrf
                        @method('put')
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-nowrap">
                                <thead>
                                    <tr class="table-info">
                                        <th scope="col" class="w-25">{{ __('FsLang::panel.table_lang_tag') }}</th>
                                        <th scope="col" class="w-25">{{ __('FsLang::panel.table_lang_name') }}</th>
                                        <th scope="col" class="w-50">{{ __('FsLang::panel.table_content') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($optionalLanguages as $lang)
                                        <tr>
                                            <td>
                                                {{ $lang['langTag'] }}
                                                @if ($lang['langTag'] == $defaultLanguage)
                                                    <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.default_language') }}"></i>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $lang['langName'] }}
                                                @if ($lang['areaName'])
                                                    {{ '('.$lang['areaName'].')' }}
                                                @endif
                                            </td>
                                            <td><input class="form-control" name="languages[{{ $lang['langTag'] }}]"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mb-3">
                            <button type="submit" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hashtag Regexp Modal -->
    <div class="modal fade" id="hashtagRegexpModal" tabindex="-1" aria-labelledby="hashtagRegexpModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">{{ __('FsLang::panel.content_hashtag_regexp') }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('panel.update.item', ['itemKey' => 'hashtag_regexp']) }}" method="post">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="itemType" value="object">

                        <div class="input-group">
                            <span class="input-group-text">{{ __('FsLang::panel.content_hashtag_format_1') }}</span>
                            <input type="text" class="form-control" name="itemValue[space]" value="{{ $params['hashtag_regexp']['space'] ?? '' }}">
                        </div>
                        <div class="form-text mb-3 ps-1">{{ __('FsLang::panel.option_default') }}: <code>/#[\p{L}\p{N}\p{M}]+[^\n\p{P}\s]/u</code></div>

                        <div class="input-group">
                            <span class="input-group-text">{{ __('FsLang::panel.content_hashtag_format_2') }}</span>
                            <input type="text" class="form-control" name="itemValue[hash]" value="{{ $params['hashtag_regexp']['hash'] ?? '' }}">
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
