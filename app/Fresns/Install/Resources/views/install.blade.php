@extends('Install::layouts/master')

@section('body')
    <div id="install-box" class="card mx-auto my-5" style="max-width:400px;">

        <div class="position-absolute top-0 start-50 translate-middle p-3" style="z-index: 11">
            <div id="errorToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body text-danger" id="toastMessage"></div>
                    <button type="button" class="btn-close btn-close-primary me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <form>
            <!--step 1: lang select-->
            <div id="step1" class="card-body visually-hidden">
                <select class="form-select" size="12" aria-label="size 12 select install_lang">
                    @foreach($langs as $key => $lang)
                        <option value="{{ $key }}">{{ $lang }}</option>
                    @endforeach
                </select>

                <div class="d-flex justify-content-end">
                    <button type="button" class="flex-end btn btn-outline-primary mt-3" onclick="next_step()">→</button>
                </div>
            </div>

            <!--step 2: intro-->
            <div id="step2" class="card-body p-5 visually-hidden">
                <h3 class="card-title">@lang('Install::install.intro_title')</h3>
                <p class="mt-4">@lang('Install::install.intro_desc')</p>
                <ul>
                    <li>@lang('Install::install.intro_database_name')</li>
                    <li>@lang('Install::install.intro_database_username')</li>
                    <li>@lang('Install::install.intro_database_password')</li>
                    <li>@lang('Install::install.intro_database_host')</li>
                    <li>@lang('Install::install.intro_database_table_prefix')</li>
                </ul>
                <p>@lang('Install::install.intro_database_desc')</p>
                <div class="d-flex justify-content-start">
                    <button type="button" class="flex-end btn btn-outline-primary mt-3" onclick="next_step()">@lang('Install::install.intro_next_btn')</button>
                </div>
            </div>

            <!--step 3: check server-->
            <div id="step3" class="card-body p-5 visually-hidden">
                <h3 class="card-title">@lang('Install::install.server_title')</h3>
                <ul class="list-group list-group-flush my-4">
                    @foreach($basicCheckResult as $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <span>{{ $item['title'] }}</span>
                                <small class="text-muted ms-3">{{ $item['message'] }}</small>
                            </span>
                            <span class="badge rounded-pill {{ $item['class'] }}">{{ $item['tips'] }}</span>
                        </li>
                    @endforeach
                </ul>
                <div>
                    <!--Try Again-->
                    @if($basicCheckPass == false)
                    <button type="button" class="btn btn-outline-info ms-3" onclick="window.location.reload()" id="envCheckRetryBtn">@lang('Install::install.btn_check')</button>
                    @else
                    <!--Next Step-->
                    <button type="button" class="btn btn-outline-primary ms-3" onclick="next_step()" id="envCheckNextBtn">@lang('Install::install.btn_next')</button>
                    @endif
                </div>
            </div>

            <!--step 4: database config-->
            <div id="step4" class="card-body p-5 visually-hidden">
                <h3 class="card-title">@lang('Install::install.database_title')</h3>
                <p class="mt-2">@lang('Install::install.database_desc')</p>

                <!--db config-->
                <div id="dbConfig">
                    <!--type-->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">@lang('Install::install.database_driver')</label>
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
                        <label class="col-sm-3 col-form-label" id="database_name">@lang('Install::install.database_name')</label>
                        <label class="col-sm-3 col-form-label" id="database_name_sqlite" style="display: none">@lang('Install::install.database_name_sqlite')</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="DB_DATABASE" name="database[DB_DATABASE]" placeholder="fresns" value="fresns">
                        </div>
                        <div class="col-sm-4 form-text">@lang('Install::install.database_name_desc')</div>
                    </div>
                    <div id="sqlForm">
                        <!--username-->
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">@lang('Install::install.database_username')</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" id="DB_USERNAME" name="database[DB_USERNAME]" placeholder="username" value="username">
                            </div>
                            <div class="col-sm-4 form-text">@lang('Install::install.database_username_desc')</div>
                        </div>
                        <!--password-->
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">@lang('Install::install.database_password')</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" id="DB_PASSWORD" name="database[DB_PASSWORD]" placeholder="password" value="password">
                            </div>
                            <div class="col-sm-4 form-text">@lang('Install::install.database_password_desc')</div>
                        </div>
                        <!--host-->
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">@lang('Install::install.database_host')</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" id="DB_HOST" name="database[DB_HOST]" placeholder="localhost" value="localhost">
                            </div>
                            <div class="col-sm-4 form-text">@lang('Install::install.database_host_desc')</div>
                        </div>
                        <!--port-->
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">@lang('Install::install.database_port')</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" id="DB_PORT" name="database[DB_PORT]" placeholder="3306" value="3306">
                            </div>
                            <div class="col-sm-4 form-text" id="database_port_mysql">@lang('Install::install.database_port_mysql_desc')</div>
                            <div class="col-sm-4 form-text" id="database_port_pgsql" style="display: none">@lang('Install::install.database_port_pgsql_desc')</div>
                            <div class="col-sm-4 form-text" id="database_port_sqlsrv" style="display: none">@lang('Install::install.database_port_sqlsrv_desc')</div>
                        </div>
                    </div>
                    <!--data timezone-->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">@lang('Install::install.database_timezone')</label>
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
                        <div class="col-sm-4 form-text">@lang('Install::install.database_timezone_desc')</div>
                    </div>
                    <!--table prefix-->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">@lang('Install::install.database_table_prefix')</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="DB_PREFIX" name="database[DB_PREFIX]" placeholder="fs_" value="fs_">
                        </div>
                        <div class="col-sm-4 form-text">@lang('Install::install.database_table_prefix_desc')</div>
                    </div>
                    <!--submit btn-->
                    <div class="row mb-4">
                        <label class="col-sm-3 col-form-label"></label>
                        <div class="col-sm-9">
                            <button id="envBtn" type="button" class="flex-end btn btn-outline-primary mt-3" onclick="next_step()">@lang('Install::install.btn_submit')</button>
                        </div>
                    </div>
                </div>

                <!--data import log-->
                <div id="dataImport" style="display:none;">
                    <label class="form-label">
                        <span class="badge bg-success rounded-pill fs-8">@lang('Install::install.database_import_log')</span>
                    </label>
                    <textarea class="form-control" rows="20" id="output" readonly></textarea>
                    <!--next step-->
                    <button id="envBtn" type="button" class="flex-end btn btn-outline-primary mt-3" onclick="to_next_page(step + 1)">@lang('Install::install.btn_next')</button>
                </div>
            </div>

            <!--step 5: register administrator-->
            <div id="registerAccount" class="card-body p-5 visually-hidden">
                <div class="alert alert-primary mb-4" role="alert">
                    @lang('Install::install.register_welcome')
                </div>
                <h3 class="card-title">@lang('Install::install.register_title')</h3>
                <p class="mt-2">@lang('Install::install.register_desc')</p>
                <!--email-->
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="email">@lang('Install::install.register_account_email')</label>
                    <div class="col-sm-9">
                        <input type="email" class="form-control" id="email" name="admin_info[email]" placeholder="name@fresns.org" value="" required>
                    </div>
                </div>
                <!--password-->
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="password">@lang('Install::install.register_account_password')</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="password" name="admin_info[password]" placeholder="@lang('Install::install.register_account_password')" value="" required>
                    </div>
                </div>
                <!--password confirm-->
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="password_confirmation">@lang('Install::install.register_account_password_confirm')</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="password_confirmation" name="admin_info[password_confirmation]" placeholder="@lang('Install::install.register_account_password_confirm')" value="" required>
                    </div>
                </div>
                <!--submit-->
                <div class="row mb-4">
                    <label class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-9">
                        <button type="button" class="flex-end btn btn-outline-primary mt-3" onclick="next_step()">@lang('Install::install.btn_submit')</button>
                    </div>
                </div>
            </div>

            <!--step 6: done-->
            <div id="finish" class="card-body p-5 visually-hidden">
                <h3 class="card-title mt-3">@lang('Install::install.done_title')</h3>
                <p class="mt-4">@lang('Install::install.done_desc')</p>
                <table class="table table-bordered">
                    <tr>
                        <th scope="row" class="p-3">@lang('Install::install.done_account')</th>
                        <td id="emailBox" class="p-3"><span>{{ $email }}</span></td>
                    </tr>
                    <tr>
                        <th scope="row" class="p-3">@lang('Install::install.done_password')</th>
                        <td class="p-3"><span class="fst-italic fw-light text-muted">@lang('Install::install.done_password_desc')</span></td>
                    </tr>
                </table>
                <p class="mt-4"><a href="/fresns/admin" class="btn btn-outline-primary">@lang('Install::install.done_btn')</a></p>
            </div>
        </form>
    </div>
@endsection

@push('js')
    <script>
        let step = @js($step);
        let install_lang;

        console.log('current step is:', step)

        if (step == 5) {
            $(`#registerAccount`).removeClass('visually-hidden').siblings().addClass('visually-hidden');
        } else if (step == 6) {
            $(`#finish`).removeClass('visually-hidden').siblings().addClass('visually-hidden');
        } else {
            $(`#step${step}`).removeClass('visually-hidden').siblings().addClass('visually-hidden');
        }

        switch (step) {
            case 1:
                $('#install-box').css('max-width', '400px');
                break;
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
                $('#install-box').css('max-width', '800px');
                break;
            default:
                $('#install-box').css('max-width', 'auto');
                break;
        }

        if (step == 4) {
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
        }

        $.ajaxSetup({
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            async success(res) {
                console.log(res)

                if (res.step === 3 && res.code !== 0) {
                    $('#envCheckRetryBtn').removeClass('visually-hidden')
                    $('#envCheckNextBtn').addClass('visually-hidden')
                } else {
                    $('#envCheckRetryBtn').addClass('visually-hidden')
                    $('#envCheckNextBtn').removeClass('visually-hidden')
                }

                if (res.code !== 0) {
                    $('#toastMessage').text(res.message)
                    toast = new bootstrap.Toast($('#errorToast'))
                    toast.show()
                    return
                }

                if (res.step === 4) {
                    $('#dbConfig').hide()
                    $('#output').text(res.data.output)
                    $('#dataImport').show()

                    if (res.data.code !== 0) {
                        $('#dataImport #envBtn').attr('disabled', true);
                        $('#dataImport #envBtn').removeClass('btn-outline-primary');
                        $('#dataImport #envBtn').addClass('btn-outline-danger');
                        $('#dataImport #envBtn').text("@lang('Install::install.install_failure')");
                        return;
                    }

                    await new Promise(resolve => setTimeout(resolve, 5e3))
                    return
                }

                if (res.step === 5) {
                    if (res.data.email) {
                        $('#finish').removeClass('visually-hidden')
                        $('#emailBox').text(res.data.email)
                        $('#registerAccount').addClass('visually-hidden')
                    }

                    await new Promise(resolve => setTimeout(resolve, 5e3))
                    return
                }

                const prevStep = Number(res.data.step)

                if (isNaN(prevStep)) {
                    return
                }

                const next = Number(res.data.step) + 1

                to_next_page(next)
            },
            error(err) {
                console.error(err)

                if (err.responseJSON) {
                    console.log(err.responseJSON.message);
                    $('#toastMessage').text(err.responseJSON.message)
                    toast = new bootstrap.Toast($('#errorToast'))
                    toast.show()
                }
            },
        });

        function to_next_page(next) {
            window.location.href = `/install?step=${next}&lang=${install_lang}`
        }

        function next_step() {
            if (step == 1) {
                install_lang = $('#step1 select').val();
            } else {
                install_lang = "{{ \request('lang') }}";
            }

            let data = {
                step: step,
                install_lang: install_lang,
            }

            if (step == 4) {
                $('form').serializeArray().forEach(item => {
                    data[item.name] = item.value
                })
                data.database = {
                    DB_CONNECTION: $('#DB_CONNECTION').val(),
                    DB_DATABASE: $('#DB_DATABASE').val(),
                    DB_USERNAME: $('#DB_USERNAME').val(),
                    DB_PASSWORD: $('#DB_PASSWORD').val(),
                    DB_HOST: $('#DB_HOST').val(),
                    DB_PORT: $('#DB_PORT').val(),
                }
            } else if (step == 5) {
                $('form').serializeArray().forEach(item => {
                    data[item.name] = item.value
                })
            }

            console.log("current data:", data)

            var btn = $(event.target);
            btn.prop('disabled', true)
            btn.prepend('<span class="spinner-border spinner-border-sm mg-r-5 d-none" role="status" aria-hidden="true"></span> ')

            if (0 === btn.children('.spinner-border').length) {
                btn.prepend('<span class="spinner-border spinner-border-sm mg-r-5 d-none" role="status" aria-hidden="true"></span> ')
            }
            btn.children('.spinner-border').removeClass('d-none');

            $.ajax({
                url: '/api/install',
                method: 'post',
                data: data,
                responseType: 'json',
                complete() {
                    btn.prop('disabled', false)
                    btn.children('.spinner-border').remove()
                }
            })
        }
    </script>
@endpush
