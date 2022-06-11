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
        <textarea class="form-control" rows="12" id="url_install_artisan_output" readonly>{{ __('FsLang::tips.install_in_progress') }}</textarea>
    </div>
@endsection
