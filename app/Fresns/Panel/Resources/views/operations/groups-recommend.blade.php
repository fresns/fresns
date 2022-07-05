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
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
        <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link" href="{{ route('panel.groups.index') }}">{{ __('FsLang::panel.sidebar_groups_tab_active') }}</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('panel.groups.inactive.index') }}">{{ __('FsLang::panel.sidebar_groups_tab_inactive') }}</a></li>
            <li class="nav-item"><a class="nav-link active" href="{{ route('panel.groups.recommend.index') }}">{{ __('FsLang::panel.sidebar_groups_tab_recommend') }}</a></li>
        </ul>
    </div>

    <!--group list-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    <th scope="col" style="width:6rem;">{{ __('FsLang::panel.group_table_recommend_order') }}</th>
                    <th scope="col">{{ __('FsLang::panel.group_category') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_name') }}</th>
                    <th scope="col">{{ __('FsLang::panel.group_table_mode') }}</th>
                    <th scope="col">{{ __('FsLang::panel.group_table_follow') }}</th>
                    <th scope="col">{{ __('FsLang::panel.group_table_admins') }}</th>
                    <th scope="col">{{ __('FsLang::panel.group_table_post_publish') }}</th>
                    <th scope="col">{{ __('FsLang::panel.group_table_comment_publish') }}</th>
                    <th scope="col" style="width:10rem;">{{ __('FsLang::panel.table_options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groups as $group)
                    <tr>
                        <td><input type="number" data-action="{{ route('panel.groups.recommend.rating.update', $group->id) }}" name="rating" class="form-control input-number rating-number" value="{{ $group->recommend_rating }}"></td>
                        <td><span class="badge rounded-pill bg-secondary">{{ optional($group->category)->name }}</span></td>
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
                                    data-params="{{ $group->toJson() }}" data-names="{{ $group->names->toJson() }}"
                                    data-admin_users="{{ $group->admins }}"
                                    data-descriptions="{{ $group->descriptions->toJson() }}"
                                    data-names="{{ $group->names->toJson() }}"
                                    data-descriptions="{{ $group->descriptions->toJson() }}" data-bs-toggle="modal"
                                    data-bs-target="#groupModal">{{ __('FsLang::panel.button_edit') }}</button>

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

    <!--group edit modal-->
    @include('FsView::operations.group-edit')
@endsection
