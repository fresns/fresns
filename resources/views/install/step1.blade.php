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
                    <span class="ms-2">@lang('install.desc')</span>
                </div>
            </div>
        </nav>
    </header>

    <main class="container">
        <div class="card mx-auto my-5" style="max-width:800px;">
            <div class="card-body p-lg-5">
                <h3 class="card-title">@lang('install.step1Title')</h3>
                <ul class="list-group list-group-flush my-4">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>@lang('install.step1CheckPhpVersion')</span>
                        <span id="php_version_status">-</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>@lang('install.step1CheckHttps')</span>
                        <span id="https_status">-</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>@lang('install.step1CheckFolderOwnership')</span>
                        <span id="folder_status">-</span>
                    </li>
                    <!--Extensions: fileinfo-->
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>@lang('install.step1CheckPhpExtensions')</span>
                        <span id="extensions_status">-</span>
                    </li>
                    <!--Functions: putenv,symlink,readlink,proc_open-->
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>@lang('install.step1CheckPhpFunctions') </span>
                        <span id="functions_status">-</span>
                    </li>
                </ul>
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-primary ms-3" onclick="window.location.reload()">@lang('install.checkBtn')</button>
                    <a class="btn btn-primary me-3" href="{{ route('install.step2') }}" id="next_step" style="display: none;">@lang('install.nextBtn')</a>
                </div>
            </div>
        </div>
    </main>

    <script src="/static/js/bootstrap.bundle.min.js"></script>
    <script src="/static/js/jquery-3.6.0.min.js"></script>
    <script>
        var items = [
            "php_version",
            "https",
            "folder",
            "extensions",
            "functions",
        ];
        var counts = 0;

        //check
        (function detect() {
            var name = items[0];
            $.ajax({
                type: "POST",
                dataType: "json",
                cache: false,
                url: '<?php echo route('install.env'); ?>',
                data: {name: name},
                success: function (data) {
                    if(data.code == '000000'){
                        counts++;
                    }
                    if ($('#' + name + '_status').length && data.result !== undefined) {
                        $('#' + name + '_status').html(data.result);
                    }
                },
                complete: function () {
                    items.shift();
                    if (items.length) {
                        setTimeout(function () {detect();}, 20);
                    }else{
                        if (counts === 5){
                            $('#next_step').show();
                        }
                    }
                }
            });
        })();
    </script>
</body>
</html>
