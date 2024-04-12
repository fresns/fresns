@extends('FsAccountView::commons.layout')

@section('title', $fsLang['accountLogin'])

@section('body')
    {{-- header --}}
    <header class="text-center">
        <p><img src="{{ $siteLogo }}" height="30"></p>
        <h1 class="fs-4">{{ $fsLang['accountLogin'] }}</h1>
    </header>

    {{-- main --}}
    <main class="m-4">
        {{-- quick login --}}
        @include('FsAccountView::commons.quick-login')

        {{-- login --}}
        @if ($fsConfig['account_email_login'] || $fsConfig['account_phone_login'])
            <form class="api-request-form" action="{{ route('account-center.api.login') }}" method="post">
                {{-- type --}}
                @if ($fsConfig['account_email_login'] && $fsConfig['account_phone_login'])
                    <div class="input-group mb-3">
                        <span class="input-group-text">{{ $fsLang['accountType'] }}</span>
                        <div class="form-control form-control-lg">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="accountType" id="email" value="email" onclick="clickEmail(this)" checked>
                                <label class="form-check-label" for="email">{{ $fsLang['email'] }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="accountType" id="phone" value="phone" onclick="clickPhone(this)">
                                <label class="form-check-label" for="phone">{{ $fsLang['phone'] }}</label>
                            </div>
                        </div>
                    </div>
                @else
                    <input type="hidden" name="accountType" value="{{ $fsConfig['account_email_login'] ? 'email' : 'phone' }}">
                @endif

                {{-- account --}}
                <input type="hidden" name="countryCode" id="countryCode" value="{{ $smsDefaultCode }}">
                <div class="input-group mb-3">
                    {{-- country code --}}
                    @if (count($smsSupportedCodes) == 1)
                        <span class="input-group-text @if ($fsConfig['account_email_login']) d-none @endif">+{{ $smsDefaultCode }}</span>
                    @else
                        <button class="btn btn-outline-secondary @if ($fsConfig['account_email_login']) d-none @endif" type="button" id="countryCodeButton" data-bs-toggle="modal" data-bs-target="#countryCodeModal">+{{ $smsDefaultCode }}</button>
                    @endif

                    {{-- input --}}
                    <input type="{{ $fsConfig['account_email_login'] ? 'email' : 'number' }}" class="form-control form-control-lg input-number @if ($fsConfig['account_email_login']) rounded-start @endif" name="account" id="accountInfo" placeholder="{{ $fsConfig['account_email_login'] ? $fsLang['email'] : $fsLang['phone'] }}" required>
                </div>

                {{-- password or verify code --}}
                <div class="input-group">
                    <input type="password" class="form-control form-control-lg rounded-end" name="password" placeholder="{{ $fsLang['password'] }}">
                    <input type="text" class="form-control form-control-lg rounded-start d-none" name="verifyCode" placeholder="{{ $fsLang['verifyCode'] }}">
                    <button type="button" class="btn btn-outline-secondary send-verify-code d-none" data-type="login" data-account-input-id="accountInfo" data-country-code-input-id="countryCode" onclick="guestSendVerifyCode(this)">{{ $fsLang['sendVerifyCode'] }}</button>
                </div>

                {{-- login_with_code --}}
                <div class="text-end mb-3">
                    @if ($fsConfig['account_login_with_code'])
                            <button class="btn btn-link" type="button" id="switchLoginType" data-type="code">{{ $fsLang['accountLoginByCode'] }}</button>
                    @endif
                </div>

                {{-- submit button --}}
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">{{ $fsLang['accountLogin'] }}</button>
                </div>

                {{-- link --}}
                <div class="mt-4 text-center" id="footer-link">
                    <a href="{{ route('account-center.reset-password') }}" class="link-primary">{{ $fsLang['passwordForgot'] }}</a>
                    @if ($fsConfig['account_register_status'])
                        <div class="vr mx-3"></div>
                        <a href="{{ route('account-center.register') }}" class="link-primary">{{ $fsLang['accountRegister'] }}</a>
                    @endif
                </div>
            </form>
        @endif
    </main>
@endsection

@push('script')
    <script>
        const siteURL = "{{ $fsConfig['site_url'] }}";

        if (siteURL && window.top == window.self) {
            console.log('current page not in iframe');
            let html = `<div class="vr mx-3"></div><a href="${siteURL}" class="link-primary">{{ $fsLang['home'] }}</a>`;

            $('#footer-link').append(html);
        }

        $('#switchLoginType').click(function () {
            var btn = $(this);
            var loginOrRegister = {{ $fsConfig['account_login_or_register'] ? 1 : 0 }};

            let type = btn.attr('data-type');
            let newType, newBtnText, submitBtnText;

            if (type == 'code') {
                newType = 'password';
                newBtnText = "{{ $fsLang['accountLoginByPassword'] }}";
                submitBtnText = "{{ $fsLang['accountLoginOrRegister'] }}";

                $('#accountInfo').removeClass('rounded-end');
                $('.send-verify-code').removeClass('d-none');
                $('input[name="password"]').addClass('d-none');
                $('input[name="verifyCode"]').removeClass('d-none');
            } else {
                newType = 'code';
                newBtnText = "{{ $fsLang['accountLoginByCode'] }}";
                submitBtnText = "{{ $fsLang['accountLogin'] }}";

                $('#accountInfo').addClass('rounded-end');
                $('.send-verify-code').addClass('d-none');
                $('input[name="password"]').removeClass('d-none');
                $('input[name="verifyCode"]').addClass('d-none');
            }

            btn.attr('data-type', newType);
            btn.text(newBtnText);
            if (loginOrRegister) {
                $('button[type="submit"]').text(submitBtnText);
            }
        });

        // api request form
        $('.api-request-form').submit(function (e) {
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
                    tips(res.message);

                    if (res.code != 0) {
                        return;
                    }

                    if (res.data && res.data.loginToken) {
                        sendAccountCallback(res.data.loginToken);
                        return;
                    }

                    window.location.href = "{{ route('account-center.user-auth') }}";
                },
                complete: function (e) {
                    btn.prop('disabled', false);
                    btn.find('.spinner-border').remove();
                },
            });
        });
    </script>
@endpush
