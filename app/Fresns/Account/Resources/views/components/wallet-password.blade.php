<div class="modal fade" id="editWalletPasswordModal" tabindex="-1" aria-labelledby="editWalletPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form class="api-request-form" action="{{ route('account-center.api.update') }}" method="patch" autocomplete="off">
                <input type="hidden" name="formType" value="walletPassword">

                <div class="modal-body">
                    {{-- collapse --}}
                    <div class="input-group mb-3 mt-2">
                        <span class="input-group-text">{{ $fsLang['settingType'] }}</span>
                        <div class="form-control">
                            @if ($accountWallet['hasPassword'])
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input collapsed" type="radio" name="codeType" id="wallet_password_to_edit" value="password" data-bs-toggle="collapse" data-bs-target=".wallet_password_to_edit:not(.show)" aria-controls="wallet_password_to_edit" aria-expanded="true" checked>
                                    <label class="form-check-label" for="wallet_password_to_edit">{{ $fsLang['walletPassword'] }}</label>
                                </div>
                            @endif
                            @if ($accountData['hasEmail'])
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input collapsed" type="radio" name="codeType" id="wallet_email_to_edit" value="email" data-bs-toggle="collapse" data-bs-target=".wallet_email_to_edit:not(.show)" aria-controls="wallet_email_to_edit" aria-expanded="false" @if (!$accountWallet['hasPassword']) checked @endif>
                                    <label class="form-check-label" for="wallet_email_to_edit">{{ $fsLang['emailVerifyCode'] }}</label>
                                </div>
                            @endif
                            @if ($accountData['hasPhone'])
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input collapsed" type="radio" name="codeType" id="wallet_phone_to_edit" value="sms" data-bs-toggle="collapse" data-bs-target=".wallet_phone_to_edit:not(.show)" aria-controls="wallet_phone_to_edit" aria-expanded="false" @if (!$accountWallet['hasPassword'] && !$accountData['hasPhone']) checked @endif>
                                    <label class="form-check-label" for="wallet_phone_to_edit">{{ $fsLang['smsVerifyCode'] }}</label>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- collapse --}}
                    <div id="edit_wallet_password_mode">
                        {{-- current password --}}
                        <div class="wallet_password_to_edit collapse @if ($accountWallet['hasPassword']) show @endif" aria-labelledby="wallet_password_to_edit" data-bs-parent="#edit_wallet_password_mode">
                            <div class="input-group mb-3">
                                <span class="input-group-text">{{ $fsLang['passwordCurrent'] }}</span>
                                <input type="text" class="form-control" name="currentWalletPassword" value="">
                            </div>
                        </div>

                        {{-- email --}}
                        <div class="wallet_email_to_edit collapse @if (!$accountWallet['hasPassword']) show @endif" aria-labelledby="wallet_email_to_edit" data-bs-parent="#edit_wallet_password_mode">
                            <div class="input-group mb-3">
                                <span class="input-group-text">{{ $fsLang['currentEmail'] }}</span>
                                <input class="form-control" type="text" value="{{ $accountPassport['email'] }}" disabled readonly>
                                <button type="button" class="btn btn-outline-secondary send-verify-code" data-type="email" data-template-id="3" onclick="sendVerifyCode(this)">{{ $fsLang['sendVerifyCode'] }}</button>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text">{{ $fsLang['verifyCode'] }}</span>
                                <input type="text" class="form-control" name="emailVerifyCode" autocomplete="off">
                            </div>
                        </div>

                        {{-- phone --}}
                        <div class="wallet_phone_to_edit collapse @if (!$accountWallet['hasPassword'] && !$accountData['hasPhone']) show @endif" aria-labelledby="wallet_phone_to_edit" data-bs-parent="#edit_wallet_password_mode">
                            <div class="input-group mb-3">
                                <span class="input-group-text">{{ $fsLang['currentPhone'] }}</span>
                                <input class="form-control" type="text" value="{{ $accountPassport['countryCode'].' '.$accountPassport['purePhone'] }}" disabled readonly>
                                <button type="button" class="btn btn-outline-secondary send-verify-code" data-type="sms" data-template-id="3" onclick="sendVerifyCode(this)">{{ $fsLang['sendVerifyCode'] }}</button>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text">{{ $fsLang['verifyCode'] }}</span>
                                <input type="text" class="form-control" name="smsVerifyCode" autocomplete="off">
                            </div>
                        </div>
                    </div>

                    {{-- new password --}}
                    <div class="input-group mb-3">
                        <span class="input-group-text">{{ $accountWallet['hasPassword'] ? $fsLang['passwordNew'] : $fsLang['password'] }}</span>
                        <input type="text" class="form-control" name="newWalletPassword" value="" autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary me-auto" data-bs-dismiss="modal">{{ $fsLang['close'] }}</button>
                    <button type="submit" class="btn btn-primary">{{ $fsLang['saveChanges'] }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
