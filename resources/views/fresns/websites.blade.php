@include('fresns.header')

    <main>
        <div class="container-lg p-0 p-lg-3">
            <div class="bg-white shadow-sm mt-4 mt-lg-2 p-3 p-lg-5">

                <!--Engines-->
                <h3>@lang('fresns.enginesTitle')</h3>
                <p class="text-secondary">@lang('fresns.enginesIntro')</p>
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap">
                        <thead>
                            <tr class="table-info">
                                <th scope="col">@lang('fresns.enginesTableName') <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('fresns.enginesTableNameInfo')"></i></th>
                                <th scope="col">@lang('fresns.enginesTableAuthor')</th>
                                <th scope="col">@lang('fresns.enginesTableTheme')</th>
                                <th scope="col" class="text-center">@lang('fresns.enginesTableOptions') <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('fresns.enginesTableOptionsInfo')"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(empty($websitePluginArr))
                                <tr>
                                    <td colspan="4" class="p-5 text-center"><i class="bi bi-view-list"></i> @lang('fresns.enginesNull')</td>
                                </tr>
                            @else
                                @foreach ($websitePluginArr as $item)
                                    <tr>
                                        <th scope="row" class="py-3">
                                            {{$item['name']}} <span class="badge bg-secondary plugin-version">{{$item['version']}}</span> 
                                            @if($item['is_upgrade'] == 1)
                                                <a href="/fresns/dashboard" unikey="{{$item['unikey']}}" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('fresns.newVersionInfo')"><span class="badge rounded-pill bg-danger plugin-version">@lang('fresns.newVersion')</span></a>
                                            @endif
                                        </th>
                                        <td><a href="{{$item['author_link']}}" class="link-info fresns-link fs-7">{{$item['author']}}</a></td>
                                        <td>
                                            <span class="badge bg-light text-dark"><i class="bi bi-laptop"></i> 
                                                @if (empty($item['websitePcPlugin']))
                                                    @lang('fresns.enginesTableThemePcNull')
                                                @else
                                                    {{$item['websitePcPlugin']}}
                                                @endif
                                            </span>
                                            <span class="badge bg-light text-dark"><i class="bi bi-phone"></i>
                                                @if (empty($item['websiteMobilePlugin']))
                                                    @lang('fresns.enginesTableThemePcNull')
                                                @else
                                                    {{$item['websiteMobilePlugin']}}
                                                @endif
                                            </span>                                   
                                        </td>
                                        <td class="text-end">
                                            @if ($item['is_enable'] == 1)
                                                <button type="button" class="btn btn-outline-success btn-sm btn_enable1" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('fresns.deactivateInfo')" data_id="{{$item['id']}}">@lang('fresns.deactivate')</button> 
                                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#themeSetting" id="linkSubject" unikey="{{$item['unikey']}}" subectUnikeyPc="{{$item['websitePc']}}" subectUnikeyMobile="{{$item['websiteMobile']}}">@lang('fresns.enginesTableOptionsTheme')</button>
                                                @if ($item['setting_path'])
                                                    <a href="/fresns/iframe?url={{$item['setting_path']}}?lang={{$lang}}" class="btn btn-primary btn-sm"  title="@lang('fresns.settingInfo')" data-bs-toggle="tooltip" data-bs-placement="top">@lang('fresns.setting')</a>
                                                @endif
                                            @else
                                                <button type="button" class="btn btn-outline-secondary btn-sm btn_enable2" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('fresns.activateInfo')" data_id="{{$item['id']}}">@lang('fresns.activate')</button>
                                                <button type="button" class="btn btn-outline-danger btn-sm uninstallUnikey" data-bs-toggle="modal" data-bs-target="#confirmUninstall" data-name="{{$item['name']}}" unikey="{{$item['unikey']}}" title="@lang('fresns.uninstallInfo')">@lang('fresns.uninstall')</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <!--Themes-->
                <h3 class="mt-5">@lang('fresns.themesTitle')</h3>
                <p class="text-secondary">@lang('fresns.themesIntro')</p>
                <div class="row">
                    @if(empty($subjectPluginArr))
                        <div class="p-5 text-center">
                            <i class="bi bi-view-list"></i> @lang('fresns.themesNull')
                        </div>
                    @else
                        @foreach ($subjectPluginArr as $item)
                        <div class="col-sm-6 col-xl-3 mb-4">
                            <div class="card">
                                <div class="position-relative">
                                    <img src="/assets/{{$item['unikey']}}/fresns.png" class="card-img-top" alt="{{$item['name']}}">
                                    @if ($item['is_upgrade'] == 1)
                                        <div class="position-absolute top-0 start-100 translate-middle"><a href="/fresns/dashboard" unikey="{{$item['unikey']}}" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('fresns.newVersionInfo')"><span class="badge rounded-pill bg-danger">@lang('fresns.newVersion')</span></a></div>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <h5 class="text-nowrap overflow-hidden">{{$item['name']}} <span class="badge bg-secondary align-middle plugin-version">{{$item['version']}}</span></h5>
                                    <p class="card-text text-height">{{$item['description']}}</p>
                                    <div>
                                    @if ($item['is_enable'] == 1)
                                        <button type="button" class="btn btn-outline-success btn-sm btn_enable1" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('fresns.deactivateInfo')" data_id="{{$item['id']}}">@lang('fresns.deactivate')</button>
                                        @if ($item['setting_path'] == '')
                                            <a href="#" class="btn btn-primary btn-sm disabled">@lang('fresns.setting')</a>
                                        @else
                                            <a href="/fresns/iframe?url=/fresns/functions?theme={{$item['unikey']}}&lang={{$lang}}" class="btn btn-primary btn-sm"  title="@lang('fresns.settingInfo')" data-bs-toggle="tooltip" data-bs-placement="top">@lang('fresns.setting')</a>
                                        @endif
                                    @else
                                        <button type="button" class="btn btn-outline-secondary btn-sm btn_enable2" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('fresns.activateInfo')" data_id="{{$item['id']}}">@lang('fresns.activate')</button>
                                        <button type="button" class="btn btn-outline-danger btn-sm uninstallUnikey" data-bs-toggle="modal" data-bs-target="#confirmUninstall" data-name="{{$item['name']}}" unikey="{{$item['unikey']}}" title="@lang('fresns.uninstallInfo')">@lang('fresns.uninstall')</button>
                                    @endif
                                    </div>
                                </div>
                                <div class="card-footer fs-8">@lang('fresns.author'): <a href="{{$item['author_link']}}" target="_blank" class="link-info fresns-link">{{$item['author']}}</a></div>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
                <!--End-->
            </div>
        </div>
    </main>

    <!--Engine Themes Setting Modal-->
    <div class="modal fade" id="themeSetting" tabindex="-1" aria-labelledby="themeSetting" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('fresns.engineThemeTitle')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <input type="hidden" id = 'updateWebsite' value="">
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-floating mb-3">
                            <select class="form-select subectUnikeyPc" id="PCTheme" aria-label="Floating label select example">
                                <option value="">@lang('fresns.engineThemeNoOption')</option>
                                @foreach ($subjectPluginArr as $item)
                                    <option value="{{$item['unikey']}}">{{$item['name']}}</option>
                                @endforeach
                            </select>
                            <label for="PCtheme"><i class="bi bi-laptop"></i> @lang('fresns.engineThemePc')</label>
                        </div>
                        <div class="form-floating mb-4">
                            <select class="form-select subectUnikeyMobile" id="mobileTheme" aria-label="Floating label select example">
                                <option value="">@lang('fresns.engineThemeNoOption')</option>
                                @foreach ($subjectPluginArr as $item)
                                    <option value="{{$item['unikey']}}">{{$item['name']}}</option>
                                @endforeach
                            </select>
                            <label for="mobileTheme"><i class="bi bi-phone"></i> @lang('fresns.engineThemeMobile')</label>
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn btn-primary updateSubject">@lang('fresns.consoleSettingBtn')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!--Uninstall Modal-->
    <div class="modal fade" id="confirmUninstall" tabindex="-1" aria-labelledby="confirmUninstall" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Name</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-check">
                            <input class="form-check-input" name="clear_plugin_data" type="checkbox" id="is-delete-data">
                            <label class="form-check-label" for="is-delete-data">@lang('fresns.uninstallOption')</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" id="btn-danger-delete" class="btn btn-danger" data-bs-toggle="modal" data-bs-dismiss="modal">@lang('fresns.confirmUninstall')</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('fresns.cancel')</button>
                </div>
            </div>
        </div>
    </div>

@include('fresns.footer')

</body>
</html>