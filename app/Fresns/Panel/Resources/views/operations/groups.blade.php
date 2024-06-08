@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::operations.sidebar')
@endsection

@section('content')
    <!--header-->
    <div class="row mb-4">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_groups') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_groups_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-action="{{ route('panel.groups.store') }}" data-bs-target="#groupModal"><i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add_group') }}</button>
                {{-- <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a> --}}
            </div>
        </div>
        <ul class="nav nav-tabs">
            <li class="nav-item"><a class="nav-link {{ request('type') ? '' : 'active' }}" href="{{ route('panel.groups.index') }}">{{ __('FsLang::panel.sidebar_groups_tab_active') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ request('type') == 'deactivate' ? 'active' : '' }}" href="{{ route('panel.groups.index', ['type' => 'deactivate']) }}">{{ __('FsLang::panel.sidebar_groups_tab_deactivate') }}</a></li>
            <li class="nav-item"><a class="nav-link {{ request('type') == 'recommend' ? 'active' : '' }}" href="{{ route('panel.groups.index', ['type' => 'recommend']) }}">{{ __('FsLang::panel.sidebar_groups_tab_recommend') }}</a></li>
        </ul>
    </div>

    <!--back-->
    @if (request('parentId'))
        <div class="mb-4">
            <a class="btn btn-outline-primary" href="{{ request()->fullUrlWithQuery(['parentId' => $parentGroup?->parentGroup?->id]) }}" role="button"><i class="bi bi-arrow-left"></i></a>
            <span class="ms-2">{{ $parentGroup->getLangContent('name', $defaultLanguage) }}</span>
        </div>
    @endif

    <!--list-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    @if (request('type'))
                        @if (request('type') == 'recommend')
                            <th scope="col" style="width:4rem;">{{ __('FsLang::panel.table_recommend_order') }}</th>
                        @endif
                        <th scope="col">{{ __('FsLang::panel.group_table_parent_group') }}</th>
                    @else
                        <th scope="col" style="width:4rem;">{{ __('FsLang::panel.table_order') }}</th>
                    @endif
                    <th scope="col">{{ __('FsLang::panel.table_name') }}</th>
                    <th scope="col">{{ __('FsLang::panel.group_table_privacy') }}</th>
                    <th scope="col">{{ __('FsLang::panel.group_table_follow_method') }}</th>
                    <th scope="col">{{ __('FsLang::panel.group_table_admins') }}</th>
                    <th scope="col">{{ __('FsLang::panel.group_table_post_permissions') }}</th>
                    <th scope="col">{{ __('FsLang::panel.group_table_comment_permissions') }}</th>
                    <th scope="col">{{ __('FsLang::panel.group_table_subgroup') }}</th>
                    <th scope="col" style="width:10rem;">{{ __('FsLang::panel.table_options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groups as $group)
                    <tr>
                        @if (request('type'))
                            @if (request('type') == 'recommend')
                                <td><input type="number" class="form-control input-number update-order" data-action="{{ route('panel.groups.recommend-order', $group->id) }}" value="{{ $group->recommend_sort_order }}"></td>
                            @endif
                            <td>{{ $group?->parentGroup?->getLangContent('name', $defaultLanguage) }}</td>
                        @else
                            <td><input type="number" class="form-control input-number update-order" data-action="{{ route('panel.groups.order', $group->id) }}" value="{{ $group->sort_order }}"></td>
                        @endif
                        <td>
                            @if ($group->getCoverUrl())
                                <img src="{{ $group->getCoverUrl() }}" width="24" height="24">
                            @endif

                            {{ $group->getLangContent('name', $defaultLanguage) }}

                            @if (request('type') != 'recommend' && $group->is_recommend)
                                <span class="badge rounded-pill text-bg-warning fs-9">{{ __('FsLang::panel.recommend') }}</span>
                            @endif
                        </td>
                        <td>{{ $typeModeLabels[$group->privacy] ?? '' }}</td>
                        <td>
                            @if ($group->follow_method == 1)
                                Fresns API
                            @elseif ($group->follow_method == 2)
                                Plugin Page <span class="badge bg-light text-dark">{{ optional($group->plugin)->name }}</span>
                            @else
                                {{ __('FsLang::panel.option_close') }}
                            @endif
                        </td>
                        <td>
                            @foreach ($group->admins as $user)
                                <span class="badge bg-light text-dark">{{ $user->nickname }}</span>
                            @endforeach
                        </td>
                        <td>
                            @if ($group->permissions['can_publish'] ?? false)
                                <span class="badge bg-light text-dark">{{ $permissionLabels[$group->permissions['publish_post'] ?? false] ?? '' }}</span>
                            @else
                                <span class="badge bg-light text-secondary">{{ __('FsLang::panel.option_close') }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($group->permissions['can_publish'] ?? false)
                                <span class="badge bg-light text-dark">{{ $permissionLabels[$group->permissions['publish_comment'] ?? false] ?? '' }}</span>
                            @else
                                <i class="bi bi-dash-lg text-secondary"></i>
                            @endif
                        </td>
                        <td>
                            @if ($group->subgroup_count)
                                <a class="badge bg-light text-primary" href="{{ route('panel.groups.index', ['parentId' => $group->id]) }}">{{ $group->subgroup_count }}</a>
                            @else
                                <span class="badge bg-light text-dark">{{ $group->subgroup_count }}</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('panel.groups.status', ['group' => $group->id]) }}" method="post">
                                @csrf
                                @method('patch')
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#groupModal"
                                    data-action="{{ route('panel.groups.update', $group->id) }}"
                                    data-parent-group-name="{{ $group?->parentGroup?->getLangContent('name', $defaultLanguage) }}"
                                    data-default-name="{{ $group->getLangContent('name', $defaultLanguage) }}"
                                    data-default-description="{{ $group->getLangContent('description', $defaultLanguage) }}"
                                    data-cover-url="{{ $group->getCoverUrl() }}"
                                    data-banner-url="{{ $group->getBannerUrl() }}"
                                    data-params="{{ $group->toJson() }}"
                                    data-admin-users="{{ $group->admins }}">
                                    {{ __('FsLang::panel.button_edit') }}
                                </button>

                                <button type="button" class="btn btn-outline-success btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#moveModal"
                                    data-action="{{ route('panel.groups.merge', $group->id) }}"
                                    data-default-name="{{ $group->getLangContent('name', $defaultLanguage) }}"
                                    data-group-id="{{ $group->id }}">
                                    {{ __('FsLang::panel.button_group_move') }}
                                </button>

                                @if ($group->is_enabled)
                                    <button type="submit" class="btn btn-link link-danger ms-1 fresns-link fs-7">{{ __('FsLang::panel.button_deactivate') }}</button>
                                @else
                                    <button type="submit" class="btn btn-outline-warning btn-sm">{{ __('FsLang::panel.button_activate') }}</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($groups instanceof \Illuminate\Pagination\LengthAwarePaginator)
        {{ $groups->appends(request()->all())->links() }}
    @endif

    <!--group edit modal-->
    @include('FsView::operations.group-edit-modal')
@endsection
