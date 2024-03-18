<div class="modal fade" id="editPhoneModal" tabindex="-1" aria-labelledby="editPhoneModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form class="api-request-form" action="{{ route('account-center.api.update') }}" method="patch" autocomplete="off">
                <input type="hidden" name="formType" value="phone">

                <div class="modal-body">
                    {{-- old phone --}}
                    @if ($accountPassport['phone'])
                        <div id="oldPhone">
                            <p class="form-text mb-3 text-center">{{ $fsLang['settingWarning'] }}</p>
                            <div class="input-group mb-3">
                                <span class="input-group-text">{{ $fsLang['currentPhone'] }}</span>
                                <input class="form-control" type="text" value="{{ $accountPassport['countryCode'].' '.$accountPassport['purePhone'] }}" disabled readonly>
                                <button type="button" class="btn btn-outline-secondary send-verify-code" data-type="sms" data-template-id="4" onclick="sendVerifyCode(this)">{{ $fsLang['sendVerifyCode'] }}</button>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text">{{ $fsLang['verifyCode'] }}</span>
                                <input type="text" class="form-control" name="verifyCode" id="smsVerifyCode" value="">
                                <button type="button" class="btn btn-outline-secondary" data-type="sms" data-template-id="4" data-input-id="smsVerifyCode" data-hidden-id="oldPhone" data-show-id="newPhone" onclick="checkVerifyCode(this)">{{ $fsLang['check'] }}</button>
                            </div>
                        </div>
                    @elseif ($accountPassport['email'])
                        <div id="currentEmail">
                            <p class="form-text mb-3 text-center">{{ $fsLang['settingWarning'] }}</p>
                            <div class="input-group mb-3">
                                <span class="input-group-text">{{ $fsLang['currentEmail'] }}</span>
                                <input class="form-control" type="text" value="{{ $accountPassport['email'] }}" disabled readonly>
                                <button type="button" class="btn btn-outline-secondary send-verify-code" data-type="email" data-template-id="4" onclick="sendVerifyCode(this)">{{ $fsLang['sendVerifyCode'] }}</button>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text">{{ $fsLang['verifyCode'] }}</span>
                                <input type="text" class="form-control" name="verifyCode" id="emailVerifyCode" value="">
                                <button type="button" class="btn btn-outline-secondary" data-type="email" data-template-id="4" data-input-id="emailVerifyCode" data-hidden-id="currentEmail" data-show-id="newPhone" onclick="checkVerifyCode(this)">{{ $fsLang['check'] }}</button>
                            </div>
                        </div>
                    @endif
                    {{-- new phone --}}
                    <div id="newPhone" @if ($accountPassport['email'] && $accountPassport['phone']) class="d-none" @endif>
                        <div class="input-group mb-3">
                            <span class="input-group-text">{{ $accountPassport['phone'] ? $fsLang['newPhone'] : $fsLang['phone'] }}</span>
                            <input type="number" class="form-control" name="newPhone" value="" required>
                            <button type="button" class="btn btn-outline-secondary send-verify-code" data-type="email" data-template-id="3" data-input-id="newPhone" onclick="sendVerifyCode(this)">{{ $fsLang['sendVerifyCode'] }}</button>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text">{{ $fsLang['verifyCode'] }}</span>
                            <input type="text" class="form-control" name="newVerifyCode" value="" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary me-auto" data-bs-dismiss="modal">{{ $fsLang['close'] }}</button>
                    <button type="submit" class="btn btn-primary fs-modal-submit d-none">{{ $fsLang['saveChanges'] }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
