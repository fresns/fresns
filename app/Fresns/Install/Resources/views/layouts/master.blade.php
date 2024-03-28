<!doctype html>
<html lang="{{ App::getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Fresns" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Fresns {{ __('Install::install.title') }}</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/css/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/static/css/fresns-panel.css">
</head>

<body>
    @include('Install::layouts.header')

    <main class="container">
        @yield('body')
    </main>

    {{-- Tip Toasts --}}
    <div id="fresns-tips"></div>

    <script src="/static/js/bootstrap.bundle.min.js"></script>
    <script src="/static/js/jquery.min.js"></script>
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
                }, 3000);
            });
        };

        // tips
        window.tips = function (message, data) {
            let html = `<div aria-live="polite" aria-atomic="true" class="position-fixed top-50 start-50 translate-middle" style="z-index:2048">
                <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header">
                        <img src="/static/images/icon.png" width="20px" height="20px" class="me-2" alt="Fresns">
                        <strong class="me-auto">Fresns</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        <p class="mb-2">${message}</p>
                        <p class="mb-0">${data}</p>
                    </div>
                </div>`;

            $('#fresns-tips').prepend(html);

            setTimeoutToastHide();
        };
    </script>
    @stack('script')
</body>

</html>
