@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--paths header-->
    <div class="row mb-5 border-bottom">
        <div class="col-lg-9">
            <h3>{{ __('FsLang::panel.sidebar_paths') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_paths_intro') }}</p>
        </div>
        <div class="col-lg-3">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>

    <!--paths config-->
    <form action="{{ route('panel.paths.update') }}" method="post">
        @csrf
        @method('put')

        <!--portal-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.portal') }}:</label>
            <div class="col-lg-8">
                <div class="input-group">
                    <span class="input-group-text">{{ $siteUrl.'/' }}</span>
                    <input type="text" class="form-control" name="website_portal_path" value="{{ $params['website_portal_path'] }}" required placeholder="portal">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_view') }}</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_portal_path'] }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!--user-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.user') }}:</label>
            <div class="col-lg-8">
                <div class="input-group">
                    <span class="input-group-text">{{ $siteUrl.'/' }}</span>
                    <input type="text" class="form-control" name="website_user_path" value="{{ $params['website_user_path'] }}" required placeholder="users">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_view') }}</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_user_path'] }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_user_path'].'/list' }}</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_user_path'].'/likes' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_user_path'].'/dislikes' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_user_path'].'/following' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_user_path'].'/blocking' }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!--group-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.group') }}:</label>
            <div class="col-lg-8">
                <div class="input-group">
                    <span class="input-group-text">{{ $siteUrl.'/' }}</span>
                    <input type="text" class="form-control" name="website_group_path" value="{{ $params['website_group_path'] }}" required placeholder="groups">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_view') }}</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_group_path'] }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_group_path'].'/list' }}</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_group_path'].'/likes' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_group_path'].'/dislikes' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_group_path'].'/following' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_group_path'].'/blocking' }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!--hashtag-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.hashtag') }}:</label>
            <div class="col-lg-8">
                <div class="input-group">
                    <span class="input-group-text">{{ $siteUrl.'/' }}</span>
                    <input type="text" class="form-control" name="website_hashtag_path" value="{{ $params['website_hashtag_path'] }}" required placeholder="hashtags">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_view') }}</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_hashtag_path'] }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_hashtag_path'].'/list' }}</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_hashtag_path'].'/likes' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_hashtag_path'].'/dislikes' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_hashtag_path'].'/following' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_hashtag_path'].'/blocking' }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!--post-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.post') }}:</label>
            <div class="col-lg-8">
                <div class="input-group">
                    <span class="input-group-text">{{ $siteUrl.'/' }}</span>
                    <input type="text" class="form-control" name="website_post_path" value="{{ $params['website_post_path'] }}" required placeholder="posts">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_view') }}</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_post_path'] }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_post_path'].'/list' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_post_path'].'/nearby' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_post_path'].'/location' }}</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_post_path'].'/likes' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_post_path'].'/dislikes' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_post_path'].'/following' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_post_path'].'/blocking' }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!--comment-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.comment') }}:</label>
            <div class="col-lg-8">
                <div class="input-group">
                    <span class="input-group-text">{{ $siteUrl.'/' }}</span>
                    <input type="text" class="form-control" name="website_comment_path" value="{{ $params['website_comment_path'] }}" required placeholder="comments">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_view') }}</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_comment_path'] }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_comment_path'].'/list' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_comment_path'].'/nearby' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_comment_path'].'/location' }}</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_comment_path'].'/likes' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_comment_path'].'/dislikes' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_comment_path'].'/following' }}</span></li>
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_comment_path'].'/blocking' }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!--user_detail-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.user_detail') }}:</label>
            <div class="col-lg-8">
                <div class="input-group">
                    <span class="input-group-text">{{ $siteUrl.'/' }}</span>
                    <input type="text" class="form-control" name="website_user_detail_path" value="{{ $params['website_user_detail_path'] }}" required placeholder="user">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_view') }}</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_user_detail_path'].'/jarvis' }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!--group_detail-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.group_detail') }}:</label>
            <div class="col-lg-8">
                <div class="input-group">
                    <span class="input-group-text">{{ $siteUrl.'/' }}</span>
                    <input type="text" class="form-control" name="website_group_detail_path" value="{{ $params['website_group_detail_path'] }}" required placeholder="group">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_view') }}</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_group_detail_path'].'/123456' }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!--hashtag_detail-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.hashtag_detail') }}:</label>
            <div class="col-lg-8">
                <div class="input-group">
                    <span class="input-group-text">{{ $siteUrl.'/' }}</span>
                    <input type="text" class="form-control" name="website_hashtag_detail_path" value="{{ $params['website_hashtag_detail_path'] }}" required placeholder="hashtag">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_view') }}</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_hashtag_detail_path'].'/123456' }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!--post_detail-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.post_detail') }}:</label>
            <div class="col-lg-8">
                <div class="input-group">
                    <span class="input-group-text">{{ $siteUrl.'/' }}</span>
                    <input type="text" class="form-control" name="website_post_detail_path" value="{{ $params['website_post_detail_path'] }}" required placeholder="post">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_view') }}</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_post_detail_path'].'/123456' }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!--comment_detail-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.comment_detail') }}:</label>
            <div class="col-lg-8">
                <div class="input-group">
                    <span class="input-group-text">{{ $siteUrl.'/' }}</span>
                    <input type="text" class="form-control" name="website_comment_detail_path" value="{{ $params['website_comment_detail_path'] }}" required placeholder="comment">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_view') }}</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text">{{ $siteUrl.'/'.$params['website_comment_detail_path'].'/123456' }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!--button_save-->
        <div class="row my-4">
            <div class="col-lg-2"></div>
            <div class="col-lg-8">
                <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
            </div>
        </div>
    </form>
@endsection
