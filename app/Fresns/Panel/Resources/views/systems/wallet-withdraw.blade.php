@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::systems.sidebar')
@endsection

@section('content')
    <!--wallet header-->
    <div class="row mb-4">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_wallet') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_wallet_tab_withdraw_services_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-action="{{ route('panel.wallet.withdraw.store') }}" data-bs-target="#createWithdrawModal">
                    <i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add_service_provider') }}
                </button>
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
        <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link" href="{{ route('panel.wallet.index') }}">{{ __('FsLang::panel.sidebar_wallet_tab_options') }}</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('panel.wallet.recharge.index') }}">{{ __('FsLang::panel.sidebar_wallet_tab_recharge_services') }}</a></li>
            <li class="nav-item"><a class="nav-link active" href="{{ route('panel.wallet.withdraw.index') }}">{{ __('FsLang::panel.sidebar_wallet_tab_withdraw_services') }}</a></li>
        </ul>
    </div>
    <!--service list-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    <th scope="col" style="width:6rem;">{{ __('FsLang::panel.table_order') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_plugin') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_name') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_parameter') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_status') }}</th>
                    <th scope="col" style="width:8rem;">{{ __('FsLang::panel.table_options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pluginUsages as $item)
                    <tr>
                        <td><input type="number" class="form-control input-number rating-number" data-action="{{ route('panel.plugin-usages.rating.update', $item->id) }}" value="{{ $item['rating'] }}"></td>
                        <td>{{ optional($item->plugin)->name }}</td>
                        <td>
                            @if ($item->getIconUrl())
                                <img src="{{ $item->getIconUrl() }}" width="24" height="24">
                            @endif
                            {{ $item->getLangName($defaultLanguage) }}
                        </td>
                        <td>{{ $item->parameter }}</td>
                        <td>
                            @if ($item['is_enable'])
                                <i class="bi bi-check-lg text-success"></i>
                            @else
                                <i class="bi bi-dash-lg text-secondary"></i>
                            @endif
                        </td>
                        <td>
                            <form method="post" action="{{ route('panel.plugin-usages.destroy', $item->id) }}">
                                @csrf
                                @method('delete')
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                    data-names="{{ $item->names->toJson() }}"
                                    data-default-name="{{ $item->getLangName($defaultLanguage) }}"
                                    data-params="{{ json_encode($item->attributesToArray()) }}"
                                    data-action="{{ route('panel.wallet.withdraw.update', $item) }}"
                                    data-bs-target="#createWithdrawModal">{{ __('FsLang::panel.button_edit') }}</button>
                                <button type="submit" class="btn btn-link link-danger ms-1 fresns-link fs-7 delete-button">{{ __('FsLang::panel.button_delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <form action="" method="post" enctype="multipart/form-data">
        @csrf
        @method('post')
        <input type="hidden" name="update_name" value="0">
        <!-- Modal -->
        <div class="modal fade name-lang-parent wallet-modal" id="createWithdrawModal" tabindex="-1" aria-labelledby="createWithdrawModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('FsLang::panel.sidebar_wallet_tab_withdraw_services') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_order') }}</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control input-number" required name="rating">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_plugin') }}</label>
                            <div class="col-sm-9">
                                <select class="form-select" name="plugin_unikey" required>
                                    <option selected disabled value="">{{ __('FsLang::panel.select_box_tip_plugin') }}</option>
                                    @foreach ($plugins as $plugin)
                                        <option value="{{ $plugin->unikey }}">{{ $plugin->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_icon') }}</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_image_upload') }}</button>
                                    <ul class="dropdown-menu selectInputType">
                                        <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                                        <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                                    </ul>
                                    <input type="file" class="form-control inputFile" name="icon_file">
                                    <input type="url" class="form-control inputUrl" name="icon_file_url" value="" style="display:none;">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_name') }}</label>
                            <div class="col-sm-9">
                                <button type="button" class="name-button btn btn-outline-secondary btn-modal w-100 text-start name-button" data-parent="#createWithdrawModal" data-bs-toggle="modal" data-bs-target="#langModal">
                                    {{ __('FsLang::panel.table_name') }}
                                </button>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_parameter') }}</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="parameter">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_status') }}</label>
                            <div class="col-sm-9 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_enable" id="status_true" value="1" checked>
                                    <label class="form-check-label" for="status_true">{{ __('FsLang::panel.option_activate') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_enable" id="status_false" value="0">
                                    <label class="form-check-label" for="status_false">{{ __('FsLang::panel.option_deactivate') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label"></label>
                            <div class="col-sm-9"><button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Language Modal -->
        <div class="modal fade name-lang-modal" id="langModal" tabindex="-1" aria-labelledby="langModal" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('FsLang::panel.table_name') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-nowrap">
                                <thead>
                                    <tr class="table-info">
                                        <th scope="col" class="w-25">{{ __('FsLang::panel.table_lang_tag') }}</th>
                                        <th scope="col" class="w-25">{{ __('FsLang::panel.table_lang_name') }}</th>
                                        <th scope="col" class="w-50">{{ __('FsLang::panel.table_content') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($optionalLanguages as $lang)
                                        <tr>
                                            <td>
                                                {{ $lang['langTag'] }}
                                                @if ($lang['langTag'] == $defaultLanguage)
                                                    <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.default_language') }}" data-bs-original-title="{{ __('FsLang::panel.default_language') }}" aria-label="{{ __('FsLang::panel.default_language') }}"></i>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $lang['langName'] }}
                                                @if ($lang['areaName'])
                                                    {{ '('.$lang['areaName'].')' }}
                                                @endif
                                            </td>
                                            <td><input type="text" name="names[{{ $lang['langTag'] }}]" class="form-control name-input" value="{{ $langParams['name'][$lang['langTag']] ?? '' }}"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!--button_save-->
                        <div class="text-center">
                            <button type="button" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_save') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
