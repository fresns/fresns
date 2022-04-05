@extends('Install::layouts/master')

@push('js')
<script src="{{ @asset('/static/js/alpinejs.min.js') }}"></script>

<script>
    $.ajaxSetup({
        headers: {
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
                    return;
                }

                await new Promise(resolve => setTimeout(resolve, 5e3))
                return
            }


            if (res.step === 5) {
                if (res.code === 0) {
                    $('#finish').removeClass('visually-hidden')
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
        window.location.href = `/install?step=${next}`
    }

    function next_step(data) {
        $.ajax({
            url: '/api/install',
            method: 'post',
            data: data,
            responseType: 'json',
        })
    }
</script>
@endpush

@section('body')
<div class="card mx-auto my-5" x-bind:style="{
        maxWidth: maxWidth
    }" x-data="{
        step: @js($step),
        maxWidth: 'auto',
    }" x-init="() => {
        switch (step) {
            case 1:
                maxWidth = '400px';
                break;
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
                maxWidth = '800px';
                break;
            default:
                maxWidth = 'auto';
                break;
        }
    }">

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
        <template x-if="step === 1">
            <div class="card-body" x-data="{ langs: {{ Js::from($langs) }}, lang: null, }">
                <select class="form-select" size="12" aria-label="size 12 select lang" x-model="lang">
                    <template x-for="(post, key) in langs">
                        <option x-bind:value="key" x-text="post"></option>
                    </template>
                </select>

                <div class="d-flex justify-content-end">
                    <button type="button" class="flex-end btn btn-outline-primary mt-3" @click="next_step({step, lang})">→</button>
                </div>
            </div>
        </template>

        <!--step 2: intro-->
        <template x-if="step === 2">
            <div class="card-body p-5" x-data="{}">
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
                    <button type="button" class="flex-end btn btn-outline-primary mt-3" @click="next_step({step})">@lang('Install::install.intro_next_btn')</button>
                </div>
            </div>
        </template>

        <!--step 3: check server-->
        <template x-if="step === 3">
            <div class="card-body p-5" x-data="{ basicCheckResult: {{ Js::from($basicCheckResult) }}, basicCheckPass: @json($basicCheckPass) }">
                <h3 class="card-title">@lang('Install::install.server_title')</h3>
                <ul class="list-group list-group-flush my-4">
                    <template x-for="item in basicCheckResult">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <span x-text="item.title"></span>
                                <small class="text-muted ms-3" x-text="item.message"></small>
                            </span>
                            <span class="badge rounded-pill" x-bind:class="[item.class]" x-text="item.tips">@lang('Install::install.server_status_success')</span>
                        </li>
                    </template>
                </ul>
                <div>
                    <!--Try Again-->
                    <template x-if="basicCheckPass === false">
                        <button type="button" class="btn btn-outline-info ms-3" @click="() => window.location.reload()" id="envCheckRetryBtn">@lang('Install::install.btn_check')</button>
                    </template>
                    <!--Next Step-->
                    <template x-if="basicCheckPass === true">
                        <button type="button" class="btn btn-outline-primary ms-3" @click="next_step({step})" id="envCheckNextBtn">@lang('Install::install.btn_next')</button>
                    </template>
                </div>
            </div>
        </template>

        <!--step 4: database config-->
        <template x-if="step === 4">
            <div class="card-body p-5" x-data="{ database: {{ Js::from($database) }} }">
                <h3 class="card-title">@lang('Install::install.database_title')</h3>
                <p class="mt-2">@lang('Install::install.database_desc')</p>

                <!--db config-->
                <div id="dbConfig">
                    <!--name-->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="DB_DATABASE">@lang('Install::install.database_name')</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="DB_DATABASE" name="database[DB_DATABASE]" placeholder="fresns" value="fresns">
                        </div>
                        <div class="col-sm-4 form-text">@lang('Install::install.database_name_desc')</div>
                    </div>
                    <!--username-->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="DB_USERNAME">@lang('Install::install.database_username')</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="DB_USERNAME" name="database[DB_USERNAME]" placeholder="username" value="username">
                        </div>
                        <div class="col-sm-4 form-text">@lang('Install::install.database_username_desc')</div>
                    </div>
                    <!--password-->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="DB_PASSWORD">@lang('Install::install.database_password')</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="DB_PASSWORD" name="database[DB_PASSWORD]" placeholder="password" value="password">
                        </div>
                        <div class="col-sm-4 form-text">@lang('Install::install.database_password_desc')</div>
                    </div>
                    <!--host-->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="DB_HOST">@lang('Install::install.database_host')</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="DB_HOST" name="database[DB_HOST]" placeholder="localhost" value="localhost">
                        </div>
                        <div class="col-sm-4 form-text">@lang('Install::install.database_host_desc')</div>
                    </div>
                    <!--port-->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="DB_PORT">@lang('Install::install.database_port')</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="DB_PORT" name="database[DB_PORT]" placeholder="3306" value="3306">
                        </div>
                        <div class="col-sm-4 form-text">@lang('Install::install.database_port_desc')</div>
                    </div>
                    <!--table prefix-->
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label" for="DB_PREFIX">@lang('Install::install.database_table_prefix')</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="DB_PREFIX" name="database[DB_PREFIX]" placeholder="fs_" value="fs_">
                        </div>
                        <div class="col-sm-4 form-text">@lang('Install::install.database_table_prefix_desc')</div>
                    </div>
                    <!--submit btn-->
                    <div class="row mb-4">
                        <label class="col-sm-3 col-form-label"></label>
                        <div class="col-sm-9">
                            <button id="envBtn" type="button" class="flex-end btn btn-outline-primary mt-3" @click="() => {
                                const data = {step}
                                $('form').serializeArray().forEach(item => {
                                    data[item.name] = item.value
                                })
                                next_step(data)
                            }">@lang('Install::install.btn_submit')</button>
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
                    <button id="envBtn" type="button" class="flex-end btn btn-outline-primary mt-3" @click="() => { to_next_page(step + 1) }">@lang('Install::install.btn_next')</button>
                </div>
            </div>
        </template>

        <!--step 5: register administrator-->
        <template x-if="step === 5">
            <div id="registerAccount" class="card-body p-5" x-data="{ admin_info: {{ Js::from($admin_info) }} }">
                <div class="alert alert-primary mb-4" role="alert">
                    @lang('Install::install.register_welcome')
                </div>
                <h3 class="card-title">@lang('Install::install.register_title')</h3>
                <p class="mt-2">@lang('Install::install.register_desc')</p>
                <!--email-->
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="email">@lang('Install::install.register_account_email')</label>
                    <div class="col-sm-9">
                        <input type="email" class="form-control" id="email" name="admin_info[email]" placeholder="name@fresns.org" value="">
                    </div>
                </div>
                <!--password-->
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="password">@lang('Install::install.register_account_password')</label>
                    <div class="col-sm-9">
                        <input type="password" class="form-control" id="password" name="admin_info[password]" placeholder="@lang('Install::install.register_account_password')" value="">
                    </div>
                </div>
                <!--password confirm-->
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label" for="password_confirmation">@lang('Install::install.register_account_password_confirm')</label>
                    <div class="col-sm-9">
                        <input type="password" class="form-control" id="password_confirmation" name="admin_info[password_confirmation]" placeholder="@lang('Install::install.register_account_password_confirm')" value="">
                    </div>
                </div>
                <!--submit-->
                <div class="row mb-4">
                    <label class="col-sm-3 col-form-label"></label>
                    <div class="col-sm-9">
                        <button type="button" class="flex-end btn btn-outline-primary mt-3" @click="() => {
                                const data = {step}
                                $('form').serializeArray().forEach(item => {
                                    data[item.name] = item.value
                                })
                                next_step(data)
                            }">@lang('Install::install.btn_submit')</button>
                    </div>
                </div>
            </div>
        </template>

        <!--step 6: done-->
        <template x-if="step === 5">
            <div id="finish" class="card-body p-5 visually-hidden" x-data="{ email: `{{ $email }}` }">
                <h3 class="card-title mt-3">@lang('Install::install.done_title')</h3>
                <p class="mt-4">@lang('Install::install.done_desc')</p>
                <table class="table table-bordered">
                    <tr>
                        <th scope="row" class="p-3">@lang('Install::install.done_account')</th>
                        <td class="p-3"><span x-text="email"></span></td>
                    </tr>
                    <tr>
                        <th scope="row" class="p-3">@lang('Install::install.done_password')</th>
                        <td class="p-3"><span class="fst-italic fw-light text-muted">@lang('Install::install.done_password_desc')</span></td>
                    </tr>
                </table>
                <p class="mt-4"><a href="/fresns/admin" class="btn btn-outline-primary">@lang('Install::install.done_btn')</a></p>
            </div>
        </template>
    </form>
</div>
@endsection