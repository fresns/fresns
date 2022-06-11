@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::operations.sidebar')
@endsection

@section('content')
    <!--publish header-->
    <div class="row mb-4">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_publish') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_publish_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
        <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link active" href="{{ route('panel.publish.post.index') }}">{{ __('FsLang::panel.sidebar_publish_tab_post') }}</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('panel.publish.comment.index') }}">{{ __('FsLang::panel.sidebar_publish_tab_comment') }}</a></li>
        </ul>
    </div>
    <!--publish config-->
    <form action="{{ route('panel.publish.post.update') }}" id="publishPost" method="post">
        @csrf
        @method('put')
        <!--publish_post_verify_config-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.publish_post_verify_config') }}:</label>
            <div class="col-lg-6 pt-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="post_email_verify" id="post_email_verify" value="true" {{ $params['post_email_verify'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="post_email_verify">{{ __('FsLang::panel.permission_option_email') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="post_phone_verify" id="post_phone_verify" value="true" {{ $params['post_phone_verify'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="post_phone_verify">{{ __('FsLang::panel.permission_option_phone') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="post_prove_verify" id="post_prove_verify" value="true" {{ $params['post_prove_verify'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="post_prove_verify">{{ __('FsLang::panel.permission_option_prove') }}</label>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.publish_verify_desc') }}</div>
        </div>
        <!--publish_post_rules_config-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.publish_post_rules_config') }}:</label>
            <div class="col-lg-6 pt-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="post_limit_status" id="post_limit_status_0" data-bs-toggle="collapse" data-bs-target="#post_limit_setting.show" aria-expanded="false" aria-controls="post_limit_setting" value="false" {{ !$params['post_limit_status'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="post_limit_status_0">{{ __('FsLang::panel.option_close') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="post_limit_status" id="post_limit_status_1" data-bs-toggle="collapse" data-bs-target="#post_limit_setting:not(.show)" aria-expanded="false" aria-controls="post_limit_setting" value="true" {{ $params['post_limit_status'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="post_limit_status_1">{{ __('FsLang::panel.option_open') }}</label>
                </div>
                <!--rules_config-->
                <div class="collapse mt-3 {{ $params['post_limit_status'] ? 'show' : '' }}" id="post_limit_setting">
                    <div class="input-group mb-3">
                        <label class="input-group-text fresns-label">{{ __('FsLang::panel.publish_rule_type') }}</label>
                        <select class="form-select" id="post_limit_type" name="post_limit_type">
                            <option value="1" {{ $params['post_limit_type'] == '1' ? 'selected' : '' }}>{{ __('FsLang::panel.permission_option_rule_datetime') }}</option>
                            <option value="2" {{ $params['post_limit_type'] == '2' ? 'selected' : '' }}>{{ __('FsLang::panel.permission_option_rule_time') }}</option>
                        </select>
                    </div>
                    <div class="input-group mb-3 collapse @if ($params['post_limit_type'] == '1') show @endif" id="post_datetime_setting">
                        <label class="input-group-text fresns-label">{{ __('FsLang::panel.publish_rule_datetime') }}</label>
                        <input type="datetime-local" name="post_limit_period_start" value="{{ $params['post_limit_period_start'] }}" class="form-control" placeholder="2022/01/01 22:00:00">
                        <input type="datetime-local" name="post_limit_period_end" value="{{ $params['post_limit_period_end'] }}" class="form-control" placeholder="2022/01/05 09:00:00">
                    </div>
                    <div class="input-group mb-3 collapse @if ($params['post_limit_type'] == '2') show @endif" id="post_time_setting" @if ($params['post_limit_type'] == '1')  @endif>
                        <label class="input-group-text fresns-label">{{ __('FsLang::panel.publish_rule_time') }}</label>
                        <input type="time" name="post_limit_cycle_start" value="{{ $params['post_limit_cycle_start'] }}" class="form-control" placeholder="22:30:00">
                        <input type="time" name="post_limit_cycle_end" value="{{ $params['post_limit_cycle_end'] }}" class="form-control" placeholder="08:30:00">
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text fresns-label">{{ __('FsLang::panel.publish_rule_rule') }}</label>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="post_limit_rule" id="post.limit.rule.0" value="1" {{ $params['post_limit_rule'] == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="post.limit.rule.0">{{ __('FsLang::panel.permission_option_review_publish') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="post_limit_rule" id="post.limit.rule.1" value="2" {{ $params['post_limit_rule'] == '2' ? 'checked' : '' }}>
                                <label class="form-check-label" for="post.limit.rule.1">{{ __('FsLang::panel.permission_option_close_publish') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text fresns-label">{{ __('FsLang::panel.publish_rule_tip') }}</label>
                        <button class="btn btn-outline-secondary text-start fresns-control name-button" type="button" data-bs-toggle="modal" data-bs-target="#langModal">{{ $languages->where('lang_tag', $defaultLanguage)->first()['lang_content'] ?? '' }}</button>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text fresns-label">{{ __('FsLang::panel.publish_rule_whitelist') }}</label>
                        <select class="form-select select2" name="post_limit_whitelist[]" multiple="multiple">
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @if ($params['post_limit_whitelist'] && is_array($params['post_limit_whitelist']) && in_array($role->id, $params['post_limit_whitelist'])) selected @endif>
                                    {{ $role->getLangName($defaultLanguage) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <!--rules_config end-->
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.publish_rules_desc') }}</div>
        </div>
        <!--publish_post_edit_config-->
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.publish_post_edit_config') }}:</label>
            <div class="col-lg-6 pt-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="post_edit" id="post_edit_status_0" data-bs-toggle="collapse" data-bs-target="#post_edit_setting.show" aria-expanded="false" aria-controls="post_edit_setting" value="false" {{ !$params['post_edit'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="post_edit_status_0">{{ __('FsLang::panel.permission_option_non_editable') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="post_edit" id="post_edit_status_1" data-bs-toggle="collapse" data-bs-target="#post_edit_setting:not(.show)" aria-expanded="true" aria-controls="post_edit_setting" value="true" {{ $params['post_edit'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="post_edit_status_1">{{ __('FsLang::panel.permission_option_editable') }}</label>
                </div>
                <!--post_edit_setting-->
                <div class="collapse mt-3 {{ $params['post_edit'] ? 'show' : '' }}" id="post_edit_setting">
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_edit_time_limit') }}</label>
                        <input type="number" name="post_edit_timelimit" value="{{ $params['post_edit_timelimit'] }}" class="form-control input-number" id="post_edit_timelimit" value="30">
                        <span class="input-group-text">{{ __('FsLang::panel.unit_within_minute') }}</span>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_edit_sticky_limit') }}</label>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="post_edit_sticky" id="post_edit_sticky_false" value="false" {{ !$params['post_edit_sticky'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="post_edit_sticky_false">{{ __('FsLang::panel.permission_option_non_editable') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="post_edit_sticky" id="post_edit_sticky_true" value="true" {{ $params['post_edit_sticky'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="post_edit_sticky_true">{{ __('FsLang::panel.permission_option_editable') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_edit_digest_limit') }}</label>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="post_edit_digest" id="post_edit_digest_false" value="false" {{ !$params['post_edit_digest'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="post_edit_digest_false">{{ __('FsLang::panel.permission_option_non_editable') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="post_edit_digest" id="post_edit_digest_true" value="true" {{ $params['post_edit_digest'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="post_edit_digest_true">{{ __('FsLang::panel.permission_option_editable') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <!--post_edit_setting end-->
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.publish_edit_desc') }}</div>
        </div>
        <!--publish_editor_config-->
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.publish_editor_config') }}:</label>
            <div class="col-lg-6">
                <select class="form-select" name="post_editor_service" id="post_editor">
                    <option value="">{{ __('FsLang::panel.option_default') }}</option>
                    @foreach ($plugins as $plugin)
                        <option value="{{ $plugin->unikey }}" @if ($plugin->unikey == $params['post_editor_service']) selected @endif>{{ $plugin->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.publish_editor_desc') }}</div>
        </div>
        <!--publish_editor_function_status-->
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.publish_editor_function_status') }}:</label>
            <div class="col-lg-10">
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" id="editor_group" value="true" name="post_editor_group" {{ $params['post_editor_group'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="editor_group">{{ __('FsLang::panel.editor_group') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" id="editor_title" value="true" name="post_editor_title" {{ $params['post_editor_title'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="editor_title">{{ __('FsLang::panel.editor_title') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" id="editor_sticker" value="true" name="post_editor_sticker" {{ $params['post_editor_sticker'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="editor_sticker">{{ __('FsLang::panel.editor_sticker') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" id="editor_image" value="true" name="post_editor_image" {{ $params['post_editor_image'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="editor_image">{{ __('FsLang::panel.editor_image') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" id="editor_video" value="true" name="post_editor_video" {{ $params['post_editor_video'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="editor_video">{{ __('FsLang::panel.editor_video') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" id="editor_audio" value="true" name="post_editor_audio" {{ $params['post_editor_audio'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="editor_audio">{{ __('FsLang::panel.editor_audio') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" id="editor_document" value="true" name="post_editor_document" {{ $params['post_editor_document'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="editor_document">{{ __('FsLang::panel.editor_document') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" id="editor_mention" value="true" name="post_editor_mention" {{ $params['post_editor_mention'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="editor_mention">{{ __('FsLang::panel.editor_mention') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" id="editor_hashtag" value="true" name="post_editor_hashtag" {{ $params['post_editor_hashtag'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="editor_hashtag">{{ __('FsLang::panel.editor_hashtag') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" id="editor_extend" value="true" name="post_editor_extend" {{ $params['post_editor_extend'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="editor_extend">{{ __('FsLang::panel.editor_extend') }}</label>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" id="editor_lbs" value="true" name="post_editor_location" {{ $params['post_editor_location'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="editor_lbs">{{ __('FsLang::panel.editor_location') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input" id="editor_anonymous" value="true" name="post_editor_anonymous" {{ $params['post_editor_anonymous'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="editor_anonymous">{{ __('FsLang::panel.editor_anonymous') }}</label>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <!--publish_editor_function_options-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.publish_editor_function_options') }}:</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.editor_upload_image_number') }}</label>
                    <input type="number" class="form-control input-number" id="post_editor_image_upload_number" name="post_editor_image_upload_number" value="{{ $params['post_editor_image_upload_number'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"></div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.editor_upload_video_number') }}</label>
                    <input type="number" class="form-control input-number" id="post_editor_video_upload_number" name="post_editor_video_upload_number" value="{{ $params['post_editor_video_upload_number'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"></div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.editor_upload_audio_number') }}</label>
                    <input type="number" class="form-control input-number" id="post_editor_audio_upload_number" name="post_editor_audio_upload_number" value="{{ $params['post_editor_audio_upload_number'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"></div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.editor_upload_document_number') }}</label>
                    <input type="number" class="form-control input-number" id="post_editor_document_upload_number" name="post_editor_document_upload_number" value="{{ $params['post_editor_document_upload_number'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"></div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.publish_editor_group_required') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="post_editor_group_required" id="post_editor_group_required_false" value="false" {{ !$params['post_editor_group_required'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="post_editor_group_required_false">{{ __('FsLang::panel.option_no') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="post_editor_group_required" id="post_editor_group_required_true" value="true" {{ $params['post_editor_group_required'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="post_editor_group_required_true">{{ __('FsLang::panel.option_yes') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.publish_editor_group_required_desc') }}</div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.publish_editor_title_input_box') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="post_editor_title_view" id="post_editor_title_view_false" value="2" {{ $params['post_editor_title_view'] == '2' ? 'checked' : '' }}>
                            <label class="form-check-label" for="post_editor_title_view_false">{{ __('FsLang::panel.permission_option_title_optional_display') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="post_editor_title_view" id="post_editor_title_view_true" value="1" {{ $params['post_editor_title_view'] == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="post_editor_title_view_true">{{ __('FsLang::panel.permission_option_title_direct_display') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.publish_editor_title_input_box_desc') }}</div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.publish_editor_title_required') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="post_editor_title_required" id="post_editor_title_required_false" value="false" {{ !$params['post_editor_title_required'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="post_editor_title_required_false">{{ __('FsLang::panel.option_no') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="post_editor_title_required" id="post_editor_title_required_true" value="true" {{ $params['post_editor_title_required'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="post_editor_title_required_true">{{ __('FsLang::panel.option_yes') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.publish_editor_title_required_desc') }}</div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.publish_editor_title_word_length') }}</label>
                    <input type="number" class="form-control input-number" id="post_editor_title_word_length" name="post_editor_title_word_length" value="{{ $params['post_editor_title_word_length'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_character') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.publish_editor_title_word_length_desc') }}</div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.publish_editor_post_word_length') }}</label>
                    <input type="number" class="form-control input-number" id="post_editor_word_length" name="post_editor_word_length" value="{{ $params['post_editor_word_length'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_character') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.publish_editor_post_word_length_desc') }}</div>
        </div>
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.publish_editor_post_brief_word_length') }}</label>
                    <input type="number" class="form-control input-number" id="post_editor_brief_length" name="post_editor_brief_length" value="{{ $params['post_editor_brief_length'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_character') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.publish_editor_post_brief_word_length_desc') }}</div>
        </div>
        <!--button_save-->
        <div class="row mt-5">
            <div class="col-lg-2"></div>
            <div class="col-lg-8">
                <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
            </div>
        </div>

        <!-- Language Modal -->
        <div class="modal fade update-name-modal" id="langModal" tabindex="-1" aria-labelledby="langModal" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('FsLang::panel.publish_rule_tip') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form>
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
                                                        <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.default_language') }}" data-bs-original-title="{{ __('FsLang::panel.default_language') }}" aria-label="{{ __('FsLang::panel.default_language') }}"></i>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $lang['langName'] }}
                                                    @if ($lang['areaName'])
                                                        {{ '('.$lang['areaName'].')' }}
                                                    @endif
                                                </td>
                                                <td><textarea class="form-control name-input" name="post_limit_tip[{{ $lang['langTag'] }}]" rows="3">{{ $languages->where('lang_tag', $lang['langTag'])->first()['lang_content'] ?? '' }}</textarea></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!--button_confirm-->
                            <div class="text-center">
                                <button type="button" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_confirm') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Language Modal End -->
    </form>
@endsection
