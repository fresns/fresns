@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--website header-->
    <div class="row mb-4">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_website') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_website_engines_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
        <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link active" href="{{ route('panel.engines.index') }}">{{ __('FsLang::panel.sidebar_website_tab_engines') }}</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('panel.themes.index') }}">{{ __('FsLang::panel.sidebar_website_tab_themes') }}</a></li>
        </ul>
    </div>
    <!--engine list-->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr class="table-info">
                    <th scope="col">{{ __('FsLang::panel.sidebar_website_tab_engines') }} <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.engine_table_name_desc') }}"></i></th>
                    <th scope="col">{{ __('FsLang::panel.author') }}</th>
                    <th scope="col">{{ __('FsLang::panel.engine_theme_title') }}</th>
                    <th scope="col" class="text-center">{{ __('FsLang::panel.table_options') }} <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.engine_table_options_desc') }}"></i></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($engines as $engine)
                    <tr>
                        <th scope="row" class="py-3">{{ $engine->name }} <span class="badge bg-secondary plugin-version">{{ $engine->version }}</span>
                            @if ($engine->is_upgrade)
                                <a href="{{ route('panel.upgrades') }}"><span class="badge rounded-pill bg-danger plugin-version">{{ __('FsLang::panel.new_version') }}</span></a>
                            @endif
                        </th>
                        <td><a href="{{ $engine->author_link }}" target="_blank" class="link-info fresns-link fs-7">{{ $engine->author }}</a></td>
                        <td>
                            <span class="badge bg-light text-dark"><i class="bi bi-laptop"></i>
                                @if ($pcPlugin = optional($configs->where('item_key', $engine->unikey.'_Pc')->first())->item_value )
                                    {{ $plugins[$pcPlugin] ?? '' }}
                                @else
                                    {{ __('FsLang::panel.engine_table_theme_null') }}
                                @endif
                            </span>
                            <span class="badge bg-light text-dark"><i class="bi bi-phone"></i>
                                @if ($mobilePlugin = optional($configs->where('item_key', $engine->unikey.'_Mobile')->first())->item_value)
                                    {{ $plugins[$mobilePlugin] }}
                                @else
                                    {{ __('FsLang::panel.engine_table_theme_null') }}
                                @endif
                            </span>
                        </td>
                        <td class="text-center">
                            @if ($engine->is_enable)
                                <button type="button" class="btn btn-outline-secondary btn-sm plugin-manage" data-action="{{ route('panel.plugin.update', ['plugin' => $engine->unikey]) }}" data-enable="0">{{ __('FsLang::panel.button_deactivate') }}</button>
                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                    data-action="{{ route('panel.engines.theme.update', ['engine' => $engine->id]) }}"
                                    data-params="{{ $engine->toJson() }}"
                                    data-pc_plugin="{{ optional($configs->where('item_key', $engine->unikey . '_Pc')->first())->item_value }}"
                                    data-mobile_plugin="{{ optional($configs->where('item_key', $engine->unikey . '_Mobile')->first())->item_value }}"
                                    data-bs-target="#themeSetting">{{ __('FsLang::panel.engine_theme_title') }}</button>
                                @if ($engine->settings_path)
                                    <a href="{{ route('panel.iframe.client', ['url' => $engine->settings_path]) }}" class="btn btn-primary btn-sm">{{ __('FsLang::panel.button_setting') }}</a>
                                @endif
                            @else
                                <button type="button" class="btn btn-outline-success btn-sm plugin-manage" data-action="{{ route('panel.plugin.update', ['plugin' => $engine->unikey]) }}" data-enable="1">{{ __('FsLang::panel.button_activate') }}</button>
                                <button type="button" class="btn btn-link btn-sm ms-2 text-danger fresns-link plugin-uninstall-button" data-action="{{ route('panel.plugin.uninstall', ['plugin' => $engine->unikey]) }}" data-name="{{ $engine->name }}" data-clear_data_desc="{{ __('FsLang::panel.option_uninstall_plugin_data') }}" data-bs-toggle="modal" data-bs-target="#uninstallConfirm">{{ __('FsLang::panel.button_uninstall') }}</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!--engine list end-->

    <!-- Modal -->
    <div class="modal fade" id="themeSetting" tabindex="-1" aria-labelledby="themeSetting" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.engine_theme_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        @csrf
                        @method('put')
                        <div class="form-floating mb-3">
                            <select class="form-select" id="pcTheme" aria-label="Floating label select example">
                                <option value="" selected>{{ __('FsLang::panel.engine_theme_option_no') }}</option>
                                @foreach ($themes as $theme)
                                    <option value="{{ $theme->unikey }}" @if (! $theme->is_enable) disabled @endif>{{ $theme->name }}</option>
                                @endforeach
                            </select>
                            <label for="PCtheme"><i class="bi bi-laptop"></i> {{ __('FsLang::panel.engine_theme_pc') }}</label>
                        </div>
                        <div class="form-floating mb-4">
                            <select class="form-select" id="mobileTheme" aria-label="Floating label select example">
                                <option value="" selected>{{ __('FsLang::panel.engine_theme_option_no') }}</option>
                                @foreach ($themes as $theme)
                                    <option value="{{ $theme->unikey }}" @if (! $theme->is_enable) disabled @endif>{{ $theme->name }}</option>
                                @endforeach
                            </select>
                            <label for="mobileTheme"><i class="bi bi-phone"></i> {{ __('FsLang::panel.engine_theme_mobile') }}</label>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
