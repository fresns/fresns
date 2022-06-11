@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--code messages header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_code_messages') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_code_messages_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <!--filter-->
        <form action="">
            <div class="input-group">
                <span class="input-group-text">{{ __('FsLang::panel.table_lang_tag') }}</span>
                <select class="form-select" id="langTag" required name="lang_tag">
                    @foreach ($languageConfig['language_menus'] as $lang)
                        <option value="{{ $lang['langTag'] }}" @if ($lang['langTag'] == $langTag) selected @endif>{{ $lang['langTag'] }} -> {{ $lang['langName'] }} @if($lang['areaName']) {{ '('.$lang['areaName'].')' }} @endif</option>
                    @endforeach
                </select>
                <span class="input-group-text">{{ __('FsLang::panel.table_plugin') }}</span>
                <select class="form-select" id="unikey" name="plugin_unikey">
                    <option value="">{{ __('FsLang::panel.option_all') }}</option>
                    <option value="Fresns">Fresns</option>
                    @foreach ($pluginsList as $plugin)
                        <option @if ($plugin['unikey'] == $pluginUnikey) selected @endif value="{{ $plugin['unikey'] }}">{{ $plugin['unikey'] }} -> {{ $plugin['name'] }}</option>
                    @endforeach
                </select>
                <span class="input-group-text">{{ __('FsLang::panel.table_number') }}</span>
                <input type="number" name="code" class="form-control" placeholder="Code" value="{{ $code }}">
                <button class="btn btn-outline-secondary" type="submit">{{ __('FsLang::panel.button_confirm') }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('panel.code.messages.index') }}">{{ __('FsLang::panel.button_reset') }}</a>
            </div>
        </form>
        <!--filter end-->
    </div>
    <!--code messages list-->
    <div class="table-responsive">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr class="table-info">
                    <th scope="col">{{ __('FsLang::panel.table_plugin') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_number') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_lang_tag') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_content') }}</th>
                    <th scope="col">{{ __('FsLang::panel.table_options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($codeMessages as $message)
                    <tr>
                        <td>
                            {{ collect($pluginsList)->where('unikey', $message->plugin_unikey)->first()['name'] ?? $message->plugin_unikey }}
                            <span class="badge bg-secondary">{{ $message->plugin_unikey }}</span>
                        </td>
                        <td>{{ $message->code }}</td>
                        <td>{{ $message->lang_tag }}</td>
                        <td><input type="text" class="form-control" value="{{ $message->message }}" readonly></td>
                        <td>
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#editMessages"
                                data-name="{{ collect($pluginsList)->where('unikey', $message->plugin_unikey)->first()['name'] ?? $message->plugin_unikey }}"
                                data-unikey="{{ $message->plugin_unikey }}"
                                data-action="{{ route('panel.code.messages.update', $message->id)}}"
                                data-messages="{{ $message->messages->toJson() }}"
                                data-code="{{ $message->code }}">
                                {{ __('FsLang::panel.button_edit') }}
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $codeMessages->links() }}

    <!-- Edit Modal -->
    <div class="modal fade" id="editMessages" tabindex="-1" aria-labelledby="editMessagesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMessagesModalLabel">
                        <span class="code-message-plugin-name">Fresns</span>
                        <span class="code-message-plugin-unikey badge bg-secondary">Fresns</span>
                        <span class="code-message-plugin-code badge bg-warning text-dark">30000</span>
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
                                    @foreach ($languageConfig['language_menus'] as $lang)
                                        <tr>
                                            <td>
                                                {{ $lang['langTag'] }}
                                                @if ($lang['langTag'] == $languageConfig['default_language'])
                                                    <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('FsLang::panel.default_language') }}" data-bs-original-title="{{ __('FsLang::panel.default_language') }}" aria-label="{{ __('FsLang::panel.default_language') }}"></i>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $lang['langName'] }}
                                                @if ($lang['areaName'])
                                                    {{ '('.$lang['areaName'].')' }}
                                                @endif
                                            </td>
                                            <td>
                                                <textarea name="messages[{{ $lang['langTag'] }}]" class="form-control" rows="3"></textarea>
                                            </td>
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
@endsection
