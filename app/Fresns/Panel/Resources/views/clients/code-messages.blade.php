@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@use('App\Helpers\StrHelper')

@section('content')
    <!--code messages header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_code_messages') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_code_messages_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                {{-- <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a> --}}
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <!--filter-->
        <form action="{{ route('panel.code-messages.index') }}" method="get">
            <div class="input-group">
                <span class="input-group-text">{{ __('FsLang::panel.table_plugin') }}</span>
                <select class="form-select" id="fskey" name="app_fskey">
                    <option value="">{{ __('FsLang::panel.option_all') }}</option>
                    <option value="Fresns" @if (request('app_fskey') == 'Fresns') selected @endif>Fresns</option>
                    <option value="CmdWord" @if (request('app_fskey') == 'CmdWord') selected @endif>CmdWord</option>
                    @foreach ($appList as $app)
                        <option @if (request('app_fskey') == $app['fskey']) selected @endif value="{{ $app['fskey'] }}">{{ $app['fskey'] }} -> {{ $app['name'] }}</option>
                    @endforeach
                </select>
                <span class="input-group-text">{{ __('FsLang::panel.table_number') }}</span>
                <input type="number" name="code" class="form-control" placeholder="Code" value="{{ request('code') }}">
                <button class="btn btn-outline-secondary" type="submit">{{ __('FsLang::panel.button_confirm') }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('panel.code-messages.index') }}">{{ __('FsLang::panel.button_reset') }}</a>
            </div>
        </form>
        <!--filter end-->
    </div>

    <!--code messages list-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info align-middle">
                    <th scope="col">{{ __('FsLang::panel.table_plugin') }}</th>
                    <th scope="col" style="width:8rem;">Code</th>
                    <th scope="col" class="w-50">
                        Message
                        @if ($languageStatus)
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-dark btn-sm ms-2 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">{{ __('FsLang::panel.default_language') }}</button>
                                <ul class="dropdown-menu">
                                    @foreach ($optionalLanguages as $lang)
                                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['langTag' => $lang['langTag']]) }}">{{ $lang['langName'] }} @if ($lang['areaName']) {{ '('.$lang['areaName'].')' }} @endif</a></li>
                                    @endforeach
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('panel.code-messages.index') }}">{{ __('FsLang::panel.button_reset') }}</a></li>
                                </ul>
                            </div>
                        @endif
                    </th>
                    <th scope="col">{{ __('FsLang::panel.table_options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($codeMessages as $message)
                    <tr>
                        <td>
                            {{ collect($appList)->where('fskey', $message->app_fskey)->first()['name'] ?? $message->app_fskey }}
                            <span class="badge bg-secondary">{{ $message->app_fskey }}</span>
                        </td>
                        <td>{{ $message->code }}</td>
                        <td><input type="text" class="form-control" value="{{ StrHelper::languageContent($message->messages, request('langTag')) }}" readonly></td>
                        <td>
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#editCodeMessages"
                                data-app-name="{{ collect($appList)->where('fskey', $message->app_fskey)->first()['name'] ?? $message->app_fskey }}"
                                data-action="{{ route('panel.code-messages.update', $message)}}"
                                data-params="{{ $message->toJson() }}">
                                {{ __('FsLang::panel.button_edit') }}
                            </button>

                            @if (! in_array($message->app_fskey, ['Fresns', 'CmdWord']))
                                <button type="button" class="btn btn-link btn-sm text-danger fresns-link ms-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteCodeMessages"
                                    data-app-name="{{ collect($appList)->where('fskey', $message->app_fskey)->first()['name'] ?? $message->app_fskey }}"
                                    data-code-message="{{ StrHelper::languageContent($message->messages, request('langTag')) }}"
                                    data-action="{{ route('panel.code-messages.destroy', $message) }}"
                                    data-params="{{ $message->toJson() }}">
                                    {{ __('FsLang::panel.button_delete') }}
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $codeMessages->appends(request()->all())->links() }}

    <!-- Edit Modal -->
    <div class="modal fade" id="editCodeMessages" tabindex="-1" aria-labelledby="editCodeMessagesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span class="message-app-name">Fresns</span>
                        <span class="message-app-fskey badge bg-secondary">Fresns</span>
                        <span class="message-code badge bg-warning text-dark">0</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="post">
                        @method('put')
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-nowrap">
                                <thead>
                                    <tr class="table-info">
                                        <th scope="col" class="w-25">{{ __('FsLang::panel.table_lang_tag') }}</th>
                                        <th scope="col" class="w-25">{{ __('FsLang::panel.table_lang_name') }}</th>
                                        <th scope="col" class="w-50">{{ __('FsLang::panel.table_content') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($optionalLanguages as $lang)
                                        <tr>
                                            <td>
                                                {{ $lang['langTag'] }}
                                                @if ($lang['langTag'] == $defaultLanguage)
                                                    <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.default_language') }}" data-bs-original-title="{{ __('FsLang::panel.default_language') }}" aria-label="{{ __('FsLang::panel.default_language') }}"></i>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $lang['langName'] }}
                                                @if ($lang['areaName'])
                                                    {{ '('.$lang['areaName'].')' }}
                                                @endif
                                            </td>
                                            <td><textarea class="form-control desc-input" name="messages[{{ $lang['langTag'] }}]" rows="3"></textarea></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!--button_save-->
                        <div class="text-center">
                            <button type="submit" class="btn btn-success" data-bs-dismiss="modal" aria-label="Close">{{ __('FsLang::panel.button_save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteCodeMessages" tabindex="-1" aria-labelledby="deleteCodeMessages" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="" method="post">
                    @csrf
                    @method('delete')
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span class="message-app-name">Fresns</span>
                            <span class="message-app-fskey badge bg-secondary">Fresns</span>
                            <span class="message-code badge bg-warning text-dark">0</span>
                        </h5>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger" data-bs-toggle="modal" data-bs-dismiss="modal">{{ __('FsLang::panel.button_confirm_delete') }}</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('FsLang::panel.button_cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
