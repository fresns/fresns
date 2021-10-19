//install extensions
$(".installLocal").click(function () {
    var dirName = $(".installDirName").val();
    var isAdd = true;
    $.ajax({
        async: false,
        type: "post",
        url: "/fresns/localInstall",
        data: { 'dirName': dirName },
        beforeSend: function (request) {
            return request.setRequestHeader('X-CSRF-Token', "{{ csrf_token() }}");
        },
        success: function (data) {
            if (data.code == 0) {
                $('#localInstallActionModal').addClass('show');
                $('#localInstallActionModal').css({
                    'display': 'block',
                });
                setTimeout(function () {
                    $('.step1').removeClass("spinner-border spinner-border-sm")
                    $('.step1').addClass("bi bi-check-lg text-success")
                    $('.step2').removeClass("bi bi-hourglass text-secondary")
                    $('.step2').addClass("spinner-border spinner-border-sm")
                }, 300)
                setTimeout(function () {
                    $('.step2').removeClass("spinner-border spinner-border-sm")
                    $('.step2').addClass("bi bi-check-lg text-success")
                    $('.step3').removeClass("bi bi-hourglass text-secondary")
                    $('.step3').addClass("spinner-border spinner-border-sm")
                }, 600)
                setTimeout(function () {
                    $('.step3').removeClass("spinner-border spinner-border-sm")
                    $('.step3').addClass("bi bi-check-lg text-success")
                    $('.step4').removeClass("bi bi-hourglass text-secondary")
                    $('.step4').addClass("spinner-border spinner-border-sm")
                }, 900)
                setTimeout(function () {
                    $('.step4').removeClass("spinner-border spinner-border-sm")
                    $('.step4').addClass("bi bi-check-lg text-success")
                    $('.step5').removeClass("bi bi-hourglass text-secondary")
                    $('.step5').addClass("spinner-border spinner-border-sm")
                }, 1200)
                setTimeout(function () {
                    $('.step5').removeClass("spinner-border spinner-border-sm")
                    $('.step5').addClass("bi bi-check-lg text-success")
                }, 1500)
                setTimeout(function () {
                    window.location.reload();
                }, 1800)
            } else {
                alert(data.message);
            }
        }
    })
});

//update extensions
$("#updateExtensions").click(function () {
    var unikey = $(this).attr('data_unikey');
    // var downloadUrl = $(this).attr('data_download_url');
    var localVision = $(this).attr('data_local_vision');
    var remoteVisionInt = $(this).attr('data_new_vision_int');
    var remoteVision = $(this).attr('data_new_vision');
    var dirName = unikey;
    $.ajax({
        async: false,
        type: "post",
        url: "/fresns/localInstall",
        data: { 'dirName': unikey },
        beforeSend: function (request) {
            return request.setRequestHeader('X-CSRF-Token', "{{ csrf_token() }}");
        },
        success: function (data) {
            if (data.code == 0) {
                window.location.reload();
            } else {
                alert(data.message);
            }
        }
    })
});

//Engine Themes Setting
$("#linkSubject").click(function () {
    var unikey = $(this).attr('unikey');
    var subectUnikeyPc = $(this).attr('subectUnikeyPc');
    var subectUnikeyMobile = $(this).attr('subectUnikeyMobile');
    $("#updateWebsite").val(unikey);
    $(".subectUnikeyPc").val(subectUnikeyPc);
    $(".subectUnikeyMobile").val(subectUnikeyMobile);
});
$(".updateSubject").click(function () {
    var websiteUnikey = $("#updateWebsite").val();
    if (!websiteUnikey) {
        alert("Unknown");
        return;
    }
    var pluginPc = $(".subectUnikeyPc").find("option:selected").val();
    var pluginMobile = $(".subectUnikeyMobile").find("option:selected").val();
    $.ajax({
        async: false,
        type: "post",
        url: "/fresns/websiteLinkSubject",
        data: { 'websiteUnikey': websiteUnikey, 'subjectUnikeyPc': pluginPc, 'subjectUnikeyMobile': pluginMobile },
        beforeSend: function (request) {
            return request.setRequestHeader('X-CSRF-Token', "{{ csrf_token() }}");
        },
        success: function (data) {
            if (data.code == 0) {
                window.location.reload();
            } else {
                alert(data.message)
            }
        }
    })
});

