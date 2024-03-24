@extends('FsAccountView::layout')

@section('title', $fsLang['accountRegister'])

@section('body')
    {{-- header --}}
    <header class="text-center">
        <p><img src="{{ $siteLogo }}" height="30"></p>
    </header>

    {{-- main --}}
    <main class="m-4">
        {{-- quick login --}}
        @if ($connectServices)
            <div class="text-center">
                <h3 class="fs-4">{{ $fsLang['accountLoginByConnects'] }}</h3>
            </div>
            <div class="d-grid gap-2">
                @foreach($connectServices as $item)
                    <button class="btn btn-outline-dark rounded-pill mt-2" type="button" data-bs-toggle="modal" data-bs-target="#fresnsModal"
                        data-title="{{ $fsLang['accountLogin'] }}"
                        data-url="{{ $item['url'] }}"
                        data-post-message-key="fresnsLogin"
                        data-connect-platform-id="{{ $item['code'] }}">
                        {{ $item['name'] }}
                    </button>
                @endforeach
            </div>

            <div class="text-center my-4">
                <span class="badge text-bg-secondary">{{ $fsLang['modifierOr'] }}</span>
            </div>
        @endif

        {{-- register --}}
        <div class="text-center mb-3">
            <h3 class="fs-4">{{ $fsLang['accountRegister'] }}</h3>
        </div>
        <form class="api-request-form" action="{{ route('account-center.api.register') }}" method="post">
            {{-- type --}}
            @if ($fsConfig['account_email_register'] && $fsConfig['account_phone_register'])
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
                <input type="hidden" name="accountType" value="{{ $fsConfig['account_email_register'] ? 'email' : 'phone' }}">
            @endif

            {{-- account --}}
            <input type="hidden" name="countryCode" id="countryCode" value="{{ $smsDefaultCode }}">
            <div class="input-group mb-3">
                {{-- country code --}}
                @if (count($smsSupportedCodes) == 1)
                    <span class="input-group-text @if ($fsConfig['account_email_register']) d-none @endif">+{{ $smsDefaultCode }}</span>
                @else
                    <button class="btn btn-outline-secondary @if ($fsConfig['account_email_register']) d-none @endif" type="button" id="countryCodeButton" data-bs-toggle="modal" data-bs-target="#countryCodeModal">+{{ $smsDefaultCode }}</button>
                @endif

                {{-- input --}}
                <input type="{{ $fsConfig['account_email_register'] ? 'email' : 'number' }}" class="form-control form-control-lg input-number @if ($fsConfig['account_email_register']) rounded-start @endif" name="account" id="accountInfo" placeholder="{{ $fsConfig['account_email_register'] ? $fsLang['email'] : $fsLang['phone'] }}" required>
            </div>

            {{-- verify code --}}
            <div class="input-group mb-3">
                <input type="text" class="form-control form-control-lg" name="verifyCode" placeholder="{{ $fsLang['verifyCode'] }}" autocomplete="off" required>
                <button type="button" class="btn btn-outline-secondary send-verify-code" data-type="register" data-account-input-id="accountInfo" onclick="guestSendVerifyCode(this)">{{ $fsLang['sendVerifyCode'] }}</button>
            </div>

            {{-- password --}}
            <div class="input-group">
                <input type="text" class="form-control form-control-lg" name="password" placeholder="{{ $fsLang['password'] }}" autocomplete="off" required>
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

            {{-- nickname --}}
            <div class="input-group">
                <input type="text" class="form-control form-control-lg" name="nickname" placeholder="{{ $fsConfig['user_nickname_name'] }}" autocomplete="off" required>
            </div>

            {{-- nickname tips --}}
            <div class="form-text mb-3 ms-1">
                {{ $fsLang['settingNicknameWarning'] }}
                <div class="vr mx-2"></div>
                {{ $fsLang['modifierLength'] }}: {{ $fsConfig['nickname_min'].'~'.$fsConfig['nickname_max'] }} {{ $fsLang['unitCharacter'] }}
            </div>

            {{-- birthday --}}
            <div class="input-group">
                <span class="input-group-text">{{ $fsLang['userBirthday'] }}</span>
                <input type="date" class="form-control form-control-lg" name="birthday" placeholder="{{ $fsLang['userBirthday'] }}" min="1920-01-01" max="{{ date('Y-m-d') }}" required>
            </div>

            {{-- birthday tips --}}
            <div class="form-text mb-3 ms-1">
                {{ $fsLang['settingBirthdayTip'] }}
            </div>

            {{-- policies --}}
            <label class="form-text mb-4">
                <i class="bi bi-check-circle-fill"></i> {{ $fsLang['accountInfo'] }}
                @if ($fsConfig['account_terms_status'])
                    <a class="badge rounded-pill bg-success link-light text-decoration-none" data-bs-toggle="modal" href="#termsModal">{{ $fsLang['accountPoliciesTerms'] }}</a>
                @endif
                @if ($fsConfig['account_privacy_status'])
                    <a class="badge rounded-pill bg-success link-light text-decoration-none" data-bs-toggle="modal" href="#privacyModal">{{ $fsLang['accountPoliciesPrivacy'] }}</a>
                @endif
                @if ($fsConfig['account_cookie_status'])
                    <a class="badge rounded-pill bg-success link-light text-decoration-none" data-bs-toggle="modal" href="#cookieModal">{{ $fsLang['accountPoliciesCookie'] }}</a>
                @endif
            </label>

            {{-- submit button --}}
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg">{{ $fsLang['accountRegister'] }}</button>
            </div>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('account-center.login') }}" class="link-primary">{{ $fsLang['accountLoginGoTo'] }}</a>
        </div>
    </main>

    {{-- Terms Modal --}}
    @if ($fsConfig['account_terms_status'])
        <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="termsModalLabel">{{ $fsLang['accountPoliciesTerms'] }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {!! $fsConfig['account_terms_policy'] ? Str::markdown($fsConfig['account_terms_policy']) : '' !!}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $fsLang['close'] }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Privacy Modal --}}
    @if ($fsConfig['account_privacy_status'])
        <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="privacyModalLabel">{{ $fsLang['accountPoliciesPrivacy'] }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {!! $fsConfig['account_privacy_policy'] ? Str::markdown($fsConfig['account_privacy_policy']) : '' !!}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $fsLang['close'] }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Cookies Modal --}}
    @if ($fsConfig['account_cookie_status'])
        <div class="modal fade" id="cookieModal" tabindex="-1" aria-labelledby="cookieModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cookieModalLabel">{{ $fsLang['accountPoliciesCookie'] }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {!! $fsConfig['account_cookie_policy'] ? Str::markdown($fsConfig['account_cookie_policy']) : '' !!}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $fsLang['close'] }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
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
                        fresnsCallbackSend(res.data.loginToken);
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
