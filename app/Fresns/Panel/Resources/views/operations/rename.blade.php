@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::operations.sidebar')
@endsection

@section('content')
    <!--rename header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_rename') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_rename_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <!--rename_user_config-->
    <div class="row mb-3">
        <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.rename_user_config') }}:</label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_user_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'user_name']) }}"
                    data-languages="{{ optional($configs['user_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="user_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['user_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_user_name_desc') }}</div>
    </div>
    <div class="row mb-3">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_user_uid_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'user_uid_name']) }}"
                    data-languages="{{ optional($configs['user_uid_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="user_uid_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['user_uid_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_user_uid_name_desc') }}</div>
    </div>
    <div class="row mb-3">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_user_username_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'user_username_name']) }}"
                    data-languages="{{ optional($configs['user_username_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="user_username_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['user_username_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_user_username_name_desc') }}</div>
    </div>
    <div class="row mb-3">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_user_nickname_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'user_nickname_name']) }}"
                    data-languages="{{ optional($configs['user_nickname_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="user_nickname_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['user_nickname_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_user_nickname_name_desc') }}</div>
    </div>
    <div class="row mb-5">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_user_role_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'user_role_name']) }}"
                    data-languages="{{ optional($configs['user_role_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="user_role_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['user_role_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_user_role_name_desc') }}</div>
    </div>
    <!--rename_content_config-->
    <div class="row mb-3">
        <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.rename_content_config') }}:</label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_group_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'group_name']) }}"
                    data-languages="{{ optional($configs['group_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="group_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['group_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_group_name_desc') }}</div>
    </div>
    <div class="row mb-3">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_hashtag_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'hashtag_name']) }}"
                    data-languages="{{ optional($configs['hashtag_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="hashtag_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['hashtag_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_hashtag_name_desc') }}</div>
    </div>
    <div class="row mb-3">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_post_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'post_name']) }}"
                    data-languages="{{ optional($configs['post_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="post_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['post_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_post_name_desc') }}</div>
    </div>
    <div class="row mb-5">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_comment_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'comment_name']) }}"
                    data-languages="{{ optional($configs['comment_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="comment_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['comment_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_comment_name_desc') }}</div>
    </div>
    <!--rename_publish_config-->
    <div class="row mb-3">
        <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.rename_publish_config') }}:</label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_publish_post_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'publish_post_name']) }}"
                    data-languages="{{ optional($configs['publish_post_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="publish_post_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['publish_post_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_publish_post_name_desc') }}</div>
    </div>
    <div class="row mb-5">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_publish_comment_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'publish_comment_name']) }}"
                    data-languages="{{ optional($configs['publish_comment_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="publish_comment_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['publish_comment_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_publish_comment_name_desc') }}</div>
    </div>
    <!--rename_like_config-->
    <div class="row mb-3">
        <label class="col-lg-2">{{ __('FsLang::panel.rename_like_config') }}:</label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_like_user_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'like_user_name']) }}"
                    data-languages="{{ optional($configs['like_user_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="like_user_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['like_user_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_like_user_name_desc') }}</div>
    </div>
    <div class="row mb-3">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_like_group_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'like_group_name']) }}"
                    data-languages="{{ optional($configs['like_group_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="like_group_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['like_group_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_like_group_name_desc') }}</div>
    </div>
    <div class="row mb-3">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_like_hashtag_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'like_hashtag_name']) }}"
                    data-languages="{{ optional($configs['like_hashtag_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="like_hashtag_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['like_hashtag_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_like_hashtag_name_desc') }}</div>
    </div>
    <div class="row mb-3">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_like_post_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'like_post_name']) }}"
                    data-languages="{{ optional($configs['like_post_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="like_post_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['like_post_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_like_post_name_desc') }}</div>
    </div>
    <div class="row mb-5">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_like_comment_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'like_comment_name']) }}"
                    data-languages="{{ optional($configs['like_comment_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="like_comment_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['like_comment_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_like_comment_name_desc') }}</div>
    </div>
    <!--rename_follow_config-->
    <div class="row mb-3">
        <label class="col-lg-2">{{ __('FsLang::panel.rename_follow_config') }}:</label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_follow_user_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'follow_user_name']) }}"
                    data-languages="{{ optional($configs['follow_user_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="follow_user_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['follow_user_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_follow_user_name_desc') }}</div>
    </div>
    <div class="row mb-3">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_follow_group_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'follow_group_name']) }}"
                    data-languages="{{ optional($configs['follow_group_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="follow_group_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['follow_group_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_follow_group_name_desc') }}</div>
    </div>
    <div class="row mb-3">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_follow_hashtag_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'follow_hashtag_name']) }}"
                    data-languages="{{ optional($configs['follow_hashtag_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="follow_hashtag_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['follow_hashtag_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_follow_hashtag_name_desc') }}</div>
    </div>
    <div class="row mb-3">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_follow_post_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'follow_post_name']) }}"
                    data-languages="{{ optional($configs['follow_post_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="follow_post_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['follow_post_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_follow_post_name_desc') }}</div>
    </div>
    <div class="row mb-5">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_follow_comment_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'follow_comment_name']) }}"
                    data-languages="{{ optional($configs['follow_comment_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="follow_comment_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['follow_comment_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_follow_comment_name_desc') }}</div>
    </div>
    <!--rename_block_config-->
    <div class="row mb-3">
        <label class="col-lg-2">{{ __('FsLang::panel.rename_block_config') }}:</label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_block_user_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'block_user_name']) }}"
                    data-languages="{{ optional($configs['block_user_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="block_user_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['block_user_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_block_user_name_desc') }}</div>
    </div>
    <div class="row mb-3">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_block_group_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'block_group_name']) }}"
                    data-languages="{{ optional($configs['block_group_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="block_group_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['block_group_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_block_group_name_desc') }}</div>
    </div>
    <div class="row mb-3">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_block_hashtag_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'block_hashtag_name']) }}"
                    data-languages="{{ optional($configs['block_hashtag_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="block_hashtag_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['block_hashtag_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_block_hashtag_name_desc') }}</div>
    </div>
    <div class="row mb-3">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_block_post_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'block_post_name']) }}"
                    data-languages="{{ optional($configs['block_post_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="block_post_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['block_post_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_block_post_name_desc') }}</div>
    </div>
    <div class="row mb-5">
        <label class="col-lg-2"></label>
        <div class="col-lg-6">
            <div class="input-group">
                <label class="input-group-text rename-label">{{ __('FsLang::panel.rename_block_comment_name') }}</label>
                <button class="btn btn-outline-secondary text-start rename-btn" type="button" data-bs-toggle="modal"
                    data-action="{{ route('panel.languages.batch.update', ['itemKey' => 'block_comment_name']) }}"
                    data-languages="{{ optional($configs['block_comment_name'] ?? null)?->languages?->toJson() }}"
                    data-item_key="block_comment_name"
                    data-bs-target="#configLangModal">{{ $defaultLangParams['block_comment_name'] ?? '' }}
                </button>
            </div>
        </div>
        <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.rename_block_comment_name_desc') }}</div>
    </div>

    <!-- Language Modal -->
    <div class="modal fade" id="configLangModal" tabindex="-1" aria-labelledby="configLangModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.sidebar_rename') }}</h5>
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
                                            <td>{{ $lang['langName'] }} @if ($lang['areaCode'])
                                                    ({{ optional($areaCodes->where('code', $lang['areaCode'])->first())['localName'] }})
                                                @endif
                                            </td>
                                            <td><input type="text" name="languages[{{ $lang['langTag'] }}]" class="form-control" value=""></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!--button_save-->
                        <div class="text-center">
                            <button type="submit" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
