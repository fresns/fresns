<!doctype html>
<html lang="{{ App::getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Fresns" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="_token" content="{{ csrf_token() }}">
    <title>{{ __('FsLang::panel.fresns_panel') }}</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="{{ @asset('/static/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ @asset('/static/css/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ @asset('/static/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ @asset('/static/css/select2-bootstrap-5-theme.min.css') }}">
    <link rel="stylesheet" href="{{ @asset("/static/css/fresns-panel.css?{$versionMd5}") }}">
    @yield('css')
</head>

<body>
    @yield('body')

    <div class="fresns-tips">
        @include('FsView::commons.tips')
    </div>

    <script src="{{ @asset('/static/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ @asset('/static/js/jquery.min.js') }}"></script>
    <script src="{{ @asset('/static/js/select2.min.js') }}"></script>
    <script src="{{ @asset("/static/js/fresns-panel.js?{$versionMd5}") }}"></script>
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
    @yield('js')
</body>

</html>
