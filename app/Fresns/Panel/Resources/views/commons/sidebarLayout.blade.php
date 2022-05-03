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

    <!--install modal-->
    <div class="modal fade" id="installModal" tabindex="-1" aria-labelledby="install" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_install') }}: <span class="install-name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('panel.plugin.install') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('put')
                    <div class="modal-body">
                        <div class="input-group">
                            <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_plugin_input') }}</button>
                            <ul class="dropdown-menu selectInputType">
                                <li data-name="inputUnikey"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_plugin_input') }}</a></li>
                                <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_plugin_upload') }}</a></li>
                            </ul>
                            <input type="hidden" name="install_type">
                            <input type="text" class="form-control inputUnikey" name="plugin_unikey" maxlength="64">
                            <input type="file" class="form-control inputFile" name="plugin_zipball" accept=".zip" style="display:none;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#installStepModal" id="installSubmit">{{ __('FsLang::panel.button_confirm_install') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!--install artisan output modal-->
    <div class="modal fade" id="installStepModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="installStepModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_install') }}: <span class="install-name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" rows="6" id="install_artisan_output" readonly>{{ __('FsLang::tips.install_in_progress') }}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="reloadPage()">{{ __('FsLang::panel.button_close') }}</button>
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
                    <button type="button" class="btn btn-danger uninstall-plugin" data-bs-toggle="modal" data-bs-dismiss="modal" data-bs-target="#uninstallStepModal" id="uninstallSubmit">{{ __('FsLang::panel.button_confirm_uninstall') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!--uninstall artisan output modal-->
    <div class="modal fade" id="uninstallStepModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="uninstallStepModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_uninstall') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" rows="6" id="uninstall_artisan_output" readonly>{{ __('FsLang::tips.uninstall_in_progress') }}</textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="reloadPage()">{{ __('FsLang::panel.button_close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!--uninstall step modal-->
    {{-- <div class="modal fade" id="uninstallStepModal" tabindex="-1" aria-labelledby="uninstallStepModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_uninstall') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body ps-5">
                    <p><i class="bi bi-x-lg text-danger me-2"></i>{{ __('FsLang::tips.uninstall_step_1') }}</p>
                    <p><i class="bi bi-check-lg text-success me-2"></i>{{ __('FsLang::tips.uninstall_step_2') }}</p>
                    <p><i class="bi bi-check-lg text-success me-2"></i>{{ __('FsLang::tips.uninstall_step_3') }}</p>
                    <p><i class="spinner-border spinner-border-sm me-2" role="status"></i>{{ __('FsLang::tips.uninstall_step_4') }}</p>
                    <p><i class="bi bi-hourglass text-secondary me-2"></i>{{ __('FsLang::tips.uninstall_step_5') }}</p>
                </div>
            </div>
        </div>
    </div> --}}
@endsection
