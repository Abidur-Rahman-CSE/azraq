@props(['posts'])

@php($posts = collect($posts)->filter(fn ($p) => filled(data_get($p, 'image_url'))))

@if ($posts->isNotEmpty())
    <div class="insta-strip">
        @foreach ($posts as $post)
            <a href="{{ $post['href'] ?: '#' }}" class="insta-strip__item" target="_blank" rel="noopener noreferrer" aria-label="View on Instagram">
                <img src="{{ $post['image_url'] }}" alt="" loading="lazy" decoding="async">
            </a>
        @endforeach
    </div>
@endif
