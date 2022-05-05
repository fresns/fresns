@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::systems.sidebar')
@endsection

@section('content')
    <!--wallet header-->
    <div class="row mb-4">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_wallet') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_wallet_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
        <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link active" href="{{ route('panel.wallet.index') }}">{{ __('FsLang::panel.sidebar_wallet_tab_options') }}</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('panel.wallet.recharge.index') }}">{{ __('FsLang::panel.sidebar_wallet_tab_recharge_services') }}</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('panel.wallet.withdraw.index') }}">{{ __('FsLang::panel.sidebar_wallet_tab_withdraw_services') }}</a></li>
        </ul>
    </div>
    <!--wallet config-->
    <form action="{{ route('panel.wallet.update') }}" method="post">
        @csrf
        @method('put')
        <!--wallet_functions-->
        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.wallet_functions') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-3">
                    <label class="input-group-text">{{ __('FsLang::panel.table_status') }}</label>
                    <div class="form-control bg-white">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="wallet_status" id="wallet_false" value="false" data-bs-toggle="collapse" data-bs-target="#wallet_setting.show" aria-expanded="false" aria-controls="wallet_setting" {{ !$params['wallet_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="wallet_false">{{ __('FsLang::panel.option_deactivate') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="wallet_status" id="wallet_true" value="true" data-bs-toggle="collapse" data-bs-target="#wallet_setting:not(.show)" aria-expanded="false" aria-controls="wallet_setting" {{ $params['wallet_status'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="wallet_true">{{ __('FsLang::panel.option_activate') }}</label>
                        </div>
                    </div>
                </div>
                <div class="collapse {{ $params['wallet_status'] == 'true' ? 'show' : '' }}" id="wallet_setting">
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.wallet_currency_code') }}</label>
                        <select class="form-select" name="wallet_currency_code">
                            @foreach ($params['currency_codes'] as $code)
                                <option value="{{ $code['code'] }}" {{ $params['wallet_currency_code'] == $code['code'] ? 'selected' : '' }}>{{ $code['code'] }} ({{ $code['name'] }}) > {{ $code['ctryName'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.wallet_currency_name') }}</label>
                        <button type="button" id="currencyNameButton" class="btn btn-outline-secondary btn-modal form-control text-start" data-bs-toggle="modal" data-bs-target="#walletCurrencyName">{{ $defaultLangParams['wallet_currency_name'] ?:  __('FsLang::panel.wallet_currency_name') }}</button>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.wallet_currency_unit') }}</label>
                        <button type="button" id="currencyUnitButton" class="btn btn-outline-secondary btn-modal form-control text-start" data-bs-toggle="modal" data-bs-target="#walletCurrencyUnit">{{ $defaultLangParams['wallet_currency_unit'] ?:  __('FsLang::panel.wallet_currency_unit') }}</button>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.wallet_currency_precision') }}</label>
                        <select class="form-select" name="wallet_currency_precision">
                            @foreach ([0,1,2] as $precision)
                                <option value="{{ $precision }}" {{ $params['wallet_currency_precision'] == $precision ? 'selected' : '' }}>{{ $precision }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text">{{ __('FsLang::panel.wallet_withdraw_status') }}</span>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="wallet_withdraw_status" id="withdraw_false" value="false" data-bs-toggle="collapse" data-bs-target="#withdraw_setting.show" aria-expanded="false" aria-controls="withdraw_setting" {{ !$params['wallet_withdraw_status'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="withdraw_false">{{ __('FsLang::panel.option_close') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="wallet_withdraw_status" id="withdraw_true" value="true" data-bs-toggle="collapse" data-bs-target="#withdraw_setting:not(.show)" aria-expanded="false" aria-controls="withdraw_setting" {{ $params['wallet_withdraw_status'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="withdraw_true">{{ __('FsLang::panel.option_open') }}</label>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.wallet_functions_desc') }}</div>
        </div>
        <!--wallet_functions-->
        <div class="collapse {{ $params['wallet_withdraw_status'] == 'true' ? 'show' : '' }}" id="withdraw_setting">
            <div class="row mb-4">
                <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.wallet_withdraw_config') }}:</label>
                <div class="col-lg-6">
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.wallet_withdraw_review') }}</label>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="wallet_withdraw_review" id="wallet_cash_review_false" value="false" {{ !$params['wallet_withdraw_review'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="wallet_cash_review_false">{{ __('FsLang::panel.option_not_required') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="wallet_withdraw_review" id="wallet_cash_review_true" value="true" {{ $params['wallet_withdraw_review'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="wallet_cash_review_true">{{ __('FsLang::panel.option_required') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.wallet_withdraw_review_prove') }}</label>
                        <div class="form-control bg-white">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="wallet_withdraw_verify" id="wallet_cash_verify_false" value="false" {{ !$params['wallet_withdraw_verify'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="wallet_cash_verify_false">{{ __('FsLang::panel.option_not_required') }}</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="wallet_withdraw_verify" id="wallet_cash_verify_true" value="true" {{ $params['wallet_withdraw_verify'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="wallet_cash_verify_true">{{ __('FsLang::panel.option_required') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.wallet_withdraw_periodicity') }}</label>
                        <input type="text" class="form-control" name="wallet_withdraw_interval_time" value="{{ $params['wallet_withdraw_interval_time'] }}">
                        <span class="input-group-text">{{ __('FsLang::panel.unit_minute') }}</span>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.wallet_withdraw_rates') }}</label>
                        <input type="text" class="form-control" name="wallet_withdraw_rate" value="{{ $params['wallet_withdraw_rate'] }}">
                        <span class="input-group-text">%</span>
                    </div>

                    <?php $currency = collect($params['currency_codes'])->where('code', $params['wallet_currency_code'])->first(); ?>

                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.wallet_withdraw_min') }}</label>
                        <input type="text" class="form-control" name="wallet_withdraw_min_sum" value="{{ $params['wallet_withdraw_min_sum'] }}">
                        <span class="input-group-text">{{ $currency['name'] ?? '' }}</span>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.wallet_withdraw_max') }}</label>
                        <input type="text" class="form-control" name="wallet_withdraw_max_sum" value="{{ $params['wallet_withdraw_max_sum'] }} ">
                        <span class="input-group-text">{{ $currency['name'] ?? '' }}</span>
                    </div>
                    <div class="input-group mb-3">
                        <label class="input-group-text">{{ __('FsLang::panel.wallet_withdraw_sum_limit') }}</label>
                        <input type="text" class="form-control" name="wallet_withdraw_sum_limit" value="{{ $params['wallet_withdraw_sum_limit'] }}">
                        <span class="input-group-text">{{ $currency['name'] ?? '' }}</span>
                    </div>
                </div>
                <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.wallet_withdraw_config_desc') }}</div>
            </div>
        </div>
        <!--button_save-->
        <div class="row my-3">
            <div class="col-lg-2"></div>
            <div class="col-lg-8">
                <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
            </div>
        </div>

        <!-- Language Modal -->
        <div class="modal fade" id="walletCurrencyName" tabindex="-1" aria-labelledby="walletCurrencyName" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('FsLang::panel.button_setting') }}: {{ __('FsLang::panel.wallet_currency_name') }}</h5>
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
                                            <td><input type="text" name="wallet_currency_name[{{ $lang['langTag'] }}]" class="form-control name-input" value="{{ $langParams['wallet_currency_name'][$lang['langTag']] ?? '' }}"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!--button_save-->
                        <div class="text-center">
                            <button type="button" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_confirm') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Language Modal -->
        <div class="modal fade" id="walletCurrencyUnit" tabindex="-1" aria-labelledby="walletCurrencyUnit" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('FsLang::panel.button_setting') }}: {{ __('FsLang::panel.wallet_currency_unit') }}</h5>
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
                                            <td><input type="text" name="wallet_currency_unit[{{ $lang['langTag'] }}]" class="form-control name-input" value="{{ $langParams['wallet_currency_unit'][$lang['langTag']] ?? '' }}"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!--button_save-->
                        <div class="text-center">
                            <button type="button" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_confirm') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
