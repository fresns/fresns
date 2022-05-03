@extends('FsView::commons.layout')

@section('body')
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
@endsection