//Deactivate
$(".btn_enable1").click(function () {
    var id = $(this).attr('data_id');
    $.ajax({
        async: false,
        type: "post",
        url: "/fresns/enableUnikeyStatus",
        data: { 'data_id': id, 'is_enable': 0 },
        beforeSend: function (request) {
            return request.setRequestHeader('X-CSRF-Token', "{{ csrf_token() }}");
        },
        success: function (data) {
            if (data.code == 0) {
                window.location.reload();
            } else {
                alert(data.message)
            }
        }
    })
});

//Activate
$(".btn_enable2").click(function () {
    var id = $(this).attr('data_id');
    $.ajax({
        async: false,
        type: "post",
        url: "/fresns/enableUnikeyStatus",
        data: { 'data_id': id, 'is_enable': 1 },
        beforeSend: function (request) {
            return request.setRequestHeader('X-CSRF-Token', "{{ csrf_token() }}");
        },
        success: function (data) {
            if (data.code == 0) {
                window.location.reload();
            } else {
                alert(data.message)
            }
        }
    })
});

//Uninstall
$('.uninstallUnikey').on('click', function () {
    var name = $(this).attr('data-name');
    $('#confirmUninstall .modal-title').text(name);
    var unikey = $(this).attr('unikey');
    $("#btn-danger-delete").attr('unikey', unikey);
});
$("#btn-danger-delete").click(function () {
    var unikey = $(this).attr('unikey');
    var clear_plugin_data = $('#is-delete-data').is(':checked') ? 1 : 0;



    $.ajax({
        async: false,
        type: "post",
        url: "/fresns/uninstall",
        data: { 'unikey': unikey, 'clear_plugin_data': clear_plugin_data },
        beforeSend: function (request) {
            return request.setRequestHeader('X-CSRF-Token', "{{ csrf_token() }}");
        },
        success: function (data) {
            if (data.code == 0) {
                $('#uninstallActionModal').addClass('show');
                $('#uninstallActionModal').css({
                    'display': 'block'
                });
                setTimeout(function () {
                    $('.step1').removeClass("spinner-border spinner-border-sm")
                    $('.step1').addClass("bi bi-check-lg text-success")
                    $('.step2').removeClass("bi bi-hourglass text-secondary")
                    $('.step2').addClass("spinner-border spinner-border-sm")
                }, 300)
                setTimeout(function () {
                    $('.step2').removeClass("spinner-border spinner-border-sm")
                    $('.step2').addClass("bi bi-check-lg text-success")
                    $('.step3').removeClass("bi bi-hourglass text-secondary")
                    $('.step3').addClass("spinner-border spinner-border-sm")
                }, 600)
                setTimeout(function () {
                    $('.step3').removeClass("spinner-border spinner-border-sm")
                    $('.step3').addClass("bi bi-check-lg text-success")
                    $('.step4').removeClass("bi bi-hourglass text-secondary")
                    $('.step4').addClass("spinner-border spinner-border-sm")
                }, 900)
                setTimeout(function () {
                    $('.step4').removeClass("spinner-border spinner-border-sm")
                    $('.step4').addClass("bi bi-check-lg text-success")
                    $('.step5').removeClass("bi bi-hourglass text-secondary")
                    $('.step5').addClass("spinner-border spinner-border-sm")
                }, 1200)
                setTimeout(function () {
                    $('.step5').removeClass("spinner-border spinner-border-sm")
                    $('.step5').addClass("bi bi-check-lg text-success")
                }, 1500)
                setTimeout(function () {
                    window.location.reload();
                }, 1800)
            } else {
                alert(data.message)
            }
        }
    })
});

//Plugin List Tab
$(".pluginList li").click(function () {
    var type = $(this).find('a').attr('data-type');
    $(".pluginList").find('li a').removeClass('active');
    $(this).find('a').addClass('active');
    if (type == 2) {
        $(".pluginLists").show();
        return;
    }
    $(".pluginLists").each(function () {
        var that = $(this);
        var enableStatus = that.attr('isEnable');
        if (type != enableStatus) {
            that.hide();
        } else {
            that.show();
        }
    })
});

//Bootstrap Tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});
