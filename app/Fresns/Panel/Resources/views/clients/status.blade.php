@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--app header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_client_status') }}</h3>
            <p class="text-secondary"><i class="bi bi-power"></i> {{ __('FsLang::panel.sidebar_client_status_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>

    <!--status conifg-->
    <form action="{{ route('panel.client.status.update') }}" method="post">
        @csrf
        @method('put')

        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.table_status') }}:</label>
            <div class="col-lg-5">
                <div class="form-control bg-white">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="activate" id="activate_true" value="1" @if($statusJson['activate'] ?? true) checked @endif>
                        <label class="form-check-label" for="activate_true">{{ __('FsLang::panel.option_activate') }}</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="activate" id="activate_false" value="0" @if(!$statusJson['activate'] ?? true) checked @endif>
                        <label class="form-check-label" for="activate_false">{{ __('FsLang::panel.option_deactivate') }}</label>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 form-text pt-1"><i class="bi bi-info-circle"></i> {{ __('FsLang::panel.client_status_desc') }}</div>
        </div>

        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.option_deactivate').' - '.__('FsLang::panel.table_description') }}:</label>
            <div class="col-lg-9 pt-2 description-textarea">
                <button type="button" class="btn btn-link btn-sm fs-7" id="addDescription"><i class="bi bi-plus-circle-dotted"></i> {{ __('FsLang::panel.button_add') }}</button>

                @foreach ($statusJson['deactivateDescription'] ?? [] as $key => $description)
                    @if ($key == 'default')
                        @continue
                    @endif

                    <div class="input-group mt-2">
                        <select class="form-select" name="descriptionLangTag[]">
                            @foreach ($optionalLanguages as $lang)
                                <option value="{{ $lang['langTag'] }}" {{ $lang['langTag'] == $key ? 'selected' : '' }}>
                                    {{ $lang['langName'] }}
                                    @if ($lang['areaName'])
                                        {{ '('.$lang['areaName'].')' }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <textarea class="form-control w-50" rows="3" name="descriptionLangContent[]">{{ $description }}</textarea>
                        <button class="btn btn-outline-secondary fs-delete" type="button"><i class="bi bi-trash3"></i></button>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">Mobile:</label>
            <div class="col-lg-9 pt-2">
                {{-- iOS --}}
                <div class="alert alert-light py-2" role="alert">
                    <span class="badge bg-secondary mb-2">iOS</span>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Version</span>
                        <input type="text" class="form-control" name="client[mobile][ios][version]" value="{{ $client['mobile']['ios']['version'] ?? '' }}" placeholder="1.0.0">
                    </div>
                    <div class="input-group">
                        <span class="input-group-text w-25">App Store</span>
                        <input type="text" class="form-control" name="client[mobile][ios][appStore]" value="{{ $client['mobile']['ios']['appStore'] ?? '' }}" placeholder="https://">
                    </div>
                </div>
                {{-- Android --}}
                <div class="alert alert-light py-2" role="alert">
                    <span class="badge bg-secondary mb-2">Android</span>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Version</span>
                        <input type="text" class="form-control" name="client[mobile][android][version]" value="{{ $client['mobile']['android']['version'] ?? '' }}" placeholder="1.0.0">
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Google Play</span>
                        <input type="text" class="form-control" name="client[mobile][android][googlePlay]" value="{{ $client['mobile']['android']['googlePlay'] ?? '' }}" placeholder="https://">
                    </div>
                    <div class="input-group">
                        <span class="input-group-text w-25">APK</span>
                        <input type="text" class="form-control" name="client[mobile][android][downloads][apk]" value="{{ $client['mobile']['android']['downloads']['apk'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.apk</span>
                    </div>
                </div>
                {{-- end --}}
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">Tablet:</label>
            <div class="col-lg-9 pt-2">
                {{-- iOS --}}
                <div class="alert alert-light py-2" role="alert">
                    <span class="badge bg-secondary mb-2">iOS</span>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Version</span>
                        <input type="text" class="form-control" name="client[tablet][ios][version]" value="{{ $client['tablet']['ios']['version'] ?? '' }}" placeholder="1.0.0">
                    </div>
                    <div class="input-group">
                        <span class="input-group-text w-25">App Store</span>
                        <input type="text" class="form-control" name="client[tablet][ios][appStore]" value="{{ $client['tablet']['ios']['appStore'] ?? '' }}" placeholder="https://">
                    </div>
                </div>
                {{-- Android --}}
                <div class="alert alert-light py-2" role="alert">
                    <span class="badge bg-secondary mb-2">Android</span>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Version</span>
                        <input type="text" class="form-control" name="client[tablet][android][version]" value="{{ $client['tablet']['android']['version'] ?? '' }}" placeholder="1.0.0">
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Google Play</span>
                        <input type="text" class="form-control" name="client[tablet][android][googlePlay]" value="{{ $client['tablet']['android']['googlePlay'] ?? '' }}" placeholder="https://">
                    </div>
                    <div class="input-group">
                        <span class="input-group-text w-25">APK</span>
                        <input type="text" class="form-control" name="client[tablet][android][downloads][apk]" value="{{ $client['tablet']['android']['downloads']['apk'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.apk</span>
                    </div>
                </div>
                {{-- end --}}
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-lg-2 col-form-label text-lg-end">Desktop:</label>
            <div class="col-lg-9 pt-2">
                {{-- macOS --}}
                <div class="alert alert-light py-2" role="alert">
                    <span class="badge bg-secondary mb-2">macOS</span>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Version</span>
                        <input type="text" class="form-control" name="client[desktop][macos][version]" value="{{ $client['desktop']['macos']['version'] ?? '' }}" placeholder="1.0.0">
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">App Store</span>
                        <input type="text" class="form-control" name="client[desktop][macos][appStore]" value="{{ $client['desktop']['macos']['appStore'] ?? '' }}" placeholder="https://">
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Intel</span>
                        <input type="text" class="form-control" name="client[desktop][macos][downloads][intel]" value="{{ $client['desktop']['macos']['downloads']['intel'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.dmg</span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text w-25">Apple Silicon</span>
                        <input type="text" class="form-control" name="client[desktop][macos][downloads][appleSilicon]" value="{{ $client['desktop']['macos']['downloads']['appleSilicon'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.dmg</span>
                    </div>
                </div>
                {{-- Windows --}}
                <div class="alert alert-light py-2" role="alert">
                    <span class="badge bg-secondary mb-2">Windows</span>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Version</span>
                        <input type="text" class="form-control" name="client[desktop][windows][version]" value="{{ $client['desktop']['windows']['version'] ?? '' }}" placeholder="1.0.0">
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">x86</span>
                        <input type="text" class="form-control" name="client[desktop][windows][downloads][x86]" value="{{ $client['desktop']['windows']['downloads']['x86'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.exe</span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text w-25">x64</span>
                        <input type="text" class="form-control" name="client[desktop][windows][downloads][x64]" value="{{ $client['desktop']['windows']['downloads']['x64'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.exe</span>
                    </div>
                </div>
                {{-- Linux --}}
                <div class="alert alert-light py-2" role="alert">
                    <span class="badge bg-secondary mb-2">Linux</span>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">Version</span>
                        <input type="text" class="form-control" name="client[desktop][linux][version]" value="{{ $client['desktop']['linux']['version'] ?? '' }}" placeholder="1.0.0">
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">X86 deb</span>
                        <input type="text" class="form-control" name="client[desktop][linux][downloads][x86Deb]" value="{{ $client['desktop']['linux']['downloads']['x86Deb'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.deb</span>
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">X86 rpm</span>
                        <input type="text" class="form-control" name="client[desktop][linux][downloads][x86Rpm]" value="{{ $client['desktop']['linux']['downloads']['x86Rpm'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.rpm</span>
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">X86 AppImage</span>
                        <input type="text" class="form-control" name="client[desktop][linux][downloads][x86AppImage]" value="{{ $client['desktop']['linux']['downloads']['x86AppImage'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.AppImage</span>
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">ARM deb</span>
                        <input type="text" class="form-control" name="client[desktop][linux][downloads][armDeb]" value="{{ $client['desktop']['linux']['downloads']['armDeb'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.deb</span>
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">ARM rpm</span>
                        <input type="text" class="form-control" name="client[desktop][linux][downloads][armRpm]" value="{{ $client['desktop']['linux']['downloads']['armRpm'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.rpm</span>
                    </div>
                    <div class="input-group mb-1">
                        <span class="input-group-text w-25">ARM AppImage</span>
                        <input type="text" class="form-control" name="client[desktop][linux][downloads][armAppImage]" value="{{ $client['desktop']['linux']['downloads']['armAppImage'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.AppImage</span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text w-25">MIPS deb</span>
                        <input type="text" class="form-control" name="client[desktop][linux][downloads][mipsDeb]" value="{{ $client['desktop']['linux']['downloads']['mipsDeb'] ?? '' }}" placeholder="https://">
                        <span class="input-group-text">.deb</span>
                    </div>
                </div>
                {{-- end --}}
            </div>
        </div>

        <!--button_save-->
        <div class="row my-4">
            <div class="col-lg-2"></div>
            <div class="col-lg-8">
                <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_save') }}</button>
            </div>
        </div>
    </form>

    <template id="descriptionTemplate">
        <div class="input-group mt-2">
            <select class="form-select" name="descriptionLangTag[]">
                @foreach ($optionalLanguages as $lang)
                    <option value="{{ $lang['langTag'] }}">
                        {{ $lang['langName'] }}
                        @if ($lang['areaName'])
                            {{ '('.$lang['areaName'].')' }}
                        @endif
                    </option>
                @endforeach
            </select>
            <textarea class="form-control w-50" rows="3" name="descriptionLangContent[]" required></textarea>
            <button class="btn btn-outline-secondary fs-delete" type="button"><i class="bi bi-trash3"></i></button>
        </div>
    </template>
@endsection

@push('script')
    <script>
        $('#addDescription').click(function () {
            let template = $('#descriptionTemplate');
            $('.description-textarea').append(template.html());
        });

        $(document).on('click', '.fs-delete', function () {
            $(this).parent().remove();
        });
    </script>
@endpush
