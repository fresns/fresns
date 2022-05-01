@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::operations.sidebar')
@endsection

@section('content')
    <!--groups header-->
    <div class="row mb-4">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_groups') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_groups_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <button class="btn btn-primary edit-group-category" type="button" data-action="{{ route('panel.groups.store') }}"><i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add_group_category') }}</button>
                <button class="btn btn-success" type="button" data-bs-toggle="modal" data-action="{{ route('panel.groups.store') }}" data-bs-target="#groupModal"><i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add_group') }}</button>
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
        <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link active" href="{{ route('panel.groups.index') }}">{{ __('FsLang::panel.sidebar_groups_tab_active') }}</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('panel.groups.inactive.index') }}">{{ __('FsLang::panel.sidebar_groups_tab_inactive') }}</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('panel.groups.recommend.index') }}">{{ __('FsLang::panel.sidebar_groups_tab_recommend') }}</a></li>
        </ul>
    </div>
    <!--groups config-->
    <div class="row">
        <div class="col-lg-3">
            <div class="list-group">
                @foreach ($categories as $category)
                    @if ($category['is_enable'])
                        <!--category activate-->
                        <a href="{{ route('panel.groups.index', ['parent_id' => $category->id]) }}" class="list-group-item list-group-item-action {{ $category->id == $parentId ? 'active' : '' }} d-flex justify-content-between align-items-center">
                            <input type="number" class="form-control input-number rank-num" data-action="{{ route('panel.groups.rank.update', $category->id) }}" value="{{ $category->rank_num }}" style="width:50px;">
                            <span class="ms-2 text-nowrap overflow-hidden">{{ $category->name }}</span>
                            <button type="button" data-params="{{ $category->toJson() }}"
                                data-names="{{ $category->names->toJson() }}"
                                data-default-name="{{ $category->getLangName($defaultLanguage) }}"
                                data-default-desc="{{ $category->getLangDescription($defaultLanguage) }}"
                                data-descriptions="{{ $category->descriptions->toJson() }}"
                                data-action="{{ route('panel.groups.update', $category->id) }}"
                                class="btn btn-outline-info btn-sm text-nowrap fs-9 ms-auto edit-group-category"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.button_edit') }}">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                        </a>
                    @else
                        <!--category deactivate-->
                        <a href="{{ route('panel.groups.index', ['parent_id' => $category->id]) }}" class="list-group-item list-group-item-secondary {{ $category->id == $parentId ? 'active' : '' }} d-flex justify-content-between align-items-center">
                            <input type="number" class="form-control input-number rank-num" data-action="{{ route('panel.groups.rank.update', $category->id) }}" value="{{ $category->rank_num }}" style="width:50px;">
                            <span class="ms-2 text-nowrap overflow-hidden">{{ $category->name }}</span>
                            <button type="button" data-params="{{ $category->toJson() }}"
                                data-names="{{ $category->names->toJson() }}"
                                data-default-name="{{ $category->getLangName($defaultLanguage) }}"
                                data-default-desc="{{ $category->getLangDescription($defaultLanguage) }}"
                                data-descriptions="{{ $category->descriptions->toJson() }}"
                                data-action="{{ route('panel.groups.update', $category->id) }}"
                                class="btn btn-outline-info btn-sm text-nowrap fs-9 ms-auto edit-group-category"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.button_edit') }}">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button type="button" class="btn {{ $category->id == $parentId ? 'btn-outline-light' : 'btn-outline-secondary' }} btn-sm text-nowrap fs-9 ms-1 delete-group-category" data-action="{{ route('panel.groups.destroy', $category->id) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.button_delete') }}"><i class="bi bi-trash"></i></button>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
        <div class="col-lg-9">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap">
                    <thead>
                        <tr class="table-info">
                            <th scope="col" style="width:4rem;">{{ __('FsLang::panel.table_order') }}</th>
                            <th scope="col">{{ __('FsLang::panel.table_name') }}</th>
                            <th scope="col">{{ __('FsLang::panel.group_table_mode') }}</th>
                            <th scope="col">{{ __('FsLang::panel.group_table_follow') }}</th>
                            <th scope="col">{{ __('FsLang::panel.group_table_recommend') }}</th>
                            <th scope="col">{{ __('FsLang::panel.group_table_admins') }}</th>
                            <th scope="col">{{ __('FsLang::panel.group_table_post_publish') }}</th>
                            <th scope="col">{{ __('FsLang::panel.group_table_comment_publish') }}</th>
                            <th scope="col" style="width:10rem;">{{ __('FsLang::panel.table_options') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groups as $group)
                            <tr>
                                <td><input type="number" data-action="{{ route('panel.groups.rank.update', $group->id) }}" class="form-control input-number rank-num" value="{{ $group->rank_num }}"></td>
                                <td>
                                    @if ($group->cover_file_url)
                                        <img src="{{ $group->cover_file_url }}" width="24" height="24">
                                    @endif
                                    {{ $group->name }}
                                </td>
                                <td>{{ $typeModeLabels[$group->type_mode] ?? '' }}</td>
                                <td>
                                    @if ($group->type_follow == 1)
                                        {{ __('FsLang::panel.group_option_follow_fresns') }}
                                    @else
                                        {{ __('FsLang::panel.group_option_follow_plugin') }} <span class="badge bg-light text-dark">{{ optional($group->plugin)->name }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($group->is_recommend)
                                        <i class="bi bi-check-lg text-success"></i>
                                    @else
                                        <i class="bi bi-dash-lg text-secondary"></i>
                                    @endif
                                </td>
                                <td>
                                    @foreach ($group->admin_users as $user)
                                        <span class="badge bg-light text-dark">{{ $user->nickname }}</span>
                                    @endforeach
                                </td>
                                <td><span class="badge bg-light text-dark">{{ $permissionLabels[$group->permission['publish_post'] ?? 0] ?? '' }}</span></td>
                                <td><span class="badge bg-light text-dark">{{ $permissionLabels[$group->permission['publish_comment'] ?? 0] ?? '' }}</span></td>
                                <td>
                                    <form action="{{ route('panel.groups.enable.update', ['group' => $group->id, 'is_enable' => 0]) }}" method="post">
                                        @csrf
                                        @method('put')
                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                            data-action="{{ route('panel.groups.update', $group->id) }}"
                                            data-params="{{ $group->toJson() }}"
                                            data-names="{{ $group->names->toJson() }}"
                                            data-admin_users="{{ $group->admin_users }}"
                                            data-descriptions="{{ $group->descriptions->toJson() }}"
                                            data-names="{{ $group->names->toJson() }}"
                                            data-descriptions="{{ $group->descriptions->toJson() }}"
                                            data-bs-toggle="modal" data-bs-target="#groupModal">{{ __('FsLang::panel.button_edit') }}</button>
                                        <button type="button" class="btn btn-outline-success btn-sm"
                                            data-action="{{ route('panel.groups.merge', $group->id) }}"
                                            data-params="{{ $group->toJson() }}" data-bs-toggle="modal"
                                            data-bs-target="#moveModal">{{ __('FsLang::panel.button_group_move') }}</button>
                                        <button type="submit" class="btn btn-link link-danger ms-1 fresns-link fs-7">{{ __('FsLang::panel.button_deactivate') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $groups ? $groups->links() : '' }}
        </div>
    </div>

    <!--group category-->
    <form action="" method="post" class="check-names" enctype="multipart/form-data">
        @csrf
        @method('post')
        <input type="hidden" name="update_name" value="0">
        <input type="hidden" name="update_description" value="0">
        <input type="hidden" name="is_category" value="1">
        <!-- Modal -->
        <div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModal"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('FsLang::panel.group_category') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.group_category') }}</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control input-number" name="rank_num" required>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_name') }}</label>
                            <div class="col-sm-9">
                                <button type="button" class="btn btn-outline-secondary btn-modal w-100 text-start name-button" data-bs-toggle="modal" data-parent="#createCategoryModal" data-bs-target="#categoryLangModal">{{ __('FsLang::panel.table_name') }}</button>
                                <div class="invalid-feedback">{{ __('FsLang::tips.required_group_category_name') }}</div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_description') }}</label>
                            <div class="col-sm-9">
                                <button type="button" class="btn btn-outline-secondary btn-modal w-100 text-start desc-button" data-bs-toggle="modal" data-parent="#createCategoryModal" data-bs-target="#categoryLangDescModal">{{ __('FsLang::panel.table_description') }}</button>
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
                                    <input type="file" class="form-control inputFile" name="cover_file">
                                    <input type="url" class="form-control inputUrl" name="cover_file_url" style="display:none;">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_banner') }}</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.button_image_upload') }}</button>
                                    <ul class="dropdown-menu selectInputType">
                                        <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                                        <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                                    </ul>
                                    <input type="file" class="form-control inputFile" name="banner_file">
                                    <input type="url" class="form-control inputUrl" name="banner_file_url" style="display:none;">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_status') }}</label>
                            <div class="col-sm-9 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_enable" id="cat_status_true" value="1" checked>
                                    <label class="form-check-label" for="cat_status_true">{{ __('FsLang::panel.option_activate') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_enable" id="cat_status_false" value="0">
                                    <label class="form-check-label" for="cat_status_false">{{ __('FsLang::panel.option_deactivate') }}</label>
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
        <div class="modal fade name-lang-modal" id="categoryLangModal" tabindex="-1" aria-labelledby="categoryLangModal"
            aria-hidden="true">
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

        <!-- Language Modal -->
        <div class="modal fade description-lang-modal" id="categoryLangDescModal" tabindex="-1"
            aria-labelledby="categoryLangDescModal" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('FsLang::panel.table_description') }}</h5>
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
                                            <td><textarea class="form-control desc-input" name="descriptions[{{ $lang['langTag'] }}]" rows="3"></textarea></td>
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

    <!--Group Modal-->
    <form action="" method="post" class="check-names" enctype="multipart/form-data">
        @csrf
        @method('put')
        <input type="hidden" name="update_name" value="0">
        <input type="hidden" name="update_description" value="0">
        <input type="hidden" name="is_category" value="0">
        <!-- Modal -->
        <div class="modal fade" id="groupModal" tabindex="-1" aria-labelledby="groupModal" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('FsLang::panel.group') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-md-2 col-form-label">{{ __('FsLang::panel.group_category') }}</label>
                            <div class="col-sm-9 col-md-10">
                                <select class="form-select" name="parent_id" required>
                                    <option selected disabled value="">{{ __('FsLang::panel.select_box_tip_group_category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-md-2 col-form-label">{{ __('FsLang::panel.table_order') }}</label>
                            <div class="col-sm-9 col-md-10">
                                <input type="number" class="form-control input-number" name="rank_num" required>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-md-2 col-form-label">{{ __('FsLang::panel.table_name') }}</label>
                            <div class="col-sm-9 col-md-10">
                                <button type="button" class="btn btn-outline-secondary btn-modal w-100 text-start name-button" data-bs-toggle="modal" data-parent="#groupModal" data-bs-target="#langGroupModal">{{ __('FsLang::panel.table_name') }}</button>
                                <div class="invalid-feedback">{{ __('FsLang::tips.required_group_name') }}</div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-md-2 col-form-label">{{ __('FsLang::panel.table_description') }}</label>
                            <div class="col-sm-9 col-md-10">
                                <button type="button" class="btn btn-outline-secondary btn-modal w-100 text-start desc-button" data-bs-toggle="modal" data-parent="#groupModal" data-bs-target="#langGroupDescModal">{{ __('FsLang::panel.table_description') }}</button>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-md-2 col-form-label">{{ __('FsLang::panel.table_icon') }}</label>
                            <div class="col-sm-9 col-md-10">
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="showIcon">{{ __('FsLang::panel.button_image_upload') }}</button>
                                    <ul class="dropdown-menu selectInputType">
                                        <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                                        <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                                    </ul>
                                    <input type="file" class="form-control inputFile" name="cover_file">
                                    <input type="url" class="form-control inputUrl" name="cover_file_url" style="display:none;">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-md-2 col-form-label">{{ __('FsLang::panel.table_banner') }}</label>
                            <div class="col-sm-9 col-md-10">
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="showIcon">{{ __('FsLang::panel.button_image_upload') }}</button>
                                    <ul class="dropdown-menu selectInputType">
                                        <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                                        <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                                    </ul>
                                    <input type="file" class="form-control inputFile" name="banner_file">
                                    <input type="url" class="form-control inputUrl" name="banner_file_url" style="display:none;">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-md-2 col-form-label">{{ __('FsLang::panel.group_table_mode') }}</label>
                            <div class="col-sm-9 col-md-10 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type_mode" id="type_mode_true" value="1" data-bs-toggle="collapse" data-bs-target="#mode_setting.show" aria-expanded="false" aria-controls="mode_setting" checked>
                                    <label class="form-check-label" for="type_mode_true">{{ __('FsLang::panel.group_option_mode_public') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type_mode" id="type_mode_false" value="2" data-bs-toggle="collapse" data-bs-target="#mode_setting:not(.show)" aria-expanded="false" aria-controls="mode_setting">
                                    <label class="form-check-label" for="type_mode_false">{{ __('FsLang::panel.group_option_mode_private') }}</label>
                                </div>
                                <div class="collapse mt-2" id="mode_setting">
                                    <div class="input-group">
                                        <span class="input-group-text">{{ __('FsLang::panel.group_table_find') }}</span>
                                        <div class="form-control">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="type_find" id="type_find_true" value="1" checked>
                                                <label class="form-check-label" for="type_find_true">{{ __('FsLang::panel.group_option_find_visible') }}</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="type_find" id="type_find_false" value="2">
                                                <label class="form-check-label" for="type_find_false">{{ __('FsLang::panel.group_option_find_hidden') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-md-2 col-form-label">{{ __('FsLang::panel.group_table_follow') }}</label>
                            <div class="col-sm-9 col-md-10 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type_follow" id="type_follow_true" value="1" data-bs-toggle="collapse" data-bs-target="#follow_setting.show" aria-expanded="false" aria-controls="follow_setting" checked>
                                    <label class="form-check-label" for="type_follow_true">{{ __('FsLang::panel.group_option_follow_fresns') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type_follow" id="type_follow_false" value="2" data-bs-toggle="collapse" data-bs-target="#follow_setting:not(.show)" aria-expanded="false" aria-controls="follow_setting">
                                    <label class="form-check-label" for="type_follow_false">{{ __('FsLang::panel.group_option_follow_plugin') }}</label>
                                </div>
                                <div class="collapse mt-2" id="follow_setting">
                                    <div class="input-group">
                                        <span class="input-group-text">{{ __('FsLang::panel.table_plugin') }}</span>
                                        <select class="form-select" name="plugin_unikey">
                                            <option selected disabled>{{ __('FsLang::panel.select_box_tip_plugin') }}</option>
                                            @foreach ($plugins as $plugin)
                                                <option value="{{ $plugin->unikey }}">{{ $plugin->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-md-2 col-form-label">{{ __('FsLang::panel.group_table_recommend') }}</label>
                            <div class="col-sm-9 col-md-10 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_recommend" id="recommend_false" value="0" checked>
                                    <label class="form-check-label" for="recommend_false">{{ __('FsLang::panel.option_no') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_recommend" id="recommend_true" value="1">
                                    <label class="form-check-label" for="recommend_true">{{ __('FsLang::panel.option_yes') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-md-2 col-form-label">{{ __('FsLang::panel.group_table_admins') }}</label>
                            <div class="col-sm-9 col-md-10">
                                <select class="form-select group-user-select2" name="permission[admin_users][]" multiple="multiple"></select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-md-2 col-form-label">{{ __('FsLang::panel.group_table_post_publish') }}</label>
                            <div class="col-sm-9 col-md-10 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="permission[publish_post]" id="publish.post.1" value="1" data-bs-toggle="collapse" data-bs-target="#publish_post_setting.show" aria-expanded="false" aria-controls="publish_post_setting" checked>
                                    <label class="form-check-label" for="publish.post.1">{{ __('FsLang::panel.group_option_publish_all') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="permission[publish_post]" id="publish.post.2" value="2" data-bs-toggle="collapse" data-bs-target="#publish_post_setting.show" aria-expanded="false" aria-controls="publish_post_setting">
                                    <label class="form-check-label" for="publish.post.2">{{ __('FsLang::panel.group_option_publish_follow') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="permission[publish_post]" id="publish.post.3" value="3" data-bs-toggle="collapse" data-bs-target="#publish_post_setting:not(.show)" aria-expanded="false" aria-controls="publish_post_setting">
                                    <label class="form-check-label" for="publish.post.3">{{ __('FsLang::panel.group_option_publish_role') }}</label>
                                </div>
                                <div class="collapse mt-2" id="publish_post_setting">
                                    <div class="input-group">
                                        <span class="input-group-text">{{ __('FsLang::panel.group_table_publish_perm_role') }}</span>
                                        <select class="form-select select2" name="permission[publish_post_roles][]" multiple="multiple">
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}">{{ $role->getLangName($defaultLanguage) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="input-group mt-2">
                                    <span class="input-group-text">{{ __('FsLang::panel.group_table_publish_perm_review') }}<i class="bi bi-info-circle ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.group_table_publish_perm_review_desc') }}"></i></span>
                                    <div class="form-control bg-white">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="permission[publish_post_review]" id="publish.post.review.0" value="0" checked>
                                            <label class="form-check-label" for="publish.post.review.0">{{ __('FsLang::panel.option_no') }}</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="permission[publish_post_review]" id="publish.post.review.1" value="1">
                                            <label class="form-check-label" for="publish.post.review.1">{{ __('FsLang::panel.option_yes') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-md-2 col-form-label">{{ __('FsLang::panel.group_table_comment_publish') }}</label>
                            <div class="col-sm-9 col-md-10 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="permission[publish_comment]" id="publish.comment.1" value="1" data-bs-toggle="collapse" data-bs-target="#publish_comment_setting.show" aria-expanded="false" aria-controls="publish_comment_setting" checked>
                                    <label class="form-check-label" for="publish.comment.1">{{ __('FsLang::panel.group_option_publish_all') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="permission[publish_comment]" id="publish.comment.2" value="2" data-bs-toggle="collapse" data-bs-target="#publish_comment_setting.show" aria-expanded="false" aria-controls="publish_comment_setting">
                                    <label class="form-check-label" for="publish.comment.2">{{ __('FsLang::panel.group_option_publish_follow') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="permission[publish_comment]" id="publish.comment.3" value="3" data-bs-toggle="collapse" data-bs-target="#publish_comment_setting:not(.show)" aria-expanded="false" aria-controls="publish_comment_setting">
                                    <label class="form-check-label" for="publish.comment.3">{{ __('FsLang::panel.group_option_publish_role') }}</label>
                                </div>
                                <div class="collapse mt-2" id="publish_comment_setting">
                                    <div class="input-group">
                                        <span class="input-group-text">{{ __('FsLang::panel.group_table_publish_perm_role') }}</span>
                                        <select class="form-select select2" name="permission[publish_comment_roles][]" multiple="multiple">
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}">{{ $role->getLangName($defaultLanguage) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="input-group mt-2">
                                    <span class="input-group-text">{{ __('FsLang::panel.group_table_publish_perm_review') }}<i class="bi bi-info-circle ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.group_table_publish_perm_review_desc') }}"></i></span>
                                    <div class="form-control bg-white">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="permission[publish_comment_review]" id="publish.comment.review.0" value="0" checked>
                                            <label class="form-check-label" for="publish.comment.review.0">{{ __('FsLang::panel.option_no') }}</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="permission[publish_comment_review]" id="publish.comment.review.1" value="1">
                                            <label class="form-check-label" for="publish.comment.review.1">{{ __('FsLang::panel.option_yes') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-md-2 col-form-label"></label>
                            <div class="col-sm-9 col-md-10">
                                <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Language Modal -->
        <div class="modal fade name-lang-modal" id="langGroupModal" tabindex="-1" aria-labelledby="langGroupModal"
            aria-hidden="true">
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

        <!-- Language Modal -->
        <div class="modal fade description-lang-modal" id="langGroupDescModal" tabindex="-1"
            aria-labelledby="langGroupDescModal" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('FsLang::panel.table_description') }}</h5>
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
                                            <td><textarea class="form-control desc-input" name="descriptions[{{ $lang['langTag'] }}]" rows="3"></textarea></td>
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

    <!-- Move Modal -->
    <div class="modal fade" id="moveModal" tabindex="-1" aria-labelledby="moveModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_group_move') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="">
                        @csrf
                        @method('put')
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.group_current') }}</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control-plaintext" name="current_group" readonly>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.group_target') }}</label>
                            <div class="col-sm-9">
                                <select class="form-select choose-category" name="category_id" data-action="{{ route('panel.groups.categories.index') }}">
                                    <option selected disabled>{{ __('FsLang::panel.select_box_tip_group_category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->getLangName($defaultLanguage) }}</option>
                                    @endforeach
                                </select>
                                <select class="form-select choose-group mt-3" name="group_id" required>
                                    <option selected disabled value="">{{ __('FsLang::panel.select_box_tip_group') }}</option>
                                </select>
                                <div class="form-text">{{ __('FsLang::panel.group_target_desc') }}</div>
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
