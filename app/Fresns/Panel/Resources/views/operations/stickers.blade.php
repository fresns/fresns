@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::operations.sidebar')
@endsection

@section('content')
    <!--stickers header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_stickers') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_stickers_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-action="{{ route('panel.stickers.store') }}" data-bs-target="#stickerGroupCreateModal"><i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add_sticker_group') }}</button>
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <!--stickers config-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    <th scope="col" style="width:6rem;">{{ __('FsLang::panel.table_order') }}</th>
                    <th scope="col">{{ __('FsLang::panel.sticker_table_group_name') }}</th>
                    <th scope="col">{{ __('FsLang::panel.sticker_table_quantity') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_status') }}</th>
                    <th scope="col" style="width:13rem;">{{ __('FsLang::panel.table_options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groups as $group)
                    <tr>
                        <td><input type="number" data-action="{{ route('panel.stickers.rating', $group->id) }}" class="form-control input-number rating-number" value="{{ $group->rating }}"></td>
                        <td>
                            @if ($group->image_file_url)
                                <img src="{{ $group->image_file_url }}" width="24" height="24">
                            @endif
                            {{ $group->getLangName($defaultLanguage) }}
                        </td>
                        <td>{{ $group->stickers->count() }}</td>
                        <td>
                            @if ($group->is_enable)
                                <i class="bi bi-check-lg text-success"></i>
                            @else
                                <i class="bi bi-dash-lg text-secondary"></i>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('panel.stickers.destroy', $group) }}" method="post">
                                @csrf
                                @method('delete')
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                    data-action="{{ route('panel.stickers.update', $group) }}"
                                    data-names="{{ $group->names->toJson() }}"
                                    data-default-name="{{ $group->getLangName($defaultLanguage) }}"
                                    data-params="{{ json_encode($group->attributesToArray()) }}"
                                    data-bs-target="#stickerGroupCreateModal">{{ __('FsLang::panel.button_edit') }}</button>
                                <button type="button" class="btn btn-outline-info btn-sm ms-1" data-bs-toggle="offcanvas"
                                    data-bs-target="#offcanvasSticker"
                                    data-action="{{ route('panel.sticker-images.store') }}"
                                    data-stickers="{{ $group->stickers->toJson() }}"
                                    data-parent_id="{{ $group->id }}" aria-controls="offcanvasSticker">{{ __('FsLang::panel.button_config_sticker') }}</button>
                                <button type="submit" class="btn btn-link link-danger ms-1 fresns-link fs-7 delete-button">{{ __('FsLang::panel.button_delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- sticker group form -->
    <form method="POST" class="check-names" enctype="multipart/form-data" action="">
        @csrf
        @method('post')
        <!-- sticker group modal -->
        <div class="modal fade name-lang-parent" id="stickerGroupCreateModal" tabindex="-1"
            aria-labelledby="stickerGroupCreateModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('FsLang::panel.sticker_group') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!--table_order-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_order') }}</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control input-number" name="rating" required>
                            </div>
                        </div>
                        <!--sticker_table_group_image-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.sticker_table_group_image') }}</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_image_upload') }}</button>
                                    <ul class="dropdown-menu selectInputType">
                                        <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                                        <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                                    </ul>
                                    <input type="file" class="form-control inputFile" name="image_file">
                                    <input type="url" class="form-control inputUrl" name="image_file_url" value="" style="display:none;">
                                </div>
                            </div>
                        </div>
                        <!--sticker_table_group_code-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.sticker_table_group_code') }}</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control input-number" name="code" required>
                            </div>
                        </div>
                        <!--sticker_table_group_name-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.sticker_table_group_name') }}</label>
                            <div class="col-sm-9">
                                <button type="button" class="btn btn-outline-secondary btn-modal w-100 text-start name-button" data-bs-toggle="modal" data-parent="#stickerGroupCreateModal" data-bs-target="#langModal">{{ __('FsLang::tips.required_sticker_group_name') }}</button>
                                <div class="invalid-feedback">{{ __('FsLang::tips.required_sticker_group_name') }}</div>
                            </div>
                        </div>
                        <!--table_status-->
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
                        <!--button_save-->
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
                        <h5 class="modal-title">{{ __('FsLang::panel.sticker_table_group_name') }}</h5>
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
                                            <td><input type="text" class="form-control name-input" name="names[{{ $lang['langTag'] }}]" value=""></td>
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

    <!-- sticker offcanvas -->
    <form method="post" action="{{ route('panel.sticker-images.batch.update') }}">
        @csrf
        @method('put')
        <input type="hidden" name="parent_id">
        <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSticker" aria-labelledby="offcanvasStickerLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasStickerLabel">
                    {{ __('FsLang::panel.sticker_manage') }}
                    <button class="btn btn-info btn-sm ms-3" type="button" data-bs-toggle="modal" data-bs-target="#stickerModal">
                        <i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add_sticker') }}
                    </button>
                </h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap">
                        <thead>
                            <tr class="table-info">
                                <th scope="col" style="width:6rem;">{{ __('FsLang::panel.table_order') }}</th>
                                <th scope="col">{{ __('FsLang::panel.sticker_table_image') }}</th>
                                <th scope="col">{{ __('FsLang::panel.sticker_table_code') }}</th>
                                <th scope="col">{{ __('FsLang::panel.table_status') }}</th>
                                <th scope="col">{{ __('FsLang::panel.table_options') }}</th>
                            </tr>
                        </thead>
                        <tbody id="stickerList">
                        </tbody>
                    </table>
                </div>
                <!--button_save-->
                <div class="text-center mb-4">
                    <button type="submit" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_save') }}</button>
                </div>
            </div>
        </div>
    </form>

    <template id="stickerData">
        <tr>
            <td><input type="number" class="form-control input-number sticker-rating"></td>
            <td><img class="sticker-img" src="" width="28" height="28"></td>
            <td>[<span class="sticker-code"></span>]</td>
            <td>
                <div class="form-check form-switch">
                    <input class="form-check-input update-sticker-enable sticker-enable" type="checkbox" value="1">
                </div>
            </td>
            <td>
                <button type="submit" class="delete-sticker btn btn-link link-danger ms-1 fresns-link fs-7">{{ __('FsLang::panel.button_delete') }}</button>
            </td>
        </tr>
    </template>

    <!-- Add Sticker Modal -->
    <div class="modal fade" id="stickerModal" tabindex="-1" aria-labelledby="stickerModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_add_sticker') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('panel.sticker-images.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="parent_id">
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_order') }}</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control input-number" name="rating" required>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.sticker_table_image') }}</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_image_upload') }}</button>
                                    <ul class="dropdown-menu selectInputType">
                                        <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                                        <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                                    </ul>
                                    <input type="file" class="form-control inputFile" name="image_file">
                                    <input type="url" class="form-control inputUrl" name="image_file_url" value="" style="display:none;">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.sticker_table_code') }}</label>
                            <div class="col-sm-9">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">[</span>
                                    <input type="text" class="form-control" name="code">
                                    <span class="input-group-text">]</span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_status') }}</label>
                            <div class="col-sm-9 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_enable" id="sticker_status_true" value="1" checked>
                                    <label class="form-check-label" for="sticker_status_true">{{ __('FsLang::panel.option_activate') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_enable" id="sticker_status_false" value="0">
                                    <label class="form-check-label" for="sticker_status_false">{{ __('FsLang::panel.option_deactivate') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label"></label>
                            <div class="col-sm-9"><button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
