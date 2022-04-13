@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::dashboard.sidebar')
@endsection

@section('content')
    <!--Dashboard-->
    <div class="row mb-4 ">
        <div class="col-lg-7">
            <h1 class="fs-3 fw-normal">{{ __('FsLang::panel.welcome') }}</h1>
            <p class="text-secondary">
                {{ __('FsLang::panel.current_version') }} v{{$currentVersion['version'] ?? ''}}
                @if (($currentVersion['versionInt'] ?? 0) != ($newVersion['versionInt'] ?? 0))
                    <a href="{{ route('panel.upgrades') }}" class="badge rounded-pill bg-danger ms-2 text-decoration-none">{{ __('FsLang::panel.new_version') }}</a>
                @endif
            </p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-success" href="{{ route('panel.cache.clear') }}" role="button">{{ __('FsLang::panel.button_clear_cache') }}</a>
            </div>
        </div>
    </div>
    <!--Dashboard data-->
    <div class="row mb-3">
        <div class="col-md mb-4 pe-lg-5">
            <h3 class="h6">{{ __('FsLang::panel.overview') }}</h3>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <i class="bi bi-person-fill"></i> {{ __('FsLang::panel.overview_accounts') }}
                    <span class="badge bg-success">{{ $params['accounts_count'] }}</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-people"></i> {{ __('FsLang::panel.overview_users') }}
                    <span class="badge bg-success">{{ $params['users_count'] }}</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-collection"></i> {{ __('FsLang::panel.overview_groups') }}
                    <span class="badge bg-success">{{ $params['groups_count'] }}</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-hash"></i> {{ __('FsLang::panel.overview_hashtags') }}
                    <span class="badge bg-success">{{ $params['hashtags_count'] }}</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-postcard"></i> {{ __('FsLang::panel.overview_posts') }}
                    <span class="badge bg-success">{{ $params['posts_count'] }}</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-chat-right-dots"></i> {{ __('FsLang::panel.overview_comments') }}
                    <span class="badge bg-success">{{ $params['comments_count'] }}</span>
                </li>
            </ul>
        </div>
        <div class="col-md mb-4 pe-lg-5">
            <h3 class="h6">{{ __('FsLang::panel.extensions') }}</h3>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <i class="bi bi-key"></i> {{ __('FsLang::panel.extensions_admins') }}
                    <span class="badge bg-info">{{ $adminCount }}</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-person"></i> {{ __('FsLang::panel.extensions_keys') }}
                    <span class="badge bg-info">{{ $keyCount }}</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-journal-code"></i> {{ __('FsLang::panel.extensions_plugins') }}
                    <span class="badge bg-info">{{ $plugins->where('type', 1)->count() }}</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-phone"></i> {{ __('FsLang::panel.extensions_apps') }}
                    <span class="badge bg-info">{{ $plugins->where('type', 2)->count() }}</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-laptop"></i> {{ __('FsLang::panel.extensions_engines') }}
                    <span class="badge bg-info">{{ $plugins->where('type', 3)->count() }}</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-brush"></i> {{ __('FsLang::panel.extensions_themes') }}
                    <span class="badge bg-info">{{ $plugins->where('type', 4)->count() }}</span>
                </li>
            </ul>
        </div>
        <div class="col-md mb-4 pe-lg-5">
            <h3 class="h6">{{ __('FsLang::panel.support') }}</h3>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <a class="fresns-link" href="https://fresns.org" target="_blank">{{ __('FsLang::panel.support_website') }}</a>
                </li>
                <li class="list-group-item">
                    <a class="fresns-link" href="https://fresns.org/community/team.html" target="_blank">{{ __('FsLang::panel.support_teams') }}</a>
                </li>
                <li class="list-group-item">
                    <a class="fresns-link" href="https://fresns.org/community/partners.html" target="_blank">{{ __('FsLang::panel.support_partners') }}</a>
                </li>
                <li class="list-group-item">
                    <a class="fresns-link" href="https://fresns.org/community/join.html" target="_blank">{{ __('FsLang::panel.support_join') }}</a>
                </li>
                <li class="list-group-item">
                    <a class="fresns-link" href="https://discuss.fresns.org" target="_blank">{{ __('FsLang::panel.support_community') }}</a>
                </li>
                <li class="list-group-item">
                    <a class="fresns-link" href="https://fresns.market" target="_blank">{{ __('FsLang::panel.support_market') }}</a>
                </li>
            </ul>
        </div>
    </div>
    <!--row-->
    <div class="row">
        <div class="col-md mb-4">
            <div class="card">
                <div class="card-header">{{ __('FsLang::panel.system_info') }}</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            {{ __('FsLang::panel.system_info_server') }}: <span>{{ $systemInfo['server'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            {{ __('FsLang::panel.system_info_web') }}: <span>{{ $systemInfo['web'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            {{ __('FsLang::panel.system_info_php_version') }}: <span>{{ $systemInfo['php']['version'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            {{ __('FsLang::panel.system_info_php_upload_max_filesize') }}: <span>{{ $systemInfo['php']['uploadMaxFileSize'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            {{ __('FsLang::panel.system_info_database_version') }}: <span>{{ $systemInfo['database']['version'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            {{ __('FsLang::panel.system_info_database_timezone') }}:
                            <span>
                                {{ $systemInfo['database']['timezone'] }}
                                @if ($systemInfo['database']['timezone'] == $systemInfo['database']['timezoneFromEnv'])
                                    <span class="badge rounded-pill bg-success ms-2 fs-9">.env {{ $systemInfo['database']['timezone'] }}</span>
                                @else
                                    <span class="badge rounded-pill bg-danger ms-2 fs-9"><span class="spinner-grow spinner-grow-sm align-text-top" role="status" aria-hidden="true"></span> .env {{ $systemInfo['database']['timezoneFromEnv'] }}</span>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            {{ __('FsLang::panel.system_info_database_collation') }}: <span>{{ $systemInfo['database']['collation'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            {{ __('FsLang::panel.system_info_database_size') }}: <span>{{ $systemInfo['database']['size'] }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md mb-4">
            <div class="card">
                <div class="card-header">{{ __('FsLang::panel.news') }}</div>
                <div class="card-body">
                    {!! $news['content'] ?? '' !!}
                </div>
            </div>
        </div>
    </div>
@endsection
