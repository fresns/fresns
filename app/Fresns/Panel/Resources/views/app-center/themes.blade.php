@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::app-center.sidebar')
@endsection

@section('content')
    <!--header-->
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_themes') }}</h3>
            <p class="text-secondary"><i class="bi bi-palette"></i> {{ __('FsLang::panel.sidebar_themes_intro') }}</p>
        </div>
        <div class="col-lg-5 ">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <label class="input-group-text"><i class="bi bi-laptop me-1"></i> 桌面端</label>
                <button class="btn btn-outline-secondary border-secondary-subtle rounded-end dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ __('FsLang::panel.language_multilingual') }}
                </button>
                <ul class="dropdown-menu">
                    @foreach ($themes as $theme)
                        <li></li>
                    @endforeach
                </ul>

                <label class="input-group-text rounded-start ms-3"><i class="bi bi-phone me-1"></i> 手机端</label>
                <button class="btn btn-outline-secondary border-secondary-subtle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ __('FsLang::panel.language_multilingual') }}
                </button>
                <ul class="dropdown-menu">
                    <li>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!--list-->
    <div class="row">
        @foreach ($themes as $theme)
            <div class="col-sm-6 col-xl-3 mb-4">
                <div class="card">
                    <div class="position-relative">
                        <img src="/assets/{{ $theme->fskey }}/fresns.png" class="card-img-top">
                        @if ($theme->is_upgrade)
                            <div class="position-absolute top-0 start-100 translate-middle">
                                <a href="{{ route('panel.upgrades') }}"><span class="badge rounded-pill bg-danger">{{ __('FsLang::panel.new_version') }}</span></a>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <h5 class="text-nowrap overflow-hidden">
                            <a href="{{ $marketplaceUrl.'/detail/'.$theme->fskey }}" target="_blank" class="link-dark fresns-link">{{ $theme->name }}</a>
                            <span class="badge bg-secondary align-middle fs-9">{{ $theme->version }}</span>
                        </h5>
                        <p class="card-text text-height">{{ $theme->description }}</p>
                        <div>
                            @if ($theme->settings_path)
                                <a href="{{ route('panel.app-center.theme.functions', ['url' => route('panel.theme.functions', ['fskey' => $theme->fskey])]) }}" class="btn btn-primary btn-sm px-4">{{ __('FsLang::panel.button_setting') }}</a>
                            @endif
                            <button type="button" class="btn btn-link btn-sm ms-2 text-danger fresns-link" data-action="{{ route('panel.theme.uninstall', ['fskey' => $theme->fskey]) }}" data-name="{{ $theme->name }}" data-clear_data_desc="{{ __('FsLang::panel.option_uninstall_theme_data') }}" data-bs-toggle="modal" data-bs-target="#uninstallConfirm">{{ __('FsLang::panel.button_uninstall') }}</button>
                        </div>
                    </div>
                    <div class="card-footer fs-8">{{ __('FsLang::panel.author') }}: <a href="{{ $theme->author_link }}" target="_blank" class="link-info fresns-link">{{ $theme->author }}</a></div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
