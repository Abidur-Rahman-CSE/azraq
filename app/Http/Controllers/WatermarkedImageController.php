<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WatermarkedImageController extends Controller
{
    public function show(Request $request)
    {
        abort_unless($request->hasValidSignature(), 403);

        $source = (string) $request->query('src', '');
        abort_if($source === '', 404);

        $bytes = $this->readImage($source);
        abort_unless($bytes, 404);

        $image = @imagecreatefromstring($bytes);
        abort_unless($image, 415);

        $width = imagesx($image);
        $height = imagesy($image);
        $canvas = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($canvas, 255, 255, 255);

        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);
        imagedestroy($image);

        $this->applyWatermark($canvas, $width, $height);

        ob_start();
        imagejpeg($canvas, null, 88);
        $output = ob_get_clean();
        imagedestroy($canvas);

        return response($output, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="azraq-watermarked.jpg"',
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function readImage(string $source): ?string
    {
        if (Str::startsWith($source, ['/storage/', '/images/'])) {
            $path = public_path(ltrim($source, '/'));

            return is_file($path) ? file_get_contents($path) : null;
        }

        $parts = parse_url($source);
        $path = $parts['path'] ?? '';

        if (($parts['host'] ?? null) === request()->getHost() && Str::startsWith($path, ['/storage/', '/images/'])) {
            $localPath = public_path(ltrim($path, '/'));

            return is_file($localPath) ? file_get_contents($localPath) : null;
        }

        if (! in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
            return null;
        }

        $response = Http::timeout(6)->get($source);

        return $response->successful() ? $response->body() : null;
    }

    private function applyWatermark($image, int $width, int $height): void
    {
        $text = 'AZRAQ BRIDAL';
        $dark = imagecolorallocatealpha($image, 0, 48, 73, 72);
        $red = imagecolorallocatealpha($image, 120, 0, 0, 78);
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $stepX = max(180, $textWidth + 80);
        $stepY = max(120, $textHeight + 90);

        for ($y = 24; $y < $height; $y += $stepY) {
            for ($x = 24; $x < $width; $x += $stepX) {
                imagestring($image, $font, $x, $y, $text, $dark);
                imagestring($image, 2, $x + 12, $y + 22, 'azraqbridal.com', $red);
            }
        }

        $corner = imagecolorallocatealpha($image, 255, 255, 255, 36);
        imagefilledrectangle($image, 12, $height - 48, min($width - 12, 172), $height - 12, $corner);
        imagestring($image, 4, 24, $height - 38, $text, $dark);
    }
}
