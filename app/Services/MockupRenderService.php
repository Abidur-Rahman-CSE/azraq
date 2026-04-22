<?php

namespace App\Services;

use Imagick;
use ImagickPixel;

class MockupRenderService
{
    public function renderFlatFromSvg(string $svg): Imagick
    {
        $image = new Imagick();
        $image->setBackgroundColor(new ImagickPixel('transparent'));
        $image->setResolution(300, 300);
        $image->readImageBlob($this->normalizeSvgAssetPaths($svg));
        $image->setImageFormat('png32');
        $image->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);

        return $image;
    }

    public function renderMockupProof(array $renderPreview, string $flatSvg): string
    {
        $mockup = data_get($renderPreview, 'mockup', []);
        $map = data_get($mockup, 'map', []);
        $baseImage = $this->readImage((string) data_get($mockup, 'base_image_url'));

        if (! $baseImage || ! is_array($map) || $map === []) {
            throw new \RuntimeException('Mockup render data is incomplete.');
        }

        $baseImage->setImageFormat('png32');
        $baseImage->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);

        $flatImage = $this->renderFlatFromSvg($flatSvg);
        $warped = clone $flatImage;
        $warped->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
        $warped->setImageBackgroundColor(new ImagickPixel('transparent'));
        $warped->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);

        $sourceWidth = $flatImage->getImageWidth();
        $sourceHeight = $flatImage->getImageHeight();
        $destinationWidth = $baseImage->getImageWidth();
        $destinationHeight = $baseImage->getImageHeight();

        $warped->distortImage(
            Imagick::DISTORTION_PERSPECTIVE,
            [
                0, 0, $map['top_left_x'] * $destinationWidth, $map['top_left_y'] * $destinationHeight,
                $sourceWidth, 0, $map['top_right_x'] * $destinationWidth, $map['top_right_y'] * $destinationHeight,
                $sourceWidth, $sourceHeight, $map['bottom_right_x'] * $destinationWidth, $map['bottom_right_y'] * $destinationHeight,
                0, $sourceHeight, $map['bottom_left_x'] * $destinationWidth, $map['bottom_left_y'] * $destinationHeight,
            ],
            true,
        );

        $composite = new Imagick();
        $composite->newImage($destinationWidth, $destinationHeight, new ImagickPixel('transparent'), 'png32');
        $composite->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);

        $page = $warped->getImagePage();
        $offsetX = (int) ($page['x'] ?? 0);
        $offsetY = (int) ($page['y'] ?? 0);
        $warped->setImagePage(0, 0, 0, 0);
        $composite->compositeImage($warped, Imagick::COMPOSITE_OVER, $offsetX, $offsetY);

        $baseImage->compositeImage($composite, Imagick::COMPOSITE_OVER, 0, 0);

        if ($overlay = $this->readImage((string) data_get($mockup, 'overlay_image_url'))) {
            $overlay->setImageFormat('png32');
            $this->applyOpacity($overlay, max(0.12, (float) data_get($map, 'highlight_strength', 0.12)));
            $baseImage->compositeImage($overlay, Imagick::COMPOSITE_OVER, 0, 0);
            $overlay->clear();
            $overlay->destroy();
        }

        if ($mask = $this->readImage((string) data_get($mockup, 'mask_image_url'))) {
            $mask->setImageFormat('png32');
            $this->applyOpacity($mask, max(0.12, (float) data_get($map, 'highlight_strength', 0.12) * 0.9));
            $baseImage->compositeImage($mask, Imagick::COMPOSITE_MULTIPLY, 0, 0);
            $mask->clear();
            $mask->destroy();
        }

        $baseImage->setImageFormat('png32');
        $baseImage->setImageCompressionQuality(100);
        $blob = $baseImage->getImagesBlob();

        $flatImage->clear();
        $flatImage->destroy();
        $warped->clear();
        $warped->destroy();
        $composite->clear();
        $composite->destroy();
        $baseImage->clear();
        $baseImage->destroy();

        return $blob;
    }

    private function readImage(?string $url): ?Imagick
    {
        if (! filled($url)) {
            return null;
        }

        $image = new Imagick();
        $localPath = $this->resolveLocalPath($url);

        if ($localPath && is_file($localPath)) {
            $image->readImage($localPath);

            return $image;
        }

        $image->readImage($url);

        return $image;
    }

    private function resolveLocalPath(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (! $path) {
            return null;
        }

        $storageRelativePath = ltrim(str_replace('/storage/', '', $path), '/');
        $candidates = [
            storage_path('app/public/'.$storageRelativePath),
            public_path(ltrim($path, '/')),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function normalizeSvgAssetPaths(string $svg): string
    {
        return preg_replace_callback('/\b(href|xlink:href)=["\']([^"\']+)["\']/', function (array $matches): string {
            $attribute = $matches[1];
            $value = $matches[2];

            if (
                str_starts_with($value, 'data:')
                || str_starts_with($value, 'http://')
                || str_starts_with($value, 'https://')
                || str_starts_with($value, 'file://')
            ) {
                return $matches[0];
            }

            $localPath = $this->resolveLocalPath($value);

            if (! $localPath) {
                return $matches[0];
            }

            return sprintf('%s="%s"', $attribute, 'file://'.$localPath);
        }, $svg) ?? $svg;
    }

    private function applyOpacity(Imagick $image, float $opacity): void
    {
        $image->evaluateImage(Imagick::EVALUATE_MULTIPLY, max(0, min(1, $opacity)), Imagick::CHANNEL_ALPHA);
    }
}
