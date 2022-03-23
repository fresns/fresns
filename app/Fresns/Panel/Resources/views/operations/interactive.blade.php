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
                    <input type="number" class="form-control input-number" name="comment_preview" value="{{ $params['comment_preview'] }}" min="0" max="3">
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.interactive_comment_preview_desc') }}</div>
        </div>
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.interactive_nearby_length') }}</label>
                    <input type="number" class="form-control input-number" name="nearby_length" value="{{ $params['nearby_length'] }}">
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
                                <input class="form-check-input" type="checkbox" id="dialog_file_image" name="dialog_files[]" value="1" {{ in_array(1, explode(',', $params['dialog_files'] ?? '')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="dialog_file_image">{{ __('FsLang::panel.editor_image') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="dialog_file_video" name="dialog_files[]" value="2" {{ in_array(2, explode(',', $params['dialog_files'] ?? '')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="dialog_file_video">{{ __('FsLang::panel.editor_video') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="dialog_file_audio" name="dialog_files[]" value="3" {{ in_array(3, explode(',', $params['dialog_files'] ?? '')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="dialog_file_audio">{{ __('FsLang::panel.editor_audio') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="dialog_file_document" name="dialog_files[]" value="4" {{ in_array(4, explode(',', $params['dialog_files'] ?? '')) ? 'checked' : '' }}>
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
                            <input type="checkbox" id="tac-1" name="it_publish_posts" value="true" class="form-check-input" {{ $params['it_publish_posts'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-1">{{ __('FsLang::panel.interactive_it_publish_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-2" name="it_publish_comments" value="true" class="form-check-input" {{ $params['it_publish_comments'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-2">{{ __('FsLang::panel.interactive_it_publish_comments') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-111" name="it_likers" value="true" class="form-check-input" {{ $params['it_likers'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-111">{{ __('FsLang::panel.interactive_it_likers') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-112" name="it_followers" value="true" class="form-check-input" {{ $params['it_followers'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-112">{{ __('FsLang::panel.interactive_it_followers') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-113" name="it_blockers" value="true" class="form-check-input" {{ $params['it_blockers'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-113">{{ __('FsLang::panel.interactive_it_blockers') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-3" name="it_like_users" value="true" class="form-check-input" {{ $params['it_like_users'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-3">{{ __('FsLang::panel.interactive_it_like_users') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-4" name="it_like_groups" value="true" class="form-check-input" {{ $params['it_like_groups'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-4">{{ __('FsLang::panel.interactive_it_like_groups') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-5" name="it_like_hashtags" value="true" class="form-check-input" {{ $params['it_like_hashtags'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-5">{{ __('FsLang::panel.interactive_it_like_hashtags') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-6" name="it_like_posts" value="true" class="form-check-input" {{ $params['it_like_posts'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-6">{{ __('FsLang::panel.interactive_it_like_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-7" name="it_like_comments" value="true" class="form-check-input" {{ $params['it_like_comments'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-7">{{ __('FsLang::panel.interactive_it_like_comments') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-8" name="it_follow_users" value="true" class="form-check-input" {{ $params['it_follow_users'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-8">{{ __('FsLang::panel.interactive_it_follow_users') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-9" name="it_follow_groups" value="true" class="form-check-input" {{ $params['it_follow_groups'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-9">{{ __('FsLang::panel.interactive_it_follow_groups') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-10" name="it_follow_hashtags" value="true" class="form-check-input" {{ $params['it_follow_hashtags'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-10">{{ __('FsLang::panel.interactive_it_follow_hashtags') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-11" name="it_follow_posts" value="true" class="form-check-input" {{ $params['it_follow_posts'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-11">{{ __('FsLang::panel.interactive_it_follow_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-12" name="it_follow_comments" value="true" class="form-check-input" {{ $params['it_follow_comments'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-12">{{ __('FsLang::panel.interactive_it_follow_comments') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-13" name="it_block_users" value="true" class="form-check-input" {{ $params['it_block_users'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-13">{{ __('FsLang::panel.interactive_it_block_users') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-14" name="it_block_groups" value="true" class="form-check-input" {{ $params['it_block_groups'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-14">{{ __('FsLang::panel.interactive_it_block_groups') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-15" name="it_block_hashtags" value="true" class="form-check-input" {{ $params['it_block_hashtags'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-15">{{ __('FsLang::panel.interactive_it_block_hashtags') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-16" name="it_block_posts" value="true" class="form-check-input" {{ $params['it_block_posts'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-16">{{ __('FsLang::panel.interactive_it_block_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" id="tac-17" name="it_block_comments" value="true" class="form-check-input" {{ $params['it_block_comments'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="tac-17">{{ __('FsLang::panel.interactive_it_block_comments') }}</label>
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
                            <input type="radio" id="ta-1" name="it_home_list" value="it_publish_posts" class="form-check-input" {{ $params['it_home_list'] == 'it_publish_posts' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-1">{{ __('FsLang::panel.interactive_it_publish_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-2" name="it_home_list" value="it_publish_comments" class="form-check-input" {{ $params['it_home_list'] == 'it_publish_comments' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-2">{{ __('FsLang::panel.interactive_it_publish_comments') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-111" name="it_home_list" value="it_likers" class="form-check-input" {{ $params['it_home_list'] == 'it_likers' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-111">{{ __('FsLang::panel.interactive_it_likers') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-112" name="it_home_list" value="it_followers" class="form-check-input" {{ $params['it_home_list'] == 'it_followers' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-112">{{ __('FsLang::panel.interactive_it_followers') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-113" name="it_home_list" value="it_blockers" class="form-check-input" {{ $params['it_home_list'] == 'it_blockers' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-113">{{ __('FsLang::panel.interactive_it_blockers') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-3" name="it_home_list" value="it_like_users" class="form-check-input" {{ $params['it_home_list'] == 'it_like_users' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-3">{{ __('FsLang::panel.interactive_it_like_users') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-4" name="it_home_list" value="it_like_groups" class="form-check-input" {{ $params['it_home_list'] == 'it_like_groups' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-4">{{ __('FsLang::panel.interactive_it_like_groups') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-5" name="it_home_list" value="it_like_hashtags" class="form-check-input" {{ $params['it_home_list'] == 'it_like_hashtags' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-5">{{ __('FsLang::panel.interactive_it_like_hashtags') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-6" name="it_home_list" value="it_like_posts" class="form-check-input" {{ $params['it_home_list'] == 'it_like_posts' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-6">{{ __('FsLang::panel.interactive_it_like_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-7" name="it_home_list" value="it_like_comments" class="form-check-input" {{ $params['it_home_list'] == 'it_like_comments' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-7">{{ __('FsLang::panel.interactive_it_like_comments') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-8" name="it_home_list" value="it_follow_users" class="form-check-input" {{ $params['it_home_list'] == 'it_follow_users' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-8">{{ __('FsLang::panel.interactive_it_follow_users') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-9" name="it_home_list" value="it_follow_groups" class="form-check-input" {{ $params['it_home_list'] == 'it_follow_groups' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-9">{{ __('FsLang::panel.interactive_it_follow_groups') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-10" name="it_home_list" value="it_follow_hashtags" class="form-check-input" {{ $params['it_home_list'] == 'it_follow_hashtags' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-10">{{ __('FsLang::panel.interactive_it_follow_hashtags') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-11" name="it_home_list" value="it_follow_posts" class="form-check-input" {{ $params['it_home_list'] == 'it_follow_posts' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-11">{{ __('FsLang::panel.interactive_it_follow_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-12" name="it_home_list" value="it_follow_comments" class="form-check-input" {{ $params['it_home_list'] == 'it_follow_comments' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-12">{{ __('FsLang::panel.interactive_it_follow_comments') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-13" name="it_home_list" value="it_block_users" class="form-check-input" {{ $params['it_home_list'] == 'it_block_users' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-13">{{ __('FsLang::panel.interactive_it_block_users') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-14" name="it_home_list" value="it_block_groups" class="form-check-input" {{ $params['it_home_list'] == 'it_block_groups' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-14">{{ __('FsLang::panel.interactive_it_block_groups') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-15" name="it_home_list" value="it_block_hashtags" class="form-check-input" {{ $params['it_home_list'] == 'it_block_hashtags' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-15">{{ __('FsLang::panel.interactive_it_block_hashtags') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-16" name="it_home_list" value="it_block_posts" class="form-check-input" {{ $params['it_home_list'] == 'it_block_posts' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-16">{{ __('FsLang::panel.interactive_it_block_posts') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" id="ta-17" name="it_home_list" value="it_block_comments" class="form-check-input" {{ $params['it_home_list'] == 'it_block_comments' ? 'checked' : '' }}>
                            <label class="form-check-label" for="ta-17">{{ __('FsLang::panel.interactive_it_block_comments') }}</label>
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
