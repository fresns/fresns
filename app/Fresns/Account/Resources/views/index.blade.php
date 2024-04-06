@extends('FsAccountView::layout')

@section('title', $fsLang['accountCenter'])

@section('body')
    {{-- header --}}
    <header class="text-center">
        <p><img src="{{ $siteLogo }}" height="30"></p>
        <h1 class="fs-4">{{ $fsLang['accountCenter'] }}</h1>
        <p class="fw-normal">{{ $fsLang['accountCenterDesc'] }}</p>
    </header>

    {{-- main --}}
    <main class="mt-4">
        <div class="list-group mb-4">
            {{-- birthday --}}
            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" aria-current="true" data-bs-toggle="modal" data-bs-target="#editBirthdayModal">
                <div class="my-1">
                    <h5 class="my-1 fs-6">{{ $fsLang['userBirthday'] }}</h5>
                    <small class="text-secondary">{{ $accountData['birthday'] ?: $fsLang['settingNot'] }}</small>
                </div>
                <i class="bi bi-chevron-right"></i>
            </button>

            {{-- email --}}
            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" aria-current="true" data-bs-toggle="modal" data-bs-target="#editEmailModal">
                <div class="my-1">
                    <h5 class="my-1 fs-6">{{ $fsLang['email'] }}</h5>
                    <small class="text-secondary">{{ $accountPassport['email'] ?: $fsLang['settingNot'] }}</small>
                </div>
                <i class="bi bi-chevron-right"></i>
            </button>

            {{-- phone --}}
            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" aria-current="true" data-bs-toggle="modal" data-bs-target="#editPhoneModal">
                <div class="my-1">
                    <h5 class="my-1 fs-6">{{ $fsLang['phone'] }}</h5>
                    <small class="text-secondary">{{ $accountData['hasPhone'] ? '+'.$accountPassport['countryCode'].' '.$accountPassport['purePhone'] : $fsLang['settingNot'] }}</small>
                </div>
                <i class="bi bi-chevron-right"></i>
            </button>

            {{-- password --}}
            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" aria-current="true" data-bs-toggle="modal" data-bs-target="#editPasswordModal">
                <div class="my-1">
                    <h5 class="my-1 fs-6">{{ $fsLang['password'] }}</h5>
                    @if (! $accountData['hasPassword'])
                        <small class="text-secondary">{{ $fsLang['settingNot'] }}</small>
                    @endif
                </div>
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        {{-- kyc service --}}
        @if ($fsConfig['account_kyc_service'])
            <h3 class="mb-2 ms-1 fs-6">{{ $fsLang['accountKyc'] }}</h3>
            <div class="list-group mb-4">
                {{-- setting --}}
                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" aria-current="true" data-bs-toggle="modal" data-bs-target="#fresnsModal"
                    data-title="{{ $fsLang['accountKyc'] }}"
                    data-url="{{ $fsConfig['account_kyc_service'] }}"
                    data-post-message-key="fresnsAccountCenter">
                    <div class="my-1">
                        <h5 class="my-1 fs-6">{{ $fsLang['setting'] }}</h5>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        @endif

        {{-- wallet --}}
        @if ($fsConfig['wallet_status'])
            <h3 class="mb-2 ms-1 fs-6">{{ $fsConfig['channel_me_wallet_name'] }}</h3>
            <div class="list-group mb-4">
                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" aria-current="true" data-bs-toggle="modal" data-bs-target="#editWalletPasswordModal">
                    <div class="my-1">
                        <h5 class="my-1 fs-6">{{ $fsLang['walletPassword'] }}</h5>
                        @if (! $accountWallet['hasPassword'])
                            <small class="text-secondary">{{ $fsLang['settingNot'] }}</small>
                        @endif
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        @endif

        {{-- users --}}
        @if ($fsConfig['account_users_service'])
            <h3 class="mb-2 ms-1 fs-6">{{ $fsConfig['user_name'] }}</h3>
            <div class="list-group mb-4">
                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" aria-current="true" data-bs-toggle="modal" data-bs-target="#fresnsModal"
                    data-title="{{ $fsConfig['user_name'] }}"
                    data-url="{{ $fsConfig['account_users_service'] }}"
                    data-post-message-key="fresnsAccountCenter">
                    <div class="my-1">
                        <h5 class="my-1 fs-6">{{ $fsLang['manage'] }}</h5>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        @endif

        {{-- connects --}}
        @if ($accountConnects)
            <h3 class="mb-2 ms-1 fs-6">{{ $fsLang['settingConnects'] }}</h3>
            <div class="list-group mb-4">
                @foreach ($accountConnects as $item)
                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" aria-current="true" data-bs-toggle="modal" data-bs-target="#fresnsModal"
                        data-title="{{ $fsLang['settingConnects'] }}"
                        data-url="{{ $item['service'] }}"
                        data-connect-platform-id="{{ $item['connectPlatformId'] }}"
                        data-post-message-key="fresnsAccountCenter">
                        <div class="my-1">
                            <h5 class="my-1 fs-6">{{ $item['connectName'] }}</h5>
                            <small class="text-secondary">{{ $item['connected'] ? $item['nickname'] : $fsLang['settingAccountConnect'] }}</small>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </button>
                @endforeach
            </div>
        @endif

        {{-- delete --}}
        @if ($fsConfig['delete_account_type'] != 1)
            <h3 class="mb-2 ms-1 fs-6">{{ $fsLang['accountDelete'] }}</h3>
            <div class="list-group mb-4">
                {{-- operation --}}
                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" aria-current="true" data-bs-toggle="modal" data-bs-target="#deletePolicyModal">
                    <div class="my-1">
                        <h5 class="my-1 fs-6"><i class="bi bi-file-earmark-text"></i> {{ $fsLang['accountPoliciesDelete'] }}</h5>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </button>
                @if ($accountData['waitDelete'])
                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center text-danger" aria-current="true" data-bs-toggle="modal" data-bs-target="#revokeDeleteModal">
                        <div class="my-1">
                            <h5 class="my-1 fs-6">{{ $fsLang['accountRevokeDelete'] }}</h5>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </button>
                @else
                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center text-danger" aria-current="true" data-bs-toggle="modal" data-bs-target="#applyDeleteModal">
                        <div class="my-1">
                            <h5 class="my-1 fs-6">{{ $fsLang['accountApplyDelete'] }}</h5>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </button>
                @endif
                {{-- status --}}
                @if ($accountData['waitDelete'])
                    <div class="list-group-item">
                        <h5 class="my-1 fs-6 fw-normal">{{ $fsLang['accountWaitDelete'] }}</h5>
                        <small class="text-secondary">{{ $fsLang['executionDate'].': '.$accountData['waitDeleteDateTime'] }}</small>
                    </div>
                @endif
            </div>
        @endif
    </main>

    {{-- modal --}}
    @component('FsAccountView::components.birthday', [
        'birthday' => $account->birthday,
    ])@endcomponent

    @component('FsAccountView::components.email', [
        'accountPassport' => $accountPassport,
    ])@endcomponent

    @component('FsAccountView::components.phone', [
        'accountPassport' => $accountPassport,
    ])@endcomponent

    @component('FsAccountView::components.password', [
        'fsConfig' => $fsConfig,
        'accountData' => $accountData,
        'accountPassport' => $accountPassport,
    ])@endcomponent

    @component('FsAccountView::components.wallet-password', [
        'fsConfig' => $fsConfig,
        'accountData' => $accountData,
        'accountPassport' => $accountPassport,
        'accountWallet' => $accountWallet,
    ])@endcomponent

    {{-- account delete policy --}}
    <div class="modal fade" id="deletePolicyModal" tabindex="-1" aria-labelledby="deletePolicyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    {!! $fsConfig['account_delete_policy'] ? Str::markdown($fsConfig['account_delete_policy']) : '' !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $fsLang['close'] }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- apply delete --}}
    @component('FsAccountView::components.apply-delete', [
        'fsConfig' => $fsConfig,
        'accountData' => $accountData,
        'accountPassport' => $accountPassport,
    ])@endcomponent

    {{-- revoke delete --}}
    <div class="modal fade" id="revokeDeleteModal" tabindex="-1" aria-labelledby="revokeDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="revokeDeleteModalLabel">{{ $fsLang['accountRevokeDelete'] }}</h1>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary me-auto" data-bs-dismiss="modal">{{ $fsLang['close'] }}</button>
                    <button type="button" class="btn btn-primary" onclick="revokeDelete(this)">{{ $fsLang['confirm'] }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        // send verify code
        function sendVerifyCode(obj) {
            let type = $(obj).data('type'),
                templateId = $(obj).data('template-id');
                countryCodeInputId = $(obj).data('country-code-input-id'),
                accountInputId = $(obj).data('account-input-id');

            let countryCode = '',
                account = '';

            if (countryCodeInputId) {
                countryCode = $('#' + countryCodeInputId).val();
            }

            if (accountInputId) {
                account = $('#' + accountInputId).val();
            }

            if (templateId == 3 && !account) {
                tips("{{ $accountEmptyError }}");

                return;
            }

            Cookies.set('fresns_account_center_verify_code_time', 60, { expires: 1 });
            setSendCodeTime();

            $.ajax({
                url: "{{ route('account-center.api.send-verify-code') }}",
                type: 'post',
                data: {
                    'type': type,
                    'account': account,
                    'countryCode': countryCode,
                    'templateId': templateId,
                },
                error: function (error) {
                    tips(error.responseJSON.message);
                },
                success: function (res) {
                    if (res.code != 0) {
                        tips(res.message);

                        Cookies.set('fresns_account_center_verify_code_time', 0, { expires: 1 });
                        return;
                    }

                    tips("{{ $fsLang['send'].': '.$fsLang['success'] }}");
                },
            });
        }

        // check verify code
        function checkVerifyCode(obj) {
            var btn = $(obj);

            let type = $(obj).data('type'),
                templateId = $(obj).data('template-id');
                inputId = $(obj).data('input-id'),
                hiddenId = $(obj).data('hidden-id'),
                showId = $(obj).data('show-id');

            let verifyCode = '';

            if (inputId) {
                verifyCode = $('#' + inputId).val();
            }

            if (!verifyCode) {
                tips("{{ $verifyCodeEmptyError }}");

                return;
            }

            btn.prop('disabled', true);
            if (btn.children('.spinner-border').length == 0) {
                btn.prepend('<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> ');
            }
            btn.children('.spinner-border').removeClass('d-none');

            $.ajax({
                url: "{{ route('account-center.api.check-verify-code') }}",
                type: 'post',
                data: {
                    'type': type,
                    'templateId': templateId,
                    'verifyCode': verifyCode,
                },
                error: function (error) {
                    tips(error.responseJSON.message);
                },
                success: function (res) {
                    if (res.code != 0) {
                        tips(res.message);

                        return;
                    }

                    if (hiddenId) {
                        $('#' + hiddenId).addClass('d-none');
                    }

                    if (showId) {
                        $('#' + showId).removeClass('d-none');
                    }

                    $('.fs-modal-submit').removeClass('d-none');
                },
                complete: function (e) {
                    btn.prop('disabled', false);
                    btn.find('.spinner-border').remove();
                },
            });
        };

        // revoke delete account
        function revokeDelete(obj) {
            var btn = $(obj);

            btn.prop('disabled', true);
            if (btn.children('.spinner-border').length == 0) {
                btn.prepend('<span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> ');
            }
            btn.children('.spinner-border').removeClass('d-none');

            $.ajax({
                url: "{{ route('account-center.api.revoke.delete') }}",
                type: 'post',
                error: function (error) {
                    tips(error.responseJSON.message);
                },
                success: function (res) {
                    tips(res.message);
                    if (res.code == 0) {
                        window.location.reload();
                    }
                },
                complete: function (e) {
                    btn.prop('disabled', false);
                    btn.find('.spinner-border').remove();
                },
            });
        };

        // api request form
        $('.api-request-form').submit(function (e) {
            e.preventDefault();
            let form = $(this),
                btn = $(this).find('button[type="submit"]');

            const actionUrl = form.attr('action'),
                methodType = form.attr('method') || 'POST',
                data = form.serialize();

            $.ajax({
                url: actionUrl,
                type: methodType,
                data: data,
                success: function (res) {
                    tips(res.message);
                    if (res.code == 0) {
                        window.location.reload();
                    }
                },
                complete: function (e) {
                    btn.prop('disabled', false);
                    btn.find('.spinner-border').remove();
                },
            });
        });
    </script>
@endpush
