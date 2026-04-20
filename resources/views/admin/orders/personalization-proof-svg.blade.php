@php
    $template = data_get($renderPreview, 'template', []);
    $flat = data_get($renderPreview, 'flat', []);
    $mockup = data_get($renderPreview, 'mockup', []);
    $layers = collect(data_get($flat, 'text_layers', []));
    $fontFamily = data_get($renderPreview, 'font.css_font_family', 'Poppins, sans-serif');

    if ($mode === 'flat') {
        $width = 900;
        $height = (int) round($width * (max(1, (int) data_get($template, 'ratio_height', 13)) / max(1, (int) data_get($template, 'ratio_width', 9))));
    } else {
        $width = 1600;
        $height = 1200;
    }

    $map = data_get($mockup, 'map', []);
    $bounds = null;
    $polygon = null;

    if ($mode === 'mockup' && is_array($map) && $map !== []) {
        $xValues = [data_get($map, 'top_left_x'), data_get($map, 'top_right_x'), data_get($map, 'bottom_right_x'), data_get($map, 'bottom_left_x')];
        $yValues = [data_get($map, 'top_left_y'), data_get($map, 'top_right_y'), data_get($map, 'bottom_right_y'), data_get($map, 'bottom_left_y')];
        $minX = min($xValues);
        $maxX = max($xValues);
        $minY = min($yValues);
        $maxY = max($yValues);
        $bounds = [
            'left' => $minX * $width,
            'top' => $minY * $height,
            'width' => max(120, ($maxX - $minX) * $width),
            'height' => max(120, ($maxY - $minY) * $height),
        ];
        $polygon = collect([
            [data_get($map, 'top_left_x') * $width, data_get($map, 'top_left_y') * $height],
            [data_get($map, 'top_right_x') * $width, data_get($map, 'top_right_y') * $height],
            [data_get($map, 'bottom_right_x') * $width, data_get($map, 'bottom_right_y') * $height],
            [data_get($map, 'bottom_left_x') * $width, data_get($map, 'bottom_left_y') * $height],
        ])->map(fn ($point) => $point[0].','.$point[1])->implode(' ');
    }
@endphp
<svg xmlns="http://www.w3.org/2000/svg" width="{{ $width }}" height="{{ $height }}" viewBox="0 0 {{ $width }} {{ $height }}" fill="none">
    <title>{{ $mode === 'mockup' ? data_get($mockup, 'title', $item->product_name.' mockup proof') : ($item->product_name.' flat proof') }}</title>
    <defs>
        @if ($mode === 'mockup' && $polygon)
            <clipPath id="proof-area">
                <polygon points="{{ $polygon }}" />
            </clipPath>
        @endif
    </defs>

    <rect width="{{ $width }}" height="{{ $height }}" fill="#F7F1E5" />

    @if ($mode === 'flat')
        @if (data_get($flat, 'image_url'))
            <image href="{{ data_get($flat, 'image_url') }}" x="0" y="0" width="{{ $width }}" height="{{ $height }}" preserveAspectRatio="xMidYMid slice" />
        @endif

        @foreach ($layers as $layer)
            @php
                $x = ($width * ((float) data_get($layer, 'x', 50) / 100));
                $y = ($height * ((float) data_get($layer, 'y', 50) / 100));
                $size = max(12, (int) data_get($layer, 'font_size_max', 24));
                $anchor = data_get($layer, 'align') === 'start' ? 'start' : (data_get($layer, 'align') === 'end' ? 'end' : 'middle');
            @endphp
            <text
                x="{{ $x }}"
                y="{{ $y }}"
                fill="{{ data_get($layer, 'color', '#780000') }}"
                font-family="{{ $fontFamily }}"
                font-size="{{ $size }}"
                letter-spacing="{{ data_get($layer, 'letter_spacing', 0) }}"
                text-anchor="{{ $anchor }}"
                dominant-baseline="middle"
                transform="rotate({{ data_get($layer, 'rotation', 0) }}, {{ $x }}, {{ $y }})"
            >{{ data_get($layer, 'value') }}</text>
        @endforeach
    @else
        @if (data_get($mockup, 'base_image_url'))
            <image href="{{ data_get($mockup, 'base_image_url') }}" x="0" y="0" width="{{ $width }}" height="{{ $height }}" preserveAspectRatio="xMidYMid slice" />
        @endif

        @if ($bounds && $polygon)
            <g clip-path="url(#proof-area)">
                @if (data_get($flat, 'image_url'))
                    <image
                        href="{{ data_get($flat, 'image_url') }}"
                        x="{{ $bounds['left'] }}"
                        y="{{ $bounds['top'] }}"
                        width="{{ $bounds['width'] }}"
                        height="{{ $bounds['height'] }}"
                        preserveAspectRatio="none"
                    />
                @else
                    <rect x="{{ $bounds['left'] }}" y="{{ $bounds['top'] }}" width="{{ $bounds['width'] }}" height="{{ $bounds['height'] }}" fill="#FFFFFF" />
                @endif

                @foreach ($layers as $layer)
                    @php
                        $x = $bounds['left'] + ($bounds['width'] * ((float) data_get($layer, 'x', 50) / 100));
                        $y = $bounds['top'] + ($bounds['height'] * ((float) data_get($layer, 'y', 50) / 100));
                        $size = max(10, (int) round(($bounds['width'] / 900) * (int) data_get($layer, 'font_size_max', 24)));
                        $anchor = data_get($layer, 'align') === 'start' ? 'start' : (data_get($layer, 'align') === 'end' ? 'end' : 'middle');
                    @endphp
                    <text
                        x="{{ $x }}"
                        y="{{ $y }}"
                        fill="{{ data_get($layer, 'color', '#780000') }}"
                        font-family="{{ $fontFamily }}"
                        font-size="{{ $size }}"
                        letter-spacing="{{ data_get($layer, 'letter_spacing', 0) }}"
                        text-anchor="{{ $anchor }}"
                        dominant-baseline="middle"
                        transform="rotate({{ data_get($layer, 'rotation', 0) }}, {{ $x }}, {{ $y }})"
                    >{{ data_get($layer, 'value') }}</text>
                @endforeach
            </g>
        @endif

        @if (data_get($mockup, 'overlay_image_url'))
            <image href="{{ data_get($mockup, 'overlay_image_url') }}" x="0" y="0" width="{{ $width }}" height="{{ $height }}" preserveAspectRatio="xMidYMid slice" opacity="{{ max(0.12, (float) data_get($map, 'highlight_strength', 0.12)) }}" />
        @endif

        @if (data_get($mockup, 'mask_image_url'))
            <image href="{{ data_get($mockup, 'mask_image_url') }}" x="0" y="0" width="{{ $width }}" height="{{ $height }}" preserveAspectRatio="xMidYMid slice" opacity="{{ max(0.12, (float) data_get($map, 'highlight_strength', 0.12) * 0.9) }}" />
        @endif
    @endif
</svg>
