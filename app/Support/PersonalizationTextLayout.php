<?php

namespace App\Support;

class PersonalizationTextLayout
{
    public static function layout(array $field, string $text, float $canvasWidth, float $canvasHeight): array
    {
        $settings = $field['settings'] ?? [];
        $boxWidth = max(24, $canvasWidth * ((float) ($field['width'] ?? 0) / 100));
        $boxHeight = max(18, $canvasHeight * ((float) ($field['height'] ?? 0) / 100));
        $zoneWidth = max(18, $boxWidth - 12);
        $zoneHeight = max(14, $boxHeight - 10);
        $x = $canvasWidth * ((float) ($field['x'] ?? $field['position_x'] ?? 50) / 100);
        $y = $canvasHeight * ((float) ($field['y'] ?? $field['position_y'] ?? 50) / 100);
        $minFont = max(8, (int) ($field['font_size_min'] ?? 12));
        $maxFont = max($minFont, (int) ($field['font_size_max'] ?? 24));
        $lineHeight = max(1, (float) ($field['line_height'] ?? 1.2));
        $letterSpacing = (float) ($field['letter_spacing'] ?? 0);
        $maxLines = max(1, (int) data_get($settings, 'max_lines', 3));
        $allowMultiline = (bool) data_get($settings, 'allow_multiline', true);
        $autoFit = (bool) data_get($settings, 'auto_fit', true);
        $overflowBehavior = (string) data_get($settings, 'overflow_behavior', 'shrink_then_wrap');
        $textTransform = (string) data_get($settings, 'text_transform', 'none');
        $value = self::applyTransform($text, $textTransform);

        if ($value === '') {
            return self::emptyLayout($x, $y, $boxWidth, $boxHeight);
        }

        $best = null;

        $evaluate = function (int $fontSize) use (
            $value,
            $zoneWidth,
            $zoneHeight,
            $lineHeight,
            $letterSpacing,
            $maxLines,
            $allowMultiline,
            $overflowBehavior
        ): array {
            $multiline = $allowMultiline && ! in_array($overflowBehavior, ['clip', 'shrink_only'], true);
            $layout = self::wrapText($value, $zoneWidth, $fontSize, $letterSpacing, $multiline);
            $lines = $overflowBehavior === 'clip' ? [$value] : $layout['lines'];
            $lineCount = count($lines);
            $totalHeight = $lineCount * ($fontSize * $lineHeight);

            return [
                'fits' => $layout['max_width'] <= $zoneWidth && $lineCount <= $maxLines && $totalHeight <= $zoneHeight,
                'font_size' => $fontSize,
                'lines' => array_slice($lines, 0, $maxLines),
                'line_count' => min($lineCount, $maxLines),
                'total_height' => min($totalHeight, $zoneHeight),
                'max_width' => $layout['max_width'],
            ];
        };

        if ($autoFit) {
            for ($fontSize = $maxFont; $fontSize >= $minFont; $fontSize -= 1) {
                $candidate = $evaluate($fontSize);

                if ($candidate['fits']) {
                    $best = $candidate;
                    break;
                }
            }
        }

        $best ??= $evaluate($minFont);

        $boxLeft = $x - ($boxWidth / 2);
        $boxTop = $y - ($boxHeight / 2);
        $textHeight = $best['line_count'] * ($best['font_size'] * $lineHeight);
        $startY = $boxTop + max(4, ($boxHeight - $textHeight) / 2) + ($best['font_size'] * 0.82);
        $align = (string) ($field['align'] ?? $field['text_align'] ?? 'center');
        $anchor = $align === 'start' ? 'start' : ($align === 'end' ? 'end' : 'middle');
        $textX = match ($anchor) {
            'start' => $boxLeft + 6,
            'end' => $boxLeft + $boxWidth - 6,
            default => $x,
        };

        return [
            'x' => $x,
            'y' => $y,
            'box_left' => $boxLeft,
            'box_top' => $boxTop,
            'box_width' => $boxWidth,
            'box_height' => $boxHeight,
            'font_size' => $best['font_size'],
            'line_height' => $lineHeight,
            'lines' => $best['lines'],
            'text_x' => $textX,
            'text_y' => $startY,
            'text_anchor' => $anchor,
        ];
    }

    private static function wrapText(string $text, float $maxWidth, int $fontSize, float $letterSpacing, bool $allowMultiline): array
    {
        if (! $allowMultiline) {
            return [
                'lines' => [$text],
                'max_width' => self::estimateWidth($text, $fontSize, $letterSpacing),
            ];
        }

        $paragraphs = preg_split('/\r\n|\r|\n/', $text) ?: [$text];
        $lines = [];

        foreach ($paragraphs as $paragraph) {
            $words = preg_split('/\s+/', trim($paragraph)) ?: [''];
            $words = array_values(array_filter($words, fn ($word) => $word !== ''));

            if ($words === []) {
                $lines[] = '';
                continue;
            }

            $current = '';

            foreach ($words as $word) {
                $next = $current === '' ? $word : $current.' '.$word;

                if (self::estimateWidth($next, $fontSize, $letterSpacing) <= $maxWidth || $current === '') {
                    $current = $next;
                    continue;
                }

                $lines[] = $current;
                $current = $word;
            }

            if ($current !== '') {
                $lines[] = $current;
            }
        }

        if ($lines === []) {
            $lines = [$text];
        }

        return [
            'lines' => $lines,
            'max_width' => max(array_map(fn ($line) => self::estimateWidth($line, $fontSize, $letterSpacing), $lines)),
        ];
    }

    private static function estimateWidth(string $text, int $fontSize, float $letterSpacing): float
    {
        $characters = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $width = 0;

        foreach ($characters as $index => $character) {
            $width += self::characterFactor($character) * $fontSize;

            if ($index < count($characters) - 1) {
                $width += $letterSpacing;
            }
        }

        return $width;
    }

    private static function characterFactor(string $character): float
    {
        if ($character === ' ') {
            return 0.32;
        }

        if (preg_match('/[ilI\.,\'`:;!\|]/u', $character)) {
            return 0.28;
        }

        if (preg_match('/[MW@#%&QGO0]/u', $character)) {
            return 0.82;
        }

        if (preg_match('/[\p{Arabic}]/u', $character)) {
            return 0.74;
        }

        return 0.56;
    }

    private static function applyTransform(string $text, string $transform): string
    {
        return match ($transform) {
            'uppercase' => mb_strtoupper($text),
            'lowercase' => mb_strtolower($text),
            'capitalize' => mb_convert_case($text, MB_CASE_TITLE),
            default => $text,
        };
    }

    private static function emptyLayout(float $x, float $y, float $boxWidth, float $boxHeight): array
    {
        return [
            'x' => $x,
            'y' => $y,
            'box_left' => $x - ($boxWidth / 2),
            'box_top' => $y - ($boxHeight / 2),
            'box_width' => $boxWidth,
            'box_height' => $boxHeight,
            'font_size' => 12,
            'line_height' => 1.2,
            'lines' => [],
            'text_x' => $x,
            'text_y' => $y,
            'text_anchor' => 'middle',
        ];
    }
}
