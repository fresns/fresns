@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::extensions.sidebar')
@endsection

@section('content')
    <!--panel header-->
    <div class="row mb-4">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_panels') }}</h3>
            <p class="text-secondary"><i class="bi bi-layers"></i> {{ __('FsLang::panel.sidebar_panels_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a href="{{ route('panel.panels.index') }}" class="nav-link {{ is_null($isEnable) ? 'active' : '' }}" type="button">{{ __('FsLang::panel.sidebar_plugins_tab_all') }}</a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="{{ route('panel.panels.index', ['status' => 'active']) }}" class="nav-link {{ $isEnable == 1 ? 'active' : '' }}" type="button">{{ __('FsLang::panel.sidebar_plugins_tab_active') }} ({{ $enableCount }})</a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="{{ route('panel.panels.index', ['status' => 'inactive']) }}" class="nav-link {{ !is_null($isEnable) && $isEnable == 0 ? 'active' : '' }}" type="button">{{ __('FsLang::panel.sidebar_plugins_tab_inactive') }}({{ $disableCount }})</a>
            </li>
        </ul>
    </div>
    <!--panel list-->
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
                @foreach ($panels as $panel)
                    <tr>
                        <td class="py-3">
                            <img src="/assets/plugins/{{ $panel->unikey }}/fresns.png" class="me-2" width="44" height="44">
                            <span class="fs-6">{{ $panel->name }}</span>
                            <span class="badge bg-secondary fs-9">{{ $panel->version }}</span>
                            @if ($panel->is_upgrade)
                                <a href="{{ route('panel.upgrades') }}" class="badge rounded-pill bg-danger fs-9 fresns-link">{{ __('FsLang::panel.new_version') }}</a>
                            @endif
                        </td>
                        <td>{{ $panel->description }}</td>
                        <td><a href="{{ $panel->author_link }}" target="_blank" class="link-info fresns-link fs-7">{{ $panel->author }}</a></td>
                        <td>
                            @if ($panel->is_enable)
                                <button type="button" class="btn btn-outline-secondary btn-sm plugin-manage" data-action="{{ route('panel.plugin.update', ['plugin' => $panel->unikey]) }}" data-enable="0">{{ __('FsLang::panel.button_deactivate') }}</button>
                                @if ($panel->settings_path)
                                    <a href="{{ route('panel.iframe.setting', ['url' => $panel->settings_path]) }}" class="btn btn-primary btn-sm">{{ __('FsLang::panel.button_setting') }}</a>
                                @endif
                            @else
                                <button type="button" class="btn btn-outline-success btn-sm plugin-manage" data-action="{{ route('panel.plugin.update', ['plugin' => $panel->unikey]) }}" data-enable="1">{{ __('FsLang::panel.button_activate') }}</button>
                                <button type="button" class="btn btn-link btn-sm ms-2 text-danger fresns-link plugin-uninstall-button" data-action="{{ route('panel.plugin.uninstall', ['plugin' => $panel->unikey]) }}" data-name="{{ $panel->name }}" data-clear_data_desc="{{ __('FsLang::panel.option_uninstall_plugin_data') }}" data-bs-toggle="modal" data-bs-target="#uninstallConfirm">{{ __('FsLang::panel.button_uninstall') }}</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!--panel list end-->
@endsection
