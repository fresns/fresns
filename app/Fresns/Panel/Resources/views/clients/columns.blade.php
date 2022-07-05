@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--columns header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_columns') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_columns_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <!--columns config-->
    <!--Users-->
    <div class="row mb-4">
        <label class="col-lg-2">{{ __('FsLang::panel.columns_user_rename') }}:</label>
        <div class="col-lg-6">
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_like_users') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_like_users']) }}"
                    data-languages="{{ optional($configs['menu_like_users'])->languages->toJson() }}"
                    data-item_key="menu_like_users"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_like_users'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_dislike_users') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_dislike_users']) }}"
                    data-languages="{{ optional($configs['menu_dislike_users'])->languages->toJson() }}"
                    data-item_key="menu_dislike_users"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_dislike_users'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_follow_users') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_follow_users']) }}"
                    data-languages="{{ optional($configs['menu_follow_users'])->languages->toJson() }}"
                    data-item_key="menu_follow_users"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_follow_users'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_block_users') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_block_users']) }}"
                    data-languages="{{ optional($configs['menu_block_users'])->languages->toJson() }}"
                    data-item_key="menu_block_users"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_block_users'] ?? '' }}
                </button>
            </div>
        </div>
    </div>

    <!--Groups-->
    <div class="row mb-4">
        <label class="col-lg-2">{{ __('FsLang::panel.columns_group_rename') }}:</label>
        <div class="col-lg-6">
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_like_groups') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_like_groups']) }}"
                    data-languages="{{ optional($configs['menu_like_groups'])->languages->toJson() }}"
                    data-item_key="menu_like_groups"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_like_groups'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_dislike_groups') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_dislike_groups']) }}"
                    data-languages="{{ optional($configs['menu_dislike_groups'])->languages->toJson() }}"
                    data-item_key="menu_dislike_groups"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_dislike_groups'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_follow_groups') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_follow_groups']) }}"
                    data-languages="{{ optional($configs['menu_follow_groups'])->languages->toJson() }}"
                    data-item_key="menu_follow_groups"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_follow_groups'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_block_groups') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_block_groups']) }}"
                    data-languages="{{ optional($configs['menu_block_groups'])->languages->toJson() }}"
                    data-item_key="menu_block_groups"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_block_groups'] ?? '' }}
                </button>
            </div>
        </div>
    </div>

    <!--Hashtags-->
    <div class="row mb-4">
        <label class="col-lg-2">{{ __('FsLang::panel.columns_hashtag_rename') }}:</label>
        <div class="col-lg-6">
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_like_hashtags') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_like_hashtags']) }}"
                    data-languages="{{ optional($configs['menu_like_hashtags'])->languages->toJson() }}"
                    data-item_key="menu_like_hashtags"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_like_hashtags'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_dislike_hashtags') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_dislike_hashtags']) }}"
                    data-languages="{{ optional($configs['menu_dislike_hashtags'])->languages->toJson() }}"
                    data-item_key="menu_dislike_hashtags"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_dislike_hashtags'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_follow_hashtags') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_follow_hashtags']) }}"
                    data-languages="{{ optional($configs['menu_follow_hashtags'])->languages->toJson() }}"
                    data-item_key="menu_follow_hashtags"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_follow_hashtags'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_block_hashtags') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_block_hashtags']) }}"
                    data-languages="{{ optional($configs['menu_block_hashtags'])->languages->toJson() }}"
                    data-item_key="menu_block_hashtags"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_block_hashtags'] ?? '' }}
                </button>
            </div>
        </div>
    </div>

    <!--Posts-->
    <div class="row mb-4">
        <label class="col-lg-2">{{ __('FsLang::panel.columns_post_rename') }}:</label>
        <div class="col-lg-6">
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_nearby_posts') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_nearby_posts']) }}"
                    data-languages="{{ optional($configs['menu_nearby_posts'])->languages->toJson() }}"
                    data-item_key="menu_nearby_posts"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_nearby_posts'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_location_posts') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_location_posts']) }}"
                    data-languages="{{ optional($configs['menu_location_posts'])->languages->toJson() }}"
                    data-item_key="menu_location_posts"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_location_posts'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_like_posts') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_like_posts']) }}"
                    data-languages="{{ optional($configs['menu_like_posts'])->languages->toJson() }}"
                    data-item_key="menu_like_posts"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_like_posts'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_dislike_posts') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_dislike_posts']) }}"
                    data-languages="{{ optional($configs['menu_dislike_posts'])->languages->toJson() }}"
                    data-item_key="menu_dislike_posts"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_dislike_posts'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_follow_posts') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_follow_posts']) }}"
                    data-languages="{{ optional($configs['menu_follow_posts'])->languages->toJson() }}"
                    data-item_key="menu_follow_posts"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_follow_posts'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_block_posts') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_block_posts']) }}"
                    data-languages="{{ optional($configs['menu_block_posts'])->languages->toJson() }}"
                    data-item_key="menu_block_posts"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_block_posts'] ?? '' }}
                </button>
            </div>
        </div>
    </div>

    <!--Comments-->
    <div class="row mb-4">
        <label class="col-lg-2">{{ __('FsLang::panel.columns_comment_rename') }}:</label>
        <div class="col-lg-6">
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_like_comments') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_like_comments']) }}"
                    data-languages="{{ optional($configs['menu_like_comments'])->languages->toJson() }}"
                    data-item_key="menu_like_comments"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_like_comments'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_dislike_comments') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_dislike_comments']) }}"
                    data-languages="{{ optional($configs['menu_dislike_comments'])->languages->toJson() }}"
                    data-item_key="menu_dislike_comments"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_dislike_comments'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_follow_comments') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_follow_comments']) }}"
                    data-languages="{{ optional($configs['menu_follow_comments'])->languages->toJson() }}"
                    data-item_key="menu_follow_comments"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_follow_comments'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_block_comments') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_block_comments']) }}"
                    data-languages="{{ optional($configs['menu_block_comments'])->languages->toJson() }}"
                    data-item_key="menu_block_comments"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_block_comments'] ?? '' }}
                </button>
            </div>
        </div>
    </div>

    <!--Follow-->
    <div class="row mb-4">
        <label class="col-lg-2">{{ __('FsLang::panel.columns_follow_rename') }}:</label>
        <div class="col-lg-6">
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_follow_all_posts') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_follow_all_posts']) }}"
                    data-languages="{{ optional($configs['menu_follow_all_posts'])->languages->toJson() }}"
                    data-item_key="menu_follow_all_posts"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_follow_all_posts'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_follow_user_posts') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_follow_user_posts']) }}"
                    data-languages="{{ optional($configs['menu_follow_user_posts'])->languages->toJson() }}"
                    data-item_key="menu_follow_user_posts"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_follow_user_posts'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_follow_group_posts') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_follow_group_posts']) }}"
                    data-languages="{{ optional($configs['menu_follow_group_posts'])->languages->toJson() }}"
                    data-item_key="menu_follow_group_posts"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_follow_group_posts'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_follow_hashtag_posts') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_follow_hashtag_posts']) }}"
                    data-languages="{{ optional($configs['menu_follow_hashtag_posts'])->languages->toJson() }}"
                    data-item_key="menu_follow_hashtag_posts"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_follow_hashtag_posts'] ?? '' }}
                </button>
            </div>
        </div>
    </div>

    <!--Account-->
    <div class="row mb-4">
        <label class="col-lg-2">{{ __('FsLang::panel.columns_account_rename') }}:</label>
        <div class="col-lg-6">
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_account') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_account']) }}"
                    data-languages="{{ optional($configs['menu_account'])->languages->toJson() }}"
                    data-item_key="menu_account"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_account'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_account_register') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_account_register']) }}"
                    data-languages="{{ optional($configs['menu_account_register'])->languages->toJson() }}"
                    data-item_key="menu_account_register"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_account_register'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_account_login') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_account_login']) }}"
                    data-languages="{{ optional($configs['menu_account_login'])->languages->toJson() }}"
                    data-item_key="menu_account_login"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_account_login'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_account_reset_password') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_account_reset_password']) }}"
                    data-languages="{{ optional($configs['menu_account_reset_password'])->languages->toJson() }}"
                    data-item_key="menu_account_reset_password"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_account_reset_password'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_account_users') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_account_users']) }}"
                    data-languages="{{ optional($configs['menu_account_users'])->languages->toJson() }}"
                    data-item_key="menu_account_users"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_account_users'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_account_wallet') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_account_wallet']) }}"
                    data-languages="{{ optional($configs['menu_account_wallet'])->languages->toJson() }}"
                    data-item_key="menu_account_wallet"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_account_wallet'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_account_settings') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_account_settings']) }}"
                    data-languages="{{ optional($configs['menu_account_settings'])->languages->toJson() }}"
                    data-item_key="menu_account_settings"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_account_settings'] ?? '' }}
                </button>
            </div>
        </div>
    </div>

    <!--Messages-->
    <div class="row mb-4">
        <label class="col-lg-2">{{ __('FsLang::panel.columns_message_rename') }}:</label>
        <div class="col-lg-6">
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_dialogs') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_dialogs']) }}"
                    data-languages="{{ optional($configs['menu_dialogs'])->languages->toJson() }}"
                    data-item_key="menu_dialogs"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_dialogs'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_messages') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_messages']) }}"
                    data-languages="{{ optional($configs['menu_messages'])->languages->toJson() }}"
                    data-item_key="menu_messages"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_messages'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_notifies') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_notifies']) }}"
                    data-languages="{{ optional($configs['menu_notifies'])->languages->toJson() }}"
                    data-item_key="menu_notifies"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_notifies'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_notify_bulletins') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_notify_bulletins']) }}"
                    data-languages="{{ optional($configs['menu_notify_bulletins'])->languages->toJson() }}"
                    data-item_key="menu_notify_bulletins"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_notify_bulletins'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_notify_systems') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_notify_systems']) }}"
                    data-languages="{{ optional($configs['menu_notify_systems'])->languages->toJson() }}"
                    data-item_key="menu_notify_systems"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_notify_systems'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_notify_recommends') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_notify_recommends']) }}"
                    data-languages="{{ optional($configs['menu_notify_recommends'])->languages->toJson() }}"
                    data-item_key="menu_notify_recommends"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_notify_recommends'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_notify_follows') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_notify_follows']) }}"
                    data-languages="{{ optional($configs['menu_notify_follows'])->languages->toJson() }}"
                    data-item_key="menu_notify_follows"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_notify_follows'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_notify_likes') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_notify_likes']) }}"
                    data-languages="{{ optional($configs['menu_notify_likes'])->languages->toJson() }}"
                    data-item_key="menu_notify_likes"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_notify_likes'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_notify_mentions') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_notify_mentions']) }}"
                    data-languages="{{ optional($configs['menu_notify_mentions'])->languages->toJson() }}"
                    data-item_key="menu_notify_mentions"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_notify_mentions'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_notify_comments') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_notify_comments']) }}"
                    data-languages="{{ optional($configs['menu_notify_comments'])->languages->toJson() }}"
                    data-item_key="menu_notify_comments"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_notify_comments'] ?? '' }}
                </button>
            </div>
        </div>
    </div>

    <!--Search-->
    <div class="row mb-4">
        <label class="col-lg-2">{{ __('FsLang::panel.columns_search_rename') }}:</label>
        <div class="col-lg-6">
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_search') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_search']) }}"
                    data-languages="{{ optional($configs['menu_search'])->languages->toJson() }}"
                    data-item_key="menu_search"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_search'] ?? '' }}
                </button>
            </div>
        </div>
    </div>

    <!--Editor-->
    <div class="row mb-4">
        <label class="col-lg-2">{{ __('FsLang::panel.columns_editor_rename') }}:</label>
        <div class="col-lg-6">
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_editor_functions') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_editor_functions']) }}"
                    data-languages="{{ optional($configs['menu_editor_functions'])->languages->toJson() }}"
                    data-item_key="menu_editor_functions"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_editor_functions'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.menu_editor_drafts') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_editor_drafts']) }}"
                    data-languages="{{ optional($configs['menu_editor_drafts'])->languages->toJson() }}"
                    data-item_key="menu_editor_drafts"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_editor_drafts'] ?? '' }}
                </button>
            </div>
        </div>
    </div>

    <!--User Profile-->
    <div class="row mb-4">
        <label class="col-lg-2">{{ __('FsLang::panel.columns_profile_rename') }}:</label>
        <div class="col-lg-6">
            <!--It Interactive-->
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_like_it') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_likes']) }}"
                    data-languages="{{ optional($configs['menu_profile_likes'])->languages->toJson() }}"
                    data-item_key="menu_profile_likes"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_likes'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_dislike_it') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_dislikes']) }}"
                    data-languages="{{ optional($configs['menu_profile_dislikes'])->languages->toJson() }}"
                    data-item_key="menu_profile_dislikes"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_dislikes'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_follow_it') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_followers']) }}"
                    data-languages="{{ optional($configs['menu_profile_followers'])->languages->toJson() }}"
                    data-item_key="menu_profile_followers"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_followers'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_block_it') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_blockers']) }}"
                    data-languages="{{ optional($configs['menu_profile_blockers'])->languages->toJson() }}"
                    data-item_key="menu_profile_blockers"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_blockers'] ?? '' }}
                </button>
            </div>
            <!--It Like-->
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_like_users') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_like_users']) }}"
                    data-languages="{{ optional($configs['menu_profile_like_users'])->languages->toJson() }}"
                    data-item_key="menu_profile_like_users"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_like_users'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_like_groups') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_like_groups']) }}"
                    data-languages="{{ optional($configs['menu_profile_like_groups'])->languages->toJson() }}"
                    data-item_key="menu_profile_like_groups"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_like_groups'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_like_hashtags') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_like_hashtags']) }}"
                    data-languages="{{ optional($configs['menu_profile_like_hashtags'])->languages->toJson() }}"
                    data-item_key="menu_profile_like_hashtags"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_like_hashtags'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_like_posts') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_like_posts']) }}"
                    data-languages="{{ optional($configs['menu_profile_like_posts'])->languages->toJson() }}"
                    data-item_key="menu_profile_like_posts"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_like_posts'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_like_comments') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_like_comments']) }}"
                    data-languages="{{ optional($configs['menu_profile_like_comments'])->languages->toJson() }}"
                    data-item_key="menu_profile_like_comments"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_like_comments'] ?? '' }}
                </button>
            </div>
            <!--It Dislike-->
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_dislike_users') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_dislike_users']) }}"
                    data-languages="{{ optional($configs['menu_profile_dislike_users'])->languages->toJson() }}"
                    data-item_key="menu_profile_dislike_users"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_dislike_users'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_dislike_groups') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_dislike_groups']) }}"
                    data-languages="{{ optional($configs['menu_profile_dislike_groups'])->languages->toJson() }}"
                    data-item_key="menu_profile_dislike_groups"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_dislike_groups'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_dislike_hashtags') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_dislike_hashtags']) }}"
                    data-languages="{{ optional($configs['menu_profile_dislike_hashtags'])->languages->toJson() }}"
                    data-item_key="menu_profile_dislike_hashtags"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_dislike_hashtags'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_dislike_posts') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_dislike_posts']) }}"
                    data-languages="{{ optional($configs['menu_profile_dislike_posts'])->languages->toJson() }}"
                    data-item_key="menu_profile_dislike_posts"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_dislike_posts'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_dislike_comments') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_dislike_comments']) }}"
                    data-languages="{{ optional($configs['menu_profile_dislike_comments'])->languages->toJson() }}"
                    data-item_key="menu_profile_dislike_comments"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_dislike_comments'] ?? '' }}
                </button>
            </div>
            <!--It Follow-->
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_follow_users') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_follow_users']) }}"
                    data-languages="{{ optional($configs['menu_profile_follow_users'])->languages->toJson() }}"
                    data-item_key="menu_profile_follow_users"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_follow_users'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_follow_groups') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_follow_groups']) }}"
                    data-languages="{{ optional($configs['menu_profile_follow_groups'])->languages->toJson() }}"
                    data-item_key="menu_profile_follow_groups"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_follow_groups'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_follow_hashtags') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_follow_hashtags']) }}"
                    data-languages="{{ optional($configs['menu_profile_follow_hashtags'])->languages->toJson() }}"
                    data-item_key="menu_profile_follow_hashtags"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_follow_hashtags'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_follow_posts') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_follow_posts']) }}"
                    data-languages="{{ optional($configs['menu_profile_follow_posts'])->languages->toJson() }}"
                    data-item_key="menu_profile_follow_posts"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_follow_posts'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_follow_comments') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_follow_comments']) }}"
                    data-languages="{{ optional($configs['menu_profile_follow_comments'])->languages->toJson() }}"
                    data-item_key="menu_profile_follow_comments"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_follow_comments'] ?? '' }}
                </button>
            </div>
            <!--It Block-->
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_block_users') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_block_users']) }}"
                    data-languages="{{ optional($configs['menu_profile_block_users'])->languages->toJson() }}"
                    data-item_key="menu_profile_block_users"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_block_users'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_block_groups') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_block_groups']) }}"
                    data-languages="{{ optional($configs['menu_profile_block_groups'])->languages->toJson() }}"
                    data-item_key="menu_profile_block_groups"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_block_groups'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_block_hashtags') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_block_hashtags']) }}"
                    data-languages="{{ optional($configs['menu_profile_block_hashtags'])->languages->toJson() }}"
                    data-item_key="menu_profile_block_hashtags"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_block_hashtags'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_block_posts') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_block_posts']) }}"
                    data-languages="{{ optional($configs['menu_profile_block_posts'])->languages->toJson() }}"
                    data-item_key="menu_profile_block_posts"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_block_posts'] ?? '' }}
                </button>
            </div>
            <div class="input-group mb-3">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.interactive_it_block_comments') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'menu_profile_block_comments']) }}"
                    data-languages="{{ optional($configs['menu_profile_block_comments'])->languages->toJson() }}"
                    data-item_key="menu_profile_block_comments"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['menu_profile_block_comments'] ?? '' }}
                </button>
            </div>
        </div>
    </div>
    <!--columns config end-->

    <!-- Language Modal -->
    <div class="modal fade" id="configLangModal" tabindex="-1" aria-labelledby="configLangModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.sidebar_columns') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        @csrf
                        @method('put')
                        <input type="hidden" name="update_config" value="">
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
                                            <td><input type="text" name="languages[{{ $lang['langTag'] }}]" class="form-control" value="{{ $langParams['menu_name'][$lang['langTag']] ?? '' }}"></td>
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
@endsection
