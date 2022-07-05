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

    <!--group list-->
    <div class="row">
        <div class="col-lg-3">
            <div class="list-group">
                @foreach ($categories as $category)
                    @if ($category['is_enable'])
                        <!--category activate-->
                        <a href="{{ route('panel.groups.index', ['parent_id' => $category->id]) }}" class="list-group-item list-group-item-action {{ $category->id == $parentId ? 'active' : '' }} d-flex justify-content-between align-items-center">
                            <input type="number" class="form-control input-number rating-number" data-action="{{ route('panel.groups.rating.update', $category->id) }}" value="{{ $category->rating }}" style="width:50px;">
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
                            <input type="number" class="form-control input-number rating-number" data-action="{{ route('panel.groups.rating.update', $category->id) }}" value="{{ $category->rating }}" style="width:50px;">
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
                                <td><input type="number" data-action="{{ route('panel.groups.rating.update', $group->id) }}" class="form-control input-number rating-number" value="{{ $group->rating }}"></td>
                                <td>
                                    @if ($group->getCoverUrl())
                                        <img src="{{ $group->getCoverUrl() }}" width="24" height="24">
                                    @endif
                                    {{ $group->getLangName($defaultLanguage) }}
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
                                    @foreach ($group->admins as $user)
                                        <span class="badge bg-light text-dark">{{ $user->nickname }}</span>
                                    @endforeach
                                </td>
                                <td><span class="badge bg-light text-dark">{{ $permissionLabels[$group->permissions['publish_post'] ?? 0] ?? '' }}</span></td>
                                <td><span class="badge bg-light text-dark">{{ $permissionLabels[$group->permissions['publish_comment'] ?? 0] ?? '' }}</span></td>
                                <td>
                                    <form action="{{ route('panel.groups.enable.update', ['group' => $group->id, 'is_enable' => 0]) }}" method="post">
                                        @csrf
                                        @method('put')
                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                            data-action="{{ route('panel.groups.update', $group->id) }}"
                                            data-params="{{ $group->toJson() }}"
                                            data-names="{{ $group->names->toJson() }}"
                                            data-admin_users="{{ $group->admins }}"
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

    <!--group category edit modal-->
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
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_order') }}</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control input-number" name="rating" required>
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

    <!--group edit modal-->
    @include('FsView::operations.group-edit')
@endsection
