@extends('FsAccountView::layout')

@section('title', $fsLang['accountReset'])

@section('body')
    {{-- header --}}
    <header class="text-center">
        <p><img src="{{ $siteLogo }}" height="30"></p>
        <h1 class="fs-4">{{ $fsLang['accountReset'] }}</h1>
    </header>

    {{-- main --}}
    <main class="m-4">
        @if (!$fsConfig['send_email_service'] && !$fsConfig['send_sms_service'])
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-info-circle"></i> {{ $fsLang['errorUnavailable'] }}
            </div>
        @endif

        @if ($fsConfig['send_email_service'] || $fsConfig['send_sms_service'])
            <form class="api-request-form" action="{{ route('account-center.api.reset-password') }}" method="patch">
                {{-- type --}}
                @if ($fsConfig['send_email_service'] && $fsConfig['send_sms_service'])
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
                @endif

                {{-- account --}}
                <input type="hidden" name="countryCode" id="countryCode" value="{{ $smsDefaultCode }}">
                <div class="input-group mb-3">
                    {{-- country code --}}
                    @if (count($smsSupportedCodes) == 1)
                        <span class="input-group-text @if ($fsConfig['send_email_service']) d-none @endif">+{{ $smsDefaultCode }}</span>
                    @else
                        <button class="btn btn-outline-secondary @if ($fsConfig['send_email_service']) d-none @endif" type="button" id="countryCodeButton" data-bs-toggle="modal" data-bs-target="#countryCodeModal">+{{ $smsDefaultCode }}</button>
                    @endif

                    {{-- input --}}
                    <input type="{{ $fsConfig['send_email_service'] ? 'email' : 'number' }}" class="form-control form-control-lg input-number @if ($fsConfig['send_email_service']) rounded-start @endif" name="account" id="accountInfo" placeholder="{{ $fsConfig['send_email_service'] ? $fsLang['email'] : $fsLang['phone'] }}" required>
                </div>

                {{-- verify code --}}
                <div class="input-group mb-3">
                    <input type="text" class="form-control form-control-lg" name="verifyCode" placeholder="{{ $fsLang['verifyCode'] }}" autocomplete="off">
                    <button type="button" class="btn btn-outline-secondary send-verify-code" data-type="resetPassword" data-account-input-id="accountInfo" data-country-code-input-id="countryCode" onclick="guestSendVerifyCode(this)">{{ $fsLang['sendVerifyCode'] }}</button>
                </div>

                {{-- new password --}}
                <div class="input-group">
                    <input type="text" class="form-control form-control-lg" name="newPassword" placeholder="{{ $fsLang['passwordNew'] }}" autocomplete="off">
                </div>

                {{-- password tips --}}
                <div class="form-text mb-3 ms-1">
                    {{ $fsLang['passwordInfo'] }}:
                    @if (in_array('number', $fsConfig['password_strength'])) {{ $fsLang['passwordInfoNumbers'] }} @endif
                    @if (in_array('number', $fsConfig['password_strength']) && in_array('lowercase', $fsConfig['password_strength'])) , @endif

                    @if (in_array('lowercase', $fsConfig['password_strength'])) {{ $fsLang['passwordInfoLowercaseLetters'] }} @endif
                    @if (in_array('lowercase', $fsConfig['password_strength']) && in_array('uppercase', $fsConfig['password_strength'])) , @endif

                    @if (in_array('uppercase', $fsConfig['password_strength'])) {{ $fsLang['passwordInfoUppercaseLetters'] }} @endif
                    @if (in_array('uppercase', $fsConfig['password_strength']) && in_array('symbols', $fsConfig['password_strength'])) , @endif

                    @if (in_array('symbols', $fsConfig['password_strength'])) {{ $fsLang['passwordInfoSymbols'] }} @endif

                    <div class="vr mx-2"></div>

                    {{ $fsLang['modifierLength'] }}: {{ $fsConfig['password_length'].'~32' }}
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">{{ $fsLang['submit'] }}</button>
                </div>
            </form>
        @endif

        <div class="mt-4 text-center">
            <a href="{{ route('account-center.login') }}" class="link-primary">{{ $fsLang['accountLogin'] }}</a>
            @if ($fsConfig['account_register_status'])
                <div class="vr mx-3"></div>
                <a href="{{ route('account-center.register') }}" class="link-primary">{{ $fsLang['accountRegister'] }}</a>
            @endif
        </div>
    </main>
@endsection

@push('script')
    <script>
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

                    if (res.code == 0) {
                        window.location.href = "{{ route('account-center.login') }}";
                    }
                },
                complete: function (e) {
                    btn.prop('disabled', false);
                    btn.find('.spinner-border').remove();
                },
            });
        });
    </script>
@endpush
