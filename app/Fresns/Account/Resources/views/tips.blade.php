@extends('FsAccountView::layout')

@section('title', $fsLang['accountCenter'])

@section('body')
    <div aria-live="polite" aria-atomic="true" class="position-fixed top-50 start-50 translate-middle">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <img src="{{ $siteIcon }}" width="20px" height="20px" class="rounded me-2" alt="{{ $siteName }}">
                <strong class="me-auto">{{ $siteName }}</strong>
                <small>{{ $code }}</small>
            </div>
            <div class="toast-body">
                {{ $message }}
            </div>
        </div>
    </div>
@endsection
