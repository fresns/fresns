@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_channels') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_channels_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <label class="input-group-text"><i class="bi bi-house-fill me-1"></i> {{ __('FsLang::panel.channel_default_homepage') }}</label>
                <span class="input-group-text">{{ __("FsLang::panel.{$params['default_homepage']}") }}</span>
                <button class="btn btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_edit') }}</button>
                <ul class="dropdown-menu">
                    @foreach (['portal', 'user', 'group', 'hashtag', 'geotag', 'post', 'comment'] as $item)
                        <form action="{{ route('panel.update.item', ['itemKey' => 'default_homepage']) }}" method="post">
                            @csrf
                            @method('patch')
                            <input type="hidden" name="itemValue" value="{{ $item }}">
                            <button class="dropdown-item ps-3" type="submit">{{ __("FsLang::panel.{$item}") }}</button>
                        </form>
                    @endforeach
                </ul>
                {{-- <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a> --}}
            </div>
        </div>
    </div>

    <!--config-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    <th scope="col">{{ __('FsLang::panel.channel_table_channel') }}</th>
                    <th scope="col" colspan="2">{{ __('FsLang::panel.channel_table_page') }}</th>
                    <th scope="col">{{ __('FsLang::panel.channel_table_path') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_name') }}</th>
                    <th scope="col">{{ __('FsLang::panel.channel_table_seo') }}</th>
                    <th scope="col">{{ __('FsLang::panel.config_list') }}</th>
                </tr>
            </thead>
            <tbody>
                {{-- portal --}}
                <tr>
                    <th scope="row" class="text-center">{{ __('FsLang::panel.portal') }}</th>
                    <td colspan="2"></td>
                    <td>{{ '/'.$params['website_portal_path'] }}</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.portal').': '.__('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_portal_name']) }}"
                            data-languages="{{ json_encode($params['channel_portal_name']) }}">
                            {{ $defaultLangParams['channel_portal_name'] ?? '' }}
                        </button>
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configSeoModal"
                            data-title="{{ __('FsLang::panel.portal').': '.__('FsLang::panel.channel_table_seo') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_portal_seo']) }}"
                            data-languages="{{ json_encode($params['channel_portal_seo']) }}">
                            {{ __('FsLang::panel.button_edit') }}
                        </button>
                    </td>
                    <td></td>
                </tr>
                {{-- user, group, hashtag, geotag, post, comment --}}
                @foreach (['user', 'group', 'hashtag', 'geotag', 'post', 'comment'] as $item)
                    <tr>
                        @if (in_array($item, ['group', 'hashtag', 'geotag']))
                            <th scope="row" rowspan="7" class="text-center">{{ __("FsLang::panel.{$item}") }}</th>
                        @else
                            <th scope="row" rowspan="6" class="text-center">{{ __("FsLang::panel.{$item}") }}</th>
                        @endif
                        <td colspan="2">{{ __('FsLang::panel.channel_table_page_home') }}</td>
                        <td>{{ '/'.$params["website_{$item}_path"] }}</td>
                        <td>
                            <button type="button" class="btn btn-outline-dark btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#configLangModal"
                                data-title="{{ __('FsLang::panel.table_name') }}"
                                data-action="{{ route('panel.update.languages', ['itemKey' => "channel_{$item}_name"]) }}"
                                data-languages="{{ json_encode($params["channel_{$item}_name"]) }}">
                                {{ $defaultLangParams["channel_{$item}_name"] ?? '' }}
                            </button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#configSeoModal"
                                data-title="{{ __('FsLang::panel.channel_table_seo') }}"
                                data-action="{{ route('panel.update.languages', ['itemKey' => "channel_{$item}_seo"]) }}"
                                data-languages="{{ json_encode($params["channel_{$item}_seo"]) }}">
                                {{ __('FsLang::panel.button_edit') }}
                            </button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#configListModal"
                                data-title="{{ __('FsLang::panel.config_list') }}"
                                data-action="{{ route('panel.channels.update', ['type' => $item]) }}"
                                @if ($item == 'group') data-index-type="{{ $params["channel_{$item}_type"] }}" @endif
                                data-query-state="{{ $params["channel_{$item}_query_state"] }}"
                                data-query-config="{{ $params["channel_{$item}_query_config"] }}">
                                {{ __('FsLang::panel.button_edit') }}
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">{{ __('FsLang::panel.channel_table_page_list') }}</td>
                        <td>{{ '/'.$params["website_{$item}_path"].'/list' }}</td>
                        <td>
                            <button type="button" class="btn btn-outline-dark btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#configLangModal"
                                data-title="{{ __('FsLang::panel.table_name') }}"
                                data-action="{{ route('panel.update.languages', ['itemKey' => "channel_{$item}_list_name"]) }}"
                                data-languages="{{ json_encode($params["channel_{$item}_list_name"]) }}">
                                {{ $defaultLangParams["channel_{$item}_list_name"] ?? '' }}
                            </button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#configSeoModal"
                                data-title="{{ __('FsLang::panel.channel_table_seo') }}"
                                data-action="{{ route('panel.update.languages', ['itemKey' => "channel_{$item}_list_seo"]) }}"
                                data-languages="{{ json_encode($params["channel_{$item}_list_seo"]) }}">
                                {{ __('FsLang::panel.button_edit') }}
                            </button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#configListModal"
                                data-title="{{ __('FsLang::panel.config_list') }}"
                                data-action="{{ route('panel.channels.update', ['type' => "{$item}_list"]) }}"
                                data-query-state="{{ $params["channel_{$item}_list_query_state"] }}"
                                data-query-config="{{ $params["channel_{$item}_list_query_config"] }}">
                                {{ __('FsLang::panel.button_edit') }}
                            </button>
                        </td>
                    </tr>
                    @if (in_array($item, ['group', 'hashtag', 'geotag']))
                        <tr>
                            <td colspan="2">{{ __('FsLang::panel.channel_table_page_detail') }}</td>
                            <td>
                                {{ '/'.$params["website_{$item}_detail_path"].'/' }}
                                @if ($item == 'group')
                                    <mark>{gid}</mark>
                                @elseif ($item == 'hashtag')
                                    <mark>{htid}</mark>
                                @elseif ($item == 'geotag')
                                    <mark>{gtid}</mark>
                                @endif
                            </td>
                            <td></td>
                            <td></td>
                            <td>
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#configDefaultListModal"
                                    data-action="{{ route('panel.update.item', ['itemKey' => "channel_{$item}_detail_type"]) }}"
                                    data-item-value="{{ $params["channel_{$item}_detail_type"] }}">
                                    {{ __('FsLang::panel.button_edit') }}
                                </button>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td rowspan="4">{{ __('FsLang::panel.channel_table_page_interaction') }}</td>
                        <td>{{ __('FsLang::panel.like') }}</td>
                        <td>{{ '/'.$params["website_{$item}_path"].'/likes' }}</td>
                        <td>
                            <button type="button" class="btn btn-outline-dark btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#configLangModal"
                                data-title="{{ __('FsLang::panel.table_name') }}"
                                data-action="{{ route('panel.update.languages', ['itemKey' => "channel_my_like_{$item}s_name"]) }}"
                                data-languages="{{ json_encode($params["channel_my_like_{$item}s_name"]) }}">
                                {{ $defaultLangParams["channel_my_like_{$item}s_name"] ?? '' }}
                            </button>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>{{ __('FsLang::panel.dislike') }}</td>
                        <td>{{ '/'.$params["website_{$item}_path"].'/dislikes' }}</td>
                        <td>
                            <button type="button" class="btn btn-outline-dark btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#configLangModal"
                                data-title="{{ __('FsLang::panel.table_name') }}"
                                data-action="{{ route('panel.update.languages', ['itemKey' => "channel_my_dislike_{$item}s_name"]) }}"
                                data-languages="{{ json_encode($params["channel_my_dislike_{$item}s_name"]) }}">
                                {{ $defaultLangParams["channel_my_dislike_{$item}s_name"] ?? '' }}
                            </button>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>{{ __('FsLang::panel.follow') }}</td>
                        <td>{{ '/'.$params["website_{$item}_path"].'/following' }}</td>
                        <td>
                            <button type="button" class="btn btn-outline-dark btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#configLangModal"
                                data-title="{{ __('FsLang::panel.table_name') }}"
                                data-action="{{ route('panel.update.languages', ['itemKey' => "channel_my_follow_{$item}s_name"]) }}"
                                data-languages="{{ json_encode($params["channel_my_follow_{$item}s_name"]) }}">
                                {{ $defaultLangParams["channel_my_follow_{$item}s_name"] ?? '' }}
                            </button>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>{{ __('FsLang::panel.block') }}</td>
                        <td>{{ '/'.$params["website_{$item}_path"].'/blocking' }}</td>
                        <td>
                            <button type="button" class="btn btn-outline-dark btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#configLangModal"
                                data-title="{{ __('FsLang::panel.table_name') }}"
                                data-action="{{ route('panel.update.languages', ['itemKey' => "channel_my_block_{$item}s_name"]) }}"
                                data-languages="{{ json_encode($params["channel_my_block_{$item}s_name"]) }}">
                                {{ $defaultLangParams["channel_my_block_{$item}s_name"] ?? '' }}
                            </button>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach
                {{-- timeline --}}
                <tr>
                    <th scope="row" rowspan="7" class="text-center">{{ __('FsLang::panel.channel_timeline') }}</th>
                    <td colspan="2">{{ __('FsLang::panel.channel_table_page_home') }}</td>
                    <td>/timelines</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_timeline_name']) }}"
                            data-languages="{{ json_encode($params['channel_timeline_name']) }}">
                            {{ $defaultLangParams['channel_timeline_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td>
                        <button type="button" class="btn btn-outline-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configDefaultListModal"
                            data-action="{{ route('panel.update.item', ['itemKey' => 'channel_timeline_type']) }}"
                            data-item-value="{{ $params['channel_timeline_type'] }}">
                            {{ __('FsLang::panel.button_edit') }}
                        </button>
                    </td>
                </tr>
                <tr>
                    <td rowspan="6">{{ __('FsLang::panel.channel_table_page_list') }}</td>
                    <td>{{ __('FsLang::panel.channel_timeline_all_posts') }}</td>
                    <td>/timelines/posts</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_timeline_posts_name']) }}"
                            data-languages="{{ json_encode($params['channel_timeline_posts_name']) }}">
                            {{ $defaultLangParams['channel_timeline_posts_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>{{ __('FsLang::panel.channel_timeline_users_posts') }}</td>
                    <td>/timelines/user-posts</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_timeline_user_posts_name']) }}"
                            data-languages="{{ json_encode($params['channel_timeline_user_posts_name']) }}">
                            {{ $defaultLangParams['channel_timeline_user_posts_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>{{ __('FsLang::panel.channel_timeline_groups_posts') }}</td>
                    <td>/timelines/group-posts</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_timeline_group_posts_name']) }}"
                            data-languages="{{ json_encode($params['channel_timeline_group_posts_name']) }}">
                            {{ $defaultLangParams['channel_timeline_group_posts_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>{{ __('FsLang::panel.channel_timeline_all_comments') }}</td>
                    <td>/timelines/comments</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_timeline_comments_name']) }}"
                            data-languages="{{ json_encode($params['channel_timeline_comments_name']) }}">
                            {{ $defaultLangParams['channel_timeline_comments_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>{{ __('FsLang::panel.channel_timeline_users_comments') }}</td>
                    <td>/timelines/user-comments</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_timeline_user_comments_name']) }}"
                            data-languages="{{ json_encode($params['channel_timeline_user_comments_name']) }}">
                            {{ $defaultLangParams['channel_timeline_user_comments_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>{{ __('FsLang::panel.channel_timeline_groups_comments') }}</td>
                    <td>/timelines/group-comments</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_timeline_group_comments_name']) }}"
                            data-languages="{{ json_encode($params['channel_timeline_group_comments_name']) }}">
                            {{ $defaultLangParams['channel_timeline_group_comments_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                {{-- nearby --}}
                <tr>
                    <th scope="row" rowspan="3" class="text-center">{{ __('FsLang::panel.channel_nearby') }}</th>
                    <td colspan="2">{{ __('FsLang::panel.channel_table_page_home') }}</td>
                    <td>/nearby</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_nearby_name']) }}"
                            data-languages="{{ json_encode($params['channel_nearby_name']) }}">
                            {{ $defaultLangParams['channel_nearby_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td>
                        <button type="button" class="btn btn-outline-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configDefaultListModal"
                            data-action="{{ route('panel.update.item', ['itemKey' => 'channel_nearby_type']) }}"
                            data-item-value="{{ $params['channel_nearby_type'] }}">
                            {{ __('FsLang::panel.button_edit') }}
                        </button>
                    </td>
                </tr>
                <tr>
                    <td rowspan="2">{{ __('FsLang::panel.channel_table_page_list') }}</td>
                    <td>{{ __('FsLang::panel.channel_nearby_posts') }}</td>
                    <td>/nearby/posts</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_nearby_posts_name']) }}"
                            data-languages="{{ json_encode($params['channel_nearby_posts_name']) }}">
                            {{ $defaultLangParams['channel_nearby_posts_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>{{ __('FsLang::panel.channel_nearby_comments') }}</td>
                    <td>/nearby/comments</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_nearby_comments_name']) }}"
                            data-languages="{{ json_encode($params['channel_nearby_comments_name']) }}">
                            {{ $defaultLangParams['channel_nearby_comments_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                {{-- me --}}
                <tr>
                    <th scope="row" rowspan="6" class="text-center">{{ __('FsLang::panel.channel_me') }}</th>
                    <td colspan="2">{{ __('FsLang::panel.channel_table_page_home') }}</td>
                    <td>/me</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_me_name']) }}"
                            data-languages="{{ json_encode($params['channel_me_name']) }}">
                            {{ $defaultLangParams['channel_me_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td rowspan="5">{{ __('FsLang::panel.channel_table_page_list') }}</td>
                    <td>{{ __('FsLang::panel.channel_me_wallet') }}</td>
                    <td>/me/wallet</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_me_wallet_name']) }}"
                            data-languages="{{ json_encode($params['channel_me_wallet_name']) }}">
                            {{ $defaultLangParams['channel_me_wallet_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>{{ __('FsLang::panel.channel_me_extcredits') }}</td>
                    <td>/me/extcredits</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_me_extcredits_name']) }}"
                            data-languages="{{ json_encode($params['channel_me_extcredits_name']) }}">
                            {{ $defaultLangParams['channel_me_extcredits_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>{{ __('FsLang::panel.channel_me_drafts') }}</td>
                    <td>/me/drafts</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_me_drafts_name']) }}"
                            data-languages="{{ json_encode($params['channel_me_drafts_name']) }}">
                            {{ $defaultLangParams['channel_me_drafts_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>{{ __('FsLang::panel.channel_me_users') }}</td>
                    <td>/me/users</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_me_users_name']) }}"
                            data-languages="{{ json_encode($params['channel_me_users_name']) }}">
                            {{ $defaultLangParams['channel_me_users_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>{{ __('FsLang::panel.channel_me_settings') }}</td>
                    <td>/me/settings</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_me_settings_name']) }}"
                            data-languages="{{ json_encode($params['channel_me_settings_name']) }}">
                            {{ $defaultLangParams['channel_me_settings_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                {{-- messages --}}
                <tr>
                    <th scope="row" rowspan="12" class="text-center">{{ __('FsLang::panel.channel_messages') }}</th>
                    <td rowspan="2">{{ __('FsLang::panel.channel_table_page_home') }}</td>
                    <td>{{ __('FsLang::panel.channel_conversations') }}</td>
                    <td>/messages</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_conversations_name']) }}"
                            data-languages="{{ json_encode($params['channel_conversations_name']) }}">
                            {{ $defaultLangParams['channel_conversations_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>{{ __('FsLang::panel.channel_notifications') }}</td>
                    <td>/notifications</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_notifications_name']) }}"
                            data-languages="{{ json_encode($params['channel_notifications_name']) }}">
                            {{ $defaultLangParams['channel_notifications_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                @foreach (['all', 'systems', 'recommends', 'likes', 'dislikes', 'follows', 'blocks', 'mentions', 'comments', 'quotes'] as $item)
                    <tr>
                        @if ($item == 'all')
                            <td rowspan="10">{{ __('FsLang::panel.channel_table_page_list') }}</td>
                        @endif
                        <td>{{ __("FsLang::panel.channel_notifications_{$item}") }}</td>
                        <td>/notifications/{{ $item }}</td>
                        <td>
                            <button type="button" class="btn btn-outline-dark btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#configLangModal"
                                data-title="{{ __('FsLang::panel.table_name') }}"
                                data-action="{{ route('panel.update.languages', ['itemKey' => "channel_notifications_{$item}_name"]) }}"
                                data-languages="{{ json_encode($params["channel_notifications_{$item}_name"]) }}">
                                {{ $defaultLangParams["channel_notifications_{$item}_name"] ?? '' }}
                            </button>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach
                {{-- search --}}
                <tr>
                    <th scope="row" class="text-center">{{ __('FsLang::panel.channel_search') }}</th>
                    <td colspan="2">{{ __('FsLang::panel.channel_table_page_home') }}</td>
                    <td>/search</td>
                    <td>
                        <button type="button" class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#configLangModal"
                            data-title="{{ __('FsLang::panel.table_name') }}"
                            data-action="{{ route('panel.update.languages', ['itemKey' => 'channel_search_name']) }}"
                            data-languages="{{ json_encode($params['channel_search_name']) }}">
                            {{ $defaultLangParams['channel_search_name'] ?? '' }}
                        </button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
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

    <!-- SEO Modal -->
    <div class="modal fade" id="configSeoModal" tabindex="-1" aria-labelledby="configSeoModal" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title lang-modal-title">{{ __('FsLang::panel.button_setting') }}</h5>
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
                                        <th scope="col">{{ __('FsLang::panel.table_lang_tag') }}</th>
                                        <th scope="col">{{ __('FsLang::panel.table_lang_name') }}</th>
                                        <th scope="col">{{ __('FsLang::panel.channel_table_seo_title') }}</th>
                                        <th scope="col">{{ __('FsLang::panel.channel_table_seo_description') }}</th>
                                        <th scope="col">{{ __('FsLang::panel.channel_table_seo_keywords') }}</th>
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
                                            <td><input class="form-control" name="languages[{{ $lang['langTag'] }}][title]"></td>
                                            <td><textarea class="form-control" name="languages[{{ $lang['langTag'] }}][description]" rows="5"></textarea></td>
                                            <td><textarea class="form-control" name="languages[{{ $lang['langTag'] }}][keywords]" rows="5"></textarea></td>
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

    <!-- List Config Modal -->
    <div class="modal fade" id="configListModal" tabindex="-1" aria-labelledby="configListModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title lang-modal-title">{{ __('FsLang::panel.button_setting') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        @csrf
                        @method('put')
                        <!--type-->
                        <div class="mb-3 row index-type" style="display: none">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_type') }}</label>
                            <div class="col-sm-9 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="index_type" id="type_tree" value="tree" checked>
                                    <label class="form-check-label" for="type_tree">{{ __('FsLang::panel.option_type_tree') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="index_type" id="type_list" value="list">
                                    <label class="form-check-label" for="type_list">{{ __('FsLang::panel.option_type_list') }}</label>
                                </div>
                            </div>
                        </div>

                        <!--query state-->
                        <div class="mb-3 row query-state">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.channel_table_query_state') }}</label>
                            <div class="col-sm-9 pt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="query_state" id="query_state_1" value="1" checked>
                                    <label class="form-check-label" for="query_state_1">{{ __('FsLang::panel.channel_query_state_1') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="query_state" id="query_state_2" value="2">
                                    <label class="form-check-label" for="query_state_2">{{ __('FsLang::panel.channel_query_state_2') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="query_state" id="query_state_3" value="3">
                                    <label class="form-check-label" for="query_state_3">{{ __('FsLang::panel.channel_query_state_3') }}</label>
                                </div>
                                <div class="form-text">{{ __('FsLang::panel.channel_table_query_state_desc') }}</div>
                            </div>
                        </div>

                        <!--query config-->
                        <div class="mb-3 row query-config">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.channel_table_query_config') }}</label>
                            <div class="col-sm-9 pt-2">
                                <textarea class="form-control" name="query_config" rows="6"></textarea>
                                <div class="form-text">{{ __('FsLang::panel.channel_table_query_config_desc') }}</div>
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

    <!-- Default List Config Modal -->
    <div class="modal fade" id="configDefaultListModal" tabindex="-1" aria-labelledby="configDefaultListModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_setting') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        @csrf
                        @method('patch')
                        <!--type-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.default_list') }}</label>
                            <div class="col-sm-9 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="itemValue" id="type_posts" value="posts" checked>
                                    <label class="form-check-label" for="type_posts">{{ __('FsLang::panel.post') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="itemValue" id="type_comments" value="comments">
                                    <label class="form-check-label" for="type_comments">{{ __('FsLang::panel.comment') }}</label>
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
