@extends('filament::page')

@section('content')
    <h1 class="text-2xl font-bold mb-4">{{ $this->record->titre }}</h1>

    <div class="prose max-w-none">
        {!! \Illuminate\Support\Str::markdown($this->record->contenu ?? '') !!}
    </div>

    @if(!empty($this->record->deftube_url))
        <p class="mt-4"><a href="{{ $this->record->deftube_url }}" target="_blank" rel="noopener" class="underline">Voir sur Deftube</a></p>
    @endif

    @if(!empty($this->record->liens) && is_array($this->record->liens))
        <div class="mt-4">
            @foreach($this->record->liens as $link)
                <div><a href="{{ $link['url'] ?? '#' }}" target="_blank" rel="noopener">{{ $link['label'] ?? $link['url'] }}</a></div>
            @endforeach
        </div>
    @endif
@endsection
