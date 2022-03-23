@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--keys header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_keys') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_keys_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#createKey"><i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add_key') }}</button>
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <!--key list-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    <th scope="col">{{ __('FsLang::panel.table_platform') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_name') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_app_id') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_app_secret') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_type') }}</th>
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
                        <td>{{ $key->app_secret }}</td>
                        <td>
                            {{ $typeLabels[$key->type] ?? '' }}
                            @if ($key->type == 3)
                                <span class="badge bg-light text-dark">{{ optional($key->plugin)->name }}</span>
                            @endif
                        </td>
                        <td><i class="bi {{ $key->is_enable ? 'bi-check-lg text-success' : 'bi-dash-lg text-secondary' }}"></i></td>
                        <td>
                            <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#updateKey" data-id="{{ $key->id }}" data-name="{{ $key->name }}" data-type="{{ $key->type }}" data-platform_id="{{ $key->platform_id }}" data-plugin_unikey="{{ $key->plugin_unikey }}" data-is_enable="{{ $key->is_enable }}" data-action="{{ route('panel.keys.update', $key) }}">{{ __('FsLang::panel.button_edit') }}</button>
                            <button type="button" class="btn btn-outline-primary btn-sm mx-2" data-bs-toggle="modal" data-app_id="{{ $key->app_id }}" data-name="{{ $key->name }}" data-action="{{ route('panel.keys.reset', $key) }}" data-bs-target="#resetSecret">{{ __('FsLang::panel.button_reset_secret') }}</button>
                            <button type="button" class="btn btn-link btn-sm text-danger fresns-link" data-bs-toggle="modal" data-app_id="{{ $key->app_id }}" data-name="{{ $key->name }}" data-action="{{ route('panel.keys.destroy', $key) }}" data-bs-target="#deleteKey">{{ __('FsLang::panel.button_delete') }}</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!--key list end-->

    <!-- Create Modal -->
    <div class="modal fade" id="createKey" tabindex="-1" aria-labelledby="createKeyLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createKeyLabel">{{ __('FsLang::panel.button_add_key') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!--Create Key-->
                    <form action="{{ route('panel.keys.store') }}" method="post">
                        @csrf
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
                                    <input class="form-check-input" type="radio" name="type" value="1" id="create_fresns_key" data-bs-toggle="collapse" data-bs-target="#key_plugin_setting.show" aria-expanded="false" aria-controls="key_plugin_setting" checked>
                                    <label class="form-check-label" for="create_fresns_key">{{ __('FsLang::panel.key_option_main_api') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" value="2" id="create_admin_key" data-bs-toggle="collapse" data-bs-target="#key_plugin_setting.show" aria-expanded="false" aria-controls="key_plugin_setting">
                                    <label class="form-check-label" for="create_admin_key">{{ __('FsLang::panel.key_option_manage_api') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" value="3" id="create_plugin_key" data-bs-toggle="collapse" data-bs-target="#key_plugin_setting:not(.show)" aria-expanded="false" aria-controls="key_plugin_setting">
                                    <label class="form-check-label" for="create_plugin_key">{{ __('FsLang::panel.key_option_plugin_api') }}</label>
                                </div>
                            </div>
                        </div>
                        <!--type: select plugin-->
                        <div class="input-group mb-3 collapse" id="key_plugin_setting">
                            <span class="input-group-text">{{ __('FsLang::panel.table_plugin') }} <i class="bi bi-info-circle ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.key_option_plugin_api_select_desc') }}"></i></span>
                            <select class="form-select" name="plugin_unikey" id="create_key_plugin">
                                <option selected disabled>{{ __('FsLang::panel.key_option_plugin_api_select') }}</option>
                                @foreach ($plugins as $plugin)
                                    <option value="{{ $plugin->unikey }}">{{ $plugin->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!--status-->
                        <div class="input-group mb-3">
                            <span class="input-group-text">{{ __('FsLang::panel.table_status') }}</span>
                            <div class="form-control bg-white">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_enable" id="keyStatus1" value="1">
                                    <label class="form-check-label" for="keyStatus1">{{ __('FsLang::panel.option_activate') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" checked type="radio" name="is_enable" id="keyStatus0" value="0">
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

    <!-- Update Modal -->
    <div class="modal fade" id="updateKey" tabindex="-1" aria-labelledby="updateKeyLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateKeyLabel">{{ __('FsLang::panel.button_edit') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!--Update Key-->
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
                                    <input class="form-check-input" type="radio" name="type" value="1" id="fresns_key" data-bs-toggle="collapse" data-bs-target="#key_plugin_setting.show" aria-expanded="false" aria-controls="key_plugin_setting" checked>
                                    <label class="form-check-label" for="fresns_key">{{ __('FsLang::panel.key_option_main_api') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" value="2" id="admin_key" data-bs-toggle="collapse" data-bs-target="#key_plugin_setting.show" aria-expanded="false" aria-controls="key_plugin_setting">
                                    <label class="form-check-label" for="admin_key">{{ __('FsLang::panel.key_option_manage_api') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" value="3" id="plugin_key" data-bs-toggle="collapse" data-bs-target="#key_plugin_setting:not(.show)" aria-expanded="false" aria-controls="key_plugin_setting">
                                    <label class="form-check-label" for="plugin_key">{{ __('FsLang::panel.key_option_plugin_api') }}</label>
                                </div>
                            </div>
                        </div>
                        <!--type: select plugin-->
                        <div class="input-group mb-3 collapse" id="key_plugin_setting">
                            <span class="input-group-text">{{ __('FsLang::panel.table_plugin') }} <i class="bi bi-info-circle ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.key_option_plugin_api_select_desc') }}"></i></span>
                            <select class="form-select" name="plugin_unikey" id="key_plugin">
                                <option selected disabled>{{ __('FsLang::panel.key_option_plugin_api_select') }}</option>
                                @foreach ($plugins as $plugin)
                                    <option value="{{ $plugin->unikey }}">{{ $plugin->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!--status-->
                        <div class="input-group mb-3">
                            <span class="input-group-text">{{ __('FsLang::panel.table_status') }}</span>
                            <div class="form-control bg-white">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_enable" id="updateKeyStatus1" value="1">
                                    <label class="form-check-label" for="updateKeyStatus1">{{ __('FsLang::panel.option_activate') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" checked type="radio" name="is_enable" id="updateKeyStatus0" value="0">
                                    <label class="form-check-label" for="updateKeyStatus0">{{ __('FsLang::panel.option_deactivate') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
                        </div>
                    </form>
                    <!--Update Key End-->
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Modal -->
    <div class="modal fade" id="resetSecret" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <form action="" method="post">
                @csrf
                @method('put')
                <div class="modal-content">
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
