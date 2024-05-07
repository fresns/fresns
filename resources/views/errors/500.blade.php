<!doctype html>
<html lang="{{ App::getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Fresns" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fresns 500</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
</head>

<body style="background-color: #f0f2f5;">
    <header>
        <nav class="navbar navbar-expand-lg bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="/"><img src="/static/images/logo.png" alt="Fresns" height="30" class="d-inline-block align-text-top"> 500</a>
                <span class="navbar-text">Internal Server Error</span>
                <ul class="navbar-nav me-auto">
                </ul>
            </div>
        </nav>
    </header>

    <main class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <img src="/static/images/500.png" loading="lazy" alt="404" style="max-width: 100%;">
            </div>
            <div class="col-12 col-md-10 col-lg-8">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Ways to share Fresns error messages</h5>
                        <p class="card-text">Modify the <code>.env</code> configuration to enable Debug mode, which allows you to view detailed error information or generate a link to share the error information.</p>
                        <a href="https://discuss.fresns.org/post/4IJjps9p" target="_blank" class="btn btn-primary">Introduction</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="text-center pt-5">
            <p class="my-5 text-muted">Powered by <a href="https://fresns.org" target="_blank" class="link-secondary">Fresns</a></p>
        </div>
    </footer>
</body>

</html>
