@php
    $width = 720;
    $height = (int) round($width * (max(1, (int) ($template->export_ratio_height ?: 13)) / max(1, (int) ($template->export_ratio_width ?: 9))));
@endphp
<svg xmlns="http://www.w3.org/2000/svg" width="{{ $width }}" height="{{ $height }}" viewBox="0 0 {{ $width }} {{ $height }}" fill="none">
    <title>{{ $template->name }} snapshot</title>

    <rect width="{{ $width }}" height="{{ $height }}" fill="#FDF0D5" />

    @if ($backgroundHref)
        <image href="{{ $backgroundHref }}" x="0" y="0" width="{{ $width }}" height="{{ $height }}" preserveAspectRatio="xMidYMid slice" />
    @endif

    @foreach ($fields as $field)
        @php
            $value = $field->preview_sample_value ?: $field->default_value ?: $field->placeholder ?: $field->label;
            $x = $width * ((float) $field->position_x / 100);
            $y = $height * ((float) $field->position_y / 100);
            $fontSize = max(10, (int) $field->font_size_max);
            $anchor = $field->text_align === 'start' ? 'start' : ($field->text_align === 'end' ? 'end' : 'middle');
            $weight = data_get($field->settings, 'font_weight', '600');
            $transform = data_get($field->settings, 'text_transform', 'none');
            $text = match ($transform) {
                'uppercase' => mb_strtoupper($value),
                'lowercase' => mb_strtolower($value),
                'capitalize' => mb_convert_case($value, MB_CASE_TITLE),
                default => $value,
            };
        @endphp
        <text
            x="{{ $x }}"
            y="{{ $y }}"
            fill="{{ $field->text_color ?: '#780000' }}"
            font-family="{{ data_get($field->settings, 'font_family_override') ?: $fontFamily }}"
            font-size="{{ $fontSize }}"
            font-weight="{{ $weight }}"
            font-style="{{ data_get($field->settings, 'font_style', 'normal') }}"
            letter-spacing="{{ (float) $field->letter_spacing }}"
            text-anchor="{{ $anchor }}"
            dominant-baseline="middle"
            transform="rotate({{ (float) $field->rotation }}, {{ $x }}, {{ $y }})"
        >{{ $text }}</text>
    @endforeach
</svg>
