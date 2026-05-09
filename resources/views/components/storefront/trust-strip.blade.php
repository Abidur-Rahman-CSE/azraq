@props(['signals'])

@php($signals = collect($signals)->filter(fn ($s) => filled(data_get($s, 'label'))))

@if ($signals->isNotEmpty())
    <div class="trust-strip">
        @foreach ($signals as $signal)
            <div class="trust-cell">
                <span class="trust-cell__icon">{{ $signal['icon'] ?? '◆' }}</span>
                <span class="trust-cell__label">{{ $signal['label'] }}</span>
            </div>
        @endforeach
    </div>
@endif
