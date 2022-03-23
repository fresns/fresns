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

    <!--uninstall modal-->
    <div class="modal fade" id="uninstallConfirm" tabindex="-1" aria-labelledby="uninstall" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_uninstall') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="uninstallData">
                        <label class="form-check-label" for="uninstallData">{{ __('FsLang::panel.option_uninstall_plugin_data') }}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('FsLang::panel.button_cancel') }}</button>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-dismiss="modal" data-bs-target="#uninstallStepModal" id="uninstallSubmit">{{ __('FsLang::panel.button_confirm_uninstall') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!--uninstall step modal-->
    <div class="modal fade" id="uninstallStepModal" tabindex="-1" aria-labelledby="uninstallStepModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_uninstall') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body ps-5">
                    <p><i class="bi bi-x-lg text-danger me-2"></i>{{ __('FsLang::panel.uninstall_step_1') }}</p>
                    <p><i class="bi bi-check-lg text-success me-2"></i>{{ __('FsLang::panel.uninstall_step_2') }}</p>
                    <p><i class="bi bi-check-lg text-success me-2"></i>{{ __('FsLang::panel.uninstall_step_3') }}</p>
                    <p><i class="spinner-border spinner-border-sm me-2" role="status"></i>{{ __('FsLang::panel.uninstall_step_4') }}</p>
                    <p><i class="bi bi-hourglass text-secondary me-2"></i>{{ __('FsLang::panel.uninstall_step_5') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
