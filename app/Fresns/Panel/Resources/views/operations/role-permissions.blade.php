@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::operations.sidebar')
@endsection

@section('content')
    <div class="row mb-4 border-bottom">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('panel.dashboard') }}">{{ __('FsLang::panel.menu_dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('panel.rename.index') }}">{{ __('FsLang::panel.menu_operations') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('panel.roles.index') }}">{{ __('FsLang::panel.sidebar_roles') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('FsLang::panel.button_config_permission') }}<span class="badge bg-secondary ms-2">{{ $role->getLangName($defaultLanguage) }}</span></li>
            </ol>
        </nav>
    </div>
    <!--form-->
    <form action="{{ route('panel.roles.permissions.update', $role->id) }}" id="rolePermissions" method="post">
        @csrf
        @method('put')
        <!--role_perm_basic_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.role_perm_basic_config') }}:</label>
            <div class="col-lg-6">
                <!--role_perm_content_view-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_content_view') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" {{ $permissions['content_view']['permValue'] ?? '' ? 'checked' : '' }} name="permissions[content_view]" id="content.view.1" value="1">
                            <label class="form-check-label" for="content.view.1">{{ __('FsLang::panel.option_yes') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" {{ !($permissions['content_view']['permValue'] ?? '') ? 'checked' : '' }} name="permissions[content_view]" id="content.view.0" value="0">
                            <label class="form-check-label" for="content.view.0">{{ __('FsLang::panel.option_no') }}</label>
                        </div>
                    </div>
                </div>
                <!--role_perm_conversation-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_conversation') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" {{ $permissions['conversation']['permValue'] ?? '' ? 'checked' : '' }} name="permissions[conversation]" id="conversation.1" value="1">
                            <label class="form-check-label" for="conversation.1">{{ __('FsLang::panel.option_yes') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" {{ !($permissions['conversation']['permValue'] ?? '') ? 'checked' : '' }} name="permissions[conversation]" id="conversation.0" value="0">
                            <label class="form-check-label" for="conversation.0">{{ __('FsLang::panel.option_no') }}</label>
                        </div>
                    </div>
                </div>
                <!--role_perm_content_link_handle-->
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_content_link_handle') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" {{ ($permissions['content_link_handle']['permValue'] ?? '') == 1 ? 'checked' : '' }} name="permissions[content_link_handle]" id="content_link_handle.1" value="1">
                            <label class="form-check-label" for="content_link_handle.1">{{ __('FsLang::panel.role_perm_content_link_handle_1') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" {{ ($permissions['content_link_handle']['permValue'] ?? '') == 2 ? 'checked' : '' }} name="permissions[content_link_handle]" id="content_link_handle.2" value="2">
                            <label class="form-check-label" for="content_link_handle.2">{{ __('FsLang::panel.role_perm_content_link_handle_2') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" {{ ($permissions['content_link_handle']['permValue'] ?? '') == 3 ? 'checked' : '' }} name="permissions[content_link_handle]" id="content_link_handle.3" value="3">
                            <label class="form-check-label" for="content_link_handle.3">{{ __('FsLang::panel.role_perm_content_link_handle_3') }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--role_perm_post_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.role_perm_post_config') }}:</label>
            <div class="col-lg-6">
                <!--role_perm_post_publish-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_post_publish') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" {{ $permissions['post_publish']['permValue'] ?? '' ? 'checked' : '' }} name="permissions[post_publish]" id="publish.post.1" value="1">
                            <label class="form-check-label" for="publish.post.1">{{ __('FsLang::panel.option_yes') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" {{ !($permissions['post_publish']['permValue'] ?? '') ? 'checked' : '' }} name="permissions[post_publish]" id="publish.post.0" value="0">
                            <label class="form-check-label" for="publish.post.0">{{ __('FsLang::panel.option_no') }}</label>
                        </div>
                    </div>
                </div>
                <!--role_perm_post_verify-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_post_verify') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" {{ $permissions['post_email_verify']['permValue'] ?? '' ? 'checked' : '' }} name="permissions[post_email_verify]" id="publish.post.verify.email" value="1">
                            <label class="form-check-label" for="publish.post.verify.email">{{ __('FsLang::panel.permission_option_email') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" {{ $permissions['post_phone_verify']['permValue'] ?? '' ? 'checked' : '' }} name="permissions[post_phone_verify]" id="publish.post.verify.phone" value="1">
                            <label class="form-check-label" for="publish.post.verify.phone">{{ __('FsLang::panel.permission_option_phone') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" {{ $permissions['post_real_name_verify']['permValue'] ?? '' ? 'checked' : '' }} name="permissions[post_real_name_verify]" id="publish.post.verify.prove" value="1">
                            <label class="form-check-label" for="publish.post.verify.prove">{{ __('FsLang::panel.permission_option_prove') }}</label>
                        </div>
                    </div>
                </div>
                <!--role_perm_post_review-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_post_review') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" {{ !($permissions['post_review']['permValue'] ?? false) ? 'checked' : '' }} name="permissions[post_review]" id="publish.post.review.0" value="0">
                            <label class="form-check-label" for="publish.post.review.0">{{ __('FsLang::panel.permission_option_direct_release') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" {{ $permissions['post_review']['permValue'] ?? false ? 'checked' : '' }} name="permissions[post_review]" id="publish.post.review.1" value="1">
                            <label class="form-check-label" for="publish.post.review.1">{{ __('FsLang::panel.permission_option_required_review') }}</label>
                        </div>
                    </div>
                </div>
                <!--role_perm_post_rules-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_post_rules') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio"
                                {{ !($permissions['post_limit_status']['permValue'] ?? '') ? 'checked' : '' }}
                                name="permissions[post_limit_status]" id="post.limit.status.0" value="0"
                                data-bs-toggle="collapse" data-bs-target="#post_limit_setting.show" aria-expanded="false"
                                aria-controls="post_limit_setting">
                            <label class="form-check-label" for="post.limit.status.0">{{ __('FsLang::panel.option_close') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio"
                                {{ $permissions['post_limit_status']['permValue'] ?? '' ? 'checked' : '' }}
                                name="permissions[post_limit_status]" id="post.limit.status.1" value="1"
                                data-bs-toggle="collapse" data-bs-target="#post_limit_setting:not(.show)"
                                aria-expanded="false" aria-controls="post_limit_setting">
                            <label class="form-check-label" for="post.limit.status.1">{{ __('FsLang::panel.option_open') }}</label>
                        </div>
                    </div>
                </div>
                <!--publish_rule-->
                <div class="collapse {{ $permissions['post_limit_status']['permValue'] ?? false ? 'show' : '' }}" id="post_limit_setting">
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_rule_type') }}</label>
                        <select class="form-select" id="post_limit_type" name="permissions[post_limit_type]">
                            <option value="1" id="post_datetime" {{ ($permissions['post_limit_type']['permValue'] ?? '') == 1 ? 'selected' : '' }}>{{ __('FsLang::panel.permission_option_rule_datetime') }}</option>
                            <option value="2" id="post_time" {{ ($permissions['post_limit_type']['permValue'] ?? '') == 2 ? 'selected' : '' }}>{{ __('FsLang::panel.permission_option_rule_time') }}</option>
                        </select>
                    </div>
                    <div class="input-group mb-3 collapse {{ $permissions['post_limit_type']['permValue'] == 1 ? 'show' : '' }}" id="post_datetime_setting">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_rule_datetime') }}</label>
                        <input type="datetime-local" class="form-control" value="{{ $permissions['post_limit_period_start']['permValue'] ?? '' }}" name="permissions[post_limit_period_start]" placeholder="2022/01/01 22:00:00">
                        <input type="datetime-local" class="form-control" value="{{ $permissions['post_limit_period_end']['permValue'] ?? '' }}" name="permissions[post_limit_period_end]" placeholder="2022/01/05 09:00:00">
                    </div>
                    <div class="input-group mb-3 collapse {{ $permissions['post_limit_type']['permValue'] == 2 ? 'show' : '' }}" id="post_time_setting">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_rule_time') }}</label>
                        <input type="time" class="form-control" value="{{ $permissions['post_limit_cycle_start']['permValue'] ?? '' }}" name="permissions[post_limit_cycle_start]" placeholder="22:00:00">
                        <input type="time" class="form-control" value="{{ $permissions['post_limit_cycle_end']['permValue'] ?? '' }}" name="permissions[post_limit_cycle_end]" placeholder="09:00:00">
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_rule_timezone') }}</label>
                        <div class="form-control bg-white">
                            {{ $ruleTimezone }}
                            ({{ __('FsLang::panel.system_info_database_timezone') }})
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_rule_rule') }}</label>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" {{ $permissions['post_limit_rule']['permValue'] ?? '' ? 'checked' : '' }} name="permissions[post_limit_rule]" id="post.limit.rule.0" value="0">
                                <label class="form-check-label" for="post.limit.rule.0">{{ __('FsLang::panel.permission_option_review_publish') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" {{ !($permissions['post_limit_rule']['permValue'] ?? '') ? 'checked' : '' }}  name="permissions[post_limit_rule]" id="post.limit.rule.1" value="1">
                                <label class="form-check-label" for="post.limit.rule.1">{{ __('FsLang::panel.permission_option_close_publish') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <!--publish_rule end-->
                <!--post_second_interval-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_post_time_interval') }}</label>
                    <input type="number" class="form-control input-number" value="{{ $permissions['post_second_interval']['permValue'] ?? '' }}" name="permissions[post_second_interval]" placeholder="60">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_second') }}</span>
                </div>
                <!--post_draft_count-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_post_draft_count') }}</label>
                    <input type="number" class="form-control input-number" value="{{ $permissions['post_draft_count']['permValue'] ?? '' }}" name="permissions[post_draft_count]" placeholder="10">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                </div>
            </div>
        </div>

        <!--role_perm_comment_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.role_perm_comment_config') }}:</label>
            <div class="col-lg-6">
                <!--role_perm_comment_publish-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_comment_publish') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" {{ $permissions['comment_publish']['permValue'] ?? '' ? 'checked' : '' }} name="permissions[comment_publish]" id="publish.comment.1" value="1">
                            <label class="form-check-label" for="publish.comment.1">{{ __('FsLang::panel.option_yes') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" {{ !($permissions['comment_publish']['permValue'] ?? '') ? 'checked' : '' }} name="permissions[comment_publish]" id="publish.comment.0" value="0">
                            <label class="form-check-label" for="publish.comment.0">{{ __('FsLang::panel.option_no') }}</label>
                        </div>
                    </div>
                </div>
                <!--role_perm_comment_verify-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_comment_verify') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" {{ $permissions['comment_email_verify']['permValue'] ?? '' ? 'checked' : '' }} name="permissions[comment_email_verify]" id="publish.comment.verify.email" value="1">
                            <label class="form-check-label" for="publish.comment.verify.email">{{ __('FsLang::panel.permission_option_email') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" {{ $permissions['comment_phone_verify']['permValue'] ?? '' ? 'checked' : '' }} name="permissions[comment_phone_verify]" id="publish.comment.verify.phone" value="1">
                            <label class="form-check-label" for="publish.comment.verify.phone">{{ __('FsLang::panel.permission_option_phone') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" {{ $permissions['comment_real_name_verify']['permValue'] ?? '' ? 'checked' : '' }} name="permissions[comment_real_name_verify]" id="publish.comment.verify.prove" value="1">
                            <label class="form-check-label" for="publish.comment.verify.prove">{{ __('FsLang::panel.permission_option_prove') }}</label>
                        </div>
                    </div>
                </div>
                <!--role_perm_comment_review-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_comment_review') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" {{ !($permissions['comment_review']['permValue'] ?? '') ? 'checked' : '' }} name="permissions[comment_review]" id="publish.comment.review.0" value="0">
                            <label class="form-check-label" for="publish.comment.review.0">{{ __('FsLang::panel.permission_option_direct_release') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" {{ $permissions['comment_review']['permValue'] ?? '' ? 'checked' : '' }} name="permissions[comment_review]" id="publish.comment.review.1" value="1">
                            <label class="form-check-label" for="publish.comment.review.1">{{ __('FsLang::panel.permission_option_required_review') }}</label>
                        </div>
                    </div>
                </div>
                <!--role_perm_comment_rules-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_comment_rules') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio"
                                {{ !($permissions['comment_limit_status']['permValue'] ?? '') ? 'checked' : '' }}
                                name="permissions[comment_limit_status]" id="comment.limit.status.0" value="0"
                                data-bs-toggle="collapse" data-bs-target="#comment_limit_setting.show" aria-expanded="false"
                                aria-controls="comment_limit_setting" checked>
                            <label class="form-check-label" for="comment.limit.status.0">{{ __('FsLang::panel.option_close') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio"
                                {{ $permissions['comment_limit_status']['permValue'] ?? '' ? 'checked' : '' }}
                                name="permissions[comment_limit_status]" id="comment.limit.status.1" value="1"
                                data-bs-toggle="collapse" data-bs-target="#comment_limit_setting:not(.show)"
                                aria-expanded="false" aria-controls="comment_limit_setting">
                            <label class="form-check-label" for="comment.limit.status.1">{{ __('FsLang::panel.option_open') }}</label>
                        </div>
                    </div>
                </div>
                <!--publish_rule-->
                <div class="collapse  {{ $permissions['comment_limit_status']['permValue'] ?? false ? 'show' : '' }}" id="comment_limit_setting">
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_rule_type') }}</label>
                        <select class="form-select" id="comment_limit_type" name="permissions[comment_limit_type]">
                            <option value="1" id="comment_datetime" {{ ($permissions['comment_limit_type']['permValue'] ?? '') == 1 ? 'selected' : '' }}>{{ __('FsLang::panel.permission_option_rule_datetime') }}</option>
                            <option value="2" id="comment_time" {{ ($permissions['comment_limit_type']['permValue'] ?? '') == 2 ? 'selected' : '' }}>{{ __('FsLang::panel.permission_option_rule_time') }}</option>
                        </select>
                    </div>
                    <div class="input-group mb-3 collapse {{ ($permissions['comment_limit_type']['permValue'] ?? '') == 1 ? 'show' : '' }}" id="comment_datetime_setting">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_rule_datetime') }}</label>
                        <input type="datetime-local" class="form-control" value="{{ $permissions['comment_limit_period_start']['permValue'] ?? '' }}" name="permissions[comment_limit_period_start]" placeholder="2022/01/01 22:00:00">
                        <input type="datetime-local" class="form-control" value="{{ $permissions['comment_limit_period_end']['permValue'] ?? '' }}" name="permissions[comment_limit_period_end]" placeholder="2022/01/05 09:00:00">
                    </div>
                    <div class="input-group mb-3 collapse {{ ($permissions['comment_limit_type']['permValue'] ?? '') == 2 ? 'show' : '' }}" id="comment_time_setting">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_rule_time') }}</label>
                        <input type="time" class="form-control" value="{{ $permissions['comment_limit_cycle_start']['permValue'] ?? '' }}" name="permissions[comment_limit_cycle_start]" placeholder="22:00:00">
                        <input type="time" class="form-control" value="{{ $permissions['comment_limit_cycle_end']['permValue'] ?? '' }}" name="permissions[comment_limit_cycle_end]" placeholder="09:00:00">
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_rule_timezone') }}</label>
                        <div class="form-control bg-white">
                            {{ $ruleTimezone }}
                            ({{ __('FsLang::panel.system_info_database_timezone') }})
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.publish_rule_rule') }}</label>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" {{ $permissions['comment_limit_rule']['permValue'] ?? '' ? 'checked' : '' }} name="permissions[comment_limit_rule]" id="comment.limit.rule.0" value="0">
                                <label class="form-check-label" for="comment.limit.rule.0">{{ __('FsLang::panel.permission_option_review_publish') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" {{ !($permissions['comment_limit_rule']['permValue'] ?? '') ? 'checked' : '' }} name="permissions[comment_limit_rule]" id="comment.limit.rule.1" value="1">
                                <label class="form-check-label" for="comment.limit.rule.1">{{ __('FsLang::panel.permission_option_close_publish') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <!--publish_rule end-->
                <!--role_perm_comment_time_interval-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_comment_time_interval') }}</label>
                    <input type="number" class="form-control input-number" value="{{ $permissions['comment_second_interval']['permValue'] ?? '' }}" name="permissions[comment_second_interval]" placeholder="60">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_second') }}</span>
                </div>
                <!--role_perm_comment_draft_count-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_comment_draft_count') }}</label>
                    <input type="number" class="form-control input-number" value="{{ $permissions['comment_draft_count']['permValue'] ?? '' }}" name="permissions[comment_draft_count]" placeholder="10">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                </div>
            </div>
        </div>
        <!--role_perm_upload_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.role_perm_upload_config') }}:</label>
            <div class="col-lg-10">
                <!--image-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_upload_image') }}</label>
                    <div class="input-group-text">
                        <input class="form-check-input mt-0" type="checkbox" {{ $permissions['post_editor_image']['permValue'] ?? '' ? 'checked' : '' }} value="1" name="permissions[post_editor_image]" id="post_editor_image" value="0">
                        <label class="form-check-label ms-1" for="post_editor_image">{{ __('FsLang::panel.post') }}</label>
                    </div>
                    <div class="input-group-text">
                        <input class="form-check-input mt-0" type="checkbox" {{ $permissions['comment_editor_image']['permValue'] ?? '' ? 'checked' : '' }} value="1" name="permissions[comment_editor_image]" id="comment_editor_image" value="0">
                        <label class="form-check-label ms-1" for="comment_editor_image">{{ __('FsLang::panel.comment') }}</label>
                    </div>
                    <input type="number" class="form-control input-number" value="{{ $permissions['image_max_size']['permValue'] ?? '' }}" value="1" name="permissions[image_max_size]" placeholder="{{ __('FsLang::panel.storage_max_size') }}">
                    <span class="input-group-text">MB</span>
                </div>
                <!--video-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_upload_video') }}</label>
                    <div class="input-group-text">
                        <input class="form-check-input mt-0" type="checkbox" {{ $permissions['post_editor_video']['permValue'] ?? '' ? 'checked' : '' }} value="1" name="permissions[post_editor_video]" id="post_editor_video" value="0">
                        <label class="form-check-label ms-1" for="post_editor_video">{{ __('FsLang::panel.post') }}</label>
                    </div>
                    <div class="input-group-text">
                        <input class="form-check-input mt-0" type="checkbox" {{ $permissions['comment_editor_video']['permValue'] ?? '' ? 'checked' : '' }} value="1" name="permissions[comment_editor_video]" id="comment_editor_video" value="0">
                        <label class="form-check-label ms-1" for="comment_editor_video">{{ __('FsLang::panel.comment') }}</label>
                    </div>
                    <input type="number" class="form-control input-number" value="{{ $permissions['video_max_size']['permValue'] ?? '' }}" value="1" name="permissions[video_max_size]" placeholder="{{ __('FsLang::panel.storage_max_size') }}">
                    <span class="input-group-text">MB</span>
                    <input type="number" class="form-control input-number" value="{{ $permissions['video_max_time']['permValue'] ?? '' }}" value="1" name="permissions[video_max_time]" placeholder="{{ __('FsLang::panel.storage_max_time') }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_second') }}</span>
                </div>
                <!--audio-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_upload_audio') }}</label>
                    <div class="input-group-text">
                        <input class="form-check-input mt-0" type="checkbox" {{ $permissions['post_editor_audio']['permValue'] ?? '' ? 'checked' : '' }} value="1" name="permissions[post_editor_audio]" id="post_editor_audio" value="0">
                        <label class="form-check-label ms-1" for="post_editor_audio">{{ __('FsLang::panel.post') }}</label>
                    </div>
                    <div class="input-group-text">
                        <input class="form-check-input mt-0" type="checkbox" {{ $permissions['comment_editor_audio']['permValue'] ?? '' ? 'checked' : '' }} value="1" name="permissions[comment_editor_audio]" id="comment_editor_audio" value="0">
                        <label class="form-check-label ms-1" for="comment_editor_audio">{{ __('FsLang::panel.comment') }}</label>
                    </div>
                    <input type="number" class="form-control input-number" value="{{ $permissions['audio_max_size']['permValue'] ?? '' }}" value="1" name="permissions[audio_max_size]" placeholder="{{ __('FsLang::panel.storage_max_size') }}">
                    <span class="input-group-text">MB</span>
                    <input type="number" class="form-control input-number" value="{{ $permissions['audio_max_time']['permValue'] ?? '' }}" value="1" name="permissions[audio_max_time]" placeholder="{{ __('FsLang::panel.storage_max_time') }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_second') }}</span>
                </div>
                <!--document-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_upload_document') }}</label>
                    <div class="input-group-text">
                        <input class="form-check-input mt-0" type="checkbox" {{ $permissions['post_editor_document']['permValue'] ?? '' ? 'checked' : '' }} value="1" name="permissions[post_editor_document]" id="post_editor_document" value="0">
                        <label class="form-check-label ms-1" for="post_editor_document">{{ __('FsLang::panel.post') }}</label>
                    </div>
                    <div class="input-group-text">
                        <input class="form-check-input mt-0" type="checkbox" {{ $permissions['comment_editor_document']['permValue'] ?? '' ? 'checked' : '' }} value="1" name="permissions[comment_editor_document]" id="comment_editor_document" value="0">
                        <label class="form-check-label ms-1" for="comment_editor_document">{{ __('FsLang::panel.comment') }}</label>
                    </div>
                    <input type="number" class="form-control input-number" value="{{ $permissions['document_max_size']['permValue'] ?? '' }}" value="1" name="permissions[document_max_size]" placeholder="{{ __('FsLang::panel.storage_max_size') }}">
                    <span class="input-group-text">MB</span>
                </div>
                <!--upload image number-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.editor_upload_image_number') }}</label>
                    <span class="input-group-text">{{ __('FsLang::panel.post') }}</span>
                    <input type="number" class="form-control input-number" value="{{ $permissions['post_editor_image_upload_number']['permValue'] ?? '' }}" value="1" name="permissions[post_editor_image_upload_number]" placeholder="{{ __('FsLang::panel.editor_upload_image_number') }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                    <span class="vr mx-3"></span>
                    <span class="input-group-text">{{ __('FsLang::panel.comment') }}</span>
                    <input type="number" class="form-control input-number" value="{{ $permissions['comment_editor_image_upload_number']['permValue'] ?? '' }}" value="1" name="permissions[comment_editor_image_upload_number]" placeholder="{{ __('FsLang::panel.editor_upload_image_number') }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                </div>
                <!--upload video number-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.editor_upload_video_number') }}</label>
                    <span class="input-group-text">{{ __('FsLang::panel.post') }}</span>
                    <input type="number" class="form-control input-number" value="{{ $permissions['post_editor_video_upload_number']['permValue'] ?? '' }}" value="1" name="permissions[post_editor_video_upload_number]" placeholder="{{ __('FsLang::panel.editor_upload_video_number') }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                    <span class="vr mx-3"></span>
                    <span class="input-group-text">{{ __('FsLang::panel.comment') }}</span>
                    <input type="number" class="form-control input-number" value="{{ $permissions['comment_editor_video_upload_number']['permValue'] ?? '' }}" value="1" name="permissions[comment_editor_video_upload_number]" placeholder="{{ __('FsLang::panel.editor_upload_video_number') }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                </div>
                <!--upload audio number-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.editor_upload_audio_number') }}</label>
                    <span class="input-group-text">{{ __('FsLang::panel.post') }}</span>
                    <input type="number" class="form-control input-number" value="{{ $permissions['post_editor_audio_upload_number']['permValue'] ?? '' }}" value="1" name="permissions[post_editor_audio_upload_number]" placeholder="{{ __('FsLang::panel.editor_upload_audio_number') }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                    <span class="vr mx-3"></span>
                    <span class="input-group-text">{{ __('FsLang::panel.comment') }}</span>
                    <input type="number" class="form-control input-number" value="{{ $permissions['comment_editor_audio_upload_number']['permValue'] ?? '' }}" value="1" name="permissions[comment_editor_audio_upload_number]" placeholder="{{ __('FsLang::panel.editor_upload_audio_number') }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                </div>
                <!--upload document number-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.editor_upload_document_number') }}</label>
                    <span class="input-group-text">{{ __('FsLang::panel.post') }}</span>
                    <input type="number" class="form-control input-number" value="{{ $permissions['post_editor_document_upload_number']['permValue'] ?? '' }}" value="1" name="permissions[post_editor_document_upload_number]" placeholder="{{ __('FsLang::panel.editor_upload_document_number') }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                    <span class="vr mx-3"></span>
                    <span class="input-group-text">{{ __('FsLang::panel.comment') }}</span>
                    <input type="number" class="form-control input-number" value="{{ $permissions['comment_editor_document_upload_number']['permValue'] ?? '' }}" value="1" name="permissions[comment_editor_document_upload_number]" placeholder="{{ __('FsLang::panel.editor_upload_document_number') }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                </div>
                <div class="form-text"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.role_perm_upload_file_desc') }}</div>
            </div>
        </div>
        <!--role_perm_interaction_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.role_perm_interaction_config') }}:</label>
            <div class="col-lg-6">
                <!--role_perm_follow_user_max_count-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_follow_user_max_count') }}</label>
                    <input type="number" class="form-control input-number" value="{{ $permissions['follow_user_max_count']['permValue'] ?? '' }}" name="permissions[follow_user_max_count]" placeholder="500">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                </div>
                <!--role_perm_block_user_max_count-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_block_user_max_count') }}</label>
                    <input type="number" class="form-control input-number" value="{{ $permissions['block_user_max_count']['permValue'] ?? '' }}" name="permissions[block_user_max_count]" placeholder="500">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number') }}</span>
                </div>
                <!--role_perm_download_file_count-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.role_perm_download_file_count') }}</label>
                    <input type="number" class="form-control input-number" value="{{ $permissions['download_file_count']['permValue'] ?? '' }}" name="permissions[download_file_count]" placeholder="10">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_number_of_times') }}</span>
                </div>
            </div>
        </div>
        <!--role_perm_customize_config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.role_perm_customize_config') }}:</label>
            <div class="col-lg-10">
                <!--options-->
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap">
                        <thead>
                            <tr class="table-info">
                                <th scope="col">{{ __('FsLang::panel.role_perm_table_name') }}</th>
                                <th scope="col">{{ __('FsLang::panel.role_perm_table_value') }}</th>
                                <th scope="col" style="width:6rem;">{{ __('FsLang::panel.table_options') }}</th>
                            </tr>
                        </thead>
                        <tbody id="customPermBox">
                            @foreach ($customPermissions as $permission)
                                <tr>
                                    <td><input type="text" class="form-control" name="custom_permissions[permKey][]" value="{{ $permission['permKey'] ?? '' }}" readonly></td>
                                    <td><input type="text" class="form-control" name="custom_permissions[permValue][]" value="{{ $permission['permValue'] ?? '' }}" readonly></td>
                                    <td><button type="button" class="btn btn-link link-danger ms-1 fresns-link fs-7 delete-custom-perm">{{ __('FsLang::panel.button_delete') }}</button></td>
                                </tr>
                            @endforeach
                            <tr id="addCustomPermTr">
                                <td colspan="3" class="text-center">
                                    <button class="btn btn-outline-success btn-sm px-3" id="addCustomPerm" type="button">
                                        <i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add') }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!--options end-->
            </div>
        </div>

        <!--button_save-->
        <div class="row my-5">
            <div class="col-lg-2"></div>
            <div class="col-lg-5">
                <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
            </div>
        </div>
    </form>

    <template id="customPerm">
        <tr>
            <td><input type="text" class="form-control" required name="custom_permissions[permKey][]"></td>
            <td><input type="text" class="form-control" required name="custom_permissions[permValue][]"></td>
            <td><button type="button" class="btn btn-link link-danger ms-1 fresns-link fs-7 delete-custom-perm">{{ __('FsLang::panel.button_delete') }}</button></td>
        </tr>
    </template>
@endsection
