@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@use('App\Helpers\StrHelper')

@section('content')
    <!--lang pack header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_language_packs') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_language_packs_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#editLanguagePacks" data-action="{{ route('panel.language-packs.store') }}">
                    <i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add') }}
                </button>
                {{-- <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a> --}}
            </div>
        </div>
    </div>

    <!--lang pack list-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info align-middle">
                    <th scope="col" class="d-flex flex-row">
                        <div class="pt-2 me-3">Key</div>
                        <form action="{{ route('panel.language-packs.index') }}" method="get">
                            <div class="input-group">
                                <input type="text" class="form-control" name="key">
                                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </form>
                    </th>
                    <th scope="col" class="w-50">
                        Value
                        @if ($languageStatus)
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-dark btn-sm ms-2 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.default_language') }}</button>
                                <ul class="dropdown-menu">
                                    @foreach ($optionalLanguages as $lang)
                                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['langTag' => $lang['langTag']]) }}">{{ $lang['langName'] }} @if ($lang['areaName']) {{ '('.$lang['areaName'].')' }} @endif</a></li>
                                    @endforeach
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('panel.language-packs.index') }}">{{ __('FsLang::panel.button_reset') }}</a></li>
                                </ul>
                            </div>
                        @endif
                    </th>
                    <th scope="col">{{ __('FsLang::panel.table_options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($languages as $language)
                    <tr>
                        <td>{{ $language->lang_key }}</td>
                        <td><input type="text" class="form-control" value="{{ StrHelper::languageContent($language->lang_values, request('langTag')) }}" readonly></td>
                        <td>
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#editLanguagePacks"
                                data-action="{{ route('panel.language-packs.update', $language)}}"
                                data-params="{{ $language->toJson() }}">
                                {{ __('FsLang::panel.button_edit') }}
                            </button>

                            @if ($language->is_custom)
                                <button type="button" class="btn btn-link btn-sm text-danger fresns-link ms-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteLangKey"
                                    data-action="{{ route('panel.language-packs.destroy', $language) }}"
                                    data-key="{{ $language->lang_key }}"
                                    data-value="{{ StrHelper::languageContent($language->lang_values, request('langTag')) }}">
                                    {{ __('FsLang::panel.button_delete') }}
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $languages->appends(request()->all())->links() }}

    <!-- Edit Modal -->
    <div class="modal fade" id="editLanguagePacks" tabindex="-1" aria-labelledby="editLanguagePacks" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="" method="post">
                    @method('put')
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('FsLang::panel.button_add') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group mb-3">
                            <span class="input-group-text">Key</span>
                            <input type="text" class="form-control" name="langKey">
                        </div>

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
                                            <td><textarea class="form-control desc-input" name="langValues[{{ $lang['langTag'] }}]" rows="3"></textarea></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!--button_save-->
                        <div class="text-center mb-3">
                            <button type="submit" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteLangKey" tabindex="-1" aria-labelledby="deleteLangKey" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <form action="" method="post">
                    @csrf
                    @method('delete')
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span class="badge text-bg-primary"></span>
                        </h5>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger" data-bs-toggle="modal" data-bs-dismiss="modal">{{ __('FsLang::panel.button_confirm_delete') }}</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('FsLang::panel.button_cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
