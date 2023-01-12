@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::dashboard.sidebar')
@endsection

@section('content')
    <!--header-->
    <div class="row mb-4">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_caches') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_caches_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-danger" href="{{ route('panel.cache.all.clear') }}"><i class="bi bi-trash3"></i> {{ __('FsLang::panel.button_clear_all_cache') }}</a>
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
        <!--tab-list-->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="config-tab" data-bs-toggle="tab" data-bs-target="#config" type="button" role="tab" aria-controls="config" aria-selected="true">{{ __('FsLang::panel.sidebar_caches_tab_config') }}</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="data-tab" data-bs-toggle="tab" data-bs-target="#data" type="button" role="tab" aria-controls="data" aria-selected="false">{{ __('FsLang::panel.sidebar_caches_tab_data') }}</button>
            </li>
        </ul>
    </div>

    <!--tab content-->
    <div class="tab-content" id="cacheTabContent">
        <!--config-->
        <div class="tab-pane fade row show active" id="config" role="tabpanel" aria-labelledby="config-tab">
            <form class="col-md-6" action="{{ route('panel.cache.select.clear') }}" method="post">
                @csrf
                <input type="hidden" name="type" value="config" />

                <div class="ms-lg-5">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="fresnsSystem" name="fresnsSystem">
                        <label class="form-check-label" for="fresnsSystem">{{ __('FsLang::panel.cache_fresns_system') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="fresnsConfig" name="fresnsConfig">
                        <label class="form-check-label" for="fresnsConfig">{{ __('FsLang::panel.cache_fresns_config') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="fresnsExtend" name="fresnsExtend">
                        <label class="form-check-label" for="fresnsExtend">{{ __('FsLang::panel.cache_fresns_extend') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="fresnsView" name="fresnsView">
                        <label class="form-check-label" for="fresnsView">{{ __('FsLang::panel.cache_fresns_view') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="fresnsRoute" name="fresnsRoute">
                        <label class="form-check-label" for="fresnsRoute">{{ __('FsLang::panel.cache_fresns_route') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="fresnsEvent" name="fresnsEvent">
                        <label class="form-check-label" for="fresnsEvent">{{ __('FsLang::panel.cache_fresns_event') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="fresnsSchedule" name="fresnsSchedule">
                        <label class="form-check-label" for="fresnsSchedule">{{ __('FsLang::panel.cache_fresns_schedule') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="frameworkConfig" name="frameworkConfig">
                        <label class="form-check-label" for="frameworkConfig">{{ __('FsLang::panel.cache_framework_config') }}</label>
                    </div>

                    <button type="submit" class="btn btn-success mt-4">{{ __('FsLang::panel.button_clear_cache') }}</button>
                </div>
            </form>
        </div>

        <!--data-->
        <div class="tab-pane fade row" id="data" role="tabpanel" aria-labelledby="data-tab">
            <form class="col-md-6" action="{{ route('panel.cache.select.clear') }}" method="post">
                @csrf
                <input type="hidden" name="type" value="data" />

                <div class="ms-lg-5">
                    <div class="input-group mb-3">
                        <span class="input-group-text">{{ __('FsLang::panel.table_type') }}</span>
                        <select class="form-select" id="cacheType" name="cacheType">
                            <option value="user">{{ __('FsLang::panel.user') }}</option>
                            <option value="group">{{ __('FsLang::panel.group') }}</option>
                            <option value="hashtag">{{ __('FsLang::panel.hashtag') }}</option>
                            <option value="post">{{ __('FsLang::panel.post') }}</option>
                            <option value="comment">{{ __('FsLang::panel.comment') }}</option>
                            <option value="file">{{ __('FsLang::panel.file') }}</option>
                            <option value="extend">{{ __('FsLang::panel.extend') }}</option>
                        </select>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text">FsID</span>
                        <input type="text" class="form-control" id="cacheFsid" name="cacheFsid" required placeholder="FsID">
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text">{{ __('FsLang::panel.cache') }}</span>
                        <div class="form-control">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="fresnsModel" name="fresnsModel" checked>
                                <label class="form-check-label" for="fresnsModel">{{ __('FsLang::panel.cache_fresns_model') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="fresnsInteraction" name="fresnsInteraction" checked>
                                <label class="form-check-label" for="fresnsInteraction">{{ __('FsLang::panel.cache_fresns_interaction') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="fresnsApiData" name="fresnsApiData" checked>
                                <label class="form-check-label" for="fresnsApiData">{{ __('FsLang::panel.cache_fresns_api_data') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="fresnsSeo" name="fresnsSeo">
                                <label class="form-check-label" for="fresnsSeo">{{ __('FsLang::panel.cache_fresns_seo') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="fresnsExtension" name="fresnsExtension">
                                <label class="form-check-label" for="fresnsExtension">{{ __('FsLang::panel.cache_fresns_extension') }}</label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success mt-4">{{ __('FsLang::panel.button_clear_cache') }}</button>
                </div>
            </form>
        </div>

    </div>
@endsection
