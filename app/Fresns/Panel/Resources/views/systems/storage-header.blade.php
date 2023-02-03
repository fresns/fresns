<div class="row mb-4">
	<div class="col-lg-7">
		<h3>{{ __('FsLang::panel.sidebar_storage') }}</h3>
		<p class="text-secondary">{{ __('FsLang::panel.sidebar_storage_intro') }}</p>
	</div>
	<div class="col-lg-5">
		<div class="input-group mt-2 mb-4 justify-content-lg-end">
			<a class="btn btn-outline-secondary" href="#" role="button">{{ __('FsLang::panel.button_support') }}</a>
		</div>
	</div>
    <ul class="nav nav-tabs nav-fill">
        <li class="nav-item"><a class="nav-link {{ \Route::is('panel.storage.image.index') ? 'active' : '' }}" href="{{ route('panel.storage.image.index') }}">{{ __('FsLang::panel.sidebar_storage_tab_image') }}</a></li>
        <li class="nav-item"><a class="nav-link {{ \Route::is('panel.storage.video.index') ? 'active' : '' }}" href="{{ route('panel.storage.video.index') }}">{{ __('FsLang::panel.sidebar_storage_tab_video') }}</a></li>
        <li class="nav-item"><a class="nav-link {{ \Route::is('panel.storage.audio.index') ? 'active' : '' }}" href="{{ route('panel.storage.audio.index') }}">{{ __('FsLang::panel.sidebar_storage_tab_audio') }}</a></li>
        <li class="nav-item"><a class="nav-link {{ \Route::is('panel.storage.document.index') ? 'active' : '' }}" href="{{ route('panel.storage.document.index') }}">{{ __('FsLang::panel.sidebar_storage_tab_document') }}</a></li>
        <li class="nav-item"><a class="nav-link {{ \Route::is('panel.storage.substitution.index') ? 'active' : '' }}" href="{{ route('panel.storage.substitution.index') }}">{{ __('FsLang::panel.sidebar_storage_tab_substitution') }}</a></li>
    </ul>
</div>
