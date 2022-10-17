@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::extensions.sidebar')
@endsection

@section('content')
    <!--install header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.install_application') }}</h3>
        </div>
    </div>
    <!--install modal-->
    <div class="row">
        <pre class="form-control" id="url_install_artisan_output">{{ __('FsLang::tips.install_in_progress') }}</pre>
    </div>
@endsection
