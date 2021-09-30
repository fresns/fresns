<!doctype html>
<html lang="{{ $lang }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Fresns" />
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no,viewport-fit=cover">
    <title>Fresns Console</title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/static/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/css/bootstrap-icons.css">
    <link rel="stylesheet" href="/static/css/console.css">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">
                    <img src="/static/images/fresns-logo.png" alt="Fresns" height="30" class="d-inline-block align-text-top">
                </a>
            </div>
        </nav>
    </header>
    
    <main class="container">
        <div class="card mx-auto my-5" style="max-width:800px;">
            <div class="card-body p-5">
                <h3 class="card-title" lang="en">Please use the correct portal to login to the console</h3>
                <p lang="en">You are logged out and cannot access the console page, please visit the login portal to login.</p>
                <h3 class="card-title mt-5" lang="zh-Hant">請使用正確的入口登錄控制台</h3>
                <p lang="zh-Hant">您已退出登錄，無法訪問控制台頁面，請訪問登錄入口頁登錄。</p>
                <h3 class="card-title mt-5" lang="zh-Hans">请使用正确的入口登录控制台</h3>
                <p lang="zh-Hans">您已退出登录，无法访问控制台页面，请访问登录入口页登录。</p>
                <h3 class="card-title mt-5" lang="ja">正しいポータルを使ってコンソールにログインしてください</h3>
                <p lang="ja">ログアウトしていてコンソールページにアクセスできません。ログインポータルにアクセスしてログインしてください。</p>
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