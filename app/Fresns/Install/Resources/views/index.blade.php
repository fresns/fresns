@extends('Install::layouts.master')

@section('body')
    <div class="card mx-auto my-5" id="install-box" style="max-width:400px;">
        {{-- step 1: lang select --}}
        <div id="step1" class="card-body">
            <select class="form-select" size="12" id="lang-select">
                @foreach($langs as $key => $lang)
                    <option value="{{ $key }}" {{ $key == App::getLocale() ? 'selected' : '' }}>{{ $lang }}</option>
                @endforeach
            </select>

            <div class="d-flex justify-content-end">
                <button type="button" class="flex-end btn btn-outline-primary mt-3" onclick="next_step(2)">→</button>
            </div>
        </div>

        {{-- step 2: intro --}}
        <div id="step2" class="card-body p-5 d-none">
            <h3 class="card-title">{{ __('Install::install.intro_title') }}</h3>
            <p class="mt-4">{{ __('Install::install.intro_desc') }}</p>
            <ul>
                <li>{{ __('Install::install.intro_database_name') }}</li>
                <li>{{ __('Install::install.intro_database_username') }}</li>
                <li>{{ __('Install::install.intro_database_password') }}</li>
                <li>{{ __('Install::install.intro_database_host') }}</li>
                <li>{{ __('Install::install.intro_database_table_prefix') }}</li>
            </ul>
            <p>{{ __('Install::install.intro_database_desc') }}</p>

            <div class="d-flex justify-content-start">
                <button type="button" class="flex-end btn btn-outline-primary mt-3" onclick="next_step(3)">{{ __('Install::install.intro_next_btn') }}</button>
            </div>
        </div>

        {{-- step 3: check server --}}
        <div id="step3" class="card-body p-5 d-none">
            <h3 class="card-title">{{ __('Install::install.server_title') }}</h3>
            <ul class="list-group list-group-flush my-4">
                {{-- php version --}}
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span>{{ __('Install::install.server_check_php_version') }}</span>
                    </div>
                    <div class="server-status" id='php-version-status'>
                        <span class="badge rounded-pill {{ $checkServer['phpVersion'] ? 'bg-success' : 'bg-danger' }}">
                            {{ $checkServer['phpVersion'] ? __('Install::install.server_status_success') : __('Install::install.server_status_failure') }}
                        </span>
                    </div>
                </li>

                {{-- composer version --}}
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span>{{ __('Install::install.server_check_composer_version') }}</span>
                        <small class="text-muted ms-3 server-message" id="composer-version-message">{{ $checkServer['composerVersion'] ? '' : $serverMessages['composerVersion'] }}</small>
                    </div>
                    <div class="server-status" id='composer-version-status'>
                        <span class="badge rounded-pill {{ $checkServer['composerVersion'] ? 'bg-success' : 'bg-warning' }}">
                            {{ $checkServer['composerVersion'] ? __('Install::install.server_status_success') : __('Install::install.server_status_warning') }}
                        </span>
                    </div>
                </li>

                {{-- ssl --}}
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span>{{ __('Install::install.server_check_https') }}</span>
                    </div>
                    <div class="server-status" id='ssl-status'>
                        <span class="badge rounded-pill {{ $checkServer['ssl'] ? 'bg-success' : 'bg-warning' }}">
                            {{ $checkServer['ssl'] ? __('Install::install.server_status_success') : __('Install::install.server_status_warning') }}
                        </span>
                    </div>
                </li>

                {{-- folder ownership --}}
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span>{{ __('Install::install.server_check_folder_ownership') }}</span>
                        <small class="text-muted ms-3 server-message" id="ownership-message">{{ $checkServer['folderOwnership'] ? '' : $serverMessages['folderOwnership'] }}</small>
                    </div>
                    <div class="server-status" id='ownership-status'>
                        <span class="badge rounded-pill {{ $checkServer['folderOwnership'] ? 'bg-success' : 'bg-danger' }}">
                            {{ $checkServer['folderOwnership'] ? __('Install::install.server_status_success') : __('Install::install.server_status_failure') }}
                        </span>
                    </div>
                </li>

                {{-- php extensions --}}
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span>{{ __('Install::install.server_check_php_extensions') }}</span>
                        <small class="text-muted ms-3 server-message" id="php-extensions-message">{{ $checkServer['phpExtensions'] ? '' : $serverMessages['phpExtensions'] }}</small>
                    </div>
                    <div class="server-status" id='php-extensions-status'>
                        <span class="badge rounded-pill {{ $checkServer['phpExtensions'] ? 'bg-success' : 'bg-danger' }}">
                            {{ $checkServer['phpExtensions'] ? __('Install::install.server_status_success') : __('Install::install.server_status_failure') }}
                        </span>
                    </div>
                </li>

                {{-- php functions --}}
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span>{{ __('Install::install.server_check_php_functions') }}</span>
                        <small class="text-muted ms-3 server-message" id="php-functions-message">{{ $checkServer['phpFunctions'] ? '' : $serverMessages['phpFunctions'] }}</small>
                    </div>
                    <div class="server-status" id='php-functions-status'>
                        <span class="badge rounded-pill {{ $checkServer['phpFunctions'] ? 'bg-success' : 'bg-danger' }}">
                            {{ $checkServer['phpFunctions'] ? __('Install::install.server_status_success') : __('Install::install.server_status_failure') }}
                        </span>
                    <div>
                </li>
            </ul>
            <div>
                {{-- Next Step --}}
                <button type="button" class="btn btn-outline-primary ms-3 @if (!$allServer) d-none @endif" onclick="next_step(4)" id="envCheckNextBtn">{{ __('Install::install.btn_next') }}</button>
                {{-- Try Again --}}
                <button type="button" class="btn btn-outline-info ms-3 @if ($allServer) d-none @endif" onclick="check_server()" id="envCheckRetryBtn">{{ __('Install::install.btn_check') }}</button>
            </div>
        </div>

        {{-- step 4: database config --}}
        <div id="step4" class="card-body p-5 d-none">
            <h3 class="card-title">{{ __('Install::install.database_title') }}</h3>
            <p class="mt-2">{{ __('Install::install.database_desc') }}</p>

            <form class="api-request-database" action="{{ route('install.config-database') }}" method="post">
                <!--type-->
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">{{ __('Install::install.database_driver') }}</label>
                    <div class="col-sm-5">
                        <select class="form-select" id="DB_CONNECTION" name="database[DB_CONNECTION]">
                            <option value="mysql" selected>MySQL</option>
                            <option value="mariadb">MariaDB</option>
                            <option value="pgsql">PostgreSQL</option>
                            <option value="sqlsrv">SQL Server</option>
                            <option value="sqlite">SQLite</option>
                        </select>
                    </div>
                </div>
                <!--name-->
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" id="database_name">{{ __('Install::install.database_name') }}</label>
                    <label class="col-sm-3 col-form-label" id="database_name_sqlite" style="display: none">{{ __('Install::install.database_name_sqlite') }}</label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control" id="DB_DATABASE" name="database[DB_DATABASE]" placeholder="fresns" value="fresns">
                    </div>
                    <div class="col-sm-4 form-text">{{ __('Install::install.database_name_desc') }}</div>
                </div>
                <div id="sqlForm">
                    <!--username-->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">{{ __('Install::install.database_username') }}</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="DB_USERNAME" name="database[DB_USERNAME]" placeholder="username" value="username">
                        </div>
                        <div class="col-sm-4 form-text">{{ __('Install::install.database_username_desc') }}</div>
                    </div>
                    <!--password-->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">{{ __('Install::install.database_password') }}</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="DB_PASSWORD" name="database[DB_PASSWORD]" placeholder="password" value="password">
                        </div>
                        <div class="col-sm-4 form-text">{{ __('Install::install.database_password_desc') }}</div>
                    </div>
                    <!--host-->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">{{ __('Install::install.database_host') }}</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="DB_HOST" name="database[DB_HOST]" placeholder="localhost" value="localhost">
                        </div>
                        <div class="col-sm-4 form-text">{{ __('Install::install.database_host_desc') }}</div>
                    </div>
                    <!--port-->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">{{ __('Install::install.database_port') }}</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="DB_PORT" name="database[DB_PORT]" placeholder="3306" value="3306">
                        </div>
                        <div class="col-sm-4 form-text" id="database_port_mysql">{{ __('Install::install.database_port_mysql_desc') }}</div>
                        <div class="col-sm-4 form-text" id="database_port_pgsql" style="display: none">{{ __('Install::install.database_port_pgsql_desc') }}</div>
                        <div class="col-sm-4 form-text" id="database_port_sqlsrv" style="display: none">{{ __('Install::install.database_port_sqlsrv_desc') }}</div>
                    </div>
                </div>
                <!--data timezone-->
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">{{ __('Install::install.database_timezone') }}</label>
                    <div class="col-sm-5">
                        <select class="form-select" id="DB_TIMEZONE" name="database[DB_TIMEZONE]">
                            <option value="Pacific/Niue">UTC -11</option>
                            <option value="Pacific/Rarotonga">UTC -10</option>
                            <option value="Pacific/Marquesas">UTC -9:30</option>
                            <option value="America/Anchorage">UTC -9</option>
                            <option value="America/Los_Angeles">UTC -8</option>
                            <option value="America/Denver">UTC -7</option>
                            <option value="America/Chicago">UTC -6</option>
                            <option value="America/New_York">UTC -5</option>
                            <option value="America/Moncton">UTC -4</option>
                            <option value="America/St_Johns">UTC -3:30</option>
                            <option value="America/Bahia">UTC -3</option>
                            <option value="America/Noronha">UTC -2</option>
                            <option value="Atlantic/Azores">UTC -1</option>
                            <option value="Europe/London">UTC +0</option>
                            <option value="Europe/Paris">UTC +1</option>
                            <option value="Asia/Jerusalem">UTC +2</option>
                            <option value="Europe/Moscow">UTC +3</option>
                            <option value="Asia/Tehran">UTC +3:30</option>
                            <option value="Asia/Dubai">UTC +4</option>
                            <option value="Asia/Kabul">UTC +4:30</option>
                            <option value="Indian/Maldives">UTC +5</option>
                            <option value="Asia/Kolkata">UTC +5:30</option>
                            <option value="Asia/Kathmandu">UTC +5:45</option>
                            <option value="Asia/Urumqi">UTC +6</option>
                            <option value="Asia/Yangon">UTC +6:30</option>
                            <option value="Asia/Ho_Chi_Minh">UTC +7</option>
                            <option value="Asia/Singapore" selected>UTC +8</option>
                            <option value="Australia/Eucla">UTC +8:45</option>
                            <option value="Asia/Tokyo">UTC +9</option>
                            <option value="Australia/Broken_Hill">UTC +9:30</option>
                            <option value="Australia/Melbourne">UTC +10</option>
                            <option value="Australia/Lord_Howe">UTC +10:30</option>
                            <option value="Asia/Sakhalin">UTC +11</option>
                            <option value="Pacific/Auckland">UTC +12</option>
                            <option value="Pacific/Chatham">UTC +12:45</option>
                            <option value="Pacific/Apia">UTC +13</option>
                            <option value="Pacific/Kiritimati">UTC +14</option>
                        </select>
                    </div>
                    <div class="col-sm-4 form-text">{{ __('Install::install.database_timezone_desc') }}</div>
                </div>
                <!--table prefix-->
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">{{ __('Install::install.database_table_prefix') }}</label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control" id="DB_PREFIX" name="database[DB_PREFIX]" placeholder="fs_" value="fs_">
                    </div>
                    <div class="col-sm-4 form-text">{{ __('Install::install.database_table_prefix_desc') }}</div>
                </div>
                <!--submit btn-->
                <div class="row mb-4">
                    <label class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-9">
                        <button type="submit" class="flex-end btn btn-outline-primary mt-3">{{ __('Install::install.btn_submit') }}</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- step 5: database import log --}}
        <div id="step5" class="card-body p-5 d-none">
            <div class="d-flex flex-row mb-3">
                <span class="badge bg-success rounded-pill fs-8">{{ __('Install::install.database_import_log') }}</span>
                <div id="db-import-status" class="ms-3">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>

            <textarea class="form-control" rows="20" id="db-import-log" readonly></textarea>

            {{-- Next Step --}}
            <button type="button" class="btn btn-outline-primary mt-3 d-none" onclick="next_step(6)" id="dbImportNextBtn">{{ __('Install::install.btn_next') }}</button>
            {{-- Try Again --}}
            <button type="button" class="btn btn-outline-danger mt-3 d-none" onclick="next_step(4)" id="dbImportRetryBtn">{{ __('Install::install.btn_check') }}</button>
        </div>

        {{-- step 6: register administrator --}}
        <div id="step6" class="card-body p-5 d-none">
            <div class="alert alert-primary mb-4" role="alert">
                {{ __('Install::install.register_welcome') }}
            </div>

            <h3 class="card-title">{{ __('Install::install.register_title') }}</h3>
            <p class="mt-2">{{ __('Install::install.register_desc') }}</p>

            <form class="api-add-admin" action="{{ route('install.add-admin') }}" method="post">
                <!--email-->
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="email">{{ __('Install::install.register_account_email') }}</label>
                    <div class="col-sm-9">
                        <input type="email" class="form-control" name="admin_email" placeholder="name@fresns.org" value="" required>
                    </div>
                </div>
                <!--password-->
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="password">{{ __('Install::install.register_account_password') }}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="admin_password" placeholder="{{ __('Install::install.register_account_password') }}" value="" required>
                    </div>
                </div>
                <!--password confirm-->
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="password_confirmation">{{ __('Install::install.register_account_password_confirm') }}</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="admin_password_confirm" placeholder="{{ __('Install::install.register_account_password_confirm') }}" value="" required>
                    </div>
                </div>
                <!--submit-->
                <div class="row mb-4">
                    <label class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-9">
                        <button type="submit" class="flex-end btn btn-outline-primary mt-3">{{ __('Install::install.btn_submit') }}</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- step 7: done --}}
        <div id="step7" class="card-body p-5 d-none">
            <h3 class="card-title mt-3">{{ __('Install::install.done_title') }}</h3>
            <p class="mt-4">{{ __('Install::install.done_desc') }}</p>
            <table class="table table-bordered">
                <tr>
                    <th scope="row" class="p-3">{{ __('Install::install.done_account') }}</th>
                    <td id="emailBox" class="p-3"></td>
                </tr>
                <tr>
                    <th scope="row" class="p-3">{{ __('Install::install.done_password') }}</th>
                    <td class="p-3"><span class="fst-italic fw-light text-muted">{{ __('Install::install.done_password_desc') }}</span></td>
                </tr>
            </table>
            <p class="mt-4"><a href="/fresns/admin" class="btn btn-outline-primary">{{ __('Install::install.done_btn') }}</a></p>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $('#lang-select').change(function() {
            var lang = $(this).val();
            let url = new URL(window.location.href);
            url.searchParams.set('lang', lang);
            window.location.href = url.href;
        });

        function next_step(step) {
            console.log('next_step', step);

            if (step != 1) {
                $('#install-box').css('max-width', '800px');
            }

            switch (step) {
                case 2:
                    $('#step1').addClass('d-none');
                    $('#step2').removeClass('d-none');
                    break;

                case 3:
                    $('#step2').addClass('d-none');
                    $('#step3').removeClass('d-none');
                    break;

                case 4:
                    $('#step3').addClass('d-none');
                    $('#step4').removeClass('d-none');
                    $('#step5').addClass('d-none');
                    break;

                case 5:
                    $('#step4').addClass('d-none');
                    $('#step5').removeClass('d-none');
                    break;

                case 6:
                    $('#step5').addClass('d-none');
                    $('#step6').removeClass('d-none');
                    break;

                case 7:
                    $('#step6').addClass('d-none');
                    $('#step7').removeClass('d-none');
                    break;
            }
        }

        const selectBox = document.getElementById('DB_CONNECTION');
        const sqlForm = document.getElementById('sqlForm');

        const databaseName = document.getElementById('database_name');
        const nameSqlite = document.getElementById('database_name_sqlite');

        const portMysql = document.getElementById('database_port_mysql');
        const portPgsql = document.getElementById('database_port_pgsql');
        const portSqlsrv = document.getElementById('database_port_sqlsrv');

        const database = document.getElementById('DB_DATABASE');
        const port = document.getElementById('DB_PORT');

        selectBox.addEventListener('change', function() {
            if (selectBox.value == 'mysql' || selectBox.value == 'mariadb') {
                portMysql.style.display = 'block';
                port.setAttribute('placeholder', '3306');
                port.setAttribute('value', '3306');
            } else {
                portMysql.style.display = 'none';
            }

            if (selectBox.value == 'pgsql') {
                portPgsql.style.display = 'block';
                port.setAttribute('placeholder', '5432');
                port.setAttribute('value', '5432');
            } else {
                portPgsql.style.display = 'none';
            }

            if (selectBox.value == 'sqlsrv') {
                portSqlsrv.style.display = 'block';
                port.setAttribute('placeholder', '1433');
                port.setAttribute('value', '1433');
            } else {
                portSqlsrv.style.display = 'none';
            }

            if (selectBox.value == 'sqlite') {
                sqlForm.style.display = 'none';
                databaseName.style.display = 'none';
                nameSqlite.style.display = 'block';

                database.setAttribute('value', "{{ database_path('fresns.sqlite') }}");
            } else {
                sqlForm.style.display = 'block';
                databaseName.style.display = 'block';
                nameSqlite.style.display = 'none';

                database.setAttribute('placeholder', 'fresns');
                database.setAttribute('value', 'fresns');
            }
        });

        // check server
        function check_server() {
            const spinnerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';

            const successHTML = '<span class="badge rounded-pill bg-success">' + "{{ __('Install::install.server_status_success') }}" + '</span>';
            const failureHTML = '<span class="badge rounded-pill bg-danger">' + "{{ __('Install::install.server_status_failure') }}" + '</span>';
            const warningHTML = '<span class="badge rounded-pill bg-warning">' + "{{ __('Install::install.server_status_warning') }}" + '</span>';

            $('.server-message').empty();
            $('.server-status').empty().append(spinnerHTML);

            $.get("{{ route('install.check-server', ['type' => 'phpVersion']) }}", function (data) {
                let apiData = data;

                if (apiData.code == 0) {
                    $('#php-version-status').empty().append(successHTML);
                } else {
                    $('#php-version-status').empty().append(failureHTML);
                }
            });

            $.get("{{ route('install.check-server', ['type' => 'composerVersion']) }}", function (data) {
                let apiData = data;

                if (apiData.code == 0) {
                    $('#composer-version-status').empty().append(successHTML);
                    $('#composer-version-message').empty();
                } else {
                    $('#composer-version-status').empty().append(warningHTML);
                    $('#composer-version-message').empty().append(apiData.message);
                }
            });

            $.get("{{ route('install.check-server', ['type' => 'ssl']) }}", function (data) {
                let apiData = data;

                if (apiData.code == 0) {
                    $('#ssl-status').empty().append(successHTML);
                } else {
                    $('#ssl-status').empty().append(warningHTML);
                }
            });

            $.get("{{ route('install.check-server', ['type' => 'folderOwnership']) }}", function (data) {
                let apiData = data;

                if (apiData.code == 0) {
                    $('#ownership-status').empty().append(successHTML);
                    $('#ownership-message').empty();
                } else {
                    $('#ownership-status').empty().append(warningHTML);
                    $('#ownership-message').empty().append(apiData.message);
                }
            });

            $.get("{{ route('install.check-server', ['type' => 'phpExtensions']) }}", function (data) {
                let apiData = data;

                if (apiData.code == 0) {
                    $('#php-extensions-status').empty().append(successHTML);
                    $('#php-extensions-message').empty();
                } else {
                    $('#php-extensions-status').empty().append(failureHTML);
                    $('#php-extensions-message').empty().append(apiData.message);
                }
            });

            $.get("{{ route('install.check-server', ['type' => 'phpFunctions']) }}", function (data) {
                let apiData = data;

                if (apiData.code == 0) {
                    $('#php-functions-status').empty().append(successHTML);
                    $('#php-functions-message').empty();
                } else {
                    $('#php-functions-status').empty().append(failureHTML);
                    $('#php-functions-message').empty().append(apiData.message);
                }
            });

            $.get("{{ route('install.check-server', ['type' => 'all']) }}", function (data) {
                let apiData = data;

                if (apiData.code == 0) {
                    $('#envCheckNextBtn').removeClass('d-none');
                    $('#envCheckRetryBtn').addClass('d-none');
                } else {
                    $('#envCheckNextBtn').addClass('d-none');
                    $('#envCheckRetryBtn').removeClass('d-none');
                }
            });
        }

        // api request database
        $('.api-request-database').submit(function (e) {
            e.preventDefault();
            let form = $(this),
                btn = $(this).find('button[type="submit"]');

            const actionUrl = form.attr('action'),
                methodType = form.attr('method') || 'POST',
                data = form.serialize();

            $.ajax({
                url: actionUrl,
                type: methodType,
                data: data,
                success: function (res) {
                    if (res.code != 0) {
                        tips(res.message, res.data);
                        return;
                    }

                    dataArtisan();

                    $('#step4').addClass('d-none');
                    $('#step5').removeClass('d-none');

                },
                error: function (response) {
                    tips(response.responseJSON.message);
                },
                complete: function (e) {
                    btn.prop('disabled', false);
                    btn.find('.spinner-border').remove();
                },
            });
        });

        // data artisan
        function dataArtisan() {
            $.ajax({
                url: "{{ route('install.data-artisan') }}",
                type: 'POST',
                success: function (res) {
                    $('#db-import-log').text(res.data);

                    if (res.code != 0) {
                        $('#db-import-status').empty().append('<i class="bi bi-clipboard-x text-danger"></i>');
                        $('#dbImportNextBtn').addClass('d-none');
                        $('#dbImportRetryBtn').removeClass('d-none');

                        return;
                    }

                    $('#db-import-status').empty().append('<i class="bi bi-clipboard-check text-success"></i>');
                    $('#dbImportNextBtn').removeClass('d-none');
                    $('#dbImportRetryBtn').addClass('d-none');
                },
                error: function (response) {
                    tips(response.responseJSON.message);

                    $('#db-import-log').text(response.responseJSON.message);

                    $('#db-import-status').empty().append('<i class="bi bi-clipboard-x text-danger"></i>');
                    $('#dbImportNextBtn').addClass('d-none');
                    $('#dbImportRetryBtn').removeClass('d-none');
                },
            });
        };

        // api add admin
        $('.api-add-admin').submit(function (e) {
            e.preventDefault();
            let form = $(this),
                btn = $(this).find('button[type="submit"]');

            const actionUrl = form.attr('action'),
                methodType = form.attr('method') || 'POST',
                data = form.serialize();

            $.ajax({
                url: actionUrl,
                type: methodType,
                data: data,
                success: function (res) {
                    if (res.code != 0) {
                        tips(res.message, res.data);
                        return;
                    }

                    $('#emailBox').text(res.data.email);

                    $('#step6').addClass('d-none');
                    $('#step7').removeClass('d-none');

                },
                error: function (response) {
                    tips(response.responseJSON.message);
                },
                complete: function (e) {
                    btn.prop('disabled', false);
                    btn.find('.spinner-border').remove();
                },
            });
        });
    </script>
@endpush
