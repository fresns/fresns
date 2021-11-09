<!doctype html>
<html lang="{{ App::getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Fresns" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fresns &rsaquo; @lang('install.title')</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/css/bootstrap-icons.css">
</head>

<body>

    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <div class="navbar-brand">
                    <img src="/static/images/fresns-logo.png" alt="Fresns" height="30" class="d-inline-block align-text-top">
                </div>
            </div>
        </nav>
    </header>

    <main class="container">
        <div class="card mx-auto my-5" style="max-width:400px;">
            <div class="card-body">
                <select class="form-select" size="12">
                    <option value="en">English - English</option>
                    <option value="es">Español - Spanish</option>
                    <option value="fr">Français - French</option>
                    <option value="de">Deutsch - German</option>
                    <option value="ja">日本語 - Japanese</option>
                    <option value="ko">한국어 - Korean</option>
                    <option value="ru">Русский - Russian</option>
                    <option value="pt">Português - Portuguese</option>
                    <option value="id">Bahasa Indonesia - Indonesian</option>
                    <option value="hi">हिन्दी - Hindi</option>
                    <option value="zh-Hans" selected>简体中文 - Chinese (Simplified)</option>
                    <option value="zh-Hant">繁體中文 - Chinese (Traditional)</option>
                </select>
                <div class="clearfix mt-3">
                    <div class="float-end">
                        <a data-href="{{ route('install.step1') }}" onclick="install_step1()" id="submit" class="btn btn-outline-primary"><i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="/static/js/bootstrap.bundle.min.js"></script>
    <script src="/static/js/jquery-3.6.0.min.js"></script>
    <script>
        function install_step1(){
            var href = $('#submit').data('href');
            var lang = $('.form-select option:selected').val();
            location.href = href+'?lang='+lang
        }
    </script>
</body>
</html>
