@extends('FsView::commons.layout')

@section('body')
    <header class="form-signin text-center">
        <img class="mt-3 mb-2" src="{{ @asset('/static/images/icon.png') }}" alt="Fresns" width="72" height="72">
        <h2 class="mb-5">{{ __('FsLang::panel.fresns_panel') }}</h2>
        <h4 class="mb-3 fw-normal">{{ __('FsLang::panel.language') }}</h4>
        <select class="form-select mb-5 change-lang" aria-label=".form-select-lg example">
            @foreach ($langs as $code => $lang)
                <option value="{{ $code }}" @if ($code == \App::getLocale()) selected @endif>{{ $lang }}</option>
            @endforeach
        </select>
    </header>

    <main class="container">
        <div class="card mx-auto my-5" style="max-width:800px;">
            <div class="card-body p-5">
                <h3 class="card-title">{{ __('FsLang::tips.auth_empty_title') }}</h3>
                <p class="mt-3 mb-0">{{ __('FsLang::tips.auth_empty_description') }}</p>
            </div>
        </div>

        <div class="text-center">
            <p class="my-5 text-muted">&copy; 2021-2022 Fresns</p>
        </div>
    </main>
@endsection

@section('js')
    <script>
        $('.change-lang').change(function() {
            var lang = $(this).val();
            let url = new URL(window.location.href);
            url.searchParams.set('lang', lang);
            window.location.href = url.href;
        });
    </script>
@endsection
