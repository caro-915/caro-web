@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6 md:py-8">
    <h1 class="text-xl font-bold mb-4">Mes messages</h1>

    @if($conversations->isEmpty())
        <p class="text-sm text-gray-500">Vous n’avez encore aucune conversation.</p>
    @else
        <div class="space-y-3">
            @foreach($conversations as $conv)
                @php
                    $isBuyer = auth()->id() === $conv->buyer_id;
                    $other   = $isBuyer ? $conv->seller : $conv->buyer;
                @endphp

                <a href="{{ route('messages.show', $conv) }}"
                   class="block bg-white rounded-2xl shadow px-4 py-3 hover:shadow-md transition">
                    <div class="flex justify-between items-center mb-1">
                        <p class="text-sm font-semibold">
                            {{ $conv->annonce->titre ?? 'Annonce #'.$conv->annonce->id }}
                        </p>
                        <p class="text-[11px] text-gray-400">
                            {{ $conv->last_message_at?->diffForHumans() }}
                        </p>
                    </div>
                    <p class="text-xs text-gray-500">
                        Avec : <span class="font-medium">{{ $other->name }}</span>
                    </p>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
