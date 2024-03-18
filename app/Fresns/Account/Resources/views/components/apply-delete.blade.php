<div class="modal fade" id="applyDeleteModal" tabindex="-1" aria-labelledby="applyDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form class="api-request-form" action="{{ route('account-center.api.apply.delete') }}" method="post" autocomplete="off">
                @if (!$accountData['hasEmail'] && !$accountData['hasPhone'])
                    <div class="modal-body">
                        <div class="py-3 text-danger">{{ $fsLang['accountDeleteDesc'] }}</div>
                    </div>
                @else
                    <div class="modal-body">
                        {{-- collapse --}}
                        <div class="input-group mb-3 mt-2">
                            <span class="input-group-text">{{ $fsLang['check'] }}</span>
                            <div class="form-control">
                                @if ($accountData['hasEmail'])
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input collapsed" type="radio" name="codeType" id="apply_delete_by_email" value="email" data-bs-toggle="collapse" data-bs-target=".apply_delete_by_email:not(.show)" aria-controls="apply_delete_by_email" aria-expanded="false" checked>
                                        <label class="form-check-label" for="apply_delete_by_email">{{ $fsLang['emailVerifyCode'] }}</label>
                                    </div>
                                @endif
                                @if ($accountData['hasPhone'])
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input collapsed" type="radio" name="codeType" id="apply_delete_by_phone" value="sms" data-bs-toggle="collapse" data-bs-target=".apply_delete_by_phone:not(.show)" aria-controls="apply_delete_by_phone" aria-expanded="false" @if (!$accountData['hasEmail']) checked @endif>
                                        <label class="form-check-label" for="apply_delete_by_phone">{{ $fsLang['smsVerifyCode'] }}</label>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- collapse --}}
                        <div id="apply_delete_mode">
                            {{-- email --}}
                            <div class="apply_delete_by_email collapse @if ($accountData['hasEmail']) show @endif" aria-labelledby="apply_delete_by_email" data-bs-parent="#apply_delete_mode">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">{{ $fsLang['currentEmail'] }}</span>
                                    <input class="form-control" type="text" value="{{ $accountPassport['email'] }}" disabled readonly>
                                    <button type="button" class="btn btn-outline-secondary send-verify-code" data-type="email" data-template-id="8" onclick="sendVerifyCode(this)">{{ $fsLang['sendVerifyCode'] }}</button>
                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text">{{ $fsLang['verifyCode'] }}</span>
                                    <input type="text" class="form-control" name="emailVerifyCode" autocomplete="off">
                                </div>
                            </div>

                            {{-- phone --}}
                            <div class="apply_delete_by_phone collapse @if (!$accountData['hasEmail'] && $accountData['hasPhone']) show @endif" aria-labelledby="apply_delete_by_phone" data-bs-parent="#apply_delete_mode">
                                <div class="input-group mb-3">
                                    <span class="input-group-text">{{ $fsLang['currentPhone'] }}</span>
                                    <input class="form-control" type="text" value="{{ $accountPassport['countryCode'].' '.$accountPassport['purePhone'] }}" disabled readonly>
                                    <button type="button" class="btn btn-outline-secondary send-verify-code" data-type="sms" data-template-id="8" onclick="sendVerifyCode(this)">{{ $fsLang['sendVerifyCode'] }}</button>
                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text">{{ $fsLang['verifyCode'] }}</span>
                                    <input type="text" class="form-control" name="smsVerifyCode" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary me-auto" data-bs-dismiss="modal">{{ $fsLang['close'] }}</button>
                        <button type="submit" class="btn btn-danger">{{ $fsLang['accountApplyDelete'] }}</button>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
