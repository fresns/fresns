<!doctype html>
<html lang="{{ App::getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Fresns" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fresns {{ $code }}</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/css/bootstrap-icons.css">
    <link rel="stylesheet" href="/static/css/fresns-panel.css">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">
                    <img src="/static/images/logo.png" alt="Fresns" height="30" class="d-inline-block align-text-top">
                </a>
            </div>
        </nav>
    </header>

    <main class="container">
        <div class="card mx-auto my-5">
            <div class="card-body p-5">
                <h3 class="card-title">Fresns {{ $code }}</h3>
                <div class="mt-4">{!! $message !!}</div>
            </div>
        </div>
    </main>

    <footer>
        <div class="text-center pt-5">
            <p class="my-5 text-muted">Powered by Fresns</p>
        </div>
    </footer>
</body>

</html>
