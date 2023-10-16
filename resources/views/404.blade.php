<!doctype html>
<html lang="{{ App::getLocale() }}">

@php
    use App\Helpers\ConfigHelper;

    $email = ConfigHelper::fresnsConfigByItemKey('site_email');
@endphp

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Fresns" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fresns 404</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/css/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/static/css/fresns-panel.css">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">
                    <img src="/static/images/logo.png" alt="Fresns" height="30" class="d-inline-block align-text-top">
                    404
                </a>
                <span class="navbar-text">Page Not Found</span>
                <ul class="navbar-nav me-auto">
                </ul>
            </div>
        </nav>
    </header>

    <main class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <img src="/static/images/404.png" loading="lazy" alt="404" style="max-width: 100%;">
            </div>
        </div>

        <div class="text-center py-4">
            @if ($email)
                Administrator Email: <a href="mailto:{{ $email }}">{{ $email }}</a>
            @endif
        </div>
    </main>

    <footer>
        <div class="text-center pt-5">
            <p class="my-5 text-muted">Powered by Fresns</p>
        </div>
    </footer>
</body>

</html>
