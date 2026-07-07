<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Models\Faq;
use App\Models\Product;
use App\Models\Setting;
use App\Services\MockupRenderService;
use App\Support\ComboPricing;
use App\Support\MockupZoneNormalizer;
use App\Support\NikahRenderPreview;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ProductDetailController extends Controller
{
    public function show(Request $request, Product $product)
    {
        $commonRelations = [
            'category',
            'tags',
            'images',
            'variants',
            'reviews' => fn ($query) => $query->where('is_approved', true)->latest()->limit(4),
            'relatedProducts.category',
            'relatedProducts.tags',
            'relatedProducts.images',
            'relatedProducts.personalizationTemplate',
            'relatedProducts.personalizationMockups.map',
            'relatedCategories',
        ];

        $typeRelations = match ($product->type) {
            ProductType::AdvancedPersonalized => [
                'personalizationTemplate.fields',
                'personalizationTemplate.fonts',
                'personalizationMockups.map',
                'personalizationMockups.template',
            ],
            ProductType::Bundle => [
                'bundleItems.childProduct.category',
                'bundleItems.childProduct.tags',
                'bundleItems.childProduct.images',
                'bundleItems.childProduct.variants',
                'bundleItems.defaultVariant',
            ],
            ProductType::Service => [
                'serviceMeta',
            ],
            default => [],
        };

        $product->load([...$commonRelations, ...$typeRelations]);

        $recentlyViewed = collect($request->session()->get('recently_viewed_products', []))
            ->reject(fn (int $id) => $id === $product->id)
            ->take(4)
            ->whenNotEmpty(fn ($ids) => Product::with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])->whereIn('id', $ids)->get()->sortBy(fn ($item) => array_search($item->id, $ids->all())))
            ->values();

        $request->session()->put('recently_viewed_products', collect([$product->id])
            ->merge($request->session()->get('recently_viewed_products', []))
            ->unique()
            ->take(8)
            ->values()
            ->all());

        $policyRows = $this->policyRowsForProduct($product);
        $faqs = $this->faqsForProduct($product);

        if ($product->type === ProductType::AdvancedPersonalized && $product->personalizationTemplate) {
            $template = $product->personalizationTemplate;
            $mockups = $product->include_mockup_gallery
                ? $product->personalizationMockups
                    ->where('is_active', true)
                    ->values()
                : collect();
            $defaultMockupId = $mockups->firstWhere('pivot.is_default', true)?->id
                ?? $mockups->first()?->id;

            return view('products.show', [
                'product' => $product,
                'template' => $template,
                'fonts' => $template->fonts
                    ->where('is_active', true)
                    ->sortBy('sort_order')
                    ->values(),
                'mockups' => $mockups->map(function ($mockup) {
                    $map = $mockup->map;
                    $normalizedMap = MockupZoneNormalizer::toImageSpace($mockup, $map);
                    $mapPayload = $map ? [
                        'top_left_x' => (float) ($normalizedMap['top_left_x'] ?? 0.2),
                        'top_left_y' => (float) ($normalizedMap['top_left_y'] ?? 0.18),
                        'top_right_x' => (float) ($normalizedMap['top_right_x'] ?? 0.8),
                        'top_right_y' => (float) ($normalizedMap['top_right_y'] ?? 0.18),
                        'bottom_right_x' => (float) ($normalizedMap['bottom_right_x'] ?? 0.8),
                        'bottom_right_y' => (float) ($normalizedMap['bottom_right_y'] ?? 0.82),
                        'bottom_left_x' => (float) ($normalizedMap['bottom_left_x'] ?? 0.2),
                        'bottom_left_y' => (float) ($normalizedMap['bottom_left_y'] ?? 0.82),
                        'opacity' => (float) ($map->opacity ?? 0.95),
                        'highlight_strength' => (float) ($map->highlight_strength ?? 0.12),
                        'manual_rotation' => (float) ($map->manual_rotation ?? 0),
                        'fit_mode' => $map->fit_mode ?? 'stretch',
                    ] : null;

                    return [
                        'id' => $mockup->id,
                        'name' => $mockup->title,
                        'title' => $mockup->title,
                        'thumbnail_url' => $mockup->thumb_image_url ?: $mockup->base_image_url,
                        'thumb_image_url' => $mockup->thumb_image_url ?: $mockup->base_image_url,
                        'image_url' => $mockup->base_image_url,
                        'base_image_url' => $mockup->base_image_url,
                        'overlay_url' => $mockup->overlay_image_url ?: $mockup->mask_image_url,
                        'overlay_image_url' => $mockup->overlay_image_url,
                        'mask_url' => $mockup->mask_image_url,
                        'mask_image_url' => $mockup->mask_image_url,
                        'render_mode' => $mockup->render_mode,
                        'template_name' => $mockup->template?->name,
                        'is_default' => (bool) ($mockup->pivot?->is_default ?? false),
                        'sort_order' => (int) ($mockup->pivot?->sort_order ?? $mockup->sort_order ?? 0),
                        'map' => $mapPayload,
                        'zone_points' => [
                            'tl' => [
                                'x' => (float) ($normalizedMap['top_left_x'] ?? 0.2),
                                'y' => (float) ($normalizedMap['top_left_y'] ?? 0.18),
                            ],
                            'tr' => [
                                'x' => (float) ($normalizedMap['top_right_x'] ?? 0.8),
                                'y' => (float) ($normalizedMap['top_right_y'] ?? 0.18),
                            ],
                            'br' => [
                                'x' => (float) ($normalizedMap['bottom_right_x'] ?? 0.8),
                                'y' => (float) ($normalizedMap['bottom_right_y'] ?? 0.82),
                            ],
                            'bl' => [
                                'x' => (float) ($normalizedMap['bottom_left_x'] ?? 0.2),
                                'y' => (float) ($normalizedMap['bottom_left_y'] ?? 0.82),
                            ],
                        ],
                        'image_width' => (int) ($mockup->image_width ?: 1600),
                        'image_height' => (int) ($mockup->image_height ?: 1200),
                        'opacity' => (float) ($map?->opacity ?? 0.95),
                        'shadow_strength' => (float) ($map?->shadow_strength ?? 0.18),
                        'highlight_strength' => (float) ($map?->highlight_strength ?? 0.12),
                        'fit_mode' => $map?->fit_mode ?? 'stretch',
                        'manual_rotation' => (float) ($map?->manual_rotation ?? 0),
                    ];
                })->values(),
                'faqs' => $faqs,
                'policyRows' => $policyRows,
                'related_products' => $product->relatedProducts->values(),
                'comboUpsells' => $product->show_related_combos_on_product ? ComboPricing::suggestionsForProduct($product) : collect(),
                'recentlyViewed' => $recentlyViewed,
                'defaultMockupId' => $defaultMockupId,
                'showFlatPreviewFirst' => false,
            ]);
        }

        if ($product->type === ProductType::Service && $product->serviceMeta) {
            $relatedServiceProducts = $product->relatedProducts->isNotEmpty()
                ? $product->relatedProducts
                : Product::query()
                    ->with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])
                    ->where('status', 'active')
                    ->where('id', '!=', $product->id)
                    ->where('category_id', $product->category_id)
                    ->latest()
                    ->limit(4)
                    ->get();

            return view('storefront.products.service', [
                'product' => $product,
                'serviceMeta' => $product->serviceMeta,
                'relatedProducts' => $relatedServiceProducts,
                'relatedCategories' => $product->relatedCategories->take(4),
                'recentlyViewed' => $recentlyViewed,
                'policyRows' => $policyRows,
                'faqs' => $faqs,
            ]);
        }

        if (! in_array($product->type, [ProductType::Standard, ProductType::LightCustomizable, ProductType::Bundle], true)) {
            return redirect()
                ->route('shop.index', ['type' => $product->type?->value]);
        }

        return match ($product->type) {
            ProductType::LightCustomizable => view('storefront.products.light-customizable', [
                'product' => $product,
                'recentlyViewed' => $recentlyViewed,
                'faqs' => $faqs,
                'policyRows' => $policyRows,
                'related_products' => $product->relatedProducts->values(),
                'comboUpsells' => $product->show_related_combos_on_product ? ComboPricing::suggestionsForProduct($product) : collect(),
            ]),
            ProductType::Bundle => view('storefront.products.bundle', [
                'product' => $product,
                'recentlyViewed' => $recentlyViewed,
                'bundlePricing' => ComboPricing::summary($product),
                'bundleValue' => $product->bundleItems->sum(fn ($item) => (float) $item->childProduct?->price * $item->quantity),
                'policyRows' => $policyRows,
                'faqs' => $faqs,
            ]),
            default => view('products.show', [
                'product' => $product,
                'template' => null,
                'fonts' => collect(),
                'mockups' => collect(),
                'faqs' => $faqs,
                'policyRows' => $policyRows,
                'related_products' => $product->relatedProducts->values(),
                'comboUpsells' => $product->show_related_combos_on_product ? ComboPricing::suggestionsForProduct($product) : collect(),
                'recentlyViewed' => $recentlyViewed,
                'defaultMockupId' => null,
                'showFlatPreviewFirst' => false,
            ]),
        };
    }

    private function policyRowsForProduct(Product $product): array
    {
        $customRows = collect($product->shipping_care_policy ?: [])
            ->map(fn ($row) => [
                'label' => trim((string) ($row['label'] ?? $row['title'] ?? '')),
                'value' => trim((string) ($row['value'] ?? $row['description'] ?? $row['copy'] ?? '')),
            ])
            ->filter(fn ($row) => filled($row['label']) || filled($row['value']))
            ->values()
            ->all();

        if ($customRows) {
            return $customRows;
        }

        $leadTime = (int) ($product->lead_time_days ?: 4);
        $defaultRows = $this->defaultPolicyRows();

        if ($defaultRows) {
            return collect($defaultRows)
                ->map(fn ($row) => [
                    'label' => $row['label'],
                    'value' => str_replace(
                        ['{lead_time}', '{lead_time_max}'],
                        [$leadTime, $leadTime + 2],
                        $row['value'],
                    ),
                ])
                ->all();
        }

        return [
            ['label' => 'Timeline', 'value' => 'Prepared within '.$leadTime.' to '.($leadTime + 2).' business days.'],
            ['label' => 'Packaging', 'value' => 'All items are gift-ready wrapped and carefully posted.'],
            ['label' => 'Care', 'value' => 'Keep prints dry, away from direct sunlight, and handle frames with clean hands.'],
            ['label' => 'Returns', 'value' => 'Personalized items are final sale once proof is approved; damaged parcels are reviewed quickly.'],
        ];
    }

    private function defaultPolicyRows(): array
    {
        $configuredRows = Setting::query()
            ->where('group', 'storefront')
            ->where('key', 'default_shipping_care_policy')
            ->value('value');

        if (! filled($configuredRows)) {
            return [];
        }

        return collect(json_decode($configuredRows, true) ?: [])
            ->map(fn ($row) => [
                'label' => trim((string) ($row['label'] ?? $row['title'] ?? '')),
                'value' => trim((string) ($row['value'] ?? $row['description'] ?? $row['copy'] ?? '')),
            ])
            ->filter(fn ($row) => filled($row['label']) || filled($row['value']))
            ->values()
            ->all();
    }

    private function faqsForProduct(Product $product)
    {
        $customFaqs = collect($product->product_faqs ?: [])
            ->map(fn ($faq) => (object) [
                'question' => trim((string) ($faq['question'] ?? $faq['title'] ?? '')),
                'answer' => trim((string) ($faq['answer'] ?? $faq['description'] ?? '')),
            ])
            ->filter(fn ($faq) => filled($faq->question) || filled($faq->answer))
            ->values();

        if ($customFaqs->isNotEmpty()) {
            return $customFaqs;
        }

        return Faq::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->get();
    }

    public function previewImage(Product $product, MockupRenderService $mockupRenderService): BinaryFileResponse|RedirectResponse|Response
    {
        $product->loadMissing([
            'images',
            'personalizationTemplate.fields',
            'personalizationTemplate.fonts',
            'personalizationMockups.map',
        ]);

        abort_unless($product->is_customizable && $product->personalizationTemplate, 404);

        $version = $product->storefrontPreviewVersion();
        $cachePath = "storefront-previews/product-{$product->id}-{$version}.png";
        $disk = Storage::disk('public');

        if (! $disk->exists($cachePath)) {
            try {
                $renderPreview = NikahRenderPreview::buildForProduct($product);
                abort_unless(is_array($renderPreview), 404);

                $flatSvg = view('admin.orders.personalization-proof-svg', [
                    'item' => (object) ['product_name' => $product->name],
                    'renderPreview' => $renderPreview,
                    'mode' => 'flat',
                    'resolutionPreset' => 'storefront',
                ])->render();

                if (data_get($renderPreview, 'mockup')) {
                    $blob = $mockupRenderService->renderMockupProof($renderPreview, $flatSvg);
                } else {
                    $flatImage = $mockupRenderService->renderFlatFromSvg($flatSvg);
                    $flatImage->setImageCompressionQuality(100);
                    $blob = $flatImage->getImagesBlob();
                    $flatImage->clear();
                    $flatImage->destroy();
                }

                $disk->put($cachePath, $blob);
            } catch (Throwable $exception) {
                report($exception);

                $fallbackResponse = $this->previewImageFallbackResponse($product);

                if ($fallbackResponse) {
                    return $fallbackResponse;
                }

                throw $exception;
            }
        }

        return response()->file($disk->path($cachePath), [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=86400',
        ]);
    }

    private function previewImageFallbackResponse(Product $product): Response|RedirectResponse|null
    {
        $template = $product->personalizationTemplate;
        $mockup = $product->defaultPersonalizationMockup()
            ?: $template?->mockups()->where('is_active', true)->orderBy('sort_order')->first();
        $map = $mockup?->map ? MockupZoneNormalizer::toImageSpace($mockup, $mockup->map) : null;
        $flatUrl = $template?->storefrontArtworkUrl()
            ?: $template?->previewArtworkUrl()
            ?: $template?->thumbnailArtworkUrl();

        if ($mockup?->base_image_url && $flatUrl && is_array($map)) {
            return response($this->fallbackCompositeSvg($mockup, $map, $flatUrl), 200, [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'public, max-age=3600, stale-while-revalidate=86400',
            ]);
        }

        $fallbackUrl = collect([
            $mockup?->thumb_image_url,
            $mockup?->base_image_url,
            $flatUrl,
            $product->featured_image_url,
            $product->primaryImage()?->image_url,
        ])
            ->filter(fn ($url) => filled($url) && ! str_starts_with((string) $url, 'blob:'))
            ->map(fn ($url) => $this->absolutePreviewAssetUrl((string) $url))
            ->first();

        return $fallbackUrl ? redirect($fallbackUrl) : null;
    }

    private function fallbackCompositeSvg($mockup, array $map, string $flatUrl): string
    {
        [$imageWidth, $imageHeight] = MockupZoneNormalizer::resolveImageDimensions($mockup);
        $width = (int) ($mockup->image_width ?: $imageWidth ?: 1600);
        $height = (int) ($mockup->image_height ?: $imageHeight ?: 1200);

        $points = [
            [(float) ($map['top_left_x'] ?? 0.2) * $width, (float) ($map['top_left_y'] ?? 0.18) * $height],
            [(float) ($map['top_right_x'] ?? 0.8) * $width, (float) ($map['top_right_y'] ?? 0.18) * $height],
            [(float) ($map['bottom_right_x'] ?? 0.8) * $width, (float) ($map['bottom_right_y'] ?? 0.82) * $height],
            [(float) ($map['bottom_left_x'] ?? 0.2) * $width, (float) ($map['bottom_left_y'] ?? 0.82) * $height],
        ];

        $xs = array_column($points, 0);
        $ys = array_column($points, 1);
        $left = min($xs);
        $top = min($ys);
        $zoneWidth = max(1, max($xs) - $left);
        $zoneHeight = max(1, max($ys) - $top);
        $polygon = collect($points)
            ->map(fn (array $point) => round($point[0], 2).','.round($point[1], 2))
            ->implode(' ');

        $baseHref = $this->svgImageHref((string) $mockup->base_image_url);
        $flatHref = $this->svgImageHref($flatUrl);
        $escapedBaseUrl = $this->escapeSvgAttribute($baseHref);
        $escapedFlatUrl = $this->escapeSvgAttribute($flatHref);
        $fitMode = (string) ($mockup->map?->fit_mode ?? 'stretch');
        $anchorX = (float) ($mockup->map?->object_position_x ?? 0.5);
        $anchorY = (float) ($mockup->map?->object_position_y ?? 0.5);
        $alignX = $anchorX <= 0.33 ? 'Min' : ($anchorX >= 0.67 ? 'Max' : 'Mid');
        $alignY = $anchorY <= 0.33 ? 'Min' : ($anchorY >= 0.67 ? 'Max' : 'Mid');
        $flatPreserveAspectRatio = match ($fitMode) {
            'contain' => 'x'.$alignX.'Y'.$alignY.' meet',
            'cover' => 'x'.$alignX.'Y'.$alignY.' slice',
            default => 'none',
        };

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}">
    <defs>
        <clipPath id="certificate-zone">
            <polygon points="{$polygon}" />
        </clipPath>
    </defs>
    <image href="{$escapedBaseUrl}" x="0" y="0" width="{$width}" height="{$height}" preserveAspectRatio="xMidYMid slice" />
    <image href="{$escapedFlatUrl}" x="{$left}" y="{$top}" width="{$zoneWidth}" height="{$zoneHeight}" preserveAspectRatio="{$flatPreserveAspectRatio}" clip-path="url(#certificate-zone)" opacity="0.98" />
</svg>
SVG;
    }

    private function absolutePreviewAssetUrl(string $url): string
    {
        if (preg_match('/^(https?:|data:)/i', $url)) {
            return $url;
        }

        return Str::startsWith($url, '/')
            ? url($url)
            : url('/'.ltrim($url, '/'));
    }

    private function svgImageHref(string $url): string
    {
        if (str_starts_with($url, 'data:')) {
            return $url;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if ($path && str_starts_with($path, '/storage/')) {
            $relativePath = Str::after($path, '/storage/');
            $disk = Storage::disk('public');

            if ($disk->exists($relativePath)) {
                $absolutePath = $disk->path($relativePath);
                $mime = @mime_content_type($absolutePath) ?: 'image/png';

                return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($absolutePath));
            }
        }

        if ($path && str_starts_with($path, '/images/')) {
            $absolutePath = public_path(ltrim($path, '/'));

            if (is_file($absolutePath)) {
                $mime = @mime_content_type($absolutePath) ?: 'image/png';

                return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($absolutePath));
            }
        }

        return $this->absolutePreviewAssetUrl($url);
    }

    private function escapeSvgAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
