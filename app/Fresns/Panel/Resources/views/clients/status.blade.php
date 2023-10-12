@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--app header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_client_status') }}</h3>
            <p class="text-secondary"><i class="bi bi-power"></i> {{ __('FsLang::panel.sidebar_client_status_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                {{-- <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a> --}}
            </div>
        </div>
    </div>

    <!--status conifg-->
    <form action="{{ route('panel.client.status.update') }}" method="post">
        @csrf
        @method('put')

        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.table_status') }}:</label>
            <div class="col-lg-5">
                <div class="form-control bg-white">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="activate" id="activate_true" value="1" @if($statusJson['activate'] ?? true) checked @endif>
                        <label class="form-check-label" for="activate_true">{{ __('FsLang::panel.option_activate') }}</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="activate" id="activate_false" value="0" @if(!$statusJson['activate'] ?? true) checked @endif>
                        <label class="form-check-label" for="activate_false">{{ __('FsLang::panel.option_deactivate') }}</label>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.client_status_desc') }}</div>
        </div>

        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.option_deactivate').' - '.__('FsLang::panel.table_description') }}:</label>
            <div class="col-lg-9">
                <button type="button" class="btn btn-outline-secondary btn-modal w-100 text-start" data-bs-toggle="modal" data-bs-target="#descriptionModal">{{ $statusJson['deactivateDescribe']['default'] ?? 'Describe the reason for the deactivate' }}</button>
            </div>
            <!-- Describe Modal -->
            <div class="modal fade" id="descriptionModal" tabindex="-1" aria-labelledby="descriptionModal" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('FsLang::panel.button_setting') }}: {{ __('FsLang::panel.option_deactivate').' - '.__('FsLang::panel.table_description') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
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
                                                    {{ $lang['langName'] }}
                                                    @if ($lang['areaName'])
                                                        {{ '('.$lang['areaName'].')' }}
                                                    @endif
                                                </td>
                                                <td><textarea name="deactivateDescribe[{{ $lang['langTag'] }}]" class="form-control" rows="5">{{ $statusJson['deactivateDescribe'][$lang['langTag']] ?? '' }}</textarea></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!--button_confirm-->
                            <div class="text-center">
                                <button type="button" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_confirm') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Describe Modal end -->
        </div>

        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">Mobile:</label>
            <div class="col-lg-9 pt-2">
                {{-- iOS --}}
                <div class="alert alert-light py-2" role="alert">
                    <span class="badge bg-secondary mb-2">iOS</span>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Version</span>
                        <input type="text" class="form-control" name="client[mobile][ios][version]" value="{{ $client['mobile']['ios']['version'] ?? '' }}" placeholder="1.0.0">
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Describe</span>
                        <button type="button" class="btn btn-outline-secondary form-control btn-modal text-start" data-bs-toggle="modal" data-bs-target="#mobileIosDescribeModal">{{ Str::limit($client['mobile']['ios']['describe']['default'] ?? 'Describe this release', 80) }}</button>
                    </div>
                    <!-- Describe Modal -->
                    <div class="modal fade" id="mobileIosDescribeModal" tabindex="-1" aria-labelledby="mobileIosDescribeModal" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ __('FsLang::panel.button_setting') }}: Mobile iOS</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
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
                                                            {{ $lang['langName'] }}
                                                            @if ($lang['areaName'])
                                                                {{ '('.$lang['areaName'].')' }}
                                                            @endif
                                                        </td>
                                                        <td><textarea name="client[mobile][ios][describe][{{ $lang['langTag'] }}]" class="form-control" rows="5">{{ $client['mobile']['ios']['describe'][$lang['langTag']] ?? '' }}</textarea></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <!--button_confirm-->
                                    <div class="text-center">
                                        <button type="button" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_confirm') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Describe Modal end -->
                    <div class="input-group">
                        <span class="input-group-text w-25">App Store</span>
                        <input type="text" class="form-control" name="client[mobile][ios][appStore]" value="{{ $client['mobile']['ios']['appStore'] ?? '' }}" placeholder="https://">
                    </div>
                </div>
                {{-- Android --}}
                <div class="alert alert-light py-2" role="alert">
                    <span class="badge bg-secondary mb-2">Android</span>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Version</span>
                        <input type="text" class="form-control" name="client[mobile][android][version]" value="{{ $client['mobile']['android']['version'] ?? '' }}" placeholder="1.0.0">
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Describe</span>
                        <button type="button" class="btn btn-outline-secondary form-control btn-modal text-start" data-bs-toggle="modal" data-bs-target="#mobileAndroidDescribeModal">{{ Str::limit($client['mobile']['android']['describe']['default'] ?? 'Describe this release', 80) }}</button>
                    </div>
                    <!-- Describe Modal -->
                    <div class="modal fade" id="mobileAndroidDescribeModal" tabindex="-1" aria-labelledby="mobileAndroidDescribeModal" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ __('FsLang::panel.button_setting') }}: Mobile Android</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
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
                                                            {{ $lang['langName'] }}
                                                            @if ($lang['areaName'])
                                                                {{ '('.$lang['areaName'].')' }}
                                                            @endif
                                                        </td>
                                                        <td><textarea name="client[mobile][android][describe][{{ $lang['langTag'] }}]" class="form-control" rows="5">{{ $client['mobile']['android']['describe'][$lang['langTag']] ?? '' }}</textarea></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <!--button_confirm-->
                                    <div class="text-center">
                                        <button type="button" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_confirm') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Describe Modal end -->
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Google Play</span>
                        <input type="text" class="form-control" name="client[mobile][android][googlePlay]" value="{{ $client['mobile']['android']['googlePlay'] ?? '' }}" placeholder="https://">
                    </div>
                    <div class="input-group">
                        <span class="input-group-text w-25">APK</span>
                        <input type="text" class="form-control" name="client[mobile][android][downloads][apk]" value="{{ $client['mobile']['android']['downloads']['apk'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.apk</span>
                    </div>
                </div>
                {{-- end --}}
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">Tablet:</label>
            <div class="col-lg-9 pt-2">
                {{-- iOS --}}
                <div class="alert alert-light py-2" role="alert">
                    <span class="badge bg-secondary mb-2">iOS</span>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Version</span>
                        <input type="text" class="form-control" name="client[tablet][ios][version]" value="{{ $client['tablet']['ios']['version'] ?? '' }}" placeholder="1.0.0">
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Describe</span>
                        <button type="button" class="btn btn-outline-secondary form-control btn-modal text-start" data-bs-toggle="modal" data-bs-target="#tabletIosDescribeModal">{{ Str::limit($client['tablet']['ios']['describe']['default'] ?? 'Describe this release', 80) }}</button>
                    </div>
                    <!-- Describe Modal -->
                    <div class="modal fade" id="tabletIosDescribeModal" tabindex="-1" aria-labelledby="tabletIosDescribeModal" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ __('FsLang::panel.button_setting') }}: Tablet iOS</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
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
                                                            {{ $lang['langName'] }}
                                                            @if ($lang['areaName'])
                                                                {{ '('.$lang['areaName'].')' }}
                                                            @endif
                                                        </td>
                                                        <td><textarea name="client[tablet][ios][describe][{{ $lang['langTag'] }}]" class="form-control" rows="5">{{ $client['tablet']['ios']['describe'][$lang['langTag']] ?? '' }}</textarea></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <!--button_confirm-->
                                    <div class="text-center">
                                        <button type="button" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_confirm') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Describe Modal end -->
                    <div class="input-group">
                        <span class="input-group-text w-25">App Store</span>
                        <input type="text" class="form-control" name="client[tablet][ios][appStore]" value="{{ $client['tablet']['ios']['appStore'] ?? '' }}" placeholder="https://">
                    </div>
                </div>
                {{-- Android --}}
                <div class="alert alert-light py-2" role="alert">
                    <span class="badge bg-secondary mb-2">Android</span>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Version</span>
                        <input type="text" class="form-control" name="client[tablet][android][version]" value="{{ $client['tablet']['android']['version'] ?? '' }}" placeholder="1.0.0">
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Describe</span>
                        <button type="button" class="btn btn-outline-secondary form-control btn-modal text-start" data-bs-toggle="modal" data-bs-target="#tabletAndroidDescribeModal">{{ Str::limit($client['tablet']['android']['describe']['default'] ?? 'Describe this release', 80) }}</button>
                    </div>
                    <!-- Describe Modal -->
                    <div class="modal fade" id="tabletAndroidDescribeModal" tabindex="-1" aria-labelledby="tabletAndroidDescribeModal" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ __('FsLang::panel.button_setting') }}: Tablet Android</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
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
                                                            {{ $lang['langName'] }}
                                                            @if ($lang['areaName'])
                                                                {{ '('.$lang['areaName'].')' }}
                                                            @endif
                                                        </td>
                                                        <td><textarea name="client[tablet][android][describe][{{ $lang['langTag'] }}]" class="form-control" rows="5">{{ $client['tablet']['android']['describe'][$lang['langTag']] ?? '' }}</textarea></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <!--button_confirm-->
                                    <div class="text-center">
                                        <button type="button" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_confirm') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Describe Modal end -->
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Google Play</span>
                        <input type="text" class="form-control" name="client[tablet][android][googlePlay]" value="{{ $client['tablet']['android']['googlePlay'] ?? '' }}" placeholder="https://">
                    </div>
                    <div class="input-group">
                        <span class="input-group-text w-25">APK</span>
                        <input type="text" class="form-control" name="client[tablet][android][downloads][apk]" value="{{ $client['tablet']['android']['downloads']['apk'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.apk</span>
                    </div>
                </div>
                {{-- end --}}
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">Desktop:</label>
            <div class="col-lg-9 pt-2">
                {{-- macOS --}}
                <div class="alert alert-light py-2" role="alert">
                    <span class="badge bg-secondary mb-2">macOS</span>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Version</span>
                        <input type="text" class="form-control" name="client[desktop][macos][version]" value="{{ $client['desktop']['macos']['version'] ?? '' }}" placeholder="1.0.0">
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Describe</span>
                        <button type="button" class="btn btn-outline-secondary form-control btn-modal text-start" data-bs-toggle="modal" data-bs-target="#desktopMacosDescribeModal">{{ Str::limit($client['desktop']['macos']['describe']['default'] ?? 'Describe this release', 80) }}</button>
                    </div>
                    <!-- Describe Modal -->
                    <div class="modal fade" id="desktopMacosDescribeModal" tabindex="-1" aria-labelledby="desktopMacosDescribeModal" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ __('FsLang::panel.button_setting') }}: Desktop macOS</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
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
                                                            {{ $lang['langName'] }}
                                                            @if ($lang['areaName'])
                                                                {{ '('.$lang['areaName'].')' }}
                                                            @endif
                                                        </td>
                                                        <td><textarea name="client[desktop][macos][describe][{{ $lang['langTag'] }}]" class="form-control" rows="5">{{ $client['desktop']['macos']['describe'][$lang['langTag']] ?? '' }}</textarea></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <!--button_confirm-->
                                    <div class="text-center">
                                        <button type="button" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_confirm') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Describe Modal end -->
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">App Store</span>
                        <input type="text" class="form-control" name="client[desktop][macos][appStore]" value="{{ $client['desktop']['macos']['appStore'] ?? '' }}" placeholder="https://">
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Intel</span>
                        <input type="text" class="form-control" name="client[desktop][macos][downloads][intel]" value="{{ $client['desktop']['macos']['downloads']['intel'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.dmg</span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text w-25">Apple Silicon</span>
                        <input type="text" class="form-control" name="client[desktop][macos][downloads][appleSilicon]" value="{{ $client['desktop']['macos']['downloads']['appleSilicon'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.dmg</span>
                    </div>
                </div>
                {{-- Windows --}}
                <div class="alert alert-light py-2" role="alert">
                    <span class="badge bg-secondary mb-2">Windows</span>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Version</span>
                        <input type="text" class="form-control" name="client[desktop][windows][version]" value="{{ $client['desktop']['windows']['version'] ?? '' }}" placeholder="1.0.0">
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Describe</span>
                        <button type="button" class="btn btn-outline-secondary form-control btn-modal text-start" data-bs-toggle="modal" data-bs-target="#desktopWindowsDescribeModal">{{ Str::limit($client['desktop']['windows']['describe']['default'] ?? 'Describe this release', 80) }}</button>
                    </div>
                    <!-- Describe Modal -->
                    <div class="modal fade" id="desktopWindowsDescribeModal" tabindex="-1" aria-labelledby="desktopWindowsDescribeModal" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ __('FsLang::panel.button_setting') }}: Desktop Windows</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
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
                                                            {{ $lang['langName'] }}
                                                            @if ($lang['areaName'])
                                                                {{ '('.$lang['areaName'].')' }}
                                                            @endif
                                                        </td>
                                                        <td><textarea name="client[desktop][windows][describe][{{ $lang['langTag'] }}]" class="form-control" rows="5">{{ $client['desktop']['windows']['describe'][$lang['langTag']] ?? '' }}</textarea></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <!--button_confirm-->
                                    <div class="text-center">
                                        <button type="button" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_confirm') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Describe Modal end -->
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">x86</span>
                        <input type="text" class="form-control" name="client[desktop][windows][downloads][x86]" value="{{ $client['desktop']['windows']['downloads']['x86'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.exe</span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text w-25">x64</span>
                        <input type="text" class="form-control" name="client[desktop][windows][downloads][x64]" value="{{ $client['desktop']['windows']['downloads']['x64'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.exe</span>
                    </div>
                </div>
                {{-- Linux --}}
                <div class="alert alert-light py-2" role="alert">
                    <span class="badge bg-secondary mb-2">Linux</span>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Version</span>
                        <input type="text" class="form-control" name="client[desktop][linux][version]" value="{{ $client['desktop']['linux']['version'] ?? '' }}" placeholder="1.0.0">
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Describe</span>
                        <button type="button" class="btn btn-outline-secondary form-control btn-modal text-start" data-bs-toggle="modal" data-bs-target="#desktopLinuxDescribeModal">{{ Str::limit($client['desktop']['linux']['describe']['default'] ?? 'Describe this release', 80) }}</button>
                    </div>
                    <!-- Describe Modal -->
                    <div class="modal fade" id="desktopLinuxDescribeModal" tabindex="-1" aria-labelledby="desktopLinuxDescribeModal" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ __('FsLang::panel.button_setting') }}: Desktop Linux</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
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
                                                            {{ $lang['langName'] }}
                                                            @if ($lang['areaName'])
                                                                {{ '('.$lang['areaName'].')' }}
                                                            @endif
                                                        </td>
                                                        <td><textarea name="client[desktop][linux][describe][{{ $lang['langTag'] }}]" class="form-control" rows="5">{{ $client['desktop']['linux']['describe'][$lang['langTag']] ?? '' }}</textarea></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <!--button_confirm-->
                                    <div class="text-center">
                                        <button type="button" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_confirm') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Describe Modal end -->
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">X86 deb</span>
                        <input type="text" class="form-control" name="client[desktop][linux][downloads][x86Deb]" value="{{ $client['desktop']['linux']['downloads']['x86Deb'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.deb</span>
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">X86 rpm</span>
                        <input type="text" class="form-control" name="client[desktop][linux][downloads][x86Rpm]" value="{{ $client['desktop']['linux']['downloads']['x86Rpm'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.rpm</span>
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">X86 AppImage</span>
                        <input type="text" class="form-control" name="client[desktop][linux][downloads][x86AppImage]" value="{{ $client['desktop']['linux']['downloads']['x86AppImage'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.AppImage</span>
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">ARM deb</span>
                        <input type="text" class="form-control" name="client[desktop][linux][downloads][armDeb]" value="{{ $client['desktop']['linux']['downloads']['armDeb'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.deb</span>
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">ARM rpm</span>
                        <input type="text" class="form-control" name="client[desktop][linux][downloads][armRpm]" value="{{ $client['desktop']['linux']['downloads']['armRpm'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.rpm</span>
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">ARM AppImage</span>
                        <input type="text" class="form-control" name="client[desktop][linux][downloads][armAppImage]" value="{{ $client['desktop']['linux']['downloads']['armAppImage'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.AppImage</span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text w-25">MIPS deb</span>
                        <input type="text" class="form-control" name="client[desktop][linux][downloads][mipsDeb]" value="{{ $client['desktop']['linux']['downloads']['mipsDeb'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.deb</span>
                    </div>
                </div>
                {{-- end --}}
            </div>
        </div>

        <!--button_save-->
        <div class="row my-4">
            <div class="col-lg-2"></div>
            <div class="col-lg-8">
                <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
            </div>
        </div>
    </form>
@endsection
