@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--lang pack header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_language_packs') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_language_packs_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <!--lang pack list-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    <th scope="col">{{ __('FsLang::panel.table_lang_tag') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_lang_name') }}</th>
                    <th scope="col">{{ __('FsLang::panel.language_table_writingDirection') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_status') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_options') }}</th>
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
                        <td>{{ $lang['writingDirection'] }}</td>
                        <td>
                            @if ($lang['isEnable'])
                                <i class="bi bi-check-lg text-success"></i>
                            @else
                                <i class="bi bi-dash-lg text-secondary"></i>
                            @endif
                        </td>
                        <td>
                            <a class="btn btn-outline-primary btn-sm text-decoration-none" href="{{ route('panel.language.packs.edit', ['langTag' => $lang['langTag']]) }}" role="button">{{ __('FsLang::panel.button_config_language_pack') }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
