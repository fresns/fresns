@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::clients.sidebar')
@endsection

@section('content')
    <!--app header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_status') }}</h3>
            <p class="text-secondary"><i class="bi bi-power"></i> {{ __('FsLang::panel.sidebar_status_intro') }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>

    <!--status conifg-->
    <form action="{{ route('panel.status.update') }}" method="post">
        @csrf
        @method('put')

        <div class="row mb-4">
            <label class="col-lg-2 col-form-label text-lg-end">{{ __('FsLang::panel.table_status') }}:</label>
            <div class="col-lg-9 pt-2">
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

        <!--button_save-->
        <div class="row my-3">
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
