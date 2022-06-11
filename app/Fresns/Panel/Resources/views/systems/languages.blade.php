@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::systems.sidebar')
@endsection

@section('content')
    <!--lang header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_languages') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_languages_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <button class="btn btn-warning dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-translate me-1"></i> {{ __('FsLang::panel.language_multilingual') }}: {{ $status ? __('FsLang::panel.option_activate') : __('FsLang::panel.option_deactivate') }}
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <form action="{{ route('panel.languageMenus.status.switch') }}" method="post">
                            @csrf
                            @method('put')
                            <button class="dropdown-item" type="submit">{{ $status ? __('FsLang::panel.button_deactivate') : __('FsLang::panel.button_activate') }}</button>
                        </form>
                    </li>
                </ul>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#createLanguage">
                    <i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add_language') }}
                </button>
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <!--lang list-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    <th scope="col" style="width:6rem;">{{ __('FsLang::panel.table_order') }}</th>
                    <th scope="col">{{ __('FsLang::panel.language_table_default') }}</th>
                    <th scope="col">{{ __('FsLang::panel.language_table_langCode') }}</th>
                    <th scope="col">{{ __('FsLang::panel.language_table_areaCode') }}</th>
                    <th scope="col">{{ __('FsLang::panel.language_table_langName') }}</th>
                    <th scope="col">{{ __('FsLang::panel.language_table_areaName') }}</th>
                    <th scope="col">{{ __('FsLang::panel.language_table_lengthUnit') }}</th>
                    <th scope="col">{{ __('FsLang::panel.language_table_writingDirection') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_status') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($languages as $language)
                    <tr>
                        <td>
                            <input type="number" data-action="{{ route('panel.languageMenus.rating.update', ['langTag' => $language['langTag']]) }}" class="form-control input-number rating-number" value="{{ $language['rating'] }}">
                        </td>
                        <td>
                            <input data-action="{{ route('panel.languageMenus.default.update') }}" class="form-check-input" type="radio" name="default_language" value="{{ $language['langTag'] }}" {{ $language['langTag'] == $defaultLanguage ? 'checked' : '' }}>
                        </td>
                        <td>{{ $language['langCode'] }}</td>
                        <td>{{ $language['areaCode'] }}</td>
                        <td>{{ $language['langName'] }}</td>
                        <td>{{ $language['areaName'] }}</td>
                        <td>{{ $language['lengthUnit'] }}</td>
                        <td>{{ $language['writingDirection'] }}</td>
                        <td><i class="bi {{ $language['isEnable'] ? 'bi-check-lg text-success' : 'bi-dash-lg text-secondary' }} "></i></td>
                        <td>
                            <form action="{{ route('panel.languageMenus.destroy', ['langTag' => $language['langTag']]) }}" method="post">
                                @csrf
                                @method('delete')
                                <button type="button" class="btn btn-outline-primary btn-sm" data-language="{{ json_encode($language) }}" data-action="{{ route('panel.languageMenus.update', ['langTag' => $language['langTag']]) }}" data-bs-toggle="modal" data-bs-target="#updateLanguageMenu">{{ __('FsLang::panel.button_edit') }}</button>
                                <button type="submit" class="btn btn-link link-danger ms-1 fresns-link fs-7 delete-button">{{ __('FsLang::panel.button_delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal: lang create -->
    <div class="modal fade" id="createLanguage" tabindex="-1" aria-labelledby="createModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_add_language') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('panel.languageMenus.store') }}" method="post">
                        @csrf
                        <!--table_order-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_order') }}</label>
                            <div class="col-sm-9">
                                <input type="number" name="rating" required class="form-control input-number">
                            </div>
                        </div>
                        <!--table_langCode-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.language_table_langCode') }}</label>
                            <div class="col-sm-9">
                                <select class="form-select" name="lang_code" required>
                                    <option selected disabled value="">{{ __('FsLang::panel.language_select_langCode') }}</option>
                                    @foreach ($codes as $code)
                                        <option value={{ $code['code'] }}>{{ $code['name'] }}-
                                            {{ $code['localName'] }} > {{ $code['code'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!--table_area-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.language_table_area') }}</label>
                            <div class="col-sm-9 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="area_status" id="create_area_status_false" value="0" data-bs-toggle="collapse" data-bs-target="#area_setting.show" aria-expanded="false" aria-controls="area_setting" checked>
                                    <label class="form-check-label" for="create_area_status_false">{{ __('FsLang::panel.option_close') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="area_status" id="create_area_status_true" value="1" data-bs-toggle="collapse" data-bs-target="#area_setting:not(.show)" aria-expanded="false" aria-controls="area_setting">
                                    <label class="form-check-label" for="create_area_status_true">{{ __('FsLang::panel.option_open') }}</label>
                                </div>
                            </div>
                        </div>
                        <!--table_areaCode-->
                        <div class="collapse" id="area_setting">
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.language_table_areaCode') }}</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <select class="form-select select-continent" data-children="{{ json_encode($areaCodes) }}" name="continent_id">
                                            <option selected disabled>{{ __('FsLang::panel.language_select_continent') }}</option>
                                            @foreach ($continents as $continent)
                                                <option value="{{ $continent['id'] }}">{{ $continent['name'] }}</option>
                                            @endforeach
                                        </select>
                                        <select class="form-select" name="area_code">
                                            <option selected disabled>{{ __('FsLang::panel.language_select_areaCode') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--table_lengthUnit-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.language_table_lengthUnit') }}</label>
                            <div class="col-sm-9">
                                <select class="form-select" name="length_unit">
                                    <option value="km" selected>{{ __('FsLang::panel.unit_kilometer') }} (km)</option>
                                    <option value="mi">{{ __('FsLang::panel.unit_mile') }} (mi)</option>
                                </select>
                            </div>
                        </div>
                        <!--table_dateFormat-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.language_table_dateFormat') }}</label>
                            <div class="col-sm-9">
                                <select class="form-select" name="date_format">
                                    <option value="Y-m-d" selected>yyyy-mm-dd</option>
                                    <option value="Y/m/d">yyyy/mm/dd</option>
                                    <option value="Y.m.d">yyyy.mm.dd</option>
                                    <option value="m-d-Y">mm-dd-yyyy</option>
                                    <option value="m/d/Y">mm/dd/yyyy</option>
                                    <option value="m.d.Y">mm.dd.yyyy</option>
                                    <option value="d-m-Y">dd-mm-yyyy</option>
                                    <option value="d/m/Y">dd/mm/yyyy</option>
                                    <option value="d.m.Y">dd.mm.yyyy</option>
                                </select>
                                <div class="form-text">{{ __('FsLang::panel.language_table_dateFormat_desc') }}</div>
                            </div>
                        </div>
                        <!--table_timeFormat-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.language_table_timeFormat') }}</label>
                            <div class="col-sm-9">
                                <div class="input-group mb-1">
                                    <span class="input-group-text">{n} minute ago</span>
                                    <input type="text" class="form-control" name="time_format_minute" required>
                                </div>
                                <div class="input-group mb-1">
                                    <span class="input-group-text">{n} hour ago</span>
                                    <input type="text" class="form-control" name="time_format_hour" required>
                                </div>
                                <div class="input-group mb-1">
                                    <span class="input-group-text">{n} day ago</span>
                                    <input type="text" class="form-control" name="time_format_day" required>
                                </div>
                                <div class="input-group mb-1">
                                    <span class="input-group-text">{n} month ago</span>
                                    <input type="text" class="form-control" name="time_format_month" required>
                                </div>
                                <div class="form-text">{{ __('FsLang::panel.language_table_timeFormat_desc') }}</div>
                            </div>
                        </div>
                        <!--table_status-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_status') }}</label>
                            <div class="col-sm-9 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_enable" id="create_language_status_true" value="1" checked>
                                    <label class="form-check-label" for="language_status_true">{{ __('FsLang::panel.option_activate') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_enable" id="create_language_status_false" value="0">
                                    <label class="form-check-label" for="language_status_false">{{ __('FsLang::panel.option_deactivate') }}</label>
                                </div>
                            </div>
                        </div>
                        <!--button_save-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label"></label>
                            <div class="col-sm-9"><button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- update Modal -->
    <div class="modal fade" id="updateLanguageMenu" tabindex="-1" aria-labelledby="updateModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_edit') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        @csrf
                        @method('put')
                        <input type="hidden" name="old_lang_tag" value="">
                        <!--table_order-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_order') }}</label>
                            <div class="col-sm-9">
                                <input type="number" name="rating" required class="form-control input-number">
                            </div>
                        </div>
                        <!--table_langCode-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.language_table_langCode') }}</label>
                            <div class="col-sm-9">
                                <select class="form-select" name="lang_code" disabled required>
                                    @foreach ($codes as $code)
                                        <option value={{ $code['code'] }}>{{ $code['name'] }}-{{ $code['localName'] }} > {{ $code['code'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!--table_area-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.language_table_area') }}</label>
                            <div class="col-sm-9 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="area_status" id="area_status_false" value="0" data-bs-toggle="collapse" data-bs-target="#area_setting.show" aria-expanded="false" aria-controls="area_setting" checked>
                                    <label class="form-check-label" for="area_status_false">{{ __('FsLang::panel.option_close') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="area_status" id="area_status_true" value="1" data-bs-toggle="collapse" data-bs-target="#area_setting:not(.show)" aria-expanded="false" aria-controls="area_setting">
                                    <label class="form-check-label" for="area_status_true">{{ __('FsLang::panel.option_open') }}</label>
                                </div>
                            </div>
                        </div>
                        <!--table_areaCode-->
                        <div class="collapse" id="area_setting">
                            <div class="mb-3 row">
                                <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.language_table_areaCode') }}</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <select class="form-select select-continent"
                                            data-children="{{ json_encode($areaCodes) }}" name="continent_id">
                                            <option selected disabled>{{ __('FsLang::panel.language_select_continent') }}</option>
                                            @foreach ($continents as $continent)
                                                <option value="{{ $continent['id'] }}">{{ $continent['name'] }}</option>
                                            @endforeach
                                        </select>
                                        <select class="form-select" name="area_code">
                                            <option selected disabled>{{ __('FsLang::panel.language_select_areaCode') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--table_lengthUnit-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.language_table_lengthUnit') }}</label>
                            <div class="col-sm-9">
                                <select class="form-select" name="length_unit">
                                    <option value="km" selected>{{ __('FsLang::panel.unit_kilometer') }} (km)</option>
                                    <option value="mi">{{ __('FsLang::panel.unit_mile') }} (mi)</option>
                                </select>
                            </div>
                        </div>
                        <!--table_writingDirection-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.language_table_dateFormat') }}</label>
                            <div class="col-sm-9">
                                <select class="form-select" name="date_format">
                                    <option value="Y-m-d" selected>yyyy-mm-dd</option>
                                    <option value="Y/m/d">yyyy/mm/dd</option>
                                    <option value="Y.m.d">yyyy.mm.dd</option>
                                    <option value="m-d-Y">mm-dd-yyyy</option>
                                    <option value="m/d/Y">mm/dd/yyyy</option>
                                    <option value="m.d.Y">mm.dd.yyyy</option>
                                    <option value="d-m-Y">dd-mm-yyyy</option>
                                    <option value="d/m/Y">dd/mm/yyyy</option>
                                    <option value="d.m.Y">dd.mm.yyyy</option>
                                </select>
                                <div class="form-text">{{ __('FsLang::panel.language_table_dateFormat_desc') }}</div>
                            </div>
                        </div>
                        <!--table_timeFormat-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.language_table_timeFormat') }}</label>
                            <div class="col-sm-9">
                                <div class="input-group mb-1">
                                    <span class="input-group-text">{n} minute ago</span>
                                    <input type="text" class="form-control" name="time_format_minute" required>
                                </div>
                                <div class="input-group mb-1">
                                    <span class="input-group-text">{n} hour ago</span>
                                    <input type="text" class="form-control" name="time_format_hour" required>
                                </div>
                                <div class="input-group mb-1">
                                    <span class="input-group-text">{n} day ago</span>
                                    <input type="text" class="form-control" name="time_format_day" required>
                                </div>
                                <div class="input-group mb-1">
                                    <span class="input-group-text">{n} month ago</span>
                                    <input type="text" class="form-control" name="time_format_month" required>
                                </div>
                                <div class="form-text">{{ __('FsLang::panel.language_table_timeFormat_desc') }}</div>
                            </div>
                        </div>
                        <!--table_status-->
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_status') }}</label>
                            <div class="col-sm-9 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_enable" id="language_status_true" value="1" checked>
                                    <label class="form-check-label" for="language_status_true">{{ __('FsLang::panel.option_activate') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_enable" id="language_status_false" value="0">
                                    <label class="form-check-label" for="language_status_false">{{ __('FsLang::panel.option_deactivate') }}</label>
                                </div>
                            </div>
                        </div>
                        <!--button_save-->
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
