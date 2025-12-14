@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6 md:py-8">
    <a href="{{ route('messages.index') }}" class="text-xs text-gray-500 hover:underline">
        ← Retour à mes messages
    </a>

    <h1 class="text-lg font-bold mt-2">
        Conversation – {{ $conversation->annonce->titre ?? 'Annonce #'.$conversation->annonce->id }}
    </h1>

    @php
        $isBuyer = auth()->id() === $conversation->buyer_id;
        $other   = $isBuyer ? $conversation->seller : $conversation->buyer;
        $lastMessageId = optional($messages->last())->id;
    @endphp

    <p class="text-xs text-gray-500 mb-4">
        Avec <span class="font-medium">{{ $other->name }}</span>
    </p>

    {{-- Messages --}}
    <div
        id="messages-container"
        class="bg-white rounded-2xl shadow p-4 mb-4 max-h-[500px] overflow-y-auto space-y-3"
        data-conversation-id="{{ $conversation->id }}"
        data-last-message-id="{{ $lastMessageId }}"
        data-fetch-url="{{ route('messages.new', $conversation) }}"
        data-auth-user-id="{{ auth()->id() }}"
    >
        @foreach($messages as $message)
            @php
                $mine = $message->sender_id === auth()->id();
            @endphp

            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[70%] rounded-2xl px-3 py-2 text-sm
                    {{ $mine ? 'bg-pink-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                    {{ $message->body }}

                    <p class="mt-1 text-[10px] opacity-70 text-right">
                        {{ $message->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Formulaire d'envoi --}}
    <div class="bg-white rounded-2xl shadow p-4">
        <form method="POST" action="{{ route('messages.store', $conversation) }}">
            @csrf
            <textarea name="body" rows="3"
                      class="w-full border rounded-xl px-3 py-2 text-sm focus:ring-pink-500 focus:border-pink-500"
                      placeholder="Votre message..." required></textarea>

            <button type="submit"
                    class="mt-3 inline-flex items-center justify-center px-4 py-2 rounded-full bg-pink-600 text-white text-sm font-semibold hover:bg-pink-700">
                Envoyer
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('messages-container');
    if (!container) return;

    const fetchUrlBase = container.dataset.fetchUrl;
    const authUserId = parseInt(container.dataset.authUserId, 10);

    let lastMessageId = container.dataset.lastMessageId
        ? parseInt(container.dataset.lastMessageId, 10)
        : null;

    function scrollToBottom() {
        container.scrollTop = container.scrollHeight;
    }

    function buildMessageElement(msg) {
        const isMine = (msg.sender_id === authUserId);

        const wrapper = document.createElement('div');
        wrapper.className = 'flex ' + (isMine ? 'justify-end' : 'justify-start');

        const bubble = document.createElement('div');
        bubble.className = 'max-w-[70%] rounded-2xl px-3 py-2 text-sm ' +
            (isMine ? 'bg-pink-600 text-white' : 'bg-gray-100 text-gray-800');

        // Sécurité XSS : textContent
        const bodyP = document.createElement('p');
        bodyP.textContent = msg.body ?? '';
        bubble.appendChild(bodyP);

        const meta = document.createElement('p');
        meta.className = 'mt-1 text-[10px] opacity-70 text-right';
        meta.textContent = msg.created_at_human ?? '';
        bubble.appendChild(meta);

        wrapper.appendChild(bubble);
        return wrapper;
    }

    function fetchNewMessages() {
        let url = fetchUrlBase;
        if (lastMessageId) {
            url += (url.includes('?') ? '&' : '?') + 'last_id=' + encodeURIComponent(lastMessageId);
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            if (!data.messages || !data.messages.length) return;

            data.messages.forEach(msg => {
                const el = buildMessageElement(msg);
                container.appendChild(el);
                lastMessageId = msg.id;
            });

            container.dataset.lastMessageId = lastMessageId;
            scrollToBottom();
        })
        .catch(err => {
            console.error('Polling messages.new failed:', err);
        });
    }

    // Initial: aller en bas
    scrollToBottom();

    // Polling toutes les 5 secondes
    setInterval(fetchNewMessages, 5000);
});
</script>
@endpush
