@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::extends.sidebar')
@endsection

@section('content')
    <!--content_handler header-->
    <div class="row mb-5 border-bottom">
        <div class="col-lg-9">
            <h3>{{ __('FsLang::panel.sidebar_extend_content_handler') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_extend_content_handler_intro') }}</p>
        </div>
        <div class="col-lg-3">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>

    <!--content_handler config-->
    <form action="{{ route('panel.content-handler.update') }}" method="post">
        @csrf
        @method('put')

        <!--content handler-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.extend_content_service') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.extend_content_notify_service') }}</label>
                    <select class="form-select" name="notify_service">
                        <option value="" {{ !$params['notify_service'] ? 'selected' : '' }}>{{ __('FsLang::panel.option_default') }}</option>
                        @foreach ($pluginParams['extendData'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['notify_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.extend_content_ip') }}</label>
                    <select class="form-select" name="ip_service">
                        <option value="" {{ !$params['ip_service'] ? 'selected' : '' }}>⛔️ {{ __('FsLang::panel.option_close') }}</option>
                        @foreach ($pluginParams['extendIp'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['ip_service'] == $plugin->unikey ? 'selected' : '' }}> {{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.extend_content_review') }}</label>
                    <select class="form-select" name="content_review_service">
                        <option value="" {{ !$params['content_review_service'] ? 'selected' : '' }}>⛔️ {{ __('FsLang::panel.option_close') }}</option>
                        @foreach ($pluginParams['extendReview'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['content_review_service'] == $plugin->unikey ? 'selected' : '' }}> {{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!--content list-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.extend_content_list') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.extend_content_list_by_all') }}</label>
                    <select class="form-select" name="content_list_service">
                        <option value="" {{ !$params['content_list_service'] ? 'selected' : '' }}>{{ __('FsLang::panel.option_default') }}</option>
                        @foreach ($pluginParams['extendData'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['content_list_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.extend_content_list_by_follow') }}</label>
                    <select class="form-select" name="content_follow_service">
                        <option value="" {{ !$params['content_follow_service'] ? 'selected' : '' }}>{{ __('FsLang::panel.option_default') }}</option>
                        @foreach ($pluginParams['extendData'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['content_follow_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.extend_content_list_by_nearby') }}</label>
                    <select class="form-select" name="content_nearby_service">
                        <option value="" {{ !$params['content_nearby_service'] ? 'selected' : '' }}>{{ __('FsLang::panel.option_default') }}</option>
                        @foreach ($pluginParams['extendData'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['content_nearby_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.extend_content_list_desc') }}</div>
        </div>

        <!--content detail-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.extend_content_detail') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.extend_content_by_detail') }}</label>
                    <select class="form-select" name="content_detail_service">
                        <option value="" {{ !$params['search_users_service'] ? 'selected' : '' }}>{{ __('FsLang::panel.option_default') }}</option>
                        @foreach ($pluginParams['extendData'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['content_detail_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!--content search-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.extend_content_search') }}:</label>
            <div class="col-lg-6">
                <!--users-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.extend_content_search_users') }}</label>
                    <select class="form-select" name="search_users_service">
                        <option value="" {{ !$params['search_users_service'] ? 'selected' : '' }}>⛔️ {{ __('FsLang::panel.option_close') }}</option>
                        @foreach ($pluginParams['extendSearch'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['search_users_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!--groups-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.extend_content_search_groups') }}</label>
                    <select class="form-select" name="search_groups_service">
                        <option value="" {{ !$params['search_groups_service'] ? 'selected' : '' }}>⛔️ {{ __('FsLang::panel.option_close') }}</option>
                        @foreach ($pluginParams['extendSearch'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['search_groups_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!--hashtags-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.extend_content_search_hashtags') }}</label>
                    <select class="form-select" name="search_hashtags_service">
                        <option value="" {{ !$params['search_hashtags_service'] ? 'selected' : '' }}>⛔️ {{ __('FsLang::panel.option_close') }}</option>
                        @foreach ($pluginParams['extendSearch'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['search_hashtags_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!--posts-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.extend_content_search_posts') }}</label>
                    <select class="form-select" name="search_posts_service">
                        <option value="" {{ !$params['search_posts_service'] ? 'selected' : '' }}>⛔️ {{ __('FsLang::panel.option_close') }}</option>
                        @foreach ($pluginParams['extendSearch'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['search_posts_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!--comments-->
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.extend_content_search_comments') }}</label>
                    <select class="form-select" name="search_comments_service">
                        <option value="" {{ !$params['search_comments_service'] ? 'selected' : '' }}>⛔️ {{ __('FsLang::panel.option_close') }}</option>
                        @foreach ($pluginParams['extendSearch'] as $plugin)
                            <option value="{{ $plugin->unikey }}" {{ $params['search_comments_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
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
