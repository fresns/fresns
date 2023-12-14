@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::operations.sidebar')
@endsection

@section('content')
    <!--header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_user') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_user_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                {{-- <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a> --}}
            </div>
        </div>
    </div>

    <!--config-->
    <form action="{{ route('panel.user.update') }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('put')
        <!--config_name-->
        <div class="row mb-2">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.config_name') }}:</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.user_name') }}</label>
                    <button class="btn btn-outline-secondary text-start rename-btn w-75" type="button" data-bs-toggle="modal"
                        data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'user_name']) }}"
                        data-languages="{{ json_encode($params['user_name']) }}"
                        data-bs-target="#configLangModal">{{ $defaultLangParams['user_name'] ?? '' }}
                    </button>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_name_desc') }}</div>
        </div>
        <div class="row mb-2">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.user_uid_name') }}</label>
                    <button class="btn btn-outline-secondary text-start rename-btn w-75" type="button" data-bs-toggle="modal"
                        data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'user_uid_name']) }}"
                        data-languages="{{ json_encode($params['user_uid_name']) }}"
                        data-bs-target="#configLangModal">{{ $defaultLangParams['user_uid_name'] ?? '' }}
                    </button>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_uid_name_desc') }}</div>
        </div>
        <div class="row mb-2">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.user_username_name') }}</label>
                    <button class="btn btn-outline-secondary text-start rename-btn w-75" type="button" data-bs-toggle="modal"
                        data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'user_username_name']) }}"
                        data-languages="{{ json_encode($params['user_username_name']) }}"
                        data-bs-target="#configLangModal">{{ $defaultLangParams['user_username_name'] ?? '' }}
                    </button>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_username_name_desc') }}</div>
        </div>
        <div class="row mb-2">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.user_nickname_name') }}</label>
                    <button class="btn btn-outline-secondary text-start rename-btn w-75" type="button" data-bs-toggle="modal"
                        data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'user_nickname_name']) }}"
                        data-languages="{{ json_encode($params['user_nickname_name']) }}"
                        data-bs-target="#configLangModal">{{ $defaultLangParams['user_nickname_name'] ?? '' }}
                    </button>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_nickname_name_desc') }}</div>
        </div>
        <div class="row mb-2">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.user_role_name') }}</label>
                    <button class="btn btn-outline-secondary text-start rename-btn w-75" type="button" data-bs-toggle="modal"
                        data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'user_role_name']) }}"
                        data-languages="{{ json_encode($params['user_role_name']) }}"
                        data-bs-target="#configLangModal">{{ $defaultLangParams['user_role_name'] ?? '' }}
                    </button>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_role_name_desc') }}</div>
        </div>
        <div class="row mb-5">
            <label class="col-lg-2"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text w-25">{{ __('FsLang::panel.user_bio_name') }}</label>
                    <button class="btn btn-outline-secondary text-start rename-btn w-75" type="button" data-bs-toggle="modal"
                        data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'user_bio_name']) }}"
                        data-languages="{{ json_encode($params['user_bio_name']) }}"
                        data-bs-target="#configLangModal">{{ $defaultLangParams['user_bio_name'] ?? '' }}
                    </button>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_bio_name_desc') }}</div>
        </div>

        <!--config_default-->
        <div class="row mb-2">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.config_default') }}:</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_default_role') }}</label>
                    <select class="form-select select2" name="default_role">
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ $params['default_role'] == $role->id ? 'selected' : '' }}>{{ $role->getLangContent('name', $defaultLanguage) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_default_role_desc') }}</div>
        </div>
        <div class="row mb-2">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_default_avatar') }}</label>
                    <!--Options-->
                    <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if ($configImageInfo['defaultAvatarType'] == 'ID')
                            {{ __('FsLang::panel.button_image_upload') }}
                        @else
                            {{ __('FsLang::panel.button_image_input') }}
                        @endif
                    </button>
                    <ul class="dropdown-menu selectInputType">
                        <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                    </ul>
                    <!--Input-->
                    <input type="file" class="form-control inputFile" name="default_avatar_file" @if ($configImageInfo['defaultAvatarType'] == 'URL') style="display:none;" @endif>
                    <input type="text" class="form-control inputUrl" name="default_avatar_url" @if ($configImageInfo['defaultAvatarType'] == 'ID') style="display:none;" @endif  @if ($configImageInfo['defaultAvatarType'] == 'URL') value="{{ $params['default_avatar'] }}" @endif>
                    <!--Hidden item-->
                    <input type="hidden" name="default_avatar" value="{{ $params['default_avatar'] }}">
                    <!--Preview-->
                    @if ($params['default_avatar'])
                        <button class="btn btn-outline-secondary preview-image" type="button" data-url="{{ $configImageInfo['defaultAvatarUrl'] }}">{{ __('FsLang::panel.button_view') }}</button>
                    @endif
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_default_avatar_desc') }}</div>
        </div>
        <div class="row mb-2">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_default_anonymous_avatar') }}</label>
                    <!--Options-->
                    <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if ($configImageInfo['anonymousAvatarType'] == 'ID')
                            {{ __('FsLang::panel.button_image_upload') }}
                        @else
                            {{ __('FsLang::panel.button_image_input') }}
                        @endif
                    </button>
                    <ul class="dropdown-menu selectInputType">
                        <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                    </ul>
                    <!--Input-->
                    <input type="file" class="form-control inputFile" name="anonymous_avatar_file" @if ($configImageInfo['anonymousAvatarType'] == 'URL') style="display:none;" @endif>
                    <input type="text" class="form-control inputUrl" name="anonymous_avatar_url" @if ($configImageInfo['anonymousAvatarType'] == 'ID') style="display:none;" @endif  @if ($configImageInfo['anonymousAvatarType'] == 'URL') value="{{ $params['anonymous_avatar'] }}" @endif>
                    <!--Hidden item-->
                    <input type="hidden" name="anonymous_avatar" value="{{ $params['anonymous_avatar'] }}">
                    <!--Preview-->
                    @if ($params['anonymous_avatar'])
                        <button class="btn btn-outline-secondary preview-image" type="button" data-url="{{ $configImageInfo['anonymousAvatarUrl'] }}">{{ __('FsLang::panel.button_view') }}</button>
                    @endif
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_default_anonymous_avatar_desc') }}</div>
        </div>
        <div class="row mb-2">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_default_deactivate_avatar') }}</label>
                    <!--Options-->
                    <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if ($configImageInfo['deactivateAvatarType'] == 'ID')
                            {{ __('FsLang::panel.button_image_upload') }}
                        @else
                            {{ __('FsLang::panel.button_image_input') }}
                        @endif
                    </button>
                    <ul class="dropdown-menu selectInputType">
                        <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                    </ul>
                    <!--Input-->
                    <input type="file" class="form-control inputFile" name="deactivate_avatar_file" @if ($configImageInfo['deactivateAvatarType'] == 'URL') style="display:none;" @endif>
                    <input type="text" class="form-control inputUrl" name="deactivate_avatar_url" @if ($configImageInfo['deactivateAvatarType'] == 'ID') style="display:none;" @endif  @if ($configImageInfo['deactivateAvatarType'] == 'URL') value="{{ $params['deactivate_avatar'] }}" @endif>
                    <!--Hidden item-->
                    <input type="hidden" name="deactivate_avatar" value="{{ $params['deactivate_avatar'] }}">
                    <!--Preview-->
                    @if ($params['deactivate_avatar'])
                        <button class="btn btn-outline-secondary preview-image" type="button" data-url="{{ $configImageInfo['deactivateAvatarUrl'] }}">{{ __('FsLang::panel.button_view') }}</button>
                    @endif
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_default_deactivate_avatar_desc') }}</div>
        </div>
        <div class="row mb-2">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_identifier') }}</label>
                    <select class="form-select" name="user_identifier">
                        <option value="uid" {{ $params['user_identifier'] == 'uid' ? 'selected' : '' }}>uid</option>
                        <option value="username" {{ $params['user_identifier'] == 'username' ? 'selected' : '' }}>username</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_identifier_desc') }}</div>
        </div>
        <div class="row mb-2">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_uid_digit') }}</label>
                    <input type="number" class="form-control input-number" name="user_uid_digit" min="5" max="16" value="{{ $params['user_uid_digit'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_length') }}</span>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_uid_digit_desc') }}</div>
        </div>
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_ban_names') }}</label>
                    <textarea class="form-control" name="user_ban_names" style="height: 200px">{!! $params['user_ban_names'] !!}</textarea>
                    <span class="input-group-text w-50 text-start text-wrap fs-7">{{ __('FsLang::panel.user_ban_names_desc') }}</span>
                </div>
            </div>
        </div>

        <!--config_edit-->
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.config_edit') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-2">
                    <label class="input-group-text">{{ __('FsLang::panel.user_edit_username_length') }}</label>
                    <input type="number" class="form-control input-number" name="username_min" value="{{ $params['username_min'] }}" placeholder="{{ __('FsLang::panel.user_edit_username_length_min') }}">
                    <input type="number" class="form-control input-number" name="username_max" value="{{ $params['username_max'] }}" placeholder="{{ __('FsLang::panel.user_edit_username_length_max') }}">
                </div>
                <div class="input-group mb-2">
                    <label class="input-group-text">{{ __('FsLang::panel.user_edit_username_periodicity') }}</label>
                    <input type="number" class="form-control input-number" name="username_edit" value="{{ $params['username_edit'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_day') }}</span>
                </div>
                <div class="input-group mb-2">
                    <label class="input-group-text">{{ __('FsLang::panel.user_edit_nickname_length') }}</label>
                    <input type="number" class="form-control input-number" name="nickname_min" value="{{ $params['nickname_min'] }}" placeholder="{{ __('FsLang::panel.user_edit_username_length_min') }}">
                    <input type="number" class="form-control input-number" name="nickname_max" value="{{ $params['nickname_max'] }}" placeholder="{{ __('FsLang::panel.user_edit_username_length_max') }}">
                </div>
                <div class="input-group mb-2">
                    <label class="input-group-text">{{ __('FsLang::panel.user_edit_nickname_periodicity') }}</label>
                    <input type="number" class="form-control input-number" name="nickname_edit" value="{{ $params['nickname_edit'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_day') }}</span>
                </div>
                <div class="input-group mb-2">
                    <label class="input-group-text">{{ __('FsLang::panel.user_edit_nickname_unique') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="nickname_unique" id="nickname_unique_false" value="false" {{ ! $params['nickname_unique'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="nickname_unique_false">{{ __('FsLang::panel.option_no') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="nickname_unique" id="nickname_unique_true" value="true" {{ $params['nickname_unique'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="nickname_unique_true">{{ __('FsLang::panel.option_yes') }}</label>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-2">
                    <label class="input-group-text">{{ __('FsLang::panel.user_edit_bio_length') }}</label>
                    <input type="number" class="form-control input-number" name="bio_length" value="{{ $params['bio_length'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_character') }}</span>
                </div>
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_edit_bio_support') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="bio_support_mention" name="bio_support_mention" value="true" {{ $params['bio_support_mention'] == 'true' ? 'checked' : '' }}>
                            <label class="form-check-label" for="bio_support_mention">{{ __('FsLang::panel.user_bio_support_mention') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="bio_support_link" name="bio_support_link" value="true" {{ $params['bio_support_link'] == 'true' ? 'checked' : '' }}>
                            <label class="form-check-label" for="bio_support_link">{{ __('FsLang::panel.user_bio_support_link') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="bio_support_hashtag" name="bio_support_hashtag" value="true" {{ $params['bio_support_hashtag'] == 'true' ? 'checked' : '' }}>
                            <label class="form-check-label" for="bio_support_hashtag">{{ __('FsLang::panel.user_bio_support_hashtag') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_edit_username_length_desc') }}</div>
        </div>

        <!--user_extcredits_config-->
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.user_extcredits_config') }}:</label>
            <div class="col-lg-6">
                @foreach ([1, 2, 3, 4, 5] as $extcreditsId)
                    <div class="input-group mb-2">
                        <label class="input-group-text">{{ 'extcredits'.$extcreditsId }}</label>
                        <button type="button" class="btn btn-outline-secondary btn-modal form-control text-start"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-action="{{ route('panel.languages.batch.update', ['itemKey' => "extcredits{$extcreditsId}_name"]) }}"
                            data-languages="{{ json_encode($params["extcredits{$extcreditsId}_name"]) }}">
                            {{ $defaultLangParams["extcredits{$extcreditsId}_name"] ??  __('FsLang::panel.user_extcredits_name') }}
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-modal form-control text-start"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-action="{{ route('panel.languages.batch.update', ['itemKey' => "extcredits{$extcreditsId}_unit"]) }}"
                            data-languages="{{ json_encode($params["extcredits{$extcreditsId}_unit"]) }}">
                            {{ $defaultLangParams["extcredits{$extcreditsId}_unit"] ??  __('FsLang::panel.user_extcredits_unit') }}
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-modal form-control"
                            data-bs-toggle="modal"
                            data-bs-target="#extcreditsSetting"
                            data-id="{{ $extcreditsId }}"
                            data-state="{{ $params["extcredits{$extcreditsId}_state"] }}">
                            @if ($params["extcredits{$extcreditsId}_state"] == 2)
                                {{ __('FsLang::panel.user_extcredits_state_private') }}
                            @elseif ($params["extcredits{$extcreditsId}_state"] == 3)
                                {{ __('FsLang::panel.user_extcredits_state_public') }}
                            @else
                                {{ __('FsLang::panel.user_extcredits_state_not_enabled') }}
                            @endif
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <!--config_conversation-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.config_conversation') }}:</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.user_conversation_status') }}</label>
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
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_conversation_status_desc') }}</div>
        </div>

        <div class="collapse conversation_setting {{ $params['conversation_status'] ? 'show' : '' }}">
            <div class="row mb-4">
                <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.user_conversation_files') }}:</label>
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
                <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.user_conversation_files_desc') }}</div>
            </div>
        </div>

        <!--button_save-->
        <div class="row mt-5">
            <div class="col-lg-2"></div>
            <div class="col-lg-6">
                <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
            </div>
        </div>
    </form>

    <!--imageZoom-->
    <div class="modal fade image-zoom" id="imageZoom" tabindex="-1" aria-labelledby="imageZoomLabel" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="position-relative image-box">
                <img class="img-fluid" src="">
            </div>
        </div>
    </div>

    <!-- Language Modal (input) -->
    <div class="modal fade" id="configLangModal" tabindex="-1" aria-labelledby="configLangModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_setting') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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
                        <div class="text-center">
                            <button type="submit" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Setting -->
    <div class="modal fade" id="extcreditsSetting" tabindex="-1" aria-labelledby="extcreditsSetting" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_setting') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('panel.user.update.extcredits') }}" method="post">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="extcreditsId" value="1">
                        <!--state-->
                        <div class="mb-3 row extcredits_state">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_status') }}</label>
                            <div class="col-sm-9 pt-2">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="extcredits_state" id="extcredits_state_1" value="1" checked>
                                    <label class="form-check-label" for="extcredits_state_1">{{ __('FsLang::panel.user_extcredits_state_not_enabled') }}</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="extcredits_state" id="extcredits_state_2" value="2">
                                    <label class="form-check-label" for="extcredits_state_2">{{ __('FsLang::panel.user_extcredits_state_private') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="extcredits_state" id="extcredits_state_3" value="3">
                                    <label class="form-check-label" for="extcredits_state_3">{{ __('FsLang::panel.user_extcredits_state_public') }}</label>
                                </div>
                            </div>
                        </div>

                        <!--button_save-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label"></label>
                            <div class="col-sm-9"><button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
