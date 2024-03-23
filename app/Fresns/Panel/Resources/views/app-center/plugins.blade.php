@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::app-center.sidebar')
@endsection

@section('content')
    <!--header-->
    <div class="row mb-4">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_plugins') }}</h3>
            <p class="text-secondary"><i class="bi bi-journal-code"></i> {{ __('FsLang::panel.sidebar_plugins_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <form method="post" action="{{ route('panel.plugin.check.status') }}">
                    @csrf
                    @method('post')
                    <button class="btn btn-primary" type="submit"><i class="bi bi-arrow-clockwise"></i> {{ __('FsLang::panel.button_check_status') }}</button>
                </form>
                {{-- <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a> --}}
            </div>
        </div>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a href="{{ route('panel.app-center.plugins') }}" class="nav-link {{ is_null($isEnabled) ? 'active' : '' }}" type="button">{{ __('FsLang::panel.sidebar_plugins_tab_all') }}</a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="{{ route('panel.app-center.plugins', ['status' => 'active']) }}" class="nav-link {{ $isEnabled == 1 ? 'active' : '' }}" type="button">{{ __('FsLang::panel.sidebar_plugins_tab_active') }} ({{ $enableCount }})</a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="{{ route('panel.app-center.plugins', ['status' => 'inactive']) }}" class="nav-link {{ !is_null($isEnabled) && $isEnabled == 0 ? 'active' : '' }}" type="button">{{ __('FsLang::panel.sidebar_plugins_tab_inactive') }}({{ $disableCount }})</a>
            </li>
        </ul>
    </div>

    <!--list-->
    <div class="table-responsive">
        <table class="table table-hover align-middle fs-7">
            <thead>
                <tr class="table-info fs-6">
                    <th scope="col">{{ __('FsLang::panel.table_name') }}</th>
                    <th scope="col" class="w-50">{{ __('FsLang::panel.table_description') }}</th>
                    <th scope="col">{{ __('FsLang::panel.author') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($plugins as $plugin)
                    <tr>
                        <td class="py-3">
                            <img src="/assets/{{ $plugin->fskey }}/fresns.png" class="me-2" width="44" height="44">
                            <span class="fs-6"><a href="{{ $marketplaceUrl.'/detail/'.$plugin->fskey }}" target="_blank" class="link-dark fresns-link">{{ $plugin->name }}</a></span>
                            <span class="badge bg-secondary fs-9">{{ $plugin->version }}</span>
                            @if ($plugin->is_upgrade)
                                <a href="{{ route('panel.upgrades') }}" class="badge rounded-pill bg-danger link-light fs-9 fresns-link">{{ __('FsLang::panel.new_version') }}</a>
                            @endif
                        </td>
                        <td>{{ $plugin->description }}</td>
                        <td>
                            @if ($plugin->author_link)
                                <a href="{{ $plugin->author_link }}" target="_blank" class="link-info fresns-link fs-7">{{ $plugin->author }}</a>
                            @else
                                <span class="fs-7">{{ $plugin->author }}</span>
                            @endif
                        </td>
                        <td {!! App::getLocale() == 'en' ? 'style="width:210px"' : '' !!}>
                            @if ($plugin->is_enabled)
                                <button type="button" class="btn btn-outline-secondary btn-sm plugin-manage" data-action="{{ route('panel.plugin.update', ['fskey' => $plugin->fskey]) }}" data-enable="0">{{ __('FsLang::panel.button_deactivate') }}</button>
                                @if ($plugin->settings_path)
                                    <a href="{{ route('panel.app-center.plugin.settings', ['url' => $plugin->settings_path]) }}" class="btn btn-primary btn-sm px-4">{{ __('FsLang::panel.button_setting') }}</a>
                                @endif
                            @else
                                <button type="button" class="btn btn-outline-success btn-sm plugin-manage" data-action="{{ route('panel.plugin.update', ['fskey' => $plugin->fskey]) }}" data-enable="1">{{ __('FsLang::panel.button_activate') }}</button>
                                <button type="button" class="btn btn-link btn-sm ms-2 text-danger fresns-link" data-action="{{ route('panel.plugin.uninstall', ['fskey' => $plugin->fskey]) }}" data-name="{{ $plugin->name }}" data-bs-toggle="modal" data-bs-target="#uninstallConfirm">{{ __('FsLang::panel.button_uninstall') }}</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($plugins instanceof \Illuminate\Pagination\LengthAwarePaginator)
        {{ $plugins->appends(request()->all())->links() }}
    @endif

    <!-- uninstall modal -->
    <div class="modal fade" id="uninstallConfirm" tabindex="-1" aria-labelledby="uninstall" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_uninstall') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="uninstallData" id="uninstallData">
                        <label class="form-check-label" for="uninstallData">{{ __('FsLang::panel.option_uninstall_plugin_data') }}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('FsLang::panel.button_cancel') }}</button>
                    <button type="button" class="btn btn-danger uninstall-plugin ajax-progress-submit" data-bs-toggle="modal" data-bs-dismiss="modal" data-bs-target="#uninstallStepModal" id="uninstallSubmit">{{ __('FsLang::panel.button_confirm_uninstall') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- uninstall artisan output modal -->
    <div class="modal fade" id="uninstallStepModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="uninstallStepModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_uninstall') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="reloadPage()"></button>
                </div>
                <div class="modal-body">
                    <pre class="form-control" id="uninstall_artisan_output">{{ __('FsLang::tips.uninstall_in_progress') }}</pre>

                    <!--progress bar-->
                    <div class="mt-2">
                        <div class="ajax-progress progress w-100 d-none" id="uninstall-progress"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="reloadPage()">{{ __('FsLang::panel.button_close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
