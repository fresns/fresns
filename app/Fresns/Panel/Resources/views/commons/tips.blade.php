@if (session('success'))
    <div aria-live="polite" aria-atomic="true" class="position-fixed top-50 start-50 translate-middle" style="z-index:99">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <img src="/static/images/icon.png" width="20px" height="20px" class="rounded me-2" alt="Fresns">
                <strong class="me-auto">Fresns</strong>
                <small>200</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session('success') }}
            </div>
        </div>
    </div>
@elseif (session('failure'))
    <div aria-live="polite" aria-atomic="true" class="position-fixed top-50 start-50 translate-middle" style="z-index:99">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <img src="/static/images/icon.png" width="20px" height="20px" class="rounded me-2" alt="Fresns">
                <strong class="me-auto">Fresns</strong>
                <small>
                    @if (session('code'))
                        {{ session('code') }}
                    @endif
                </small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ session('failure') }}
            </div>
        </div>
    </div>
@elseif($errors->any())
    <div aria-live="polite" aria-atomic="true" class="position-fixed top-50 start-50 translate-middle" style="z-index:99">
        @foreach ($errors->all() as $error)
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <img src="/static/images/icon.png" width="20px" height="20px" class="rounded me-2" alt="Fresns">
                    <strong class="me-auto">Fresns</strong>
                    <small>400</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    {!! $error !!}
                </div>
            </div>
        @endforeach
    </div>
@endif
