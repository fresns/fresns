@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::expands.sidebar')
@endsection

@section('content')
    <!--content_type header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-9">
            <h3>{{ __('FsLang::panel.sidebar_expand_content_type') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_expand_content_type_intro') }}</p>
        </div>
        <div class="col-lg-3">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#createTypeModal">
                    <i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add_service_provider') }}
                </button>
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <!--content_type config-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    <th scope="col" style="width:6rem;">{{ __('FsLang::panel.table_order') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_plugin') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_name') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_data_source') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_status') }}</th>
                    <th scope="col" style="width:8rem;">{{ __('FsLang::panel.table_options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pluginUsages as $item)
                    <tr>
                        <td><input type="number" data-action="{{ route('panel.content-type.rank', $item->id) }}" class="form-control input-number rank-num" value="{{ $item['rank_num'] }}"></td>
                        <td>{{ optional($item->plugin)->name }}</td>
                        <td>{{ $item->name }}</td>
                        <td>
                            @if (!empty($item->data_sources['postByAll']['pluginUnikey']))
                                <button type="button" class="btn btn-outline-secondary btn-sm update-data-source"
                                    data-bs-toggle="modal"
                                    data-action="{{ route('panel.content-type.source', ['id' => $item->id, 'key' => 'postByAll']) }}"
                                    data-params="{{ json_encode($item->data_sources['postByAll']['rankNumber'] ?? []) }} "
                                    data-bs-target="#rankNumberModal">{{ __('FsLang::panel.expand_content_type_option_post_all') }}</button>
                            @endif
                            @if (!empty($item->data_sources['postByFollow']['pluginUnikey']))
                                <button type="button" class="btn btn-outline-secondary btn-sm update-data-source"
                                    data-bs-toggle="modal"
                                    data-action="{{ route('panel.content-type.source', ['id' => $item->id, 'key' => 'postByFollow']) }}"
                                    data-params="{{ json_encode($item->data_sources['postByFollow']['rankNumber'] ?? []) }} "
                                    data-bs-target="#rankNumberModal">{{ __('FsLang::panel.expand_content_type_option_post_follow') }}</button>
                            @endif
                            @if (!empty($item->data_sources['postByNearby']['pluginUnikey']))
                                <button type="button" class="btn btn-outline-secondary btn-sm update-data-source"
                                    data-bs-toggle="modal"
                                    data-action="{{ route('panel.content-type.source', ['id' => $item->id, 'key' => 'postByNearby']) }}"
                                    data-params="{{ json_encode($item->data_sources['postByNearby']['rankNumber'] ?? []) }} "
                                    data-bs-target="#rankNumberModal">{{ __('FsLang::panel.expand_content_type_option_post_nearby') }}</button>
                            @endif
                        </td>
                        <td>
                            @if ($item->is_enable)
                                <i class="bi bi-check-lg text-success"></i>
                            @else
                                <i class="bi bi-dash-lg text-secondary"></i>
                            @endif
                        </td>
                        <td>
                            <form method="post" action="{{ route('panel.plugin-usages.destroy', $item) }}">
                                @csrf
                                @method('delete')
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                    data-names="{{ $item->names->toJson() }}"
                                    data-default-name="{{ $item->getLangName($defaultLanguage) }}"
                                    data-params="{{ json_encode($item->attributesToArray()) }}"
                                    data-action="{{ route('panel.content-type.update', $item->id) }}"
                                    data-bs-toggle="modal" data-bs-target="#createTypeModal">{{ __('FsLang::panel.button_edit') }}</button>
                                @if ($item->can_delete)
                                    <button type="submit" class="btn btn-link link-danger ms-1 fresns-link fs-7 delete-button">{{ __('FsLang::panel.button_delete') }}</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $pluginUsages->links() }}
    <!--list end-->

    <form action="" method="post">
        @csrf
        @method('post')
        <input type="hidden" name="update_name" value="0">
        <!-- Config Modal -->
        <div class="modal fade name-lang-parent" id="createTypeModal" tabindex="-1" aria-labelledby="createTypeModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('FsLang::panel.sidebar_expand_content_type') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_order') }}</label>
                            <div class="col-sm-9">
                                <input type="number" name="rank_num" required class="form-control input-number">
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
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_name') }}</label>
                            <div class="col-sm-9">
                                <button type="button" class="btn btn-outline-secondary btn-modal w-100 text-start name-button" data-parent="#createTypeModal" data-bs-toggle="modal" data-bs-target="#langModal">{{ __('FsLang::panel.table_name') }}</button>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_data_source') }}</label>
                            <div class="col-sm-9">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="floatingSelect" name="post_all" aria-label="Floating label select example">
                                        <option disabled>{{ __('FsLang::panel.select_box_tip_data_source') }}</option>
                                        <option value="" selected>{{ __('FsLang::panel.option_default') }}</option>
                                        @foreach ($plugins as $plugin)
                                            <option value="{{ $plugin->unikey }}">{{ $plugin->name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="floatingSelect">/api/fresns/post/lists</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="floatingSelect" name="post_follow" aria-label="Floating label select example">
                                        <option disabled>{{ __('FsLang::panel.select_box_tip_data_source') }}</option>
                                        <option value="" selected>{{ __('FsLang::panel.option_default') }}</option>
                                        @foreach ($plugins as $plugin)
                                            <option value="{{ $plugin->unikey }}">{{ $plugin->name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="floatingSelect">/api/fresns/post/follows</label>
                                </div>
                                <div class="form-floating">
                                    <select class="form-select" id="floatingSelect" name="post_nearby" aria-label="Floating label select example">
                                        <option disabled>{{ __('FsLang::panel.select_box_tip_data_source') }}</option>
                                        <option value="" selected>{{ __('FsLang::panel.option_default') }}</option>
                                        @foreach ($plugins as $plugin)
                                            <option value="{{ $plugin->unikey }}">{{ $plugin->name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="floatingSelect">/api/fresns/post/nearbys</label>
                                </div>
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
                                            <td><input type="text" name="names[{{ $lang['langTag'] }}]" class="form-control name-input" value=""></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!--button_confirm-->
                        <div class="text-center">
                            <button type="button" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_confirm') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- rankNumber Modal -->
    <div class="modal fade name-lang-modal" id="rankNumberModal" tabindex="-1" aria-labelledby="rankNumberModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.expand_content_type_rank_number') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rankNumberForm" method="post">
                        @csrf
                        @method('put')
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-nowrap">
                                <thead>
                                    <tr class="table-info">
                                        <th scope="col" style="width:10rem;">{{ __('FsLang::panel.table_number') }} <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.expand_content_type_rank_number_desc') }}"></i></th>
                                        <th scope="col">{{ __('FsLang::panel.table_title') }}</th>
                                        <th scope="col">{{ __('FsLang::panel.table_description') }}</th>
                                        <th scope="col" style="width:6rem;">{{ __('FsLang::panel.table_options') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="add-rank-tr">
                                        <td colspan="4"><button class="btn btn-outline-success btn-sm px-3 add-rank" type="button"><i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add') }}</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <template id="rankTemplate">
        <tr class="rank-item">
            <td><input required type="number" name="ids[]" class="form-control input-number"></td>
            <td>
                <button type="button" class="btn btn-outline-secondary btn-modal w-100 text-start rank-title" data-bs-toggle="modal" data-bs-target="#rankNumberTitleLangModal">{{ __('FsLang::panel.table_title') }}</button>
                <input type="hidden" name="titles[]">
            </td>
            <td>
                <button type="button" class="btn btn-outline-secondary btn-modal w-100 text-start rank-description" data-bs-toggle="modal" data-bs-target="#rankNumberDescLangModal">{{ __('FsLang::panel.table_description') }}</button>
                <input type="hidden" name="descriptions[]">
            </td>
            <td><button type="button" class="btn btn-link link-danger ms-1 fresns-link fs-7 delete-rank-number">{{ __('FsLang::panel.button_delete') }}</button></td>
        </tr>
    </template>

    <!-- rankNumber Language Modal -->
    <div class="modal fade" id="rankNumberTitleLangModal" tabindex="-1" aria-labelledby="rankNumberTitleLangModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.table_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rankTitleForm">
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
                                            <td><input type="text" name="{{ $lang['langTag'] }}" class="form-control" value=""></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_confirm') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- rankNumber Language Modal -->
    <div class="modal fade" id="rankNumberDescLangModal" tabindex="-1" aria-labelledby="rankNumberDescLangModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.table_description') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
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
                                            <td><input type="text" name="{{ $lang['langTag'] }}" class="form-control" value=""></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_confirm') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
