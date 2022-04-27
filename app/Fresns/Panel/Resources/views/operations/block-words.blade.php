@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::operations.sidebar')
@endsection

@section('content')
    <!--block words header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_block_words') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_block_words_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-action="{{ route('panel.block-words.store') }}" data-bs-target="#blockWordModal">
                    <i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add_block_word') }}
                </button>
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <!--block words function-->
    <div class="row mb-3">
        <div class="col-lg-4">
            <form action="{{ route('panel.block-words.index') }}">
                <div class="input-group">
                    <input type="text" class="form-control" name="keyword" value="{{ $keyword }}" placeholder="{{ __('FsLang::panel.block_word') }}">
                    <button class="btn btn-outline-secondary" type="submit">{{ __('FsLang::panel.button_search') }}</button>
                </div>
            </form>
        </div>
        <div class="col-lg-8">
            <div class="input-group justify-content-lg-end">
                <form method="post" action="{{ route('panel.block-words.import') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file" id="importBlockInput" style="display:none">
                    <button class="btn btn-outline-info rounded-0 rounded-start" id="importBlockWords" type="button">{{ __('FsLang::panel.button_batch_import') }}</button>
                </form>
                <form method="post" action="{{ route('panel.block-words.export') }}">
                    @csrf
                    <button class="btn btn-outline-info rounded-0 rounded-end" type="submit">{{ __('FsLang::panel.button_batch_export') }}</button>
                </form>
            </div>
        </div>
    </div>
    <!--block words list-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    <th scope="col">{{ __('FsLang::panel.block_word') }}</th>
                    <th scope="col">{{ __('FsLang::panel.block_word_content_mode_desc') }}</th>
                    <th scope="col">{{ __('FsLang::panel.block_word_user_mode_desc') }}</th>
                    <th scope="col">{{ __('FsLang::panel.block_word_dialog_mode_desc') }}</th>
                    <th scope="col">{{ __('FsLang::panel.replace_word') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($words as $word)
                    <tr>
                        <td><input type="text" class="form-control" disabled value="{{ $word->word }}"></td>
                        <td>{{ $contentModeLabels[$word->content_mode] ?? '' }}</td>
                        <td>{{ $userModeLabels[$word->user_mode] ?? '' }}</td>
                        <td>{{ $dialogModeLabels[$word->dialog_mode] ?? '' }}</td>
                        <td><input type="text" class="form-control" disabled value="{{ $word->replace_word }}"></td>
                        <td>
                            <form action="{{ route('panel.block-words.destroy', $word) }}" method="post">
                                @csrf
                                @method('delete')
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                    data-action="{{ route('panel.block-words.update', $word) }}"
                                    data-params="{{ $word->toJson() }}"
                                    data-bs-target="#blockWordModal">{{ __('FsLang::panel.button_edit') }}</button>
                                <button type="submit" class="btn btn-link link-danger ms-1 fresns-link fs-7 delete-button">{{ __('FsLang::panel.button_delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tr>
            </tbody>
        </table>
    </div>
    {{ $words->links() }}

    <!-- Modal -->
    <div class="modal fade" id="blockWordModal" tabindex="-1" aria-labelledby="blockWordModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.sidebar_block_words') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        @csrf
                        @method('post')
                        <div class="input-group mb-3">
                            <span class="input-group-text">{{ __('FsLang::panel.block_word') }}</span>
                            <input type="text" name="word" required class="form-control">
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">{{ __('FsLang::panel.replace_word') }}</span>
                            <input type="text" name="replace_word" class="form-control">
                        </div>
                        <div class="input-group mb-3">
                            <label class="input-group-text">{{ __('FsLang::panel.block_word_content_mode') }}</label>
                            <select class="form-select" aria-label="Default select example" name="content_mode">
                                @foreach ($contentModeLabels as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <label class="input-group-text">{{ __('FsLang::panel.block_word_user_mode') }}</label>
                            <select class="form-select" aria-label="Default select example" name="user_mode">
                                @foreach ($userModeLabels as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <label class="input-group-text">{{ __('FsLang::panel.block_word_dialog_mode') }}</label>
                            <select class="form-select" aria-label="Default select example" name="dialog_mode">
                                @foreach ($dialogModeLabels as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
