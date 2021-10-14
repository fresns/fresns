<!--Action Modal local install-->
<div class="modal fade" id="localInstallActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-laptop"></i>
                @lang('fresns.localInstall')
                    <!--Style when upgrading
                        Plugin name <span class="badge bg-secondary">Current version</span> to <span class="badge bg-danger">New version</span>
                    -->
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body ps-5">
                <p><i class="spinner-border spinner-border-sm me-2 step1"></i>@lang('fresns.localInstallStep1')</p>
                <p><i class="bi bi-hourglass text-secondary me-2 step2"></i>@lang('fresns.localInstallStep2')</p>
                <p><i class="bi bi-hourglass text-secondary me-2 step3"></i>@lang('fresns.localInstallStep3')</p>
                <p><i class="bi bi-hourglass text-secondary me-2 step4"></i>@lang('fresns.localInstallStep4')</p>
                <p><i class="bi bi-hourglass text-secondary me-2 step5"></i>@lang('fresns.localInstallStep5')</p>
            </div>
        </div>
    </div>
</div>

<!--Action Modal code install-->
<div class="modal fade" id="codeInstallActionModal" tabindex="-1" aria-labelledby="codeInstall" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-laptop"></i> @lang('fresns.codeInstall')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body ps-5">
                <p><i class="spinner-border spinner-border-sm me-2 step1"></i>@lang('fresns.codeInstallStep1')</p>
                <p><i class="bi bi-hourglass text-secondary me-2 step2"></i>@lang('fresns.codeInstallStep2')</p>
                <p><i class="bi bi-hourglass text-secondary me-2 step3"></i>@lang('fresns.codeInstallStep3')</p>
                <p><i class="bi bi-hourglass text-secondary me-2 step4"></i>@lang('fresns.codeInstallStep4')</p>
                <p><i class="bi bi-hourglass text-secondary me-2 step5"></i>@lang('fresns.codeInstallStep5')</p>
                <p><i class="bi bi-hourglass text-secondary me-2 step5"></i>@lang('fresns.codeInstallStep6')</p>
            </div>
        </div>
    </div>
</div>

<!--Action Modal uninstall-->
<div class="modal fade" id="uninstallActionModal" tabindex="-1" aria-labelledby="uninstall" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-laptop"></i> @lang('fresns.uninstall')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body ps-5">
                <p><i class="spinner-border spinner-border-sm me-2 step1"></i>@lang('fresns.uninstallStep1')</p>
                <p><i class="bi bi-hourglass text-secondary me-2 step2"></i>@lang('fresns.uninstallStep2')</p>
                <p><i class="bi bi-hourglass text-secondary me-2 step3"></i>@lang('fresns.uninstallStep3')</p>
                <p><i class="bi bi-hourglass text-secondary me-2 step4"></i>@lang('fresns.uninstallStep4')</p>
                <p><i class="bi bi-hourglass text-secondary me-2 step5"></i>@lang('fresns.uninstallStep5')</p>
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="copyright text-center">
        <p class="mt-5 mb-5 text-muted">Powered by Fresns</p>
    </div>
</footer>

<script src="/static/js/bootstrap.bundle.min.js"></script>
<script src="/static/js/jquery-3.6.0.min.js"></script>
<script src="/static/js/console.js"></script>

<script>
    $(function () {
        $(document).ready(function(){
            var val = window.location.search;
            if(val){
                var text = $('#navbarContent .dropdown-item[href="'+val+'"]').text();
                $('#language').find('span').text(text)
            }
            $("#navbarContent .dropdown-item").click(function(){
                var url = $(this).attr('href');
                $.ajax({
                    url: '/fresns/setLanguage',
                    type: 'post',
                    data: {'lang':url},
                    dataType: 'json',
                    success: function (resp) {
                        // console.log(file);
                        if(resp.code == 0){
                            window.location.reload();
                        }
                    }
                })
            })
            function getQueryVariable(variable)
            {
                var query = window.location.search.substring(1);
                var vars = query.split("&");
                for (var i=0;i<vars.length;i++) {
                    var pair = vars[i].split("=");
                    if(pair[0] == variable){return pair[1];}
                }
                return(false);
            }
        })
    })
</script>
