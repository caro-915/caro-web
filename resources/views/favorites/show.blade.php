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
    @endphp

    <p class="text-xs text-gray-500 mb-4">
        Avec <span class="font-medium">{{ $other->name }}</span>
    </p>

    {{-- Messages --}}
    <div class="bg-white rounded-2xl shadow p-4 mb-4 max-h-[500px] overflow-y-auto space-y-3">
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
