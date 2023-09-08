@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::dashboard.sidebar')
@endsection

@section('content')
    <!--Dashboard-->
    <div class="row mb-4 ">
        <h1 class="fs-3 fw-normal">{{ __('FsLang::panel.welcome') }}</h1>
        <p class="text-secondary">
            {{ __('FsLang::panel.current_version') }} v{{$currentVersion['version'] ?? ''}}

            <a href="{{ route('panel.upgrades') }}" class="badge rounded-pill bg-danger ms-2 text-decoration-none" id="checkVersion" style="display: none">{{ __('FsLang::panel.new_version') }}</a>
        </p>
    </div>

    <!--Dashboard data-->
    <div class="row mb-3">
        <div class="col-md mb-4 pe-lg-5">
            <h3 class="h6">{{ __('FsLang::panel.overview') }}</h3>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <i class="bi bi-person-fill"></i> {{ __('FsLang::panel.overview_accounts') }}
                    <span class="badge bg-success" id="accountCount">0</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-people"></i> {{ __('FsLang::panel.overview_users') }}
                    <span class="badge bg-success" id="userCount">0</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-collection"></i> {{ __('FsLang::panel.overview_groups') }}
                    <span class="badge bg-success" id="groupCount">0</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-hash"></i> {{ __('FsLang::panel.overview_hashtags') }}
                    <span class="badge bg-success" id="hashtagCount">0</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-postcard"></i> {{ __('FsLang::panel.overview_posts') }}
                    <span class="badge bg-success" id="postCount">0</span>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-chat-right-dots"></i> {{ __('FsLang::panel.overview_comments') }}
                    <span class="badge bg-success" id="commentCount">0</span>
                </li>
            </ul>
        </div>
        <div class="col-md mb-4 pe-lg-5">
            <h3 class="h6">{{ __('FsLang::panel.extensions') }}</h3>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <i class="bi bi-person"></i> {{ __('FsLang::panel.sidebar_admins') }}
                    <a href="{{ route('panel.admins.index') }}">
                        <span class="badge bg-info">{{ $adminCount }}</span>
                    </a>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-key"></i> {{ __('FsLang::panel.sidebar_keys') }}
                    <a href="{{ route('panel.keys.index') }}">
                        <span class="badge bg-info">{{ $keyCount }}</span>
                    </a>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-journal-code"></i> {{ __('FsLang::panel.sidebar_plugins') }}
                    <a href="{{ route('panel.plugins.index') }}">
                        <span class="badge bg-info">{{ $plugins->where('type', 1)->count() }}</span>
                    </a>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-layers"></i> {{ __('FsLang::panel.sidebar_panels') }}
                    <a href="{{ route('panel.panels.index') }}">
                        <span class="badge bg-info">{{ $plugins->where('type', 2)->count() }}</span>
                    </a>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-laptop"></i> {{ __('FsLang::panel.sidebar_engines') }}
                    <a href="{{ route('panel.engines.index') }}">
                        <span class="badge bg-info">{{ $plugins->where('type', 3)->count() }}</span>
                    </a>
                </li>
                <li class="list-group-item">
                    <i class="bi bi-palette"></i> {{ __('FsLang::panel.sidebar_themes') }}
                    <a href="{{ route('panel.themes.index') }}">
                        <span class="badge bg-info">{{ $plugins->where('type', 4)->count() }}</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="col-md mb-4 pe-lg-5">
            <h3 class="h6">{{ __('FsLang::panel.support') }}</h3>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <a class="fresns-link" href="{{ $docsUrl }}" target="_blank">{{ __('FsLang::panel.support_website') }}</a>
                </li>
                <li class="list-group-item">
                    <a class="fresns-link" href="{{ $docsUrl.'/community/teams.html' }}" target="_blank">{{ __('FsLang::panel.support_teams') }}</a>
                </li>
                <li class="list-group-item">
                    <a class="fresns-link" href="{{ $docsUrl.'/community/sponsor.html' }}" target="_blank">{{ __('FsLang::panel.support_sponsor') }}</a>
                </li>
                <li class="list-group-item">
                    <a class="fresns-link" href="{{ $docsUrl.'/community/join.html' }}" target="_blank">{{ __('FsLang::panel.support_join') }}</a>
                </li>
                <li class="list-group-item">
                    <a class="fresns-link" href="{{ $communityUrl }}" target="_blank">{{ __('FsLang::panel.support_community') }}</a>
                </li>
                <li class="list-group-item">
                    <a class="fresns-link" href="{{ $marketplaceUrl }}" target="_blank">{{ __('FsLang::panel.support_marketplace') }}</a>
                </li>
            </ul>
        </div>
    </div>

    <!--row-->
    <div class="row">
        <!--system info-->
        <div class="col-md">
            <div class="card mb-4">
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
                            {{ __('FsLang::panel.system_info_database_driver') }}: <span>{{ $databaseInfo['name'] }}</span>
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
                            {{ __('FsLang::panel.system_info_database_size') }}: <span>{{ $databaseInfo['size'] }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!--tips and news-->
        <div class="col-md">
            <!--tips-->
            <div class="card mb-4">
                <div class="card-header">{{ __('FsLang::panel.tips') }}</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item text-secondary">{{ __('FsLang::panel.tip_config') }}</li>
                        <li class="list-group-item text-secondary">{{ __('FsLang::panel.tip_plugin_install_or_upgrade') }}</li>
                    </ul>
                </div>
            </div>
            <!--news-->
            <div class="card mb-4">
                <div class="card-header">{{ __('FsLang::panel.news') }}</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush" id="news"></ul>
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

@push('script')
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

        // dashboard data
        fetch('/fresns/dashboard-data?type=accountCount')
            .then(response => response.json())
            .then(data => {
                const accountCountElement = document.getElementById('accountCount');

                if (accountCountElement) {
                    accountCountElement.innerText = data;
                }
            })
            .catch(error => {
                console.error('accountCount Error: ', error);
            });

        fetch('/fresns/dashboard-data?type=userCount')
            .then(response => response.json())
            .then(data => {
                const userCountElement = document.getElementById('userCount');

                if (userCountElement) {
                    userCountElement.innerText = data;
                }
            })
            .catch(error => {
                console.error('userCount Error: ', error);
            });

        fetch('/fresns/dashboard-data?type=groupCount')
            .then(response => response.json())
            .then(data => {
                const groupCountElement = document.getElementById('groupCount');

                if (groupCountElement) {
                    groupCountElement.innerText = data;
                }
            })
            .catch(error => {
                console.error('groupCount Error: ', error);
            });

        fetch('/fresns/dashboard-data?type=hashtagCount')
            .then(response => response.json())
            .then(data => {
                const hashtagCountElement = document.getElementById('hashtagCount');

                if (hashtagCountElement) {
                    hashtagCountElement.innerText = data;
                }
            })
            .catch(error => {
                console.error('hashtagCount Error: ', error);
            });

        fetch('/fresns/dashboard-data?type=postCount')
            .then(response => response.json())
            .then(data => {
                const postCountElement = document.getElementById('postCount');

                if (postCountElement) {
                    postCountElement.innerText = data;
                }
            })
            .catch(error => {
                console.error('postCount Error: ', error);
            });

        fetch('/fresns/dashboard-data?type=commentCount')
            .then(response => response.json())
            .then(data => {
                const commentCountElement = document.getElementById('commentCount');

                if (commentCountElement) {
                    commentCountElement.innerText = data;
                }
            })
            .catch(error => {
                console.error('commentCount Error: ', error);
            });

        fetch('/fresns/dashboard-data?type=checkVersion')
            .then(response => response.json())
            .then(data => {
                const checkVersionElement = document.getElementById('checkVersion');

                if (checkVersionElement && data) {
                    $('#checkVersion').show();
                }
            })
            .catch(error => {
                console.error('checkVersion Error: ', error);
            });

        fetch('/fresns/dashboard-data?type=news')
            .then(response => response.json())
            .then(data => {
                // ul
                const newsList = document.getElementById('news');

                // for
                data.forEach(newsItem => {
                    // li
                    const listItem = document.createElement('li');
                    listItem.classList.add('list-group-item');

                    // span
                    const badge = document.createElement('span');
                    badge.classList.add('badge', 'bg-warning', 'text-dark');
                    badge.textContent = newsItem.date;

                    // a
                    const link = document.createElement('a');
                    link.classList.add('fresns-link', 'ms-2');
                    link.href = newsItem.link;
                    link.target = '_blank';
                    link.textContent = newsItem.title;
                    link.style.color = newsItem.color;

                    // list
                    listItem.appendChild(badge);
                    listItem.appendChild(link);

                    newsList.appendChild(listItem);
                });
            })
            .catch(error => {
                console.error('News Error: ', error);
            });
    </script>
@endpush
