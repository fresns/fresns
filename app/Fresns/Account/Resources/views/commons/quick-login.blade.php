@if ($connectServices)
    <div class="d-grid gap-2">
        @foreach($connectServices as $item)
            <button class="btn btn-outline-dark rounded-pill mt-2" type="button" data-bs-toggle="modal" data-bs-target="#fresnsModal"
                data-title="{{ $fsLang['accountLogin'] }}"
                data-url="{{ $item['url'] }}"
                data-connect-platform-id="{{ $item['code'] }}"
                data-redirect-url="{{ urlencode(route('account-center.user-auth', ['loginToken' => '{loginToken}'])) }}"
                data-post-message-key="{{ $postMessageKey }}">
                @if ($item['icon']) <img src="{{ $item['icon'] }}" height="20"> @endif
                {{ $item['name'] }}
            </button>
        @endforeach
    </div>

    @if ($emailConfig || $phoneConfig)
        <div class="text-center my-4">
            <span class="badge text-bg-secondary">{{ $fsLang['modifierOr'] }}</span>
        </div>
    @endif
@endif
