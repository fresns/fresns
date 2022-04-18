@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::operations.sidebar')
@endsection

@section('content')
    <!--roles header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_roles') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_roles_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-action="{{ route('panel.roles.store') }}" data-bs-target="#createRoleModal">
                    <i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add_role') }}
                </button>
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <!--roles list-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    <th scope="col" style="width:6rem;">{{ __('FsLang::panel.table_order') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_type') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_icon') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_name') }}</th>
                    <th scope="col">{{ __('FsLang::panel.role_table_display') }}</th>
                    <th scope="col">{{ __('FsLang::panel.role_table_nickname_color') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_status') }}</th>
                    <th scope="col" style="width:13rem;">{{ __('FsLang::panel.table_options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr>
                        <td><input type="number" data-action="{{ route('panel.roles.rank', $role->id) }}" class="form-control input-number rank-num" value="{{ $role->rank_num }}"></td>
                        <td>{{ $typeLabels[$role->type] }}</td>
                        <td>
                            @if ($role->icon_file_url)
                                <img src="{{ $role->icon_file_url }}" width="24" height="24">
                            @endif
                        </td>
                        <td>{{ $role->getLangName($defaultLanguage) }}</td>
                        <td>
                            @if ($role->is_display_icon)
                                <i class="bi bi-image me-3" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.role_option_display_icon') }}"></i>
                            @endif
                            @if ($role->is_display_name)
                                <i class="bi bi-textarea-t me-3" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.role_option_display_text') }}"></i>
                            @endif
                        </td>
                        <td>
                            @if ($role->nickname_color)
                                <input type="color" class="form-control form-control-color" value="{{ $role->nickname_color }}" disabled>
                            @endif
                        </td>
                        <td>
                            @if ($role->is_enable)
                                <i class="bi bi-check-lg text-success"></i>
                            @else
                                <i class="bi bi-dash-lg text-secondary"></i>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                data-names="{{ $role->names->toJson() }}"
                                data-default-name="{{ $role->getLangName($defaultLanguage) }}"
                                data-params="{{ $role->toJson() }}"
                                data-action="{{ route('panel.roles.update', ['role' => $role->id]) }}"
                                data-bs-target="#createRoleModal">{{ __('FsLang::panel.button_edit') }}</button>
                            <a class="btn btn-outline-info btn-sm text-decoration-none ms-1" href="{{ route('panel.roles.permissions.index', $role->id) }}" role="button">{{ __('FsLang::panel.button_config_permission') }}</a>
                            <button type="butmit" class="btn btn-link link-danger ms-1 fresns-link fs-7" data-bs-toggle="modal" data-action="{{ route('panel.roles.destroy', $role->id) }}" data-params="{{ $role->toJson() }}" data-bs-target="#deleteRoleModal">{{ __('FsLang::panel.button_delete') }}</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <form action="" method="post" class="check-names">
        @csrf
        @method('post')
        <div class="modal fade name-lang-parent" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModal"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('FsLang::panel.sidebar_roles') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_type') }}</label>
                            <div class="col-sm-9">
                                <select class="form-select" name="type" required>
                                    <option selected disabled value="">{{ __('FsLang::panel.select_box_tip_role_type') }}</option>
                                    @foreach ($typeLabels as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_order') }}</label>
                            <div class="col-sm-9">
                                <input type="number" required name="rank_num" class="form-control input-number">
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
                                    <input type="url" class="form-control inputUrl" name="icon_url" value="" style="display:none;">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_name') }}</label>
                            <div class="col-sm-9">
                                <button type="button" class="btn btn-outline-secondary btn-modal w-100 text-start name-button" data-bs-toggle="modal" data-parent="#createRoleModal" data-bs-target="#langModal">{{ __('FsLang::panel.table_name') }}</button>
                                <div class="invalid-feedback">{{ __('FsLang::tips.required_user_role_name') }}</div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.role_table_display') }}</label>
                            <div class="col-sm-9 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" name="is_display_icon" type="checkbox" id="inlineCheckbox1" value="1">
                                    <label class="form-check-label" for="inlineCheckbox1"><i class="bi bi-image"></i> {{ __('FsLang::panel.role_option_display_icon') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" name="is_display_name" type="checkbox" id="inlineCheckbox2" value="1">
                                    <label class="form-check-label" for="inlineCheckbox2"><i class="bi bi-textarea-t"></i> {{ __('FsLang::panel.role_option_display_text') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.role_table_nickname_color') }}</label>
                            <div class="col-sm-2 choose-color">
                                <input type="color" name="nickname_color" class="form-control form-control-color" value="#FF4400">
                            </div>
                            <div class="col-sm-7">
                                <div class="form-check form-check-inline mt-2">
                                    <input class="form-check-input" type="checkbox" id="emptyColor" name="no_color" value="1">
                                    <label class="form-check-label" for="emptyColor">{{ __('FsLang::panel.role_option_close_nickname_color') }}</label>
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

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-labelledby="deleteRoleModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_delete') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        @csrf
                        @method('delete')
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.role_current') }}</label>
                            <div class="col-sm-9">
                                <input type="text" name="name" class="form-control-plaintext" readonly>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.role_target') }}</label>
                            <div class="col-sm-9">
                                <select class="form-select" name="role_id" id="chooseRole" required>
                                    <option selected disabled value="">{{ __('FsLang::panel.select_box_tip_role') }}</option>
                                    @foreach ($roles as $role)
                                        <option class="role-option" value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">{{ __('FsLang::panel.role_target_desc') }}</div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label"></label>
                            <div class="col-sm-9"><button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_confirm') }}</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
