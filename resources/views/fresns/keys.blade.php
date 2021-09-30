@include('fresns.header')

    <main>
        <div class="container-lg p-0 p-lg-3">
            <div class="bg-white shadow-sm mt-4 mt-lg-2 p-3 p-lg-5">
                <div class="row mb-2">
                    <div class="col-7">
                        <h3>@lang('fresns.keysTitle')</h3>
                        <p class="text-secondary">@lang('fresns.keysIntro')</p>
                    </div>
                    <div class="col-5 text-end">
                        <button class="btn btn-primary mt-2" type="button" data-bs-toggle="modal" data-bs-target="#createKey"><i class="bi bi-plus-circle-dotted"></i> @lang('fresns.addKey')</button>
                    </div>
                </div>
                <!--Key List-->
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap">
                        <thead>
                            <tr class="table-info">
                                <th scope="col">@lang('fresns.keysTablePlatform')</th>
                                <th scope="col">@lang('fresns.keysTableName')</th>
                                <th scope="col">@lang('fresns.keysTableAppId')</th>
                                <th scope="col">@lang('fresns.keysTableAppSecret')</th>
                                <th scope="col">@lang('fresns.keysTableType')</th>
                                <th scope="col">@lang('fresns.keysTableEnableStatus')</th>
                                <th scope="col">@lang('fresns.keysTableOptions')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(empty($location))
                                <tr>
                                    <td colspan="7" class="p-5 text-center"><i class="bi bi-view-list"></i> @lang('fresns.keysNull')</td>
                                </tr>
                            @else
                                @foreach ($location as $item)
                                <tr>
                                    <th scope="row" class="py-3">{{ $item['platformName'] }}</th>
                                    <td class="key_name">{{ $item['name'] }}</td>
                                    <td>{{ $item['app_id'] }}</td>
                                    <td>{{ $item['app_secret'] }}</td>
                                    <td class="key_type">
                                        {{ $item['typeName'] }}
                                        @if ($item['type'] == 2)
                                            <span class="badge bg-light text-dark key_plugin">{{ $item['plugin_unikey'] }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($item['is_enable'] == 1)
                                            <i class="bi bi-check-lg text-success"></i>
                                        @else
                                            <i class="bi bi-dash-lg text-secondary"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-success btn-sm update_data" data-bs-toggle="modal" data-bs-target="#editKey" data_id="{{$item['id']}}" data_platform="{{$item['platform_id']}}" data_name="{{$item['name']}}" data_type="{{$item['type']}}" data_plugin="{{$item['plugin_unikey']}}" data_status="{{$item['is_enable']}}">@lang('fresns.keysTableOptionEdit')</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm resetKey" data-bs-toggle="modal" data-bs-target="#confirmReset" data_name="{{$item['name']}}" data_app="{{$item['app_id']}}" data-id="{{$item['id']}}">@lang('fresns.keysTableOptionReset')</button>
                                        <button type="button" class="btn btn-link text-danger fs-7 fresns-link deleteKey" data-bs-toggle="modal" data-bs-target="#confirmDelete" data_name="{{$item['name']}}" data_app="{{$item['app_id']}}" data-id="{{$item['id']}}">@lang('fresns.keysTableOptionDelete')</button>
                                    </td>
                                </tr>
                                @endforeach
                        </tbody>
                        @endif
                    </table>
                </div>
                <!--Key List End-->
            </div>
        </div>
    </main>

    <!--Create Modal-->
    <div class="modal fade" id="createKey" tabindex="-1" aria-labelledby="createKeyLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createKeyLabel">@lang('fresns.addKeyTitle')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="input-group mb-3">
                            <span class="input-group-text">@lang('fresns.keyFormPlatform')</span>
                            <select class="form-select" id="key_platform">
                                <option selected disabled>@lang('fresns.keyFormPlatformChooseOption')</option>
                                @foreach ($platform as $item)
                                    <option value="{{$item['id']}}">{{$item['name']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">@lang('fresns.keyFormName')</span>
                            <input type="text" class="form-control keyName" id="key_name">
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">@lang('fresns.keyFormType')</span>
                            <div class="form-control bg-white keyType">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="key_type" id="fresns_key" value="1" data-bs-toggle="collapse" data-bs-target="#key_plugin_setting.show" aria-expanded="false" aria-controls="key_plugin_setting"  checked>
                                    <label class="form-check-label" for="fresns_key">@lang('fresns.keyTypeFresns')</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="key_type" id="plugin_key" value="2" data-bs-toggle="collapse" data-bs-target="#key_plugin_setting:not(.show)" aria-expanded="false" aria-controls="key_plugin_setting">
                                    <label class="form-check-label" for="plugin_key">@lang('fresns.keyTypePlugin')</label>
                                </div>
                            </div>
                        </div>
                        <!--keyType Select-->
                        <div class="input-group mb-3 collapse" id="key_plugin_setting">
                            <span class="input-group-text">@lang('fresns.keyFormTypePlugin')<i class="bi bi-info-circle ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('fresns.keyFormTypePluginInfo')"></i></span>
                            <select class="form-select" id="key_plugin">
                                <option selected disabled>@lang('fresns.keyFormTypePluginChooseOption')</option>
                                @foreach ($plugin as $item)
                                    <option value="{{$item['unikey']}}">{{$item['name']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <!--keyType Select End-->
                        <div class="input-group mb-3">
                            <span class="input-group-text">@lang('fresns.keyFormStatus')</span>
                            <div class="form-control bg-white">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="inlineRadioOptions" id="addKeyActivate" value="1" checked>
                                    <label class="form-check-label" for="addKeyActivate">@lang('fresns.keyStatusActivate')</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="inlineRadioOptions" id="addKeyDeactivate" value="0">
                                    <label class="form-check-label" for="addKeyDeactivate">@lang('fresns.keyStatusDeactivate')</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <div type="submit" class="btn btn-primary submitKey">@lang('fresns.addKeyBtn')</div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!--Edit Modal-->
    <div class="modal fade" id="editKey" tabindex="-1" aria-labelledby="editKeyLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editKeyLabel">@lang('fresns.editKeyTitle')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="input-group mb-3">
                            <span class="input-group-text">@lang('fresns.keyFormPlatform')</span>
                            <select class="form-select" id="key_platform_update">
                                <option selected disabled>@lang('fresns.keyFormPlatformChooseOption')</option>
                                @foreach ($platform as $item)
                                    <option value="{{$item['id']}}">{{$item['name']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">@lang('fresns.keyFormName')</span>
                            <input type="text" class="form-control keyName" id="key_name_update">
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">@lang('fresns.keyFormType')</span>
                            <div class="form-control bg-white keyTypeUpdate">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="key_type" id="fresns_key_edit" value="1" data-bs-toggle="collapse" data-bs-target="#edit_key_plugin_setting.show" aria-expanded="false" aria-controls="edit_key_plugin_setting">
                                    <label class="form-check-label" for="fresns_key_edit">@lang('fresns.keyTypeFresns')</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="key_type" id="plugin_key_edit" value="2" data-bs-toggle="collapse" data-bs-target="#edit_key_plugin_setting:not(.show)" aria-expanded="false" aria-controls="edit_key_plugin_setting">
                                    <label class="form-check-label" for="plugin_key_edit">@lang('fresns.keyTypePlugin')</label>
                                </div>
                            </div>
                        </div>
                        <!--keyType Select-->
                        <div class="input-group mb-3 collapse" id="edit_key_plugin_setting">
                            <span class="input-group-text">@lang('fresns.keyFormTypePlugin')<i class="bi bi-info-circle ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('fresns.keyFormTypePluginInfo')"></i></span>
                            <select class="form-select" id="key_plugin_update">
                                <option selected disabled>@lang('fresns.keyFormTypePluginChooseOption')</option>
                                @foreach ($plugin as $item)
                                    <option value="{{$item['unikey']}}">{{$item['name']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <!--keyType Select End-->
                        <div class="input-group mb-3">
                            <span class="input-group-text">@lang('fresns.keyFormStatus')</span>
                            <div class="form-control bg-white keyStatus">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="updInlineRadioOptions" id="editKeyActivate" value="1">
                                    <label class="form-check-label" for="editKeyActivate">@lang('fresns.keyStatusActivate')</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="updInlineRadioOptions" id="editKeyDeactivate" value="0">
                                    <label class="form-check-label" for="editKeyDeactivate">@lang('fresns.keyStatusDeactivate')</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <div type="submit" class="btn btn-primary updateKey">@lang('fresns.editKeyBtn')</div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!--Reset Modal-->
    <div class="modal fade" id="confirmReset" tabindex="-1" aria-labelledby="confirmReset" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Key Name</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>App ID: <span class="app_id">1234567890</span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary reset-key-btn" >@lang('fresns.keysTableOptionReset')</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('fresns.cancel')</button>
                </div>
            </div>
        </div>
    </div>

    <!--Delete Modal-->
    <div class="modal fade" id="confirmDelete" tabindex="-1" aria-labelledby="confirmDelete" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('fresns.confirmDelete')？</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>App ID: <span class="app_id">1234567890</span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger delete-btn" >@lang('fresns.confirmDelete')</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('fresns.cancel')</button>
                </div>
            </div>
        </div>
    </div>

@include('fresns.footer')

<script>
    //Create Key
    $(".submitKey").click(function(){
        var platformId = $("#key_platform").find("option:selected").val();
        var keyName = $(".keyName").val();
        var type = $(".keyType input:radio:checked").val();
        var plugin = $("#key_plugin").find("option:selected").val();
        var enAbleStatus = $('input[name="inlineRadioOptions"]:checked').val()
        $.ajax({
            async: false,
            type: "post",
            url: "/fresns/submitKey",
            data: {'platformId':platformId,'keyName':keyName,'type':type,'plugin':plugin,'enAbleStatus':enAbleStatus},
            beforeSend: function (request) {
                return request.setRequestHeader('X-CSRF-Token', "{{ csrf_token() }}");
            },
            success: function (data) {
                if(data.code == 0){
                    window.location.reload();
                }else{
                    alert(data.message)
                }
            }
        })
    });

    //Delete Key
    $('.deleteKey').on('click', function() {
        var id = $(this).attr('data-id');
        var name = $(this).attr('data_name');
        var app = $(this).attr('data_app');
        $('#confirmDelete .app_id').text(app);
        $('#confirmDelete .modal-title').text(name);
        var id = $(this).attr('data-id');
        $(".delete-btn").attr('data-id', id);
    });
    $(".delete-btn").click(function() {
        var id = $(this).attr('data-id');
        $.ajax({
            async: false,
            type: "post",
            url: "/fresns/delKey",
            data: {'data_id':id,is_enable:status},
            beforeSend: function (request) {
                return request.setRequestHeader('X-CSRF-Token', "{{ csrf_token() }}");
            },
            success: function (data) {
                window.location.reload();
            }
        })
    });

    //Edit Key
    $(".update_data").click(function(){
        var data_id = $(this).attr('data_id');
        var name = $(this).attr('data_name');
        var data_status = $(this).attr('data_status');
        if(!data_status){
            var data_status = 0;
        }
        var type = $(this).attr('data_type');
        var platform_id = $(this).attr('data_platform');
        var plugin_unikey = $(this).attr('data_plugin');
        $("#key_name_update").attr('data_id',data_id)
        $("#key_name_update").val(name);
        $("#key_platform_update").val(platform_id);
        $(".keyStatus input:radio[value="+data_status+"]").attr('checked',true);
        if(type == 2){
            $(".keyTypeUpdate input:radio").eq(1).prop('checked',true);
            $("#edit_key_plugin_setting").addClass('show');
            $("#key_plugin_update").val(plugin_unikey)
        }else{
            $(".keyTypeUpdate input:radio").eq(0).prop('checked',true);
            $("#edit_key_plugin_setting").removeClass('show');
            $("#key_plugin_update").val("")
        }
    })

    //Reset Secret
    $(".resetKey").click(function(){
        var id = $(this).attr('data-id');
        var name = $(this).attr('data_name');
        var app = $(this).attr('data_app');
        $('#confirmReset .app_id').text(app);
        $('#confirmReset .modal-title').text(name);
        $(".reset-key-btn").attr('data_app', app);
        $(".reset-key-btn").attr('data-id', id);
    })
    $(".reset-key-btn").click(function() {
        var data_id = $(this).attr('data-id');
        $.ajax({
            async: false,
            type: "post",
            url: "/fresns/resetKey",
            data: {'data_id':data_id},
            beforeSend: function (request) {
                return request.setRequestHeader('X-CSRF-Token', "{{ csrf_token() }}");
            },
            success: function (data) {
                window.location.reload();
            }
        })
    });

    //Submit Edit
    $(".updateKey").click(function(){
        var id = $("#key_name_update").attr('data_id');
        var platformId = $("#key_platform_update").find("option:selected").val();
        var keyName = $("#key_name_update").val();
        var type = $(".keyTypeUpdate input:radio:checked").val();
        var plugin = $("#key_plugin_update").find("option:selected").val();
        var enAbleStatus = $('.keyStatus input[name="updInlineRadioOptions"]:checked').val();
        $.ajax({
            async: false,
            type: "post",
            url: "/fresns/updateKey",
            data: {'id':id,'platformId':platformId,'keyName':keyName,'type':type,'plugin':plugin,'enAbleStatus':enAbleStatus},
            beforeSend: function (request) {
                return request.setRequestHeader('X-CSRF-Token', "{{ csrf_token() }}");
            },
            success: function (data) {
                if(data.code == 0){
                    window.location.reload();
                }else{
                    alert(data.message)
                }
            }
        });
    })
</script>

</body>
</html>