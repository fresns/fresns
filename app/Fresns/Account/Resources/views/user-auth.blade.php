@extends('FsAccountView::commons.layout')

@section('title', $loginType == 'userAuth' ? $fsLang['select'] : $fsLang['accountLoggingIn'])

@section('body')
    {{-- header --}}
    <header class="text-center">
        <p><img src="{{ $siteLogo }}" height="30"></p>
        @if ($loginType == 'userAuth')
            <h1 class="fs-4">{{ $fsLang['select'] }}</h1>
        @endif
    </header>

    {{-- main --}}
    <main class="m-4">
        {{-- callback --}}
        <div class="d-none" id="loginLoading">
            <div class="d-flex justify-content-center">
                <div class="spinner-border text-secondary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <p class="text-center mt-3 mb-5">{{ $fsLang['accountLoggingIn'] }}</p>
        </div>

        {{-- select user --}}
        @if ($loginType == 'userAuth')
            <div class="row justify-content-center" id="selectUser">
                @foreach($accountDetail['users'] as $user)
                    <div class="col-6 col-md-4 d-flex flex-column align-items-center">
                        {{-- avatar --}}
                        <img src="{{ $user['avatar'] }}" loading="lazy" class="auth-avatar rounded-circle">

                        {{-- nickname --}}
                        <div class="auth-nickname mt-2">{{ $user['nickname'] }}</div>

                        {{-- username --}}
                        <div class="text-secondary">{{ '@' . $user['username'] }}</div>

                        {{-- button --}}
                        @if ($user['hasPin'])
                            <div class="btn-group my-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-dismiss="modal" data-bs-target="#userPinLogin" data-uid="{{ $user['uid'] }}" data-nickname="{{ $user['nickname'] }}">
                                    {{ $fsLang['userPinLogin'] }}
                                </button>

                                @if ($usersService)
                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split btn-sm" data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#fresnsModal"
                                                data-title="{{ $fsLang['userPinReset'] }}"
                                                data-url="{{ $usersService }}"
                                                data-uid="{{ $user['uid'] }}"
                                                data-post-message-key="reload">
                                                {{ $fsLang['userPinReset'] }}
                                            </button>
                                        </li>
                                    </ul>
                                @endif
                            </div>
                        @else
                            <form class="api-request-form" action="{{ route('account-center.api.user-auth') }}" method="post">
                                <input type="hidden" name="uid" value="{{ $user['uid'] }}">
                                <button type="submit" class="btn btn-outline-secondary btn-sm my-2">{{ $fsLang['select'] }}</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </main>

    {{-- After Login: Select User - Enter Password Modal --}}
    <div class="modal fade" id="userPinLogin" aria-hidden="true" aria-labelledby="userPinLoginLabel" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" id="user-password-auth">
                <form class="api-request-form" action="{{ route('account-center.api.user-auth') }}" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userPinLoginLabel">{{ $fsLang['userPinLogin'] }}</h5>
                        <button type="button" class="btn-close" data-bs-target="#userAuth" data-bs-toggle="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="uid" value="" id="userUid">
                        <div class="input-group">
                            <span class="input-group-text">{{ $fsLang['userPin'] }}</span>
                            <input type="password" class="form-control" name="pin" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        @if ($usersService)
                            <button type="button" class="btn btn-secondary me-auto" id="userPinReset" data-bs-toggle="modal" data-bs-target="#fresnsModal"
                                data-title="{{ $fsLang['userPinReset'] }}"
                                data-url="{{ $usersService }}"
                                data-uid=""
                                data-post-message-key="reload">
                                {{ $fsLang['userPinReset'] }}
                            </button>
                        @endif

                        <button type="submit" class="btn btn-primary">{{ $fsLang['userEnter'] }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .auth-avatar {
            width: 4rem;
            height: 4rem;
        }
    </style>
@endpush

@push('script')
    <script>
        const loginType = "{{ $loginType }}";
        const redirectURL = "{!! $redirectURL !!}";

        const selectUser = $('#selectUser');

        if (loginType == 'callback') {
            $('#loginLoading').removeClass('d-none');
            if (selectUser.length) {
                selectUser.addClass('d-none');
            }

            const loginToken = "{{ $loginToken }}";

            sendAccountCallback(loginToken);

            if (redirectURL && window.top == window.self) {
                console.log('current page not in iframe');

                window.location.href = redirectURL;
            }
        }

        $('#userPinLogin').on('show.bs.modal', function (e) {
            var button = $(e.relatedTarget);

            const uid = button.data('uid');
            const nickname = button.data('nickname');

            $('#userPinLoginLabel').text(nickname);
            $('#userUid').val(uid);
            $('#userPinReset').attr('data-uid', uid);
        });

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

                    if (res.code != 0) {
                        return;
                    }

                    if (res.data && res.data.loginToken) {
                        sendAccountCallback(res.data.loginToken);

                        $('#loginLoading').removeClass('d-none');
                        if (selectUser.length) {
                            selectUser.addClass('d-none');
                        }

                        if (redirectURL && window.top == window.self) {
                            console.log('api request form: current page not in iframe');

                            window.location.href = redirectURL;
                        }
                        return;
                    }

                    if (res.code == 32201 || res.code == 35201 || res.code == 31604 || res.code == 35204) {
                        return;
                    }

                    window.location.reload();
                },
                complete: function (e) {
                    btn.prop('disabled', false);
                    btn.find('.spinner-border').remove();
                },
            });
        });
    </script>
@endpush
