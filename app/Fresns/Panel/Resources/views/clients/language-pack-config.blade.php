@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--breadcrumb-->
    <div class="row mb-4 border-bottom">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('panel.menus.index') }}">{{ __('FsLang::panel.menu_clients') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('panel.language.packs.index') }}">{{ __('FsLang::panel.sidebar_language_packs') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php
                    $lang = $optionalLanguages->where('langTag', $langTag)->first() ?: [];
                    $langName = $lang['langName'] ?? '';
                    if ($lang['areaCode']) {
                        $langName .= '(' . optional($areaCodes->where('code', $lang['areaCode'])->first())['localName'] . ')';
                    }
                    ?>
                    {{ $langTag }}
                    <span class="badge bg-secondary ms-2">{{ $langName }}</span>
                </li>
            </ol>
        </nav>
    </div>
    <!--name list-->
    <form action="{{ route('panel.language.packs.update', ['langTag' => $langTag]) }}" method="post">
        @csrf
        @method('put')
        <div class="table-responsive">
            <table class="table table-hover align-middle text-nowrap">
                <thead>
                    <tr class="table-info">
                        <th scope="col">{{ __('FsLang::panel.language_pack_name') }}</th>
                        <th scope="col">{{ __('FsLang::panel.language_pack_default') }} <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.language_pack_default_desc') }}"></i></th>
                        <th scope="col">{{ __('FsLang::panel.language_pack_current') }}</th>
                        <th scope="col" style="width:6rem;">{{ __('FsLang::panel.table_options') }}</th>
                    </tr>
                </thead>
                <tbody class="lang-pack-box">
                    @foreach ($languageKeys as $key)
                        <tr>
                            <td><input type="text" class="form-control" name="keys[]" value="{{ $key['name'] }}" readonly></td>
                            <td><input type="text" class="form-control" value="{{ $defaultLanguages[$key['name']] ?? '' }}" readonly></td>
                            <td><input type="text" class="form-control" name="contents[]" value="{{ $languages[$key['name']] ?? '' }}"></td>
                            <td>
                                @if ($key['canDelete'])
                                    <button type="button" class="btn btn-link link-danger ms-1 fresns-link fs-7 delete-lang-pack">{{ __('FsLang::panel.button_delete') }}</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="text-center">
                <button class="btn btn-outline-success btn-sm px-3 me-3" id="addLangPack" type="button"><i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add') }}</button>
                <button class="btn btn-primary btn-sm" type="submit">{{ __('FsLang::panel.button_save') }}</button>
            </div>
        </div>
    </form>
    <!--name list end-->

    <template id="languagePackTemplate">
        <tr>
            <td><input type="text" class="form-control" name="keys[]" value=""></td>
            <td><input type="text" class="form-control" value="" readonly></td>
            <td><input type="text" class="form-control" name="contents[]" value=""></td>
            <td><button type="button" class="btn btn-link link-danger ms-1 fresns-link fs-7">{{ __('FsLang::panel.button_delete') }}</button></td>
        </tr>
    </template>
@endsection
