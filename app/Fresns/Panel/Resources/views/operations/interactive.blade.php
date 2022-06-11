@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::operations.sidebar')
@endsection

@section('content')
    <!--interactive header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_interaction') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_interaction_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <!--interactive config-->
    <form action="{{ route('panel.interactive.update') }}" method="post">
        @csrf
        @method('put')
        <!--interactive_content_config-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.interactive_content_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.interactive_hashtag_show') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="hashtag_show" id="hashtag_show_1" value="1" {{ $params['hashtag_show'] == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="hashtag_show_1">{{ __('FsLang::panel.interactive_hashtag_show_1') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="hashtag_show" id="hashtag_show_2" value="2" {{ $params['hashtag_show'] == 2 ? 'checked' : '' }}>
                            <label class="form-check-label" for="hashtag_show_2">{{ __('FsLang::panel.interactive_hashtag_show_2') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {!! __('FsLang::panel.interactive_hashtag_show_desc') !!}</div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.interactive_post_hot') }}</label>
                    <input type="number" class="form-control input-number" name="post_hot" value="{{ $params['post_hot'] }}">
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.interactive_post_hot_desc') }}</div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.interactive_comment_preview') }}</label>
                    <select class="form-select" name="comment_preview">
                        <option value="0" {{ $params['comment_preview'] == 0 ? 'selected' : '' }}>{{ __('FsLang::panel.option_close') }}</option>
                        <option value="1" {{ $params['comment_preview'] == 1 ? 'selected' : '' }}>1</option>
                        <option value="2" {{ $params['comment_preview'] == 2 ? 'selected' : '' }}>2</option>
                        <option value="3" {{ $params['comment_preview'] == 3 ? 'selected' : '' }}>3</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.interactive_comment_preview_desc') }}</div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.interactive_nearby_length') }}</label>
                    <input type="number" class="form-control input-number" name="nearby_length_km" value="{{ $params['nearby_length_km'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_kilometer') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.interactive_nearby_length_desc') }}</div>
        </div>
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.interactive_nearby_length') }}</label>
                    <input type="number" class="form-control input-number" name="nearby_length_mi" value="{{ $params['nearby_length_mi'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_mile') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.interactive_nearby_length_desc') }}</div>
        </div>
        <!--interactive_dialog_config-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.interactive_dialog_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.interactive_dialog_status') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="dialog_status" id="dialog_status_true" value="true" data-bs-toggle="collapse" data-bs-target="#dialog_setting:not(.show)" aria-expanded="false" aria-controls="dialog_setting" {{ $params['dialog_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="dialog_status_true">{{ __('FsLang::panel.option_open') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="dialog_status" id="dialog_status_false" value="false" data-bs-toggle="collapse" data-bs-target="#dialog_setting.show" aria-expanded="false" aria-controls="dialog_setting" {{ !$params['dialog_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="dialog_status_false">{{ __('FsLang::panel.option_close') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.interactive_dialog_status_desc') }}</div>
        </div>
        <div class="collapse {{ $params['dialog_status'] ? 'show' : '' }}" id="dialog_setting">
            <div class="row">
                <label class="col-lg-2 col-form-label text-lg-end"></label>
                <div class="col-lg-6">
                    <div class="input-group">
                        <label class="input-group-text">{{ __('FsLang::panel.interactive_dialog_files') }}</label>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="dialog_file_image" name="dialog_files[]" value="1" {{ in_array(1, $params['dialog_files']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="dialog_file_image">{{ __('FsLang::panel.editor_image') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="dialog_file_video" name="dialog_files[]" value="2" {{ in_array(2, $params['dialog_files']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="dialog_file_video">{{ __('FsLang::panel.editor_video') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="dialog_file_audio" name="dialog_files[]" value="3" {{ in_array(3, $params['dialog_files']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="dialog_file_audio">{{ __('FsLang::panel.editor_audio') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="dialog_file_document" name="dialog_files[]" value="4" {{ in_array(4, $params['dialog_files']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="dialog_file_document">{{ __('FsLang::panel.editor_document') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.interactive_dialog_files_desc') }}</div>
            </div>
        </div>
        <!--interactive_function_config-->
        <div class="row mt-5">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.interactive_function_config') }}:</label>
            <div class="col-lg-10">
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="like_user" name="like_user_setting" value="true" class="form-check-input" {{ $params['like_user_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="like_user">{{ __('FsLang::panel.interactive_like_user') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="like_group" name="like_group_setting" value="true" class="form-check-input" {{ $params['like_group_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="like_group">{{ __('FsLang::panel.interactive_like_group') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="like_hashtag" name="like_hashtag_setting" value="true" class="form-check-input" {{ $params['like_hashtag_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="like_hashtag">{{ __('FsLang::panel.interactive_like_hashtag') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="like_post" name="like_post_setting" value="true" class="form-check-input" {{ $params['like_post_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="like_post">{{ __('FsLang::panel.interactive_like_post') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="like_comment" name="like_comment_setting" value="true" class="form-check-input" {{ $params['like_comment_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="like_comment">{{ __('FsLang::panel.interactive_like_comment') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="dislike_user" name="dislike_user_setting" value="true" class="form-check-input" {{ $params['dislike_user_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="dislike_user">{{ __('FsLang::panel.interactive_dislike_user') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="dislike_group" name="dislike_group_setting" value="true" class="form-check-input" {{ $params['dislike_group_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="dislike_group">{{ __('FsLang::panel.interactive_dislike_group') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="dislike_hashtag" name="dislike_hashtag_setting" value="true" class="form-check-input" {{ $params['dislike_hashtag_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="dislike_hashtag">{{ __('FsLang::panel.interactive_dislike_hashtag') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="dislike_post" name="dislike_post_setting" value="true" class="form-check-input" {{ $params['dislike_post_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="dislike_post">{{ __('FsLang::panel.interactive_dislike_post') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="dislike_comment" name="dislike_comment_setting" value="true" class="form-check-input" {{ $params['dislike_comment_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="dislike_comment">{{ __('FsLang::panel.interactive_dislike_comment') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="follow_user" name="follow_user_setting" value="true" class="form-check-input" {{ $params['follow_user_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="follow_user">{{ __('FsLang::panel.interactive_follow_user') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="follow_group" name="follow_group_setting" value="true" class="form-check-input" {{ $params['follow_group_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="follow_group">{{ __('FsLang::panel.interactive_follow_group') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="follow_hashtag" name="follow_hashtag_setting" value="true" class="form-check-input" {{ $params['follow_hashtag_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="follow_hashtag">{{ __('FsLang::panel.interactive_follow_hashtag') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="follow_post" name="follow_post_setting" value="true" class="form-check-input" {{ $params['follow_post_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="follow_post">{{ __('FsLang::panel.interactive_follow_post') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="follow_comment" name="follow_comment_setting" value="true" class="form-check-input" {{ $params['follow_comment_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="follow_comment">{{ __('FsLang::panel.interactive_follow_comment') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="block_user" name="block_user_setting" value="true" class="form-check-input" {{ $params['block_user_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="block_user">{{ __('FsLang::panel.interactive_block_user') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="block_group" name="block_group_setting" value="true" class="form-check-input" {{ $params['block_group_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="block_group">{{ __('FsLang::panel.interactive_block_group') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="block_hashtag" name="block_hashtag_setting" value="true" class="form-check-input" {{ $params['block_hashtag_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="block_hashtag">{{ __('FsLang::panel.interactive_block_hashtag') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="block_post" name="block_post_setting" value="true" class="form-check-input" {{ $params['block_post_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="block_post">{{ __('FsLang::panel.interactive_block_post') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="block_comment" name="block_comment_setting" value="true" class="form-check-input" {{ $params['block_comment_setting'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="block_comment">{{ __('FsLang::panel.interactive_block_comment') }}</label>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="col-lg-2"></div>
            <div class="col-lg-10 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.interactive_function_config_desc') }}</div>
        </div>
        <!--interactive_view_config-->
        <div class="row mt-5">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.interactive_view_config') }}:</label>
            <!--interactive_view_content-->
            <div class="col-lg-10 mb-3">
                <ul class="list-group">
                    <li class="list-group-item list-group-item-secondary">{{ __('FsLang::panel.interactive_view_content') }}</li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_posts" name="it_posts" value="true" class="form-check-input" {{ $params['it_posts'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_posts">{{ __('FsLang::panel.interactive_it_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_comments" name="it_comments" value="true" class="form-check-input" {{ $params['it_comments'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_comments">{{ __('FsLang::panel.interactive_it_comments') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_likers" name="it_likers" value="true" class="form-check-input" {{ $params['it_likers'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_likers">{{ __('FsLang::panel.interactive_it_likers') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_dislikers" name="it_dislikers" value="true" class="form-check-input" {{ $params['it_dislikers'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_dislikers">{{ __('FsLang::panel.interactive_it_dislikers') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_followers" name="it_followers" value="true" class="form-check-input" {{ $params['it_followers'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_followers">{{ __('FsLang::panel.interactive_it_followers') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_blockers" name="it_blockers" value="true" class="form-check-input" {{ $params['it_blockers'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_blockers">{{ __('FsLang::panel.interactive_it_blockers') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_like_users" name="it_like_users" value="true" class="form-check-input" {{ $params['it_like_users'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_like_users">{{ __('FsLang::panel.interactive_it_like_users') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_like_groups" name="it_like_groups" value="true" class="form-check-input" {{ $params['it_like_groups'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_like_groups">{{ __('FsLang::panel.interactive_it_like_groups') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_like_hashtags" name="it_like_hashtags" value="true" class="form-check-input" {{ $params['it_like_hashtags'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_like_hashtags">{{ __('FsLang::panel.interactive_it_like_hashtags') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_like_posts" name="it_like_posts" value="true" class="form-check-input" {{ $params['it_like_posts'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_like_posts">{{ __('FsLang::panel.interactive_it_like_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_like_comments" name="it_like_comments" value="true" class="form-check-input" {{ $params['it_like_comments'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_like_comments">{{ __('FsLang::panel.interactive_it_like_comments') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_dislike_users" name="it_dislike_users" value="true" class="form-check-input" {{ $params['it_dislike_users'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_dislike_users">{{ __('FsLang::panel.interactive_it_dislike_users') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_dislike_groups" name="it_dislike_groups" value="true" class="form-check-input" {{ $params['it_dislike_groups'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_dislike_groups">{{ __('FsLang::panel.interactive_it_dislike_groups') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_dislike_hashtags" name="it_dislike_hashtags" value="true" class="form-check-input" {{ $params['it_dislike_hashtags'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_dislike_hashtags">{{ __('FsLang::panel.interactive_it_dislike_hashtags') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_dislike_posts" name="it_dislike_posts" value="true" class="form-check-input" {{ $params['it_dislike_posts'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_dislike_posts">{{ __('FsLang::panel.interactive_it_dislike_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_dislike_comments" name="it_dislike_comments" value="true" class="form-check-input" {{ $params['it_dislike_comments'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_dislike_comments">{{ __('FsLang::panel.interactive_it_dislike_comments') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_follow_users" name="it_follow_users" value="true" class="form-check-input" {{ $params['it_follow_users'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_follow_users">{{ __('FsLang::panel.interactive_it_follow_users') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_follow_groups" name="it_follow_groups" value="true" class="form-check-input" {{ $params['it_follow_groups'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_follow_groups">{{ __('FsLang::panel.interactive_it_follow_groups') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_follow_hashtags" name="it_follow_hashtags" value="true" class="form-check-input" {{ $params['it_follow_hashtags'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_follow_hashtags">{{ __('FsLang::panel.interactive_it_follow_hashtags') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_follow_posts" name="it_follow_posts" value="true" class="form-check-input" {{ $params['it_follow_posts'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_follow_posts">{{ __('FsLang::panel.interactive_it_follow_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_follow_comments" name="it_follow_comments" value="true" class="form-check-input" {{ $params['it_follow_comments'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_follow_comments">{{ __('FsLang::panel.interactive_it_follow_comments') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_block_users" name="it_block_users" value="true" class="form-check-input" {{ $params['it_block_users'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_block_users">{{ __('FsLang::panel.interactive_it_block_users') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_block_groups" name="it_block_groups" value="true" class="form-check-input" {{ $params['it_block_groups'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_block_groups">{{ __('FsLang::panel.interactive_it_block_groups') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_block_hashtags" name="it_block_hashtags" value="true" class="form-check-input" {{ $params['it_block_hashtags'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_block_hashtags">{{ __('FsLang::panel.interactive_it_block_hashtags') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_block_posts" name="it_block_posts" value="true" class="form-check-input" {{ $params['it_block_posts'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_block_posts">{{ __('FsLang::panel.interactive_it_block_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="it_block_comments" name="it_block_comments" value="true" class="form-check-input" {{ $params['it_block_comments'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="it_block_comments">{{ __('FsLang::panel.interactive_it_block_comments') }}</label>
                        </div>
                    </li>
                </ul>
            </div>
            <label class="col-lg-2"></label>
            <!--interactive_user_profile-->
            <div class="col-lg-10 mb-3">
                <ul class="list-group">
                    <li class="list-group-item list-group-item-secondary">{{ __('FsLang::panel.interactive_user_profile') }}</li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_posts" name="it_home_list" value="it_posts" class="form-check-input" {{ $params['it_home_list'] == 'it_posts' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_posts">{{ __('FsLang::panel.interactive_it_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_comments" name="it_home_list" value="it_comments" class="form-check-input" {{ $params['it_home_list'] == 'it_comments' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_comments">{{ __('FsLang::panel.interactive_it_comments') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_likers" name="it_home_list" value="it_likers" class="form-check-input" {{ $params['it_home_list'] == 'it_likers' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_likers">{{ __('FsLang::panel.interactive_it_likers') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_dislikers" name="it_home_list" value="it_dislikers" class="form-check-input" {{ $params['it_home_list'] == 'it_dislikers' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_dislikers">{{ __('FsLang::panel.interactive_it_dislikers') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_followers" name="it_home_list" value="it_followers" class="form-check-input" {{ $params['it_home_list'] == 'it_followers' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_followers">{{ __('FsLang::panel.interactive_it_followers') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_blockers" name="it_home_list" value="it_blockers" class="form-check-input" {{ $params['it_home_list'] == 'it_blockers' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_blockers">{{ __('FsLang::panel.interactive_it_blockers') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_like_users" name="it_home_list" value="it_like_users" class="form-check-input" {{ $params['it_home_list'] == 'it_like_users' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_like_users">{{ __('FsLang::panel.interactive_it_like_users') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_like_groups" name="it_home_list" value="it_like_groups" class="form-check-input" {{ $params['it_home_list'] == 'it_like_groups' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_like_groups">{{ __('FsLang::panel.interactive_it_like_groups') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_like_hashtags" name="it_home_list" value="it_like_hashtags" class="form-check-input" {{ $params['it_home_list'] == 'it_like_hashtags' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_like_hashtags">{{ __('FsLang::panel.interactive_it_like_hashtags') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_like_posts" name="it_home_list" value="it_like_posts" class="form-check-input" {{ $params['it_home_list'] == 'it_like_posts' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_like_posts">{{ __('FsLang::panel.interactive_it_like_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_like_comments" name="it_home_list" value="it_like_comments" class="form-check-input" {{ $params['it_home_list'] == 'it_like_comments' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_like_comments">{{ __('FsLang::panel.interactive_it_like_comments') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_dislike_users" name="it_home_list" value="it_dislike_users" class="form-check-input" {{ $params['it_home_list'] == 'it_dislike_users' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_dislike_users">{{ __('FsLang::panel.interactive_it_dislike_users') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_dislike_groups" name="it_home_list" value="it_dislike_groups" class="form-check-input" {{ $params['it_home_list'] == 'it_dislike_groups' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_dislike_groups">{{ __('FsLang::panel.interactive_it_dislike_groups') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_dislike_hashtags" name="it_home_list" value="it_dislike_hashtags" class="form-check-input" {{ $params['it_home_list'] == 'it_dislike_hashtags' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_dislike_hashtags">{{ __('FsLang::panel.interactive_it_dislike_hashtags') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_dislike_posts" name="it_home_list" value="it_dislike_posts" class="form-check-input" {{ $params['it_home_list'] == 'it_dislike_posts' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_dislike_posts">{{ __('FsLang::panel.interactive_it_dislike_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_dislike_comments" name="it_home_list" value="it_dislike_comments" class="form-check-input" {{ $params['it_home_list'] == 'it_dislike_comments' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_dislike_comments">{{ __('FsLang::panel.interactive_it_dislike_comments') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_follow_users" name="it_home_list" value="it_follow_users" class="form-check-input" {{ $params['it_home_list'] == 'it_follow_users' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_follow_users">{{ __('FsLang::panel.interactive_it_follow_users') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_follow_groups" name="it_home_list" value="it_follow_groups" class="form-check-input" {{ $params['it_home_list'] == 'it_follow_groups' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_follow_groups">{{ __('FsLang::panel.interactive_it_follow_groups') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_follow_hashtags" name="it_home_list" value="it_follow_hashtags" class="form-check-input" {{ $params['it_home_list'] == 'it_follow_hashtags' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_follow_hashtags">{{ __('FsLang::panel.interactive_it_follow_hashtags') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_follow_posts" name="it_home_list" value="it_follow_posts" class="form-check-input" {{ $params['it_home_list'] == 'it_follow_posts' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_follow_posts">{{ __('FsLang::panel.interactive_it_follow_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_follow_comments" name="it_home_list" value="it_follow_comments" class="form-check-input" {{ $params['it_home_list'] == 'it_follow_comments' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_follow_comments">{{ __('FsLang::panel.interactive_it_follow_comments') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_block_users" name="it_home_list" value="it_block_users" class="form-check-input" {{ $params['it_home_list'] == 'it_block_users' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_block_users">{{ __('FsLang::panel.interactive_it_block_users') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_block_groups" name="it_home_list" value="it_block_groups" class="form-check-input" {{ $params['it_home_list'] == 'it_block_groups' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_block_groups">{{ __('FsLang::panel.interactive_it_block_groups') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_block_hashtags" name="it_home_list" value="it_block_hashtags" class="form-check-input" {{ $params['it_home_list'] == 'it_block_hashtags' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_block_hashtags">{{ __('FsLang::panel.interactive_it_block_hashtags') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_block_posts" name="it_home_list" value="it_block_posts" class="form-check-input" {{ $params['it_home_list'] == 'it_block_posts' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_block_posts">{{ __('FsLang::panel.interactive_it_block_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="home-it_block_comments" name="it_home_list" value="it_block_comments" class="form-check-input" {{ $params['it_home_list'] == 'it_block_comments' ? 'checked' : '' }}>
                            <label class="form-check-label" for="home-it_block_comments">{{ __('FsLang::panel.interactive_it_block_comments') }}</label>
                        </div>
                    </li>
                </ul>
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
