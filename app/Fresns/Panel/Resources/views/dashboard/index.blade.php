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
                @if ($checkVersion)
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
                    <span class="badge bg-success">{{ $overview['accountCount'] }}</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-people"></i> {{ __('FsLang::panel.overview_users') }}
                    <span class="badge bg-success">{{ $overview['userCount'] }}</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-collection"></i> {{ __('FsLang::panel.overview_groups') }}
                    <span class="badge bg-success">{{ $overview['groupCount'] }}</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-hash"></i> {{ __('FsLang::panel.overview_hashtags') }}
                    <span class="badge bg-success">{{ $overview['hashtagCount'] }}</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-postcard"></i> {{ __('FsLang::panel.overview_posts') }}
                    <span class="badge bg-success">{{ $overview['postCount'] }}</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-chat-right-dots"></i> {{ __('FsLang::panel.overview_comments') }}
                    <span class="badge bg-success">{{ $overview['commentCount'] }}</span>
                </li>
            </ul>
        </div>
        <div class="col-md mb-4 pe-lg-5">
            <h3 class="h6">{{ __('FsLang::panel.extensions') }}</h3>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <i class="bi bi-person"></i> {{ __('FsLang::panel.extensions_admins') }}
                    <a href="{{ route('panel.admins.index') }}">
                        <span class="badge bg-info">{{ $adminCount }}</span>
                    </a>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-key"></i> {{ __('FsLang::panel.extensions_keys') }}
                    <a href="{{ route('panel.keys.index') }}">
                        <span class="badge bg-info">{{ $keyCount }}</span>
                    </a>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-journal-code"></i> {{ __('FsLang::panel.extensions_plugins') }}
                    <a href="{{ route('panel.plugin.index') }}">
                        <span class="badge bg-info">{{ $plugins->where('type', 1)->count() }}</span>
                    </a>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-phone"></i> {{ __('FsLang::panel.extensions_apps') }}
                    <a href="{{ route('panel.app.index') }}">
                        <span class="badge bg-info">{{ $plugins->where('type', 2)->count() }}</span>
                    </a>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-laptop"></i> {{ __('FsLang::panel.extensions_engines') }}
                    <a href="{{ route('panel.engine.index') }}">
                        <span class="badge bg-info">{{ $plugins->where('type', 3)->count() }}</span>
                    </a>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-brush"></i> {{ __('FsLang::panel.extensions_themes') }}
                    <a href="{{ route('panel.theme.index') }}">
                        <span class="badge bg-info">{{ $plugins->where('type', 4)->count() }}</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="col-md mb-4 pe-lg-5">
            <h3 class="h6">{{ __('FsLang::panel.support') }}</h3>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <a class="fresns-link" href="https://fresns.cn" target="_blank">{{ __('FsLang::panel.support_website') }}</a>
                </li>
                <li class="list-group-item">
                    <a class="fresns-link" href="https://fresns.cn/community/teams.html" target="_blank">{{ __('FsLang::panel.support_teams') }}</a>
                </li>
                <li class="list-group-item">
                    <a class="fresns-link" href="https://fresns.cn/community/partners.html" target="_blank">{{ __('FsLang::panel.support_partners') }}</a>
                </li>
                <li class="list-group-item">
                    <a class="fresns-link" href="https://fresns.cn/community/join.html" target="_blank">{{ __('FsLang::panel.support_join') }}</a>
                </li>
                <li class="list-group-item">
                    <a class="fresns-link" href="https://discuss.fresns.cn" target="_blank">{{ __('FsLang::panel.support_community') }}</a>
                </li>
                <li class="list-group-item">
                    <a class="fresns-link" href="https://market.fresns.cn" target="_blank">{{ __('FsLang::panel.support_market') }}</a>
                </li>
            </ul>
        </div>
    </div>
    <!--row-->
    <div class="row">
        <!--system info-->
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
                            {{ __('FsLang::panel.system_info_php_cli_info') }}: <span><a data-bs-toggle="modal" href="#phpCliModal" role="button">{{ __('FsLang::panel.button_view') }}</a></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            {{ __('FsLang::panel.system_info_php_upload_max_filesize') }}: <span>{{ $systemInfo['php']['uploadMaxFileSize'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            {{ __('FsLang::panel.system_info_composer_version') }}: <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $systemInfo['composer']['versionInfo'] }}">{{ $systemInfo['composer']['version'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            {{ __('FsLang::panel.system_info_composer_info') }}: <span><a class="composer_info" data-bs-toggle="modal" href="#composerInfoModal" role="button">{{ __('FsLang::panel.button_view') }}</a></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            {{ __('FsLang::panel.system_info_database_version') }}: <span>{{ $databaseInfo['version'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            {{ __('FsLang::panel.system_info_database_timezone') }}: <span><a data-bs-toggle="modal" href="#timezoneListModal" role="button">{{ $databaseInfo['timezone'] }}</a></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            {{ __('FsLang::panel.system_info_env_timezone') }}:
                            <span @if ($databaseInfo['timezone'] != $databaseInfo['envTimezoneToUtc']) data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::tips.timezone_error') }}" @endif>
                                @if ($databaseInfo['timezone'] !== $databaseInfo['envTimezoneToUtc'])
                                    <span class="spinner-grow spinner-grow-sm text-danger" role="status" aria-hidden="true"></span>
                                    <a data-bs-toggle="modal" href="#timezoneNameModal" role="button">{{ $databaseInfo['envTimezone'] }}</a>
                                @else
                                    {{ $databaseInfo['envTimezone'] }}
                                @endif
                                <span class="badge rounded-pill bg-secondary ms-2 fs-9">{{ $databaseInfo['envTimezoneToUtc'] }}</span>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            {{ __('FsLang::panel.system_info_database_collation') }}: <span>{{ $databaseInfo['collation'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            {{ __('FsLang::panel.system_info_database_size') }}: <span>{{ $databaseInfo['sizeMb'].' MB' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!--news-->
        <div class="col-md mb-4">
            <div class="card">
                <div class="card-header">{{ __('FsLang::panel.news') }}</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach ($newsList as $news)
                            <li class="list-group-item">
                                <span class="badge bg-warning text-dark">{{ $news['date'] }}</span>
                                <a class="fresns-link ms-2" href="{{ $news['link'] }}" target="_blank" {{ 'style=color:'.$news['color'] }}>{{ $news['title'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: php cli info -->
    <div class="modal fade" id="phpCliModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.system_info_php_cli_info') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {!! nl2br($systemInfo['php']['cliInfo']) !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: composer info -->
    <div class="modal fade" id="composerInfoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.system_info_composer_info') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5 class="text-success fs-6 fw-normal">Composer Diagnose</h5>
                    <pre class="composer_diagnose">{{ __('FsLang::tips.request_in_progress') }}</pre>
                    <hr>
                    <h5 class="text-success fs-6 fw-normal">Composer Config List</h5>
                    <pre class="composer_config_list">{{ __('FsLang::tips.request_in_progress') }}</pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: timezone name list -->
    <div class="modal fade" id="timezoneListModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span class="badge bg-primary">{{ $databaseInfo['timezone'] }}</span>
                        {{ __('FsLang::panel.system_info_env_timezone_list') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush">
                        @foreach ($timezones as $timezone)
                            <li class="list-group-item">{{ $timezone }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: timezone name -->
    <div class="modal fade" id="timezoneNameModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::tips.timezone_error') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('FsLang::panel.system_info_database_timezone') }}: <code>{{ $databaseInfo['timezone'] }}</code></p>
                    <p>{{ __('FsLang::panel.system_info_env_timezone_name') }}: <code>{{ $databaseInfo['envTimezone'] }}</code></p>
                    <p>{{ __('FsLang::panel.system_info_env_timezone_utc') }}: <code>{{ $databaseInfo['envTimezoneToUtc'] }}</code></p>
                    <p>{{ __('FsLang::tips.timezone_env_edit_tip') }} <code>DB_TIMEZONE</code></p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item list-group-item-info">
                            <span class="badge bg-primary">{{ $databaseInfo['timezone'] }}</span>
                            {{ __('FsLang::panel.system_info_env_timezone_list') }}
                        </li>
                        @foreach ($timezones as $timezone)
                            <li class="list-group-item">{{ $timezone }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $('.composer_info').click(function (event) {
            event.preventDefault();
            $.ajax({
                method: 'get',
                url: '/fresns/composer/diagnose',
                success: function (response) {
                    console.log('composer diagnose info', response)
                    $('.composer_diagnose').html(response)
                },
                error: function (response) {
                    $('.composer_diagnose').html("{{ __('FsLang::tips.requestFailure') }}")
                    window.tips("{{ __('FsLang::tips.requestFailure') }}");
                },
            });
            $.ajax({
                method: 'get',
                url: '/fresns/composer/config',
                success: function (response) {
                    console.log('composer config info', response)
                    $('.composer_config_list').html(response)
                },
                error: function (response) {
                    $('.composer_diagnose').html("{{ __('FsLang::tips.requestFailure') }}")
                    window.tips("{{ __('FsLang::tips.requestFailure') }}");
                },
            });
        });
    </script>
@endsection
