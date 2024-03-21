<div class="modal fade" id="editPasswordModal" tabindex="-1" aria-labelledby="editPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form class="api-request-form" action="{{ route('account-center.api.update') }}" method="patch" autocomplete="off">
                <input type="hidden" name="formType" value="password">

                <div class="modal-body">
                    {{-- collapse --}}
                    <div class="input-group mb-3 mt-2">
                        <span class="input-group-text">{{ $fsLang['settingType'] }}</span>
                        <div class="form-control">
                            @if ($accountData['hasPassword'])
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input collapsed" type="radio" name="codeType" id="password_to_edit" value="password" data-bs-toggle="collapse" data-bs-target=".password_to_edit:not(.show)" aria-controls="password_to_edit" aria-expanded="true" checked>
                                    <label class="form-check-label" for="password_to_edit">{{ $fsLang['accountPassword'] }}</label>
                                </div>
                            @endif
                            @if ($accountData['hasEmail'])
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input collapsed" type="radio" name="codeType" id="email_to_edit" value="email" data-bs-toggle="collapse" data-bs-target=".email_to_edit:not(.show)" aria-controls="email_to_edit" aria-expanded="false" @if (!$accountData['hasPassword']) checked @endif>
                                    <label class="form-check-label" for="email_to_edit">{{ $fsLang['emailVerifyCode'] }}</label>
                                </div>
                            @endif
                            @if ($accountData['hasPhone'])
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input collapsed" type="radio" name="codeType" id="phone_to_edit" value="sms" data-bs-toggle="collapse" data-bs-target=".phone_to_edit:not(.show)" aria-controls="phone_to_edit" aria-expanded="false" @if (!$accountData['hasPassword'] && !$accountData['hasPhone']) checked @endif>
                                    <label class="form-check-label" for="phone_to_edit">{{ $fsLang['smsVerifyCode'] }}</label>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- collapse --}}
                    <div id="edit_password_mode">
                        {{-- current password --}}
                        <div class="password_to_edit collapse @if ($accountData['hasPassword']) show @endif" aria-labelledby="password_to_edit" data-bs-parent="#edit_password_mode">
                            <div class="input-group mb-3">
                                <span class="input-group-text">{{ $fsLang['passwordCurrent'] }}</span>
                                <input type="text" class="form-control" name="currentPassword" value="">
                            </div>
                        </div>

                        {{-- email --}}
                        <div class="email_to_edit collapse @if (!$accountData['hasPassword']) show @endif" aria-labelledby="email_to_edit" data-bs-parent="#edit_password_mode">
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
                        <div class="phone_to_edit collapse @if (!$accountData['hasPassword'] && !$accountData['hasPhone']) show @endif" aria-labelledby="phone_to_edit" data-bs-parent="#edit_password_mode">
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
                        <span class="input-group-text">{{ $accountData['hasPassword'] ? $fsLang['passwordNew'] : $fsLang['password'] }}</span>
                        <input type="text" class="form-control" name="newPassword" value="" autocomplete="off">
                    </div>

                    <div class="form-text">
                        {{ $fsLang['passwordInfo'] }}:
                        @if (in_array('number', $fsConfig['password_strength'])) {{ $fsLang['passwordInfoNumbers'] }} @endif
                        @if (in_array('number', $fsConfig['password_strength']) && in_array('lowercase', $fsConfig['password_strength'])) , @endif

                        @if (in_array('lowercase', $fsConfig['password_strength'])) {{ $fsLang['passwordInfoLowercaseLetters'] }} @endif
                        @if (in_array('lowercase', $fsConfig['password_strength']) && in_array('uppercase', $fsConfig['password_strength'])) , @endif

                        @if (in_array('uppercase', $fsConfig['password_strength'])) {{ $fsLang['passwordInfoUppercaseLetters'] }} @endif
                        @if (in_array('uppercase', $fsConfig['password_strength']) && in_array('symbols', $fsConfig['password_strength'])) , @endif

                        @if (in_array('symbols', $fsConfig['password_strength'])) {{ $fsLang['passwordInfoSymbols'] }} @endif

                        <div class="vr mx-2"></div>

                        {{ $fsLang['modifierLength'] }}: {{ $fsConfig['password_length'].'~32' }}
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
