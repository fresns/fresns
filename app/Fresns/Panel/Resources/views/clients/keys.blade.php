@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_keys') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_keys_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#appKeyModal" data-action="{{ route('panel.keys.store') }}"><i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add_key') }}</button>
                {{-- <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a> --}}
            </div>
        </div>
    </div>

    <!--list-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    <th scope="col">{{ __('FsLang::panel.table_platform') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_name') }}</th>
                    <th scope="col">App ID</th>
                    <th scope="col">App Key</th>
                    <th scope="col">{{ __('FsLang::panel.table_type') }}</th>
                    <th scope="col">{{ __('FsLang::panel.key_table_read_only') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_status') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($keys as $key)
                    <tr>
                        <th scope="row" class="py-3">{{ $key->platformName($platforms) }}</th>
                        <td>{{ $key->name }}</td>
                        <td>{{ $key->app_id }}</td>
                        <td>{{ $key->app_key }}</td>
                        <td>
                            {{ $typeLabels[$key->type] ?? '' }}
                            @if ($key->type == 3)
                                <span class="badge bg-light text-dark">{{ optional($key->app)->name }}</span>
                            @endif
                        </td>
                        <td><i class="bi {{ $key->is_read_only ? 'bi-check-lg text-success' : 'bi-dash-lg text-secondary' }}"></i></td>
                        <td><i class="bi {{ $key->is_enabled ? 'bi-check-lg text-success' : 'bi-dash-lg text-secondary' }}"></i></td>
                        <td>
                            <button type="button" class="btn btn-outline-success btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#appKeyModal"
                                data-action="{{ route('panel.keys.update', $key) }}"
                                data-params="{{ $key->toJson() }}">
                                {{ __('FsLang::panel.button_edit') }}
                            </button>

                            <button type="button" class="btn btn-outline-primary btn-sm mx-2"
                                data-bs-toggle="modal"
                                data-bs-target="#resetKey"
                                data-action="{{ route('panel.keys.reset', $key) }}"
                                data-name="{{ $key->name }}"
                                data-app-id="{{ $key->app_id }}">
                                {{ __('FsLang::panel.button_reset_key') }}
                            </button>

                            <button type="button" class="btn btn-link btn-sm text-danger fresns-link"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteKey"
                                data-action="{{ route('panel.keys.destroy', $key) }}"
                                data-name="{{ $key->name }}"
                                data-app-id="{{ $key->app_id }}">
                                {{ __('FsLang::panel.button_delete') }}
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!--key list end-->

    <!-- App Key Modal -->
    <div class="modal fade" id="appKeyModal" tabindex="-1" aria-labelledby="appKeyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title key-modal-title">{{ __('FsLang::panel.button_add_key') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!--Key-->
                    <form action="" method="post">
                        @csrf
                        @method('put')
                        <!--platform-->
                        <div class="input-group mb-3">
                            <span class="input-group-text">{{ __('FsLang::panel.table_platform') }}</span>
                            <select name="platform_id" class="form-select" required id="key_platform">
                                <option selected disabled value="">{{ __('FsLang::panel.key_select_platform') }}</option>
                                @foreach ($platforms as $platform)
                                    <option value="{{ $platform['id'] }}">{{ $platform['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!--name-->
                        <div class="input-group mb-3">
                            <span class="input-group-text">{{ __('FsLang::panel.table_name') }}</span>
                            <input type="text" name="name" required class="form-control" id="key_name">
                        </div>
                        <!--type-->
                        <div class="input-group mb-3">
                            <span class="input-group-text">{{ __('FsLang::panel.table_type') }}</span>
                            <div class="form-control bg-white">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" value="1" id="create_fresns_key" data-bs-toggle="collapse" data-bs-target=".key_plugin_setting.show" aria-expanded="false" aria-controls="key_plugin_setting" checked>
                                    <label class="form-check-label" for="create_fresns_key">{{ __('FsLang::panel.key_option_main_api') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" value="2" id="create_admin_key" data-bs-toggle="collapse" data-bs-target=".key_plugin_setting.show" aria-expanded="false" aria-controls="key_plugin_setting">
                                    <label class="form-check-label" for="create_admin_key">{{ __('FsLang::panel.key_option_manage_api') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" value="3" id="create_plugin_key" data-bs-toggle="collapse" data-bs-target=".key_plugin_setting:not(.show)" aria-expanded="false" aria-controls="key_plugin_setting">
                                    <label class="form-check-label" for="create_plugin_key">{{ __('FsLang::panel.key_option_plugin_api') }}</label>
                                </div>
                            </div>
                        </div>
                        <!--type: select plugin-->
                        <div class="input-group mb-3 collapse key_plugin_setting">
                            <span class="input-group-text">{{ __('FsLang::panel.table_plugin') }} <i class="bi bi-info-circle ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.key_option_plugin_api_select_desc') }}"></i></span>
                            <select class="form-select" name="app_fskey" id="app_key_plugin">
                                <option selected disabled>{{ __('FsLang::panel.key_option_plugin_api_select') }}</option>
                                @foreach ($plugins as $plugin)
                                    <option value="{{ $plugin->fskey }}">{{ $plugin->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!--read only-->
                        <div class="input-group mb-3">
                            <span class="input-group-text">{{ __('FsLang::panel.key_table_read_only') }}</span>
                            <div class="form-control bg-white">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_read_only" id="readOnly_no" value="0" checked>
                                    <label class="form-check-label" for="readOnly_no">{{ __('FsLang::panel.option_no') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_read_only" id="readOnly_yes" value="1">
                                    <label class="form-check-label" for="readOnly_yes">{{ __('FsLang::panel.option_yes') }}</label>
                                </div>
                            </div>
                        </div>
                        <!--status-->
                        <div class="input-group mb-3">
                            <span class="input-group-text">{{ __('FsLang::panel.table_status') }}</span>
                            <div class="form-control bg-white">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_enabled" id="keyStatus1" value="1" checked>
                                    <label class="form-check-label" for="keyStatus1">{{ __('FsLang::panel.option_activate') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_enabled" id="keyStatus0" value="0">
                                    <label class="form-check-label" for="keyStatus0">{{ __('FsLang::panel.option_deactivate') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
                        </div>
                    </form>
                    <!--Create Key End-->
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Key Modal -->
    <div class="modal fade" id="resetKey" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <form action="" method="post">
                    @csrf
                    @method('patch')
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('FsLang::panel.table_name') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>{{ __('FsLang::panel.table_app_id') }}: <span class="app-id"></span></p>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" data-bs-toggle="modal" data-bs-dismiss="modal">{{ __('FsLang::panel.button_reset') }}</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('FsLang::panel.button_cancel') }}</button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteKey" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <form action="" method="post">
                    @csrf
                    @method('delete')
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('FsLang::panel.table_name') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('FsLang::panel.table_app_id') }}: <span class="app-id"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger" data-bs-toggle="modal" data-bs-dismiss="modal">{{ __('FsLang::panel.button_confirm_delete') }}</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('FsLang::panel.button_cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
