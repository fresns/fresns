@include('fresns.header')

    <main>
        <div class="container-lg p-0 p-lg-3">
            <div class="bg-white shadow-sm mt-4 mt-lg-2 p-3 p-lg-5">
                <h3>@lang('fresns.appsTitle')</h3>
                <p class="text-secondary mb-4">@lang('fresns.appsIntro')</p>

                <div class="row">
                    <!--List-->
                    @if(empty($location))
                        <div class="p-5 text-center">
                            <i class="bi bi-view-list"></i> @lang('fresns.appsNull')
                        </div>
                    @else
                        @foreach ($location as $item)
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
                                            @if ($item['setting_path'])
                                                <a href="/fresns/iframe?url={{$item['setting_path']}}?lang={{$lang}}" class="btn btn-primary btn-sm"  title="@lang('fresns.settingInfo')" data-bs-toggle="tooltip" data-bs-placement="top">@lang('fresns.setting')</a>
                                            @endif
                                        @else
                                            <button type="button" class="btn btn-outline-secondary btn-sm btn_enable2" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('fresns.activateInfo')" data_id="{{$item['id']}}">@lang('fresns.activate')</button>
                                            <button type="button" class="btn btn-outline-danger btn-sm uninstallUnikey" data-bs-toggle="modal" data-bs-target="#confirmUninstall" data-name="{{ $item['name'] }}" unikey="{{$item['unikey']}}" title="@lang('fresns.uninstallInfo')">@lang('fresns.uninstall')</button>
                                        @endif
                                        </div>
                                    </div>
                                    <div class="card-footer fs-8">@lang('fresns.author'): <a href="{{$item['author_link']}}" target="_blank" class="link-info fresns-link">{{$item['author']}}</a></div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    <!--List End-->
                </div>
            </div>
        </div>
    </main>

    <!--Uninstall Modal-->
    <div class="modal fade" id="confirmUninstall" tabindex="-1" aria-labelledby="confirmUninstall" aria-hidden="true">
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