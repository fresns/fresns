@include('fresns.header')

    <main>
        <div class="container-lg p-0 p-lg-3">
            <div class="bg-white shadow-sm mt-4 mt-lg-2 p-3 p-lg-5">
                <div class="row">
                    <!--Console Setting-->
                    <div class="col-lg-5">
                        <h3>@lang('fresns.consoleTitle')</h3>
                        <p class="text-secondary">@lang('fresns.consoleIntro')</p>
                        <form>
                            <div class="input-group mb-3">
                                <span class="input-group-text">@lang('fresns.backendDomain')</span>
                                <input type="url" class="form-control border-end-0 backend-address" name="backend_url" placeholder="https://abc.com" value={{ $backend_url }}>
                                <span class="input-group-text bg-white border-start-0" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('fresns.backendDomainInfo')"><i class="bi bi-info-circle"></i></span>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text ">@lang('fresns.backendPath')</span>
                                <input type="text" class="form-control border-end-0 safe-entrance" name="admin_path" placeholder="admin" value={{ $admin_path }}>
                                <span class="input-group-text bg-white border-start-0" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('fresns.backendPathInfo')"><i class="bi bi-info-circle"></i></span>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text">@lang('fresns.consoleUrlName')</span>
                                <span class="form-control bg-white" id="copy_info" style="word-break:break-all;">{{$path}}</span>
                                <button class="btn btn-outline-secondary copy-btn" type="button" id="button-addon1">@lang('fresns.copyConsoleUrl')</button>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text">@lang('fresns.siteDomain')</span>
                                <input type="url" class="form-control border-end-0 site-url" name="site_url" placeholder="https://" value="{{ $site_url }}">
                                <span class="input-group-text bg-white border-start-0" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('fresns.siteDomainInfo')"><i class="bi bi-info-circle"></i></span>
                            </div>
                            <button id="submit" class="btn btn-primary">@lang('fresns.consoleSettingBtn')</button>
                        </form>
                        <textarea id="console_url" style="opacity: 0;width: 0;height:0;">{{ $path }}</textarea>
                    </div>

                    <!--Admin Setting-->
                    <div class="col-lg-1 mb-5"></div>
                    <div class="col-lg-5">
                        <h3>@lang('fresns.systemAdminTitle')</h3>
                        <p class="text-secondary">@lang('fresns.systemAdminIntro')</p>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-nowrap">
                                <thead>
                                    <tr class="table-info">
                                        <th scope="col">@lang('fresns.systemAdminUserId')</th>
                                        <th scope="col">@lang('fresns.systemAdminAccount')</th>
                                        <th scope="col">@lang('fresns.systemAdminOptions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($user_arr as $v)
                                    <tr>
                                        <td>{{ $v['uuid'] }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark"><i class="bi bi-envelope"></i> {{ $v['email_desc'] ?? "@lang('fresns.air')" }}</span>
                                            <span class="badge bg-light text-dark"><i class="bi bi-phone"></i> {{ $v['phone_desc'] ?? "@lang('fresns.air')" }}</span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-link btn-sm text-danger fresns-link delete" data-bs-toggle="modal" data-bs-target="#confirmDelete" data-uuid="{{ $v['uuid'] }}">@lang('fresns.deleteSystemAdmin')</button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newAdmin"><i class="bi bi-plus-circle-dotted"></i> @lang('fresns.addSystemAdmin')</button>
                    </div>
                    <!--End-->
                </div>
            </div>
        </div>
    </main>

    <!--Add Admin Modal-->
    <div class="modal fade" id="newAdmin" tabindex="-1" aria-labelledby="newAdmin" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('fresns.addSystemAdminTitle')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">@lang('fresns.addSystemAdminAccount')</span>
                            <input type="text" class="form-control account" placeholder="@lang('fresns.addSystemAdminAccountDesc')">
                            <button class="btn btn-outline-secondary" type="submit" id="folderInstall-button">@lang('fresns.addSystemAdminBtn')</button>
                        </div>
                        <div class="form-text"><i class="bi bi-info-circle"></i> @lang('fresns.addSystemAdminAccountInfo')</div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!--Delete Admin Modal-->
    <div class="modal fade" id="confirmDelete" tabindex="-1" aria-labelledby="confirmDelete" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('fresns.confirmDelete')?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>@lang('fresns.systemAdminUserId'): <span class="app_id">uid</span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-dismiss="modal">@lang('fresns.confirmDelete')</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('fresns.cancel')</button>
                </div>
            </div>
        </div>
    </div>

@include('fresns.footer')

    <script>
        //Console Setting
        $('.copy-btn').click(function() {
            var copy_info = document.getElementById('copy_info').innerText;
            var input = document.getElementById("console_url");
            input.value = copy_info;
            input.select();
            try {
                if (document.execCommand("Copy", "false", null)) {
                    alert("@lang('fresns.copyConsoleUrlSuccess')!");
                } else {
                    alert("@lang('fresns.copyConsoleUrlWarning')");
                }
            } catch (err) {
                alert("@lang('fresns.copyConsoleUrlWarning')");
            }
        });
        $('.safe-entrance').bind('input propertychange', function() {
            var entrance = $(this).val();
            var address = $('.backend-address').val();
            var text = address+'/fresns/' + entrance;
            $('#copy_info').text(text);
            $('#console_url').text(text);
        });
        $('.backend-address').bind('input propertychange', function() {
            var entrance = $(this).val();
            var address = $('.safe-entrance').val();
            var text = entrance+'/fresns/' + address;
            $('#copy_info').text(text);
            $('#console_url').text(text);
        });

        $("#submit").click(function() {
            var admin_path = $('.safe-entrance').val();
            var backend_url = $('.backend-address').val();
            var site_url = $('.site-url').val();
            $.ajax({
                async: false,
                type: "post",
                url: "/fresns/updateSetting",
                data: {
                    'admin_path': admin_path,
                    'backend_url': backend_url,
                    'site_url': site_url,
                },
                beforeSend: function(request) {
                    return request.setRequestHeader('X-CSRF-Token', "{{ csrf_token() }}");
                },
                success: function(data) {
                    if (data.code == 0) {
                        alert(data.message)
                        window.location.reload();
                    } else {
                        alert(data.message)
                    }
                }
            })
        });

        //Admin Setting
        $("#folderInstall-button").click(function() {
            var account = $('.account').val();
            $.ajax({
                async: false,
                type: "post",
                url: "/fresns/addAdmin",
                data: {
                    'account': account
                },
                beforeSend: function(request) {
                    return request.setRequestHeader('X-CSRF-Token', "{{ csrf_token() }}");
                },
                success: function(data) {
                    if (data.code == 0) {
                        //window.location.reload();
                    } else {
                        alert(data.message)
                    }
                }
            })
        });

        $('.delete').on('click', function() {
            var uuid = $(this).attr('data-uuid');
            $('#confirmDelete .app_id').text(uuid);
            $(".btn-danger").attr('data-uuid', uuid);
        })
        $(".btn-danger").click(function() {
            var uuid = $(this).attr('data-uuid');
            $.ajax({
                async: false,
                type: "post",
                url: "/fresns/delAdmin",
                data: {
                    'uuid': uuid
                },
                beforeSend: function(request) {
                    return request.setRequestHeader('X-CSRF-Token', "{{ csrf_token() }}");
                },
                success: function(data) {
                    if (data.code == 0) {
                        window.location.reload();
                    } else {
                        alert(data.message)
                    }
                }
            })
        });
</script>

</body>
</html>