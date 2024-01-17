@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::extends.sidebar')
@endsection

@section('content')
    <!--header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-9">
            <h3>{{ __('FsLang::panel.sidebar_extend_command_words') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_extend_command_words_intro') }}</p>
        </div>
        <div class="col-lg-3">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add') }}
                </button>
                {{-- <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a> --}}
            </div>
        </div>
    </div>

    <!--list-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    <th scope="col">{{ __('FsLang::panel.table_plugin') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_command_word') }}</th>
                    <th scope="col" style="width:8rem;">{{ __('FsLang::panel.table_options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($commandWords as $item)
                    <tr>
                        <td>{{ $item['fskey'] }}</td>
                        <td>{{ $item['cmdWord'] }}</td>
                        <td>
                            <form action="{{ route('panel.command-words.destroy', ['fskey' => $item['fskey'], 'cmdWord' => $item['cmdWord']]) }}" method="post">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-link link-danger ms-1 fresns-link fs-7 delete-button">{{ __('FsLang::panel.button_delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!--modal-->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.sidebar_extend_command_words') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('panel.command-words.store') }}" method="post">
                    @csrf
                    @method('post')
                    <div class="modal-body">
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_plugin') }}</label>
                            <div class="col-sm-9">
                                <select class="form-select" name="fskey" required>
                                    <option selected disabled value="">{{ __('FsLang::tips.select_box_tip_plugin') }}</option>
                                    @foreach ($plugins as $plugin)
                                        <option value="{{ $plugin->fskey }}">{{ $plugin->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label">{{ __('FsLang::panel.table_command_word') }}</label>
                            <div class="col-sm-9">
                                <input type="text" name="cmdWord" class="form-control" value="" required>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-3 col-form-label"></label>
                            <div class="col-sm-9"><button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
