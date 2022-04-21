@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--code messages header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_code_messages') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_code_messages_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <!--filter-->
        <form action="">
            @csrf
            <div class="input-group">
                <span class="input-group-text">{{ __('FsLang::panel.table_lang_tag') }}</span>
                <select class="form-select" id="langTag" required>
                    <option value="en">en -> English</option>
                    <option value="zh-Hans" selected>zh-Hans -> 简体中文</option>
                    <option value="zh-Hant">zh-Hant -> 繁體中文</option>
                </select>
                <span class="input-group-text">{{ __('FsLang::panel.table_plugin') }}</span>
                <select class="form-select" id="unikey">
                    <option value="" selected>全部</option>
                    <option value="Fresns">Fresns</option>
                    <option value="QiNiu">QiNiu -> 七牛云</option>
                </select>
                <span class="input-group-text">{{ __('FsLang::panel.table_number') }}</span>
                <input type="number" class="form-control" placeholder="Code">
                <button class="btn btn-outline-secondary" type="submit">{{ __('FsLang::panel.button_confirm') }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('panel.code.messages.index') }}">{{ __('FsLang::panel.button_reset') }}</a>
            </div>
        </form>
        <!--filter end-->
    </div>
    <!--code messages list-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    <th scope="col">{{ __('FsLang::panel.table_plugin') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_number') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_lang_tag') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_content') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_options') }}</th>
                </tr>
            </thead>
            <tbody>
                    <tr>
                        <td>七牛云 <span class="badge bg-secondary">QiNiu</span></td>
                        <td>30000</td>
                        <td>zh-Hans</td>
                        <td><input type="text" class="form-control" value="描述" readonly></td>
                        <td><button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editMessages">{{ __('FsLang::panel.button_edit') }}</button></td>
                    </tr>
            </tbody>
        </table>
    </div>
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <li class="page-item">
                <a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>
            </li>
            <li class="page-item"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item">
                <a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>
            </li>
        </ul>
    </nav>

    <!-- Edit Modal -->
    <div class="modal fade" id="editMessages" tabindex="-1" aria-labelledby="editMessagesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMessagesModalLabel">
                        <span class="code_plugin">七牛云</span>
                        <span class="badge bg-secondary">QiNiu</span>
                        <span class="badge bg-warning text-dark">30000</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
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
                                        <tr>
                                            <td>
                                                langTag
                                                <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.default_language') }}" data-bs-original-title="{{ __('FsLang::panel.default_language') }}" aria-label="{{ __('FsLang::panel.default_language') }}"></i>
                                            </td>
                                            <td>langName</td>
                                            <td>
                                                <textarea name="" class="form-control" rows="3">Code Message</textarea>
                                            </td>
                                        </tr>
                                </tbody>
                            </table>
                        </div>
                        <!--button_save-->
                        <div class="text-center">
                            <button type="submit" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
