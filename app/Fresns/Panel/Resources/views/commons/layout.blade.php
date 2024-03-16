<!doctype html>
<html lang="{{ App::getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Fresns" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('FsLang::panel.fresns_panel') }}</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/static/css/bootstrap.min.css?v={{ $versionMd5 }}">
    <link rel="stylesheet" href="/static/css/bootstrap-icons.min.css?v={{ $versionMd5 }}">
    <link rel="stylesheet" href="/static/css/select2.min.css?v={{ $versionMd5 }}">
    <link rel="stylesheet" href="/static/css/select2-bootstrap-5-theme.min.css?v={{ $versionMd5 }}">
    <link rel="stylesheet" href="/static/css/fresns-panel.css?v={{ $versionMd5 }}">
    @stack('css')
</head>

<body>
    @yield('body')

    <div class="fresns-tips">
        @include('FsView::commons.tips')
    </div>

    <script src="/static/js/bootstrap.bundle.min.js?v={{ $versionMd5 }}"></script>
    <script src="/static/js/jquery.min.js?v={{ $versionMd5 }}"></script>
    <script src="/static/js/select2.min.js?v={{ $versionMd5 }}"></script>
    <script src="/static/js/ansi_up.js?v={{ $versionMd5 }}"></script>
    <script src="/static/js/fresns-panel.js?v={{ $versionMd5 }}"></script>
    <script>
        $(document).ready(function () {
            window.locale = $('html').attr('lang')
            if (window.locale) {
                $.ajax({
                    url: "{{ route('panel.translations', ['locale' => \App::getLocale()]) }}",
                    method: 'get',
                    success(response) {
                        if (response.data) {
                            window.translations = response.data
                        } else {
                            console.error('Failed to get translation')
                        }
                    }
                })
            }
        })
    </script>
    @stack('script')
</body>

</html>
