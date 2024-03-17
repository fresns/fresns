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

    {{-- Tip Toasts --}}
    <div id="fresns-tips"></div>

    {{-- Fresns Extensions Modal --}}
    <div class="modal fade fresnsExtensions" id="fresnsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="fresnsModalLabel" aria-hidden="true">
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
    <script src="/static/js/iframeResizer.min.js"></script>
    <script>
        $(document).on('submit', 'form', function () {
            var btn = $(this).find('button[type="submit"]');

            btn.prop('disabled', true);
            if (btn.children('.spinner-border').length == 0) {
                btn.prepend(
                    '<span class="spinner-border spinner-border-sm mg-r-5 d-none" role="status" aria-hidden="true"></span> '
                );
            }
            btn.children('.spinner-border').removeClass('d-none');
        });

        $.ajaxSetup({
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
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

        // send timer
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

        // send verify code
        function sendVerifyCode(obj) {
            let type = $(obj).data('type'),
                templateId = $(obj).data('template-id');
                countryCodeSelectId = $(obj).data('country-code-select-id'),
                accountInputId = $(obj).data('account-input-id');

            let countryCode = '',
                account = '';

            if (countryCodeSelectId) {
                countryCode = $('#' + countryCodeSelectId).val();
            }

            if (accountInputId) {
                account = $('#' + accountInputId).val();
            }

            if (templateId != 3 && templateId != 4 && !account) {
                tips("{{ $fsLang['errorEmpty'] }}");

                return;
            }

            $.ajax({
                url: "{{ route('account-center.send-verify-code') }}",
                type: 'post',
                data: {
                    'type': type,
                    'account': account,
                    'countryCode': countryCode,
                    'templateId': templateId,
                },
                error: function (error) {
                    tips(error.responseJSON.message);
                },
                success: function (res) {
                    if (res.code != 0) {
                        tips(res.message);

                        return;
                    }

                    tips("{{ $fsLang['send'].': '.$fsLang['success'] }}");

                    Cookies.set('fresns_account_center_verify_code_time', 60, { expires: 1 });
                    setSendCodeTime();
                },
            });
        }

        // make access token
        function makeAccessToken() {
            let accessToken;

            $.ajaxSettings.async = false;
            $.post("{{ route('account-center.make-access-token') }}", {}, function (res) {
                accessToken = res.data.accessToken;
            });
            $.ajaxSettings.async = true;

            return accessToken;
        }

        (function ($) {
            $('#fresnsModal.fresnsExtensions').on('show.bs.modal', function (e) {
                let button = $(e.relatedTarget),
                    modalHeight = button.data('modal-height'),
                    modalWidth = button.data('modal-width'),
                    reg = /\{[^\}]+\}/g,
                    url = button.data('url'),
                    replaceJson = button.data(),
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

                let inputHtml = `<iframe src="` + url + `" class="iframe-modal"></iframe>`;
                $(this).find('.modal-body').empty().html(inputHtml);

                // iFrame Resizer
                let isOldIE = navigator.userAgent.indexOf('MSIE') !== -1;
                $('#fresnsModal.fresnsExtensions iframe').on('load', function () {
                    $(this).iFrameResize({
                        autoResize: true,
                        minHeight: modalHeight ? modalHeight : 300,
                        heightCalculationMethod: isOldIE ? 'max' : 'lowestElement',
                        scrolling: true,
                    });
                });
            });
        })(jQuery);

        // fresns extensions callback
        window.onmessage = function (event) {
            let fresnsCallback;

            try {
                fresnsCallback = JSON.parse(event.data);
            } catch (error) {
                return;
            }

            console.log('fresnsCallback', fresnsCallback);

            if (!fresnsCallback) {
                return;
            }

            if (fresnsCallback.code != 0) {
                if (fresnsCallback.message) {
                    tips(fresnsCallback.message);
                }
                return;
            }

            window.location.reload();
        };
    </script>
    @stack('script')
</body>

</html>
