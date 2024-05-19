@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--basic header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-9">
            <h3>{{ __('FsLang::panel.sidebar_client_basic') }}</h3>
            <p class="text-secondary"><i class="bi bi-laptop"></i> {{ __('FsLang::panel.sidebar_client_basic_intro') }}</p>
        </div>
        <div class="col-lg-3">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                {{-- <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a> --}}
            </div>
        </div>
    </div>

    <!--basic config-->
    <form action="{{ route('panel.client.basic.update') }}" method="post">
        @csrf
        @method('put')

        <!--website_stat_code-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.website_stat_code') }}:</label>
            <div class="col-lg-5 pt-2"><textarea class="form-control" name="website_stat_code" rows="4">{{ $params['website_stat_code'] }}</textarea></div>
            <div class="col-lg-5 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.website_stat_code_desc') }}</div>
        </div>

        <!--website_stat_position-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.website_stat_position') }}:</label>
            <div class="col-lg-5 pt-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="website_stat_position" id="website_stat_position_head" value="head" @if($params['website_stat_position'] == 'head') checked @endif>
                    <label class="form-check-label" for="website_stat_position_head">&lt;head&gt;</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="website_stat_position" id="website_stat_position_body" value="body" @if($params['website_stat_position'] == 'body') checked @endif>
                    <label class="form-check-label" for="website_stat_position_body">&lt;body&gt;</label>
                </div>
            </div>
            <div class="col-lg-5 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.website_stat_position_desc') }}</div>
        </div>

        <!--appleAppSiteAssociation-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">Apple App Site Association:</label>
            <div class="col-lg-5 pt-2"><textarea class="form-control" name="appleAppSiteAssociation" rows="10">{{ $appleAppSiteAssociation }}</textarea></div>
        </div>

        <!--site_china_mode-->
        <div class="row">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.china_mode') }}:</label>
            <div class="col-lg-5 pt-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="site_china_mode" id="china_server_false" value=false data-bs-toggle="collapse" data-bs-target=".china_server_setting.show" aria-expanded="false" aria-controls="china_server_setting" {{ !$params['site_china_mode'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="china_server_false">{{ __('FsLang::panel.option_no') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="site_china_mode" id="china_server_true" value=true data-bs-toggle="collapse" data-bs-target=".china_server_setting:not(.show)" aria-expanded="false" aria-controls="china_server_setting" {{ $params['site_china_mode'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="china_server_true">{{ __('FsLang::panel.option_yes') }}</label>
                </div>
            </div>
            <div class="col-lg-5 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.china_mode_desc') }}</div>
        </div>
        <!--China Mode Config-->
        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end"></label>
            <div class="col-lg-10">
                <div class="collapse china_server_setting {{ $params['site_china_mode'] ? 'show' : '' }}">
                    <div class="card mt-1">
                        <div class="card-header">{{ __('FsLang::panel.china_mode_config') }}</div>
                        <div class="card-body">
                            <!--Config-->
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="china_icp_filing">{{ __('FsLang::panel.china_icp_filing') }}</label>
                                <input type="text" class="form-control" id="china_icp_filing" name="china_icp_filing" value="{{ $params['china_icp_filing'] }}">
                            </div>
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="china_icp_license">{{ __('FsLang::panel.china_icp_license') }}</label>
                                <input type="text" class="form-control" id="china_icp_license" name="china_icp_license" value="{{ $params['china_icp_license'] }}">
                            </div>
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="china_mps_filing">{{ __('FsLang::panel.china_mps_filing') }}</label>
                                <input type="text" class="form-control" id="china_mps_filing" name="china_mps_filing" value="{{ $params['china_mps_filing'] }}">
                            </div>
                            <div class="input-group mb-1">
                                <label class="input-group-text" for="china_broadcasting_license">{{ __('FsLang::panel.china_broadcasting_license') }}</label>
                                <input type="text" class="form-control" id="china_broadcasting_license" name="china_broadcasting_license" value="{{ $params['china_broadcasting_license'] }}">
                            </div>
                            <!--Config end-->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--button_save-->
        <div class="row my-3">
            <div class="col-lg-2"></div>
            <div class="col-lg-8">
                <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
            </div>
        </div>
    </form>
@endsection
