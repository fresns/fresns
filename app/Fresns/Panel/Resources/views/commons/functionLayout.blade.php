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
    <link rel="stylesheet" href="{{ @asset('/static/css/fresns-panel.css?9c26d1a06118c93e') }}">
    @yield('css')
</head>

<body>
    @yield('body')

    <!--fresns tips-->
    <div class="fresns-tips">
        @include('FsView::commons.tips')
    </div>

    <!--imageZoom-->
    <div class="modal fade image-zoom" id="imageZoom" tabindex="-1" aria-labelledby="imageZoomLabel" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="position-relative image-box">
                <img class="img-fluid" src="">
            </div>
        </div>
    </div>

    <footer>
        <div class="copyright text-center">
            <p class="mt-5 mb-5 text-muted">&copy; 2021 Fresns</p>
        </div>
    </footer>

    <script src="{{ @asset('/static/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ @asset('/static/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ @asset('/static/js/select2.min.js') }}"></script>
    <script src="{{ route('panel.translations', ['locale' => \App::getLocale()]) }}"></script>
    <script src="{{ @asset('/static/js/fresns-functions.js?9c26d1a06118c93e') }}"></script>
    @yield('js')
</body>

</html>
