@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::systems.sidebar')
@endsection

@section('content')
    <!--policy header-->
    <div class="row mb-4">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_policy') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_policy_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
        <!--tab-list-->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="options-tab" data-bs-toggle="tab" data-bs-target="#options" type="button" role="tab" aria-controls="options" aria-selected="true">{{ __('FsLang::panel.sidebar_policy_tab_options') }}</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="contents-tab" data-bs-toggle="tab" data-bs-target="#contents" type="button" role="tab" aria-controls="contents" aria-selected="false">{{ __('FsLang::panel.sidebar_policy_tab_contents') }}</button>
            </li>
        </ul>
    </div>
    <!--policy config-->
    <div class="tab-content" id="policiesTabContent">
        <!--options-->
        <div class="tab-pane fade show active" id="options" role="tabpanel" aria-labelledby="options-tab">
            <form action="{{ route('panel.policy.update') }}" method="post">
                @csrf
                @method('put')
                <div class="row mb-3">
                    <label for="delete_account" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.policy_terms') }}:</label>
                    <div class="col-lg-6 pt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="account_terms_close" id="account_terms_false" value="false" {{ !$params['account_terms_close'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="account_terms_false">{{ __('FsLang::panel.option_hidden') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="account_terms_close" id="account_terms_true" value="true" {{ $params['account_terms_close'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="account_terms_true">{{ __('FsLang::panel.option_visible') }}</label>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="delete_account" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.policy_privacy') }}:</label>
                    <div class="col-lg-6 pt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="account_privacy_close" id="account_privacy_false" value="false" {{ !$params['account_privacy_close'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="account_privacy_false">{{ __('FsLang::panel.option_hidden') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="account_privacy_close" id="account_privacy_true" value="true" {{ $params['account_privacy_close'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="account_privacy_true">{{ __('FsLang::panel.option_visible') }}</label>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="delete_account" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.policy_cookie') }}:</label>
                    <div class="col-lg-6 pt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="account_cookie_close" id="account_cookie_false" value="false" {{ !$params['account_cookie_close'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="account_cookie_false">{{ __('FsLang::panel.option_hidden') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="account_cookie_close" id="account_cookie_true" value="true" {{ $params['account_cookie_close'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="account_cookie_true">{{ __('FsLang::panel.option_visible') }}</label>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="delete_account" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.policy_delete_account') }}:</label>
                    <div class="col-lg-6 pt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="account_delete_close" id="account_delete_false" value="false" {{ !$params['account_delete_close'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="account_delete_false">{{ __('FsLang::panel.option_hidden') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="account_delete_close" id="account_delete_true" value="true" {{ $params['account_delete_close'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="account_delete_true">{{ __('FsLang::panel.option_visible') }}</label>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="delete_account" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.policy_delete_options') }}:</label>
                    <div class="col-lg-6 pt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="delete_account" id="delete_account" value="1" data-bs-toggle="collapse" data-bs-target="#delete_account_todo_setting.show" aria-expanded="false" aria-controls="delete_account_todo_setting" {{ $params['delete_account'] == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="delete_account">{{ __('FsLang::panel.policy_delete_option_1') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="delete_account" id="delete_account_1" value="2" data-bs-toggle="collapse" data-bs-target="#delete_account_todo_setting:not(.show)" aria-expanded="false" aria-controls="delete_account_todo_setting" {{ $params['delete_account'] == 2 ? 'checked' : '' }}>
                            <label class="form-check-label" for="delete_account_1">{{ __('FsLang::panel.policy_delete_option_2') }}</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="delete_account" id="delete_account_2" value="3" data-bs-toggle="collapse" data-bs-target="#delete_account_todo_setting:not(.show)" aria-expanded="false" aria-controls="delete_account_todo_setting" {{ $params['delete_account'] == 3 ? 'checked' : '' }}>
                            <label class="form-check-label" for="delete_account_2">{{ __('FsLang::panel.policy_delete_option_3') }}</label>
                        </div>
                        <div class="collapse {{ $params['delete_account'] ? 'show' : '' }}" id="delete_account_todo_setting">
                            <div class="input-group mt-2">
                                <span class="input-group-text">{{ __('FsLang::panel.policy_delete_crontab') }}</span>
                                <input type="number" class="form-control input-number" name="delete_account_todo" value="{{ $params['delete_account_todo'] }}">
                                <span class="input-group-text">{{ __('FsLang::panel.unit_day') }}</span>
                            </div>
                            <div class="form-text">{{ __('FsLang::panel.policy_delete_options_desc') }}</div>
                            <div class="form-text"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.policy_delete_option_2_desc') }}</div>
                            <div class="form-text"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.policy_delete_option_3_desc') }}</div>
                        </div>
                    </div>
                </div>
                <!--button_save-->
                <div class="row my-3">
                    <div class="col-lg-2"></div>
                    <div class="col-lg-6">
                        <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
                    </div>
                </div>
            </form>
        </div>
        <!--contents-->
        <div class="tab-pane fade" id="contents" role="tabpanel" aria-labelledby="contents-tab">
            <table class="table table-hover align-middle text-nowrap">
                <thead>
                    <tr class="table-info">
                        <th scope="col">{{ __('FsLang::panel.table_lang_tag') }}</th>
                        <th scope="col">{{ __('FsLang::panel.table_lang_name') }}</th>
                        <th scope="col">{{ __('FsLang::panel.policy_terms') }}</th>
                        <th scope="col">{{ __('FsLang::panel.policy_privacy') }}</th>
                        <th scope="col">{{ __('FsLang::panel.policy_cookie') }}</th>
                        <th scope="col">{{ __('FsLang::panel.policy_delete_account') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($optionalLanguages as $lang)
                        <?php
                            $langName = $lang['langName'];
                            if ($lang['areaCode']) {
                                $langName .= '('.optional($areaCodes->where('code', $lang['areaCode'])->first())['localName'].')';
                            }
                        ?>
                        <tr>
                            <td>
                                {{ $lang['langTag'] }}
                                @if ($lang['langTag'] == $defaultLanguage)
                                    <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.default_language') }}" data-bs-original-title="{{ __('FsLang::panel.default_language') }}" aria-label="{{ __('FsLang::panel.default_language') }}"></i>
                                @endif
                            </td>
                            <td>{{ $langName }}</td>
                            <td>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#updateLanguage"
                                    data-action="{{ route('panel.languages.update', ['itemKey' => 'account_terms']) }}"
                                    data-lang_tag_desc="{{ $langName }}" data-lang_tag="{{ $lang['langTag'] }}"
                                    data-key="account_terms"
                                    data-title="{{ __('FsLang::panel.policy_terms') }}"
                                    data-content="{{ $langParams['account_terms'][$lang['langTag']] ?? '' }}">{{ __('FsLang::panel.button_edit') }}</button>
                            </td>
                            <td>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#updateLanguage"
                                    data-action="{{ route('panel.languages.update', ['itemKey' => 'account_privacy']) }}"
                                    data-lang_tag_desc="{{ $langName }}" data-lang_tag="{{ $lang['langTag'] }}"
                                    data-key="account_privacy"
                                    data-title="{{ __('FsLang::panel.policy_privacy') }}"
                                    data-content="{{ $langParams['account_privacy'][$lang['langTag']] ?? '' }}">{{ __('FsLang::panel.button_edit') }}</button>
                            </td>
                            <td><button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#updateLanguage"
                                    data-action="{{ route('panel.languages.update', ['itemKey' => 'account_cookie']) }}"
                                    data-lang_tag_desc="{{ $langName }}" data-lang_tag="{{ $lang['langTag'] }}"
                                    data-key="account_cookie"
                                    data-title="{{ __('FsLang::panel.policy_cookie') }}"
                                    data-content="{{ $langParams['account_cookie'][$lang['langTag']] ?? '' }}">{{ __('FsLang::panel.button_edit') }}</button>
                            </td>
                            <td><button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#updateLanguage"
                                    data-action="{{ route('panel.languages.update', ['itemKey' => 'account_delete']) }}"
                                    data-lang_tag_desc="{{ $langName }}" data-lang_tag="{{ $lang['langTag'] }}"
                                    data-key="account_delete"
                                    data-title="{{ __('FsLang::panel.policy_delete_account') }}"
                                    data-content="{{ $langParams['account_delete'][$lang['langTag']] ?? '' }}">{{ __('FsLang::panel.button_edit') }}</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Language Modal -->
    <div class="modal fade" id="updateLanguage" tabindex="-1" aria-labelledby="createModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" id="updateLanguageForm">
                        @csrf
                        @method('put')
                        <input type="hidden" name="lang_tag">
                        <input type="hidden" name="lang_key">
                        <div class="form-floating">
                            <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea2" name="content" style="height:400px"></textarea>
                            <label for="floatingTextarea2" class="lang-label"></label>
                        </div>
                        <div class="form-text">{{ __('FsLang::panel.policy_editor_desc') }}</div>
                        <button type="submit" class="btn btn-primary mt-3">{{ __('FsLang::panel.button_save') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
