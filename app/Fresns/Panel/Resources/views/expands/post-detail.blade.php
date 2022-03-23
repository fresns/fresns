@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::expands.sidebar')
@endsection

@section('content')
    <!--post_detail header-->
    <div class="row mb-5 border-bottom">
        <div class="col-lg-9">
            <h3>{{ __('FsLang::panel.sidebar_expand_post_detail') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_expand_post_detail_intro') }}</p>
        </div>
        <div class="col-lg-3">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <!--post_detail config-->
    <form action="{{ route('panel.post-detail.update', 'post_detail_service') }}" method="post">
        @csrf
        @method('put')
        <div class="row mb-5">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.table_plugin') }}:</label>
            <div class="col-lg-6">
                <select class="form-select" id="post_editor" name="post_detail_service">
                    <option value="" selected>{{ __('FsLang::panel.option_default') }}</option>
                    @foreach ($pluginParams['expandData'] as $plugin)
                        <option value="{{ $plugin->unikey }}" {{ $params['post_detail_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!--button_save-->
        <div class="row mt-5">
            <div class="col-lg-2"></div>
            <div class="col-lg-6">
                <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
            </div>
        </div>
    </form>
@endsection
