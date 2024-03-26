@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::systems.sidebar')
@endsection

@section('content')
    <!--header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_account') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_account_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                {{-- <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a> --}}
            </div>
        </div>
    </div>

    <!--config-->
    <form action="{{ route('panel.account.update') }}" id="accountConfigForm" method="post">
        @csrf
        @method('put')

        <!--account_center_config-->
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.account_center_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.account_center_service') }}</label>
                    <select class="form-select" name="account_center_service">
                        <option value="" {{ !$params['account_center_service'] ? 'selected' : '' }}>{{ __('FsLang::panel.option_default') }}</option>
                        @foreach ($accountCenterPlugins as $plugin)
                            <option value="{{ $plugin->fskey }}" {{ $params['account_center_service'] == $plugin->fskey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="card">
                    <div class="card-header">{{ __('FsLang::panel.account_center_captcha') }}</div>
                    <div class="card-body">
                        <!--captcha-->
                        <div class="input-group mb-3">
                            <label class="input-group-text">{{ __('FsLang::panel.table_status') }}</label>
                            <select class="form-select" name="account_center_captcha[type]">
                                <option value="" {{ !($params['account_center_captcha']['type'] ?? '') ? 'selected' : '' }}>🚫 {{ __('FsLang::panel.option_deactivate') }}</option>
                                <option value="turnstile" {{ ($params['account_center_captcha']['type'] ?? '') == 'turnstile' ? 'selected' : '' }}>Turnstile (Cloudflare)</option>
                                <option value="reCAPTCHA" {{ ($params['account_center_captcha']['type'] ?? '') == 'reCAPTCHA' ? 'selected' : '' }}>reCAPTCHA v3 (Google)</option>
                                <option value="hCaptcha" {{ ($params['account_center_captcha']['type'] ?? '') == 'hCaptcha' ? 'selected' : '' }}>hCaptcha (Intuition Machines)</option>
                            </select>
                        </div>
                        <div class="input-group mb-2">
                            <label class="input-group-text">Site Key</label>
                            <input type="text" class="form-control" name="account_center_captcha[siteKey]" value="{{ $params['account_center_captcha']['siteKey'] }}">
                        </div>
                        <div class="input-group">
                            <label class="input-group-text">Secret Key</label>
                            <input type="text" class="form-control" name="account_center_captcha[secretKey]" value="{{ $params['account_center_captcha']['secretKey'] }}">
                        </div>
                        <!--captcha end-->
                    </div>
                </div>
            </div>
        </div>

        <!--account_register_config-->
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.account_register_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.account_register_service') }}</label>
                    <select class="form-select" name="account_register_service">
                        <option value="" {{ !$params['account_register_service'] ? 'selected' : '' }}>{{ __('FsLang::panel.option_default') }}</option>
                        @foreach ($accountRegisterPlugins as $plugin)
                            <option value="{{ $plugin->fskey }}" {{ $params['account_register_service'] == $plugin->fskey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.account_register_status') }}</label>
                    <select class="form-select" id="account_register_status" name="account_register_status">
                        <option value="false" {{ $params['account_register_status'] == 'false' ? 'selected' : '' }}>{{ __('FsLang::panel.option_close') }}</option>
                        <option value="true" {{ $params['account_register_status'] == 'true' ? 'selected' : '' }}>{{ __('FsLang::panel.option_open') }}</option>
                    </select>
                </div>
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.account_register_type') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="account_email_register" name="account_email_register" value="true" {{ $params['account_email_register'] == 'true' ? 'checked' : '' }}>
                            <label class="form-check-label" for="account_email_register">{{ __('FsLang::panel.account_register_type_email') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="account_phone_register" name="account_phone_register" value="true" {{ $params['account_phone_register'] == 'true' ? 'checked' : '' }}>
                            <label class="form-check-label" for="account_phone_register">{{ __('FsLang::panel.account_register_type_phone') }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--account_login_config-->
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.account_login_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.account_login_service') }}</label>
                    <select class="form-select" name="account_login_service">
                        <option value="" {{ !$params['account_login_service'] ? 'selected' : '' }}>{{ __('FsLang::panel.option_default') }}</option>
                        @foreach ($accountLoginPlugins as $plugin)
                            <option value="{{ $plugin->fskey }}" {{ $params['account_login_service'] == $plugin->fskey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.account_login_support') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="account_email_login" name="account_email_login" value="true" {{ $params['account_email_login'] == 'true' ? 'checked' : '' }}>
                            <label class="form-check-label" for="account_email_login">{{ __('FsLang::panel.account_login_type_email') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="account_phone_login" name="account_phone_login" value="true" {{ $params['account_phone_login'] == 'true' ? 'checked' : '' }}>
                            <label class="form-check-label" for="account_phone_login">{{ __('FsLang::panel.account_login_type_phone') }}</label>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.account_code_login') }}</label>
                    <select class="form-select" id="account_login_with_code" name="account_login_with_code">
                        <option value="false" {{ $params['account_login_with_code'] == 'false' ? 'selected' : '' }}>{{ __('FsLang::panel.option_close') }}</option>
                        <option value="true" {{ $params['account_login_with_code'] == 'true' ? 'selected' : '' }}>{{ __('FsLang::panel.option_open') }}</option>
                    </select>
                </div>
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.account_login_or_register') }}</label>
                    <select class="form-select" id="account_login_or_register" name="account_login_or_register">
                        <option value="false" {{ $params['account_login_or_register'] == 'false' ? 'selected' : '' }}>{{ __('FsLang::panel.option_no') }}</option>
                        <option value="true" {{ $params['account_login_or_register'] == 'true' ? 'selected' : '' }}>{{ __('FsLang::panel.option_yes') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!--password_config-->
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.account_password_config') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.account_password_length') }}</label>
                    <input type="number" class="form-control input-number" name="password_length" value="{{ $params['password_length'] }}">
                    <span class="input-group-text">{{ __('FsLang::panel.unit_length') }}</span>
                </div>
                <div class="input-group">
                    <label class="input-group-text">{{ __('FsLang::panel.account_password_strength') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="digital" name="password_strength[]" value="number" {{ in_array('number', $params['password_strength']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="digital">{{ __('FsLang::panel.account_password_strength_digital') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="lower_letter" name="password_strength[]" value="lowercase" {{ in_array('lowercase', $params['password_strength']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="lower_letter">{{ __('FsLang::panel.account_password_strength_lowerLetters') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="upper_letter" name="password_strength[]" value="uppercase" {{ in_array('uppercase', $params['password_strength']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="upper_letter">{{ __('FsLang::panel.account_password_strength_upperLetters') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="symbol" name="password_strength[]" value="symbols" {{ in_array('symbols', $params['password_strength']) ? 'checked' : '' }}>
                            <label class="form-check-label" for="symbol">{{ __('FsLang::panel.account_password_strength_symbols') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.account_password_length_desc') }}<br><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.account_password_strength_desc') }}</div>
        </div>

        <!--account_connect_services-->
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.account_connect_services') }}:</label>
            <div class="col-lg-10 connect-box">
                <div class="d-flex justify-content-start pt-1">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addConnect">{{ __('FsLang::panel.button_add_account_connect') }}</button>
                    <div class="form-text ms-3 pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.account_connect_services_desc') }}</div>
                </div>
                @foreach ($params['account_connect_services'] ?? [] as $connectService)
                    <div class="input-group mt-3">
                        <label class="input-group-text">{{ __('FsLang::panel.table_platform') }}</label>
                        <select class="form-select" name="connectId[]">
                            @foreach ($params['connects'] as $connect)
                                @if ($connect['id'] == 23 || $connect['id'] == 29)
                                    @continue
                                @endif
                                <option value="{{ $connect['id'] }}" @if ($connectService['code'] == $connect['id']) selected @endif>{{ $connect['name'] }}</option>
                            @endforeach
                        </select>

                        <label class="input-group-text">{{ __('FsLang::panel.table_name') }}</label>
                        <input type="hidden" name="connectNames[]" value="{{ json_encode($connectService['name']) }}">
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#connectNameModal">{{ __('FsLang::panel.button_config') }}</button>

                        <label class="input-group-text">{{ __('FsLang::panel.table_plugin') }}</label>
                        <select class="form-select" name="connectPlugin[]">
                            @foreach ($accountConnectPlugins as $plugin)
                                <option value="{{ $plugin->fskey }}" {{ $connectService['fskey'] == $plugin->fskey ? 'selected' : '' }}> {{ $plugin->name }}</option>
                            @endforeach
                        </select>

                        <label class="input-group-text">{{ __('FsLang::panel.table_order') }}</label>
                        <input type="number" class="form-control input-number" name="connectOrder[]" value="{{ $connectService['order'] ?? '' }}">

                        <button class="btn btn-outline-secondary delete-connect" type="button">{{ __('FsLang::panel.button_delete') }}</button>
                    </div>
                @endforeach
            </div>
        </div>

        <!--account_kyc_service-->
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.account_kyc_service') }}:</label>
            <div class="col-lg-6">
                <select class="form-select" name="account_kyc_service">
                    <option value="" {{ !$params['account_kyc_service'] ? 'selected' : '' }}>🚫 {{ __('FsLang::panel.option_deactivate') }}</option>
                    @foreach ($accountKycPlugins as $plugin)
                        <option value="{{ $plugin->fskey }}" {{ $params['account_kyc_service'] == $plugin->fskey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.account_kyc_service_desc') }}</div>
        </div>

        <!--account_users_service-->
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.account_users_service') }}:</label>
            <div class="col-lg-6">
                <select class="form-select" name="account_users_service">
                    <option value="" {{ !$params['account_users_service'] ? 'selected' : '' }}>🚫 {{ __('FsLang::panel.option_deactivate') }}</option>
                    @foreach ($accountUsersPlugins as $plugin)
                        <option value="{{ $plugin->fskey }}" {{ $params['account_users_service'] == $plugin->fskey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!--button_save-->
        <div class="row my-3">
            <div class="col-lg-2"></div>
            <div class="col-lg-6">
                <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
            </div>
        </div>
    </form>

    <!--connects template-->
    <template id="connectTemplate">
        <div class="input-group mt-3">
            <label class="input-group-text">{{ __('FsLang::panel.table_platform') }}</label>
            <select class="form-select" name="connectId[]">
                @foreach ($params['connects'] as $connect)
                    @if ($connect['id'] == 23 || $connect['id'] == 29)
                        @continue
                    @endif
                    <option value="{{ $connect['id'] }}">{{ $connect['name'] }}</option>
                @endforeach
            </select>

            <label class="input-group-text">{{ __('FsLang::panel.table_name') }}</label>
            <input type="hidden" name="connectNames[]" value="">
            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#connectNameModal">{{ __('FsLang::panel.button_config') }}</button>

            <label class="input-group-text">{{ __('FsLang::panel.table_plugin') }}</label>
            <select class="form-select" name="connectPlugin[]">
                @foreach ($accountConnectPlugins as $plugin)
                    <option value="{{ $plugin->fskey }}">{{ $plugin->name }}</option>
                @endforeach
            </select>

            <label class="input-group-text">{{ __('FsLang::panel.table_order') }}</label>
            <input type="number" class="form-control input-number" name="connectOrder[]">

            <button class="btn btn-outline-secondary delete-connect" type="button">{{ __('FsLang::panel.button_delete') }}</button>
        </div>
    </template>

    <!-- Name Language Modal -->
    <div class="modal fade connect-name-lang-modal" id="connectNameModal" tabindex="-1" aria-labelledby="connectNameModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.table_name') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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
                                        <td><input type="text" name="names[{{ $lang['langTag'] }}]" class="form-control name-input" value=""></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!--button_confirm-->
                    <div class="text-center">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_confirm') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $('#connectNameModal').on('show.bs.modal', function (e) {
            var button = $(e.relatedTarget);
            $(this).data('triggerButton', button);

            $(this).find('.name-input').each(function() {
                $(this).val('');
            });

            var namesString = button.closest('.input-group').find('input[name="connectNames[]"]').val();

            if (namesString) {
                try {
                    var names = JSON.parse(namesString);
                    Object.entries(names).forEach(([langTag, langContent]) => {
                        $(this).find(`input[name='names[${langTag}]']`).val(langContent);
                    });
                } catch (error) {
                    console.error("Parsing error:", error);
                }
            }
        });

        $('#connectNameModal').on('hide.bs.modal', function (e) {
            var updatedNames = {};
            var modal = $(this);

            modal.find('.name-input').each(function () {
                var inputName = $(this).attr('name');
                var langTag = inputName.match(/names\[(.*?)\]/)[1];
                if (langTag) {
                    updatedNames[langTag] = $(this).val();
                }
            });

            var updatedNamesString = JSON.stringify(updatedNames);

            var triggerButton = modal.data('triggerButton');

            if (triggerButton) {
                triggerButton.closest('.input-group').find('input[name="connectNames[]"]').val(updatedNamesString);
            }
        });
    </script>
@endpush
