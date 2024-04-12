<!doctype html>
<html lang="{{ $langTag }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Fresns" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ $siteName }}</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/css/bootstrap-icons.min.css">
    <style>
        .iframe-modal {
            width: 100%;
            overflow: auto;
        }
        .input-number::-webkit-inner-spin-button {
            -webkit-appearance: none;
        }
        .input-number::-webkit-outer-spin-button {
            -webkit-appearance: none;
        }
    </style>
    @stack('style')
</head>

<body>
    <div class="container p-3">
        <div class="row justify-content-center">
            <div class="col-12 col-md-4">
                @yield('body')
            </div>
        </div>
    </div>

    {{-- Country Code Modal --}}
    <div class="modal fade" id="countryCodeModal" tabindex="-1" aria-labelledby="countryCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm d-flex justify-content-center">
            <div class="modal-content w-50">
                <div class="modal-body p-0">
                    <div class="list-group">
                        <button type="button" class="list-group-item list-group-item-success">{{ $fsLang['countryCode'] }}</button>
                        @foreach($smsSupportedCodes as $code)
                            <button type="button" class="list-group-item list-group-item-action" data-bs-dismiss="modal" data-code="{{ $code }}" onclick="countryCodeSelect(this)">+{{ $code }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tip Toasts --}}
    <div id="fresns-tips"></div>

    {{-- Fresns Extensions Modal --}}
    <div class="modal fade" id="fresnsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="fresnsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body" style="padding:0"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $fsLang['close'] }}</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/static/js/bootstrap.bundle.min.js"></script>
    <script src="/static/js/jquery.min.js"></script>
    <script src="/static/js/js-cookie.min.js"></script>
    <script src="/static/js/fresns-callback.js"></script>
    @switch($captcha['type'])
        {{-- Turnstile (Cloudflare) --}}
        @case('turnstile')
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js"></script>
            @break

        {{-- reCAPTCHA (Google) --}}
        @case('reCAPTCHA')
            <script src="https://www.google.com/recaptcha/api.js?render={{ $captcha['siteKey'] }}"></script>
            @break

        {{-- hCaptcha (Intuition Machines) --}}
        @case('hCaptcha')
            <script src="https://js.hcaptcha.com/1/api.js?hl={{ $langTag }}" async defer></script>
            @break
    @endswitch

    <script>
        /* fresns token */
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // submit button
        $(document).on('submit', 'form', function () {
            var btn = $(this).find('button[type="submit"]');

            btn.prop('disabled', true);
            if (btn.children('.spinner-border').length == 0) {
                btn.prepend('<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> ');
            }
            btn.children('.spinner-border').removeClass('d-none');
        });

        // set timeout toast hide
        const setTimeoutToastHide = () => {
            $('.toast.show').each((k, v) => {
                setTimeout(function () {
                    $(v).hide();
                }, 1500);
            });
        };

        // tips
        window.tips = function (message) {
            let html = `<div aria-live="polite" aria-atomic="true" class="position-fixed top-50 start-50 translate-middle" style="z-index:2048">
                <div class="toast align-items-center text-bg-primary border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>`;

            $('#fresns-tips').prepend(html);

            setTimeoutToastHide();
        };

        // verify code timer
        var verifyCodeTime = Cookies.get('fresns_account_center_verify_code_time');

        var isNumeric = !isNaN(Number(verifyCodeTime));

        if (isNumeric && verifyCodeTime != 0) {
            let btnText = "{{ $fsLang['resendVerifyCode'] }}" + ' (' + verifyCodeTime + ')';

            $('.send-verify-code').prop('disabled', true);
            $('.send-verify-code').text(btnText);

            setTimeout(function () {
                setSendCodeTime();
            }, 1000);
        }

        function setSendCodeTime() {
            let verifyCodeTime = Cookies.get('fresns_account_center_verify_code_time');

            let btnText = "{{ $fsLang['sendVerifyCode'] }}";

            if (verifyCodeTime == 0) {
                $('.send-verify-code').prop('disabled', false);
                $('.send-verify-code').text(btnText);

                return;
            }

            verifyCodeTime--

            btnText = "{{ $fsLang['resendVerifyCode'] }}" + ' (' + verifyCodeTime + ')';

            $('.send-verify-code').prop('disabled', true);
            $('.send-verify-code').text(btnText);

            Cookies.set('fresns_account_center_verify_code_time', verifyCodeTime, { expires: 1 });

            setTimeout(function () {
                setSendCodeTime();
            }, 1000);
        }

        // guest send verify code
        function guestSendVerifyCode(obj) {
            let type = $(obj).data('type'),
                accountInputId = $(obj).data('account-input-id'),
                countryCodeInputId = $(obj).data('country-code-input-id');

            let account = '';
            let countryCode = '';

            if (accountInputId) {
                account = $('#' + accountInputId).val();
            }

            if (countryCodeInputId) {
                countryCode = $('#' + countryCodeInputId).val();
            }

            if (!account) {
                tips("{{ $accountEmptyError }}");

                return;
            }

            Cookies.set('fresns_account_center_verify_code_time', 60, { expires: 1 });
            setSendCodeTime();

            $.ajax({
                url: "{{ route('account-center.api.guest-send-verify-code') }}",
                type: 'post',
                data: {
                    'type': type,
                    'account': account,
                    'countryCode': countryCode,
                },
                error: function (error) {
                    tips(error.responseText);
                },
                success: function (res) {
                    if (res.code != 0) {
                        tips(res.message);

                        Cookies.set('fresns_account_center_verify_code_time', 0, { expires: 1 });
                        return;
                    }

                    tips("{{ $fsLang['send'].': '.$fsLang['success'] }}");
                },
            });
        }

        // click email
        function clickEmail() {
            $('#countryCodeButton').addClass('d-none');
            $('#accountInfo').addClass('rounded-start');

            var inputElement = document.getElementById('accountInfo');
            inputElement.type = 'email';
            inputElement.placeholder = "{{ $fsLang['email'] }}";
        };

        // click phone
        function clickPhone() {
            $('#countryCodeButton').removeClass('d-none');
            $('#accountInfo').removeClass('rounded-start');

            var inputElement = document.getElementById('accountInfo');
            inputElement.type = 'number';
            inputElement.placeholder = "{{ $fsLang['phone'] }}";
        };

        // country code select
        function countryCodeSelect(obj) {
            let code = $(obj).data('code');

            $('input[name="countryCode"]').val(code);

            $('#countryCodeButton').text('+' + code);

            var editPhoneModal = document.getElementById('editPhoneModal');
            if (editPhoneModal) {
                new bootstrap.Modal('#editPhoneModal').show();
            }
        };

        // make access token
        function makeAccessToken() {
            let accessToken;

            $.ajaxSettings.async = false;
            $.post("{{ route('account-center.api.make-access-token') }}", {}, function (res) {
                accessToken = res.data.accessToken;
            });
            $.ajaxSettings.async = true;

            return accessToken;
        }

        // fresns extensions modal
        $('#fresnsModal').on('show.bs.modal', function (e) {
            let button = $(e.relatedTarget),
                url = button.data('url'),
                replaceJson = button.data(),
                reg = /\{[^\}]+\}/g,
                searchArr = url.match(reg);

            if (searchArr) {
                searchArr.forEach(function (v) {
                    let attr = v.substring(1, v.length - 1);
                    if (replaceJson[attr]) {
                        url = url.replace(v, replaceJson[attr]);
                    } else {
                        if (v === '{accessToken}') {
                            url = url.replace('{accessToken}', makeAccessToken());
                        } else {
                            url = url.replace(v, '');
                        }
                    }
                });
            }

            let inputHtml = '<iframe src="' + url + '" class="iframe-modal" scrolling="yes" style="min-height:450px;"></iframe>';

            $(this).find('.modal-body').empty().html(inputHtml);
        });

        // fresns extensions callback
        @if (! Route::is('account-center.user-auth'))
            window.onmessage = function (event) {
                let callbackData = FresnsCallback.decode(event.data);

                if (callbackData.code == 40000) {
                    // callback data format error
                    return;
                }

                if (callbackData.code != 0) {
                    tips(callbackData.message);
                    return;
                }

                if (callbackData.action.windowClose) {
                    $('#fresnsModal').modal('hide');
                }

                if (callbackData.action.redirectUrl) {
                    window.location.href = callbackData.action.redirectUrl;
                }

                if (callbackData.action.postMessageKey == 'reload' || callbackData.action.dataHandler == 'reload') {
                    window.location.reload();
                }

                if (callbackData.data && callbackData.data.loginToken) {
                    $.ajax({
                        url: "{{ route('account-center.api.check-login-token') }}",
                        type: 'post',
                        data: {
                            'loginToken': callbackData.data.loginToken,
                        },
                        error: function (error) {
                            tips(error.responseText);
                        },
                        success: function (res) {
                            tips(res.message);

                            if (res.code == 31508) {
                                window.location.href = "{{ route('account-center.user-auth') }}";
                                return;
                            }

                            if (res.code != 0) {
                                return;
                            }

                            sendAccountCallback(callbackData.data.loginToken);
                        },
                    });
                }
            };
        @endif

        // fresns extensions send
        function sendAccountCallback(loginToken) {
            let callbackAction = {
                postMessageKey: Cookies.get('fresns_post_message_key'),
                windowClose: true,
                redirectUrl: '',
                dataHandler: '',
            };
            let apiData = {
                loginToken: loginToken
            };

            // /static/js/fresns-callback.js
            FresnsCallback.send(callbackAction, apiData);
        }
    </script>

    @stack('script')
</body>

</html>
