@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::systems.sidebar')
@endsection

@section('content')
    <!--general header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_general') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_general_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <!--general config-->
    <form action="{{ route('panel.general.update') }}" method="post">
        @csrf
        @method('put')
        <div class="row mb-4">
            <label for="site_url" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.site_url') }}:</label>
            <div class="col-lg-6"><input type="url" class="form-control" name="site_domain" value="{{ $params['site_domain'] }}" id="site_url" placeholder="https://"></div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.site_url_desc') }}</div>
        </div>

        <div class="row mb-4">
            <label for="site_name" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.site_name') }}:</label>
            <div class="col-lg-6">
                <button type="button" class="btn btn-outline-secondary btn-modal w-100 text-start" data-bs-toggle="modal" data-bs-target="#siteNameModal">{{ $defaultLangParams['site_name'] }}</button>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.site_name_desc') }}</div>
        </div>

        <div class="row mb-4">
            <label for="site_desc" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.site_desc') }}:</label>
            <div class="col-lg-6">
                <button type="button" class="btn btn-outline-secondary btn-modal w-100 text-start" data-bs-toggle="modal" data-bs-target="#siteDescModal">{{ $defaultLangParams['site_desc'] }}</button>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.site_desc_desc') }}</div>
        </div>

        <div class="row mb-4">
            <label for="site_img" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.site_logo') }}:</label>
            <div class="col-lg-6">
                <div class="input-group mb-1">
                    <label class="input-group-text font-monospace" for="ICON">ICON</label>
                    <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if ($params['site_icon'])
                            {{ __('FsLang::panel.button_image_upload') }}
                        @else
                            {{ __('FsLang::panel.button_image_input') }}
                        @endif
                    </button>
                    <ul class="dropdown-menu selectImageType">
                        <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                    </ul>
                    <input type="file" class="form-control inputFile" name="site_icon_file" @if ($params['site_icon']) style="display:none;" @endif>
                    <input type="url" class="form-control inputUrl" name="site_icon" value="{{ $params['site_icon'] }}" @if (!$params['site_icon']) style="display:none;" @endif>
                    <button class="btn btn-outline-secondary preview-image" type="button">{{ __('FsLang::panel.button_image_view') }}</button>
                </div>
                <div class="input-group">
                    <label class="input-group-text font-monospace" for="LOGO">LOGO</label>
                    <button class="btn btn-outline-secondary dropdown-toggle showSelectTypeName" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if ($params['site_logo'])
                            {{ __('FsLang::panel.button_image_upload') }}
                        @else
                            {{ __('FsLang::panel.button_image_input') }}
                        @endif
                    </button>
                    <ul class="dropdown-menu selectImageType">
                        <li data-name="inputFile"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_upload') }}</a></li>
                        <li data-name="inputUrl"><a class="dropdown-item" href="#">{{ __('FsLang::panel.button_image_input') }}</a></li>
                    </ul>
                    <input type="file" class="form-control inputFile" name="site_logo_file" @if ($params['site_logo']) style="display:none;" @endif>
                    <input type="url" class="form-control inputUrl" name="site_logo" value="{{ $params['site_logo'] }}" @if (!$params['site_logo']) style="display:none;" @endif>
                    <button class="btn btn-outline-secondary preview-image" type="button">{{ __('FsLang::panel.button_image_view') }}</button>
                </div>
            </div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.site_logo_desc') }}</div>
        </div>

        <div class="row mb-4">
            <label for="site_copyright" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.site_copyright') }}:</label>
            <div class="col-lg-6">
                <div class="input-group">
                    <span class="input-group-text">&copy;</span>
                    <input type="text" class="form-control" id="site_copyright" placeholder="Fresns" name="site_copyright" value="{{ $params['site_copyright'] }}">
                    <input type="text" class="form-control" id="site_copyright_years" placeholder="2020-2022" name="site_copyright_years" value="{{ $params['site_copyright_years'] }}">
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <label for="site_copyright_years" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.site_timezone') }}:</label>
            <div class="col-lg-6">
                <select class="form-select" name="default_timezone">
                    @foreach ($params['utc'] as $utcItem)
                        <option value="{{ $utcItem['value'] }}" {{ $params['default_timezone'] == $utcItem['value'] ? 'selected' : '' }}> {{ $utcItem['name'] }} </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-4">
            <label for="site_mode" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.site_mode') }}:</label>
            <div class="col-lg-6 pt-2" id="accordionSiteMode">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="site_mode" id="site_mode_public" value="public" data-bs-toggle="collapse" data-bs-target="#public_setting:not(.show)" aria-expanded="true" aria-controls="public_setting" {{ $params['site_mode'] == 'public' ? 'checked' : '' }}>
                    <label class="form-check-label" for="site_mode_public">{{ __('FsLang::panel.site_mode_public') }}</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="site_mode" id="site_mode_private" value="private" data-bs-toggle="collapse" data-bs-target="#private_setting:not(.show)" aria-expanded="false" aria-controls="private_setting" {{ $params['site_mode'] == 'private' ? 'checked' : '' }}>
                    <label class="form-check-label" for="site_mode_private">{{ __('FsLang::panel.site_mode_private') }}</label>
                </div>
                <!--public-->
                <div class="collapse {{ $params['site_mode'] == 'public' ? 'show' : '' }}" id="public_setting" aria-labelledby="site_mode_public" data-bs-parent="#accordionSiteMode">
                    <div class="card mt-2">
                        <div class="card-header text-success">{{ __('FsLang::panel.site_mode_public_desc') }}</div>
                        <div class="card-body">
                            <!--public config-->
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="register_close">{{ __('FsLang::panel.site_mode_public_register_status') }}</label>
                                <select class="form-select" id="register_close" name="site_public_close">
                                    <option value="false" {{ $params['site_public_close'] == 'false' ? 'selected' : '' }}>{{ __('FsLang::panel.option_open') }}</option>
                                    <option value="true" {{ $params['site_public_close'] == 'true' ? 'selected' : '' }}>{{ __('FsLang::panel.option_close') }}</option>
                                </select>
                            </div>
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="register_plugin">{{ __('FsLang::panel.site_mode_public_register_service') }}</label>
                                <select class="form-select" id="register_plugin" name="site_public_service">
                                    <option value="" selected>{{ __('FsLang::panel.option_default') }}</option>
                                    @foreach ($registerPlugins as $plugin)
                                        <option value="{{ $plugin->unikey }}" {{ $params['site_public_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="input-group mb-1">
                                <label class="input-group-text" for="site_private_end">{{ __('FsLang::panel.site_mode_public_register_type') }}</label>
                                <div class="form-control bg-white">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="account_email" name="site_register_email" value="true" {{ $params['site_register_email'] == 'true' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="account_email">{{ __('FsLang::panel.site_mode_public_register_type_email') }}</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="account_phone" name="site_register_phone" value="true" {{ $params['site_register_phone'] == 'true' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="account_phone">{{ __('FsLang::panel.site_mode_public_register_type_phone') }}</label>
                                    </div>
                                </div>
                            </div>
                            <!--public config end-->
                        </div>
                    </div>
                </div>
                <!--private-->
                <div class="collapse {{ $params['site_mode'] == 'private' ? 'show' : '' }}" id="private_setting" aria-labelledby="site_mode_private" data-bs-parent="#accordionSiteMode">
                    <div class="card mt-2">
                        <div class="card-header text-danger">{{ __('FsLang::panel.site_mode_private_desc') }}</div>
                        <div class="card-body">
                            <!--private config-->
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="site_private_type">{{ __('FsLang::panel.site_mode_private_join_status') }}</label>
                                <select class="form-select" id="site_private_type" name="site_private_close">
                                    <option value="false" {{ $params['site_private_close'] == 'false' ? 'selected' : '' }}>{{ __('FsLang::panel.option_close') }}</option>
                                    <option value="true" {{ $params['site_private_close'] == 'true' ? 'selected' : '' }}>{{ __('FsLang::panel.option_open') }}</option>
                                </select>
                            </div>
                            <div class="input-group mb-3">
                                <label class="input-group-text" for="site_private_plugin">{{ __('FsLang::panel.site_mode_private_join_service') }}</label>
                                <select class="form-select" id="site_private_plugin" name="site_private_service">
                                    <option value="" selected>🚫 {{ __('FsLang::panel.option_deactivate') }}</option>
                                    @foreach ($joinPlugins as $plugin)
                                        <option value="{{ $plugin->unikey }}" {{ $params['site_private_service'] == $plugin->unikey ? 'selected' : '' }}>{{ $plugin->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="input-group mb-1">
                                <label class="input-group-text" for="site_private_end">{{ __('FsLang::panel.site_mode_private_content_policy') }}</label>
                                <select class="form-select" id="site_private_end" name="site_private_end">
                                    <option value="1" {{ $params['site_private_end'] == 1 ? 'selected' : '' }}>{{ __('FsLang::panel.site_mode_private_content_policy_1') }}</option>
                                    <option value="2" {{ $params['site_private_end'] == 2 ? 'selected' : '' }}>{{ __('FsLang::panel.site_mode_private_content_policy_2') }}</option>
                                </select>
                            </div>
                            <!--private config end-->
                        </div>
                    </div>
                </div>
                <!--mode end-->
            </div>
        </div>

        <div class="row mb-4">
            <label for="site_email" class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.site_email') }}:</label>
            <div class="col-lg-6"><input type="email" class="form-control" id="site_email" name="site_email" value="{{ $params['site_email'] }}" placeholder="support@fresns.org"></div>
            <div class="col-lg-4 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.site_email_desc') }}</div>
        </div>

        <!--button_save-->
        <div class="row my-3">
            <div class="col-lg-2"></div>
            <div class="col-lg-6">
                <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
            </div>
        </div>
    </form>
    <!--site form end-->

    <!-- Language Modal -->
    <div class="modal fade" id="siteNameModal" tabindex="-1" aria-labelledby="siteNameModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_setting') }}: {{ __('FsLang::panel.site_name') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('panel.languages.batch.update', ['itemKey' => 'site_name']) }}" method="post">
                        @csrf
                        @method('put')
                        <input type="hidden" name="update_config" value="site_name">
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
                                                {{ $lang['langName'] }} @if ($lang['areaCode'])
                                                ({{ optional($areaCodes->where('code', $lang['areaCode'])->first())['localName'] }})
                                                @endif
                                            </td>
                                            <td><input type="text" name="languages[{{ $lang['langTag'] }}]" class="form-control" value="{{ $langParams['site_name'][$lang['langTag']] ?? '' }}"></td>
                                        </tr>
                                    @endforeach
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

    <!-- Language Modal -->
    <div class="modal fade" id="siteDescModal" tabindex="-1" aria-labelledby="siteDescModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('FsLang::panel.button_setting') }}: {{ __('FsLang::panel.site_desc') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('panel.languages.batch.update', ['itemKey' => 'site_desc']) }}" method="post">
                        @csrf
                        @method('put')
                        <input type="hidden" name="update_config" value="site_desc">
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
                                                {{ $lang['langName'] }} @if ($lang['areaCode'])
                                                ({{ optional($areaCodes->where('code', $lang['areaCode'])->first())['localName'] }})
                                                @endif
                                            </td>
                                            <td>
                                                <textarea name="languages[{{ $lang['langTag'] }}]" class="form-control" rows="3">{{ $langParams['site_desc'][$lang['langTag']] ?? '' }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
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

    <!--imageZoom-->
    <div class="modal fade image-zoom" id="imageZoom" tabindex="-1" aria-labelledby="imageZoomLabel" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="position-relative image-box">
                <img class="img-fluid" src="">
            </div>
        </div>
    </div>
@endsection
