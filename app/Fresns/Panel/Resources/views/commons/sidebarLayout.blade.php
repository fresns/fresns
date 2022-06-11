@extends('FsView::commons.layout')

@section('body')
    @include('FsView::commons.header')

    <div class="container-fluid">
        <div class="row">
            <!--left-->
            @yield('sidebar')
            <!--left end-->
            <!--right-->
            <div class="col-lg-10 fresns-setting mt-3 mt-lg-0 p-lg-3">
                <!--setting-->
                <div class="bg-white mb-2 p-3 p-lg-5">
                    @yield('content')
                </div>
                <!--setting end-->
                @include('FsView::commons.footer')
            </div>
            <!--right end-->
        </div>
    </div>

    <!--delete modal-->
    <div class="modal fade" id="deleteConfirm" tabindex="-1" aria-labelledby="delete" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_delete') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('FsLang::panel.delete_desc') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('FsLang::panel.button_cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="deleteSubmit">{{ __('FsLang::panel.button_confirm_delete') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
