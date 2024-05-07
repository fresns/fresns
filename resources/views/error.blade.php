@use('App\Helpers\ConfigHelper')

@php
    $email = ConfigHelper::fresnsConfigByItemKey('site_email');
@endphp

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
</head>

<body style="background-color: #f0f2f5;">
    <header>
        <nav class="navbar navbar-expand-lg bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="/"><img src="/static/images/logo.png" alt="Fresns" height="30" class="d-inline-block align-text-top"></a>
            </div>
        </nav>
    </header>

    <main class="container">
        <div class="card mx-auto my-5">
            <div class="card-body p-5">
                <h3 class="card-title">Fresns {{ $code }}</h3>
                <div class="mt-4">{!! $message !!}</div>

                <div class="mt-4">
                    <button type="button" class="btn btn-outline-primary" onclick="refreshPage()"><i class="bi bi-arrow-clockwise"></i></button>
                </div>

                @if ($email)
                    <div class="mt-4 pt-3">Administrator Email: <a href="mailto:{{ $email }}">{{ $email }}</a></div>
                @endif
            </div>
        </div>
    </main>

    <footer>
        <div class="text-center pt-5">
            <p class="my-5 text-muted">Powered by <a href="https://fresns.org" target="_blank" class="link-secondary">Fresns</a></p>
        </div>
    </footer>

    <script src="/static/js/jquery.min.js"></script>
    <script>
        // Spinner for button click
        $(document).on('click', 'button', function () {
            var btn = $(this);
            btn.prop('disabled', true);

            // Hide <i> element
            btn.find('i').hide();

            // Add spinner if it does not exist
            if (0 === btn.children('.spinner-border').length) {
                btn.prepend(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> '
                );
            }

            // Perform other actions (e.g., reload the page)
            if (btn.attr('onclick')) {
                eval(btn.attr('onclick'));
            }
        });

        function refreshPage() {
            location.reload();
        }
    </script>
</body>

</html>
