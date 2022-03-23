@extends('FsView::commons.layout')

@section('body')
    @include('FsView::commons.header')
    <main>
        <iframe src="{{ $url }}" width="100%" height="100%" class="iframe-preview"></iframe>
    </main>
@endsection
