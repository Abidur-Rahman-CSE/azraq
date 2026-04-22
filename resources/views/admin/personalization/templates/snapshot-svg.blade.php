@php
    use App\Support\PersonalizationTextLayout;

    $width = 1440;
    $height = (int) round($width * (max(1, (int) ($template->export_ratio_height ?: 13)) / max(1, (int) ($template->export_ratio_width ?: 9))));
@endphp
<svg xmlns="http://www.w3.org/2000/svg" width="{{ $width }}" height="{{ $height }}" viewBox="0 0 {{ $width }} {{ $height }}" fill="none">
    <title>{{ $template->name }} snapshot</title>

    <defs>
        @foreach ($fields as $field)
            @php
                $value = $field->preview_sample_value ?: $field->default_value ?: $field->placeholder ?: $field->label;
                $layout = PersonalizationTextLayout::layout([
                    'position_x' => (float) $field->position_x,
                    'position_y' => (float) $field->position_y,
                    'width' => (float) $field->width,
                    'height' => (float) $field->height,
                    'font_size_min' => (int) $field->font_size_min,
                    'font_size_max' => (int) $field->font_size_max,
                    'line_height' => (float) $field->line_height,
                    'letter_spacing' => (float) $field->letter_spacing,
                    'text_align' => $field->text_align,
                    'settings' => $field->settings ?? [],
                ], $value, $width, $height);
            @endphp
            <clipPath id="snapshot-field-clip-{{ $loop->index }}">
                <rect
                    x="{{ $layout['box_left'] }}"
                    y="{{ $layout['box_top'] }}"
                    width="{{ $layout['box_width'] }}"
                    height="{{ $layout['box_height'] }}"
                    rx="4"
                />
            </clipPath>
        @endforeach
    </defs>

    <rect width="{{ $width }}" height="{{ $height }}" fill="#FDF0D5" />

    @if ($backgroundHref)
        <image href="{{ $backgroundHref }}" x="0" y="0" width="{{ $width }}" height="{{ $height }}" preserveAspectRatio="xMidYMid slice" />
    @endif

    @foreach ($fields as $field)
        @php
            $value = $field->preview_sample_value ?: $field->default_value ?: $field->placeholder ?: $field->label;
            $weight = data_get($field->settings, 'font_weight', '600');
            $layout = PersonalizationTextLayout::layout([
                'position_x' => (float) $field->position_x,
                'position_y' => (float) $field->position_y,
                'width' => (float) $field->width,
                'height' => (float) $field->height,
                'font_size_min' => (int) $field->font_size_min,
                'font_size_max' => (int) $field->font_size_max,
                'line_height' => (float) $field->line_height,
                'letter_spacing' => (float) $field->letter_spacing,
                'text_align' => $field->text_align,
                'settings' => $field->settings ?? [],
            ], $value, $width, $height);
        @endphp
        <g clip-path="url(#snapshot-field-clip-{{ $loop->index }})">
            <text
                x="{{ $layout['text_x'] }}"
                y="{{ $layout['text_y'] }}"
                fill="{{ $field->text_color ?: '#780000' }}"
                font-family="{{ data_get($field->settings, 'font_family_override') ?: $fontFamily }}"
                font-size="{{ $layout['font_size'] }}"
                font-weight="{{ $weight }}"
                font-style="{{ data_get($field->settings, 'font_style', 'normal') }}"
                letter-spacing="{{ (float) $field->letter_spacing }}"
                text-anchor="{{ $layout['text_anchor'] }}"
                transform="rotate({{ (float) $field->rotation }}, {{ $layout['x'] }}, {{ $layout['y'] }})"
            >
                @foreach ($layout['lines'] as $lineIndex => $line)
                    <tspan
                        x="{{ $layout['text_x'] }}"
                        @if ($lineIndex > 0)
                            dy="{{ $layout['font_size'] * $layout['line_height'] }}"
                        @endif
                    >{{ $line }}</tspan>
                @endforeach
            </text>
        </g>
    @endforeach
</svg>
