@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::operations.sidebar')
@endsection

@section('content')
    <!--header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_interaction') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_interaction_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                {{-- <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a> --}}
            </div>
        </div>
    </div>

    <!--config-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    <th scope="col"></th>
                    <th scope="col">{{ __('FsLang::panel.table_type') }}</th>
                    <th scope="col">{{ __('FsLang::panel.interaction_function_status') }}</th>
                    <th scope="col">{{ __('FsLang::panel.interaction_operation_name') }}</th>
                    <th scope="col">{{ __('FsLang::panel.interaction_user_title') }}</th>
                    <th scope="col">{{ __('FsLang::panel.interaction_public_record') }}</th>
                    <th scope="col">{{ __('FsLang::panel.interaction_public_count') }}</th>
                </tr>
            </thead>
            <tbody>
                <!--user-->
                @foreach (['like', 'dislike', 'follow', 'block'] as $type)
                    <tr>
                        @if ($type == 'like')
                            <th scope="row" rowspan="4" class="text-center">{{ __('FsLang::panel.user') }}</th>
                        @endif
                        <td>{{ __("FsLang::panel.{$type}") }}</td>
                        <td>
                            <button type="button" class="btn btn-sm {{ $params["user_{$type}_enabled"] ? 'btn-success' : 'btn-outline-dark' }}"
                                data-bs-toggle="modal"
                                data-bs-target="#configStatusModal"
                                data-title="{{ __("FsLang::panel.{$type}").': '.__('FsLang::panel.interaction_function_status') }}"
                                data-action="{{ route('panel.update.item', ['itemKey' => "user_{$type}_enabled"]) }}"
                                data-status="{{ $params["user_{$type}_enabled"] }}">
                                {{ $params["user_{$type}_enabled"] ? __('FsLang::panel.option_activate') : __('FsLang::panel.option_deactivate') }}
                            </button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-dark btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#configLangModal"
                                data-title="{{ __("FsLang::panel.{$type}").': '.__('FsLang::panel.interaction_operation_name') }}"
                                data-action="{{ route('panel.update.languages', ['itemKey' => "user_{$type}_name"]) }}"
                                data-languages="{{ json_encode($params["user_{$type}_name"]) }}">
                                {{ $defaultLangParams["user_{$type}_name"] ?? '' }}
                            </button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-dark btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#configLangModal"
                                data-title="{{ __("FsLang::panel.{$type}").': '.__('FsLang::panel.interaction_user_title') }}"
                                data-action="{{ route('panel.update.languages', ['itemKey' => "user_{$type}_user_title"]) }}"
                                data-languages="{{ json_encode($params["user_{$type}_user_title"]) }}">
                                {{ $defaultLangParams["user_{$type}_user_title"] ?? '' }}
                            </button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-dark btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#configStateModal"
                                data-title="{{ __("FsLang::panel.{$type}").': '.__('FsLang::panel.interaction_public_record') }}"
                                data-action="{{ route('panel.update.item', ['itemKey' => "user_{$type}_public_record"]) }}"
                                data-state="{{ $params["user_{$type}_public_record"] }}">
                                @if ($params["user_{$type}_public_record"] == 2)
                                    {{ __('FsLang::panel.option_data_private') }}
                                @elseif ($params["user_{$type}_public_record"] == 3)
                                    {{ __('FsLang::panel.option_data_public') }}
                                @else
                                    {{ __('FsLang::panel.option_data_close') }}
                                @endif
                            </button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-dark btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#configStateModal"
                                data-title="{{ __("FsLang::panel.{$type}").': '.__('FsLang::panel.interaction_public_count') }}"
                                data-action="{{ route('panel.update.item', ['itemKey' => "user_{$type}_public_count"]) }}"
                                data-state="{{ $params["user_{$type}_public_count"] }}">
                                @if ($params["user_{$type}_public_count"] == 2)
                                    {{ __('FsLang::panel.option_data_private') }}
                                @elseif ($params["user_{$type}_public_count"] == 3)
                                    {{ __('FsLang::panel.option_data_public') }}
                                @else
                                    {{ __('FsLang::panel.option_data_close') }}
                                @endif
                            </button>
                        </td>
                    </tr>
                @endforeach
                <!--group, hashtag, geotag, post, comment-->
                @foreach (['group', 'hashtag', 'geotag', 'post', 'comment'] as $item)
                    @foreach (['like', 'dislike', 'follow', 'block'] as $type)
                        <tr>
                            @if ($type == 'like')
                                <th scope="row" rowspan="4" class="text-center">{{ __("FsLang::panel.{$item}") }}</th>
                            @endif
                            <td>{{ __("FsLang::panel.{$type}") }}</td>
                            <td>
                                <button type="button" class="btn btn-sm {{ $params["{$item}_{$type}_enabled"] ? 'btn-success' : 'btn-outline-dark' }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#configStatusModal"
                                    data-title="{{ __("FsLang::panel.{$type}").': '.__('FsLang::panel.interaction_function_status') }}"
                                    data-action="{{ route('panel.update.item', ['itemKey' => "{$item}_{$type}_enabled"]) }}"
                                    data-status="{{ $params["{$item}_{$type}_enabled"] }}">
                                    {{ $params["{$item}_{$type}_enabled"] ? __('FsLang::panel.option_activate') : __('FsLang::panel.option_deactivate') }}
                                </button>
                            </td>
                            <td>
                                <button type="button" class="btn btn-outline-dark btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#configLangModal"
                                    data-title="{{ __("FsLang::panel.{$type}").': '.__('FsLang::panel.interaction_operation_name') }}"
                                    data-action="{{ route('panel.update.languages', ['itemKey' => "{$item}_{$type}_name"]) }}"
                                    data-languages="{{ json_encode($params["{$item}_{$type}_name"]) }}">
                                    {{ $defaultLangParams["{$item}_{$type}_name"] ?? '' }}
                                </button>
                            </td>
                            <td>
                                <button type="button" class="btn btn-outline-dark btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#configLangModal"
                                    data-title="{{ __("FsLang::panel.{$type}").': '.__('FsLang::panel.interaction_user_title') }}"
                                    data-action="{{ route('panel.update.languages', ['itemKey' => "{$item}_{$type}_user_title"]) }}"
                                    data-languages="{{ json_encode($params["{$item}_{$type}_user_title"]) }}">
                                    {{ $defaultLangParams["{$item}_{$type}_user_title"] ?? '' }}
                                </button>
                            </td>
                            <td>
                                <button type="button" class="btn btn-outline-dark btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#configPublicStatusModal"
                                    data-title="{{ __("FsLang::panel.{$type}").': '.__('FsLang::panel.interaction_public_record') }}"
                                    data-action="{{ route('panel.update.item', ['itemKey' => "{$item}_{$type}_public_record"]) }}"
                                    data-status="{{ $params["{$item}_{$type}_public_record"] }}">
                                    {{ $params["{$item}_{$type}_public_record"] ? __('FsLang::panel.option_data_public') : __('FsLang::panel.option_data_close') }}
                                </button>
                            </td>
                            <td>
                                <button type="button" class="btn btn-outline-dark btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#configPublicStatusModal"
                                    data-title="{{ __("FsLang::panel.{$type}").': '.__('FsLang::panel.interaction_public_count') }}"
                                    data-action="{{ route('panel.update.item', ['itemKey' => "{$item}_{$type}_public_count"]) }}"
                                    data-status="{{ $params["{$item}_{$type}_public_count"] }}">
                                    {{ $params["{$item}_{$type}_public_count"] ? __('FsLang::panel.option_data_public') : __('FsLang::panel.option_data_close') }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="table-responsive mt-3">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    <th scope="col"></th>
                    <th scope="col">{{ __('FsLang::panel.user_detail') }}</th>
                    <th scope="col">{{ __('FsLang::panel.interaction_function_status') }}</th>
                    <th scope="col">{{ __('FsLang::panel.interaction_column_name') }}</th>
                </tr>
            </thead>
            <tbody>
                <!--content-->
                <tr>
                    <th scope="row" rowspan="2" class="text-center">{{ __('FsLang::panel.table_content') }}</th>
                    <td>{{ __('FsLang::panel.profile_posts') }}</td>
                    <td>
                        <button type="button" class="btn btn-sm {{ $params['profile_posts_enabled'] ? 'btn-success' : 'btn-outline-dark' }}"
                            data-bs-toggle="modal"
                            data-bs-target="#configStatusModal"
                            data-title="{{ __('FsLang::panel.profile_posts').': '.__('FsLang::panel.interaction_function_status') }}"
                            data-action="{{ route('panel.update.item', ['itemKey' => 'profile_posts_enabled']) }}"
                            data-status="{{ $params['profile_posts_enabled'] }}">
                            {{ $params['profile_posts_enabled'] ? __('FsLang::panel.option_activate') : __('FsLang::panel.option_deactivate') }}
                        </button>
                    </td>
                    <td>{{ $defaultLangParams['post_name'] ?? '' }}</td>
                </tr>
                <tr>
                    <td>{{ __('FsLang::panel.profile_comments') }}</td>
                    <td>
                        <button type="button" class="btn btn-sm {{ $params['profile_comments_enabled'] ? 'btn-success' : 'btn-outline-dark' }}"
                            data-bs-toggle="modal"
                            data-bs-target="#configStatusModal"
                            data-title="{{ __('FsLang::panel.profile_comments').': '.__('FsLang::panel.interaction_function_status') }}"
                            data-action="{{ route('panel.update.item', ['itemKey' => 'profile_comments_enabled']) }}"
                            data-status="{{ $params['profile_comments_enabled'] }}">
                            {{ $params['profile_comments_enabled'] ? __('FsLang::panel.option_activate') : __('FsLang::panel.option_deactivate') }}
                        </button>
                    </td>
                    <td>{{ $defaultLangParams['comment_name'] ?? '' }}</td>
                </tr>
                <!--interaction-->
                <tr>
                    <th scope="row" rowspan="5" class="text-center">{{ __('FsLang::panel.config_interaction') }}</th>
                    <td>{{ __('FsLang::panel.profile_likers') }}</td>
                    <td>
                        @if ($params['user_like_public_record'] == 2)
                        {{ __('FsLang::panel.option_data_private') }}
                        @elseif ($params['user_like_public_record'] == 3)
                            {{ __('FsLang::panel.option_data_public') }}
                        @else
                            {{ __('FsLang::panel.option_data_close') }}
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.profile_likers').': '.__('FsLang::panel.interaction_column_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'profile_likes_name']) }}"
                            data-languages="{{ json_encode($params['profile_likes_name']) }}">
                            {{ $defaultLangParams['profile_likes_name'] ?? '' }}
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>{{ __('FsLang::panel.profile_dislikers') }}</td>
                    <td>
                        @if ($params['user_dislike_public_record'] == 2)
                        {{ __('FsLang::panel.option_data_private') }}
                        @elseif ($params['user_dislike_public_record'] == 3)
                            {{ __('FsLang::panel.option_data_public') }}
                        @else
                            {{ __('FsLang::panel.option_data_close') }}
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.profile_dislikers').': '.__('FsLang::panel.interaction_column_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'profile_dislikes_name']) }}"
                            data-languages="{{ json_encode($params['profile_dislikes_name']) }}">
                            {{ $defaultLangParams['profile_dislikes_name'] ?? '' }}
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>{{ __('FsLang::panel.profile_followers') }}</td>
                    <td>
                        @if ($params['user_follow_public_record'] == 2)
                        {{ __('FsLang::panel.option_data_private') }}
                        @elseif ($params['user_follow_public_record'] == 3)
                            {{ __('FsLang::panel.option_data_public') }}
                        @else
                            {{ __('FsLang::panel.option_data_close') }}
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.profile_followers').': '.__('FsLang::panel.interaction_column_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'profile_followers_name']) }}"
                            data-languages="{{ json_encode($params['profile_followers_name']) }}">
                            {{ $defaultLangParams['profile_followers_name'] ?? '' }}
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>{{ __('FsLang::panel.profile_blockers') }}</td>
                    <td>
                        @if ($params['user_block_public_record'] == 2)
                        {{ __('FsLang::panel.option_data_private') }}
                        @elseif ($params['user_block_public_record'] == 3)
                            {{ __('FsLang::panel.option_data_public') }}
                        @else
                            {{ __('FsLang::panel.option_data_close') }}
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.profile_blockers').': '.__('FsLang::panel.interaction_column_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'profile_blockers_name']) }}"
                            data-languages="{{ json_encode($params['profile_blockers_name']) }}">
                            {{ $defaultLangParams['profile_blockers_name'] ?? '' }}
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>{{ __('FsLang::panel.profile_followers_you_follow') }}</td>
                    <td>
                        <button type="button" class="btn btn-sm {{ $params['profile_followers_you_follow_enabled'] ? 'btn-success' : 'btn-outline-dark' }}"
                            data-bs-toggle="modal"
                            data-bs-target="#configStatusModal"
                            data-title="{{ __('FsLang::panel.profile_followers_you_follow').': '.__('FsLang::panel.interaction_function_status') }}"
                            data-action="{{ route('panel.update.item', ['itemKey' => 'profile_followers_you_follow_enabled']) }}"
                            data-status="{{ $params['profile_followers_you_follow_enabled'] }}">
                            {{ $params['profile_followers_you_follow_enabled'] ? __('FsLang::panel.option_activate') : __('FsLang::panel.option_deactivate') }}
                        </button>
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.profile_followers_you_follow').': '.__('FsLang::panel.interaction_column_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'profile_followers_you_follow_name']) }}"
                            data-languages="{{ json_encode($params['profile_followers_you_follow_name']) }}">
                            {{ $defaultLangParams['profile_followers_you_follow_name'] ?? '' }}
                        </button>
                    </td>
                </tr>
                <!--user interaction-->
                @foreach (['likes', 'dislikes', 'following', 'blocking'] as $type)
                    @foreach (['users', 'groups', 'hashtags', 'geotags', 'posts', 'comments'] as $item)
                        <tr>
                            @if ($item == 'users')
                                <th scope="row" rowspan="6" class="text-center">
                                    @switch($type)
                                        @case('likes')
                                            {{ __("FsLang::panel.like") }}
                                            @break

                                        @case('dislikes')
                                            {{ __("FsLang::panel.dislike") }}
                                            @break

                                        @case('following')
                                            {{ __("FsLang::panel.follow") }}
                                            @break

                                        @case('blocking')
                                            {{ __("FsLang::panel.block") }}
                                            @break
                                    @endswitch
                                </th>
                            @endif
                            <td>{{ __("FsLang::panel.profile_{$type}_{$item}") }}</td>
                            <td>
                                <button type="button" class="btn btn-sm {{ $params["profile_{$type}_{$item}_enabled"] ? 'btn-success' : 'btn-outline-dark' }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#configStatusModal"
                                    data-title="{{ __("FsLang::panel.profile_{$type}_{$item}").': '.__('FsLang::panel.interaction_function_status') }}"
                                    data-action="{{ route('panel.update.item', ['itemKey' => "profile_{$type}_{$item}_enabled"]) }}"
                                    data-status="{{ $params["profile_{$type}_{$item}_enabled"] }}">
                                    {{ $params["profile_{$type}_{$item}_enabled"] ? __('FsLang::panel.option_activate') : __('FsLang::panel.option_deactivate') }}
                                </button>
                            </td>
                            <td>
                                <button type="button" class="btn btn-outline-dark btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#configLangModal"
                                    data-title="{{ __("FsLang::panel.profile_{$type}_{$item}").': '.__('FsLang::panel.interaction_column_name') }}"
                                    data-action="{{ route('panel.update.languages', ['itemKey' => "profile_{$type}_{$item}_name"]) }}"
                                    data-languages="{{ json_encode($params["profile_{$type}_{$item}_name"]) }}">
                                    {{ $defaultLangParams["profile_{$type}_{$item}_name"] ?? '' }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Status Modal -->
    <div class="modal fade" id="configStatusModal" tabindex="-1" aria-labelledby="configStatusModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title lang-modal-title">{{ __('FsLang::panel.button_setting') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="itemType" value="boolean">
                        <!--state-->
                        <div class="row mb-4">
                            <label class="col-sm-2"></label>
                            <div class="col-sm-10 pt-2">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="itemValue" id="status_true" value="1" checked>
                                    <label class="form-check-label" for="status_true">{{ __('FsLang::panel.option_activate') }}</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="itemValue" id="status_false" value="0">
                                    <label class="form-check-label" for="status_false">{{ __('FsLang::panel.option_deactivate') }}</label>
                                </div>
                            </div>
                        </div>

                        <!--button_save-->
                        <div class="mb-3 row">
                            <label class="col-sm-2"></label>
                            <div class="col-sm-9"><button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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

    <!-- State Modal -->
    <div class="modal fade" id="configStateModal" tabindex="-1" aria-labelledby="configStateModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title lang-modal-title">{{ __('FsLang::panel.button_setting') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="itemType" value="number">
                        <!--state-->
                        <div class="row mb-4">
                            <label class="col-sm-2"></label>
                            <div class="col-sm-10 pt-2">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="itemValue" id="state_1" value="1" checked>
                                    <label class="form-check-label" for="state_1">{{ __('FsLang::panel.option_data_close') }}</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="itemValue" id="state_2" value="2">
                                    <label class="form-check-label" for="state_2">{{ __('FsLang::panel.option_data_private') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="itemValue" id="state_3" value="3">
                                    <label class="form-check-label" for="state_3">{{ __('FsLang::panel.option_data_public') }}</label>
                                </div>
                            </div>
                        </div>

                        <!--button_save-->
                        <div class="mb-3 row">
                            <label class="col-sm-2"></label>
                            <div class="col-sm-10"><button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Public Status Modal -->
    <div class="modal fade" id="configPublicStatusModal" tabindex="-1" aria-labelledby="configPublicStatusModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title lang-modal-title">{{ __('FsLang::panel.button_setting') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="itemType" value="boolean">
                        <!--state-->
                        <div class="row mb-4">
                            <label class="col-sm-2"></label>
                            <div class="col-sm-10 pt-2">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="itemValue" id="public_status_false" value="0">
                                    <label class="form-check-label" for="public_status_false">{{ __('FsLang::panel.option_data_close') }}</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="itemValue" id="public_status_true" value="1" checked>
                                    <label class="form-check-label" for="public_status_true">{{ __('FsLang::panel.option_data_public') }}</label>
                                </div>
                            </div>
                        </div>

                        <!--button_save-->
                        <div class="mb-3 row">
                            <label class="col-sm-2"></label>
                            <div class="col-sm-9"><button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
