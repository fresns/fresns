@extends('FsView::commons.sidebarLayout')

@section('sidebar')
    @include('FsView::dashboard.sidebar')
@endsection

@section('content')
    <div class="row mb-4 border-bottom">
        <div class="col-lg-7">
            <h3>{{ __('FsLang::panel.sidebar_upgrades') }}</h3>
            <p class="text-secondary">{{ __('FsLang::panel.sidebar_upgrades_intro') }} {{ now() }}</p>
        </div>
        <div class="col-lg-5">
            <div class="input-group mt-2 mb-4 justify-content-lg-end">
                <button class="btn btn-primary" type="button"><i class="bi bi-arrow-clockwise"></i> {{ __('FsLang::panel.button_check_upgrade') }}</button>
                <a class="btn btn-outline-success" href="{{ route('panel.settings') }}" role="button">{{ __('FsLang::panel.setting_build_type') }}</a>
                <a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
            </div>
        </div>
    </div>

    <!--Fresns Core-->
    <div class="card mb-4">
        <div class="card-header">{{ __('FsLang::panel.fresns_core') }}</div>
        <div class="card-body">
            @if (($currentVersion['versionInt'] ?? 0 ) < ($newVersion['versionInt'] ?? 0))
                <h5 class="card-title">{{ __('FsLang::panel.upgrade_fresns') }}</h5>
                <p class="card-text">{{ __('FsLang::panel.upgrade_fresns_desc') }} v{{ $newVersion['version'] ?? ''}}</p>
                @if ($upgradeStep)
                    <button id="upgradeButton" type="button" class="btn btn-info" data-action="{{ route('panel.upgrade.info') }}" data-upgrading="1">
                        {{ __('FsLang::panel.upgrade_in_progress') }}
                    </button>
                @else
                    <button id="upgradeButton" type="button" class="btn btn-primary" data-action="{{ route('panel.upgrade.info') }}">
                        {{ __('FsLang::panel.button_upgrade') }}
                    </button>
                @endif
            @else
                <div class="p-5 text-center">
                    <i class="bi bi-view-list"></i> {{ __('FsLang::panel.upgrade_null') }}
                </div>
            @endif
        </div>
    </div>

    <!--Extensions-->
    <div class="row">
        <!--Plugins-->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">{{ __('FsLang::panel.extensions_plugins') }}</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <div class="p-5 text-center">
                            <i class="bi bi-view-list"></i> {{ __('FsLang::panel.upgrade_null') }}
                        </div>
                    </ul>
                </div>
            </div>
        </div>
        <!--Apps-->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">{{ __('FsLang::panel.extensions_apps') }}</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <div class="p-5 text-center">
                            <i class="bi bi-view-list"></i> {{ __('FsLang::panel.upgrade_null') }}
                        </div>
                    </ul>
                </div>
            </div>
        </div>
        <!--Engines-->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">{{ __('FsLang::panel.extensions_engines') }}</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <div class="p-5 text-center">
                            <i class="bi bi-view-list"></i> {{ __('FsLang::panel.upgrade_null') }}
                        </div>
                    </ul>
                </div>
            </div>
        </div>
        <!--Themes-->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">{{ __('FsLang::panel.extensions_themes') }}</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <div class="p-5 text-center">
                            <i class="bi bi-view-list"></i> {{ __('FsLang::panel.upgrade_null') }}
                        </div>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: upgrade confirmation -->
    <div class="modal fade" id="upgradeConfirm" tabindex="-1" aria-labelledby="upgrade" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <form method="post" action="{{ route('panel.upgrade') }}" id="upgradeForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Fresns</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('FsLang::panel.upgrade_confirm_desc') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('FsLang::panel.button_cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('FsLang::panel.button_confirm_upgrade') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: fresns upgrade -->
    <div class="modal fade" id="upgrade" tabindex="-1" aria-labelledby="upgrade" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-laptop"></i> {{ __('FsLang::panel.fresns_core') }}
                        <span class="badge bg-secondary">{{ $currentVersion['version'] ?? '' }}</span> to <span class="badge bg-danger">{{ $version['version'] ?? '' }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body ps-5">
                    @foreach($steps as $step => $description)
                        <p id="upgrade{{$step}}">
                        @if ($upgradeStep < $step)
                            <i class="bi bi-hourglass text-secondary me-2"></i>
                        @elseif($upgradeStep == $step)
                            <i class="spinner-border spinner-border-sm me-2" role="status"></i>
                        @else
                            <i class="bi bi-check-lg text-success me-2"></i>
                        @endif
                        {{$description}}
                        </p>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
