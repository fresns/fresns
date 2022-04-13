@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::dashboard.sidebar')
@endsection

@section('content')
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_admins') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_admins_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#newAdmin"><i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add_admin') }}</button>
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <!--Admin List-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    <th scope="col">ID</th>
                    <th scope="col">AID</th>
                    <th scope="col">{{ __('FsLang::panel.admin_add_form_account') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($admins as $admin)
                    <tr>
                        <td>{{ $admin->id }} </td>
                        <td>{{ $admin->aid }} </td>
                        <td>
                            <span class="badge bg-light text-dark"><i class="bi bi-envelope"></i>
                                @if ($admin->email)
                                    {{ $admin->secret_email }}
                                @else
                                    None
                                @endif
                            </span>
                            <span class="badge bg-light text-dark"><i class="bi bi-phone"></i>
                                @if ($admin->pure_phone)
                                    +{{ $admin->country_code }} {{ $admin->secret_pure_phone }}
                                @else
                                    None
                                @endif
                            </span>
                        </td>
                        <td>
                            @if ($admin->id != \Auth::user()->id)
                                <form action="{{ route('panel.admins.destroy', $admin) }}" class="mb-3" method="post">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-link btn-sm text-danger fresns-link delete-button">{{ __('FsLang::panel.button_delete') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="newAdmin" tabindex="-1" aria-labelledby="newAdmin" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.admin_add_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('panel.admins.store') }}" class="mb-3" method="post">
                        @csrf
                        <div class="input-group">
                            <span class="input-group-text">{{ __('FsLang::panel.admin_add_form_account') }}</span>
                            <input type="text" name="accountName" class="form-control" placeholder="{{ __('FsLang::panel.admin_add_form_account_placeholder') }}">
                            <button class="btn btn-outline-secondary" type="submit" id="folderInstall-button">{{ __('FsLang::panel.admin_add_form_account_btn') }}</button>
                        </div>
                        <div class="form-text"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.admin_add_form_account_desc') }}</div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
