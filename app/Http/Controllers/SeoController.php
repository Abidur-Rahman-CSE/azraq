<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public function robots()
    {
        return response(implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Sitemap: '.route('seo.sitemap'),
            '',
        ]), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap()
    {
        $urls = Cache::remember('seo.sitemap.urls', now()->addMinutes(15), function (): array {
            $staticUrls = [
                route('home'),
                route('shop.index'),
                route('faq.index'),
                route('orders.track.form'),
            ];

            $productUrls = Product::query()
                ->where('status', 'active')
                ->pluck('slug')
                ->map(fn (string $slug) => route('products.show', ['product' => $slug]))
                ->all();

            $categoryUrls = Category::query()
                ->where('is_active', true)
                ->pluck('slug')
                ->map(fn (string $slug) => route('categories.show', ['category' => $slug]))
                ->all();

            $collectionUrls = Collection::query()
                ->where('is_active', true)
                ->pluck('slug')
                ->map(fn (string $slug) => route('collections.show', ['collection' => $slug]))
                ->all();

            $pageUrls = Page::query()
                ->where('is_published', true)
                ->pluck('slug')
                ->map(fn (string $slug) => route('pages.show', ['page' => $slug]))
                ->all();

            return array_values(array_unique([
                ...$staticUrls,
                ...$productUrls,
                ...$categoryUrls,
                ...$collectionUrls,
                ...$pageUrls,
            ]));
        });

        $xml = collect($urls)
            ->map(fn (string $url) => "    <url>\n        <loc>".e($url)."</loc>\n    </url>")
            ->implode("\n");

        return response(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n{$xml}\n</urlset>\n",
            200,
            ['Content-Type' => 'application/xml; charset=UTF-8']
        );
    }
}
