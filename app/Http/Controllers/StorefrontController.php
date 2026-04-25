<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Faq;
use App\Models\HomepageSection;
use App\Models\Product;
use App\Models\Review;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Cache;

class StorefrontController extends Controller
{
    public function home()
    {
        $featuredProducts = $this->productsFromCachedIds(
            'storefront.home.featured_product_ids',
            fn () => Product::query()
                ->where('status', 'active')
                ->where('is_featured', true)
                ->latest()
                ->take(4)
                ->pluck('id')
                ->all()
        );

        $featuredCategories = $this->categoriesFromCachedIds(
            'storefront.home.featured_category_ids',
            fn () => Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->take(4)
                ->pluck('id')
                ->all()
        );

        $featuredCollections = $this->collectionsFromCachedIds(
            'storefront.home.featured_collection_ids',
            fn () => Collection::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->take(3)
                ->pluck('id')
                ->all()
        );

        $homepageSections = $this->homepageSectionsFromCachedKeys(
            'storefront.home.section_keys',
            fn () => HomepageSection::query()
                ->where('is_enabled', true)
                ->orderBy('sort_order')
                ->pluck('section_key')
                ->all()
        );

        $faqPreview = $this->faqsFromCachedIds(
            'storefront.home.faq_preview_ids',
            fn () => Faq::query()
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->take(3)
                ->pluck('id')
                ->all()
        );

        $signatureNikah = Product::with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])
            ->where('slug', 'signature-nikah-nama')
            ->first();

        $comboSpotlight = Product::with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])
            ->where('type', ProductType::Bundle->value)
            ->where('status', 'active')
            ->latest()
            ->take(3)
            ->get();

        $bookingHighlights = Product::with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])
            ->where('type', ProductType::Service->value)
            ->where('status', 'active')
            ->latest()
            ->take(3)
            ->get();

        $bridalWearSpotlight = Product::with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])
            ->whereHas('category', fn ($query) => $query->where('slug', 'customized-bridal-wear'))
            ->where('status', 'active')
            ->take(2)
            ->get();

        $testimonials = Review::with('product')
            ->where('is_approved', true)
            ->latest()
            ->take(3)
            ->get();

        return view('storefront.home', compact(
            'featuredProducts',
            'featuredCategories',
            'featuredCollections',
            'homepageSections',
            'faqPreview',
            'signatureNikah',
            'comboSpotlight',
            'bookingHighlights',
            'bridalWearSpotlight',
            'testimonials',
        ));
    }

    public function shop(Request $request)
    {
        $listingQuery = $this->productListingQuery($request);

        $products = (clone $listingQuery)
            ->with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])
            ->paginate(12)
            ->withQueryString();

        return view('storefront.shop.index', [
            'title' => 'Shop',
            'description' => 'Browse the Azraq Bridal catalog by product type, collection, and category.',
            'products' => $products,
            'filters' => $this->filters($request),
            'currentCategory' => null,
            'currentCollection' => null,
            'heroCollections' => $this->collectionsFromCachedIds(
                'storefront.shop.hero_collection_ids',
                fn () => Collection::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->take(3)->pluck('id')->all()
            ),
            'featuredStrip' => (clone $listingQuery)
                ->with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])
                ->latest()
                ->take(3)
                ->get(),
        ]);
    }

    public function search(Request $request)
    {
        $queryText = trim($request->string('search')->toString());
        $listingQuery = $this->productListingQuery($request);

        $products = (clone $listingQuery)
            ->with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])
            ->paginate(12)
            ->withQueryString();

        $suggestionChips = collect([
            'nikah',
            'customized pen',
            'combo',
            'bridal booking',
            'giftable',
        ])->reject(fn (string $suggestion) => str($suggestion)->lower()->value() === str($queryText)->lower()->value())->take(5)->values();

        return view('storefront.search.index', [
            'queryText' => $queryText,
            'products' => $products,
            'filters' => $this->filters($request),
            'suggestionChips' => $suggestionChips,
        ]);
    }

    public function category(Request $request, Category $category)
    {
        $listingQuery = $this->productListingQuery($request, $category);

        $products = (clone $listingQuery)
            ->with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])
            ->paginate(12)
            ->withQueryString();

        return view('storefront.shop.index', [
            'title' => $category->name,
            'description' => $category->description,
            'products' => $products,
            'filters' => $this->filters($request),
            'currentCategory' => $category,
            'currentCollection' => null,
            'heroCollections' => Collection::withCount('products')
                ->whereHas('products', fn ($query) => $query->where('category_id', $category->id))
                ->take(3)
                ->get(),
            'featuredStrip' => (clone $listingQuery)
                ->with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])
                ->take(3)
                ->get(),
        ]);
    }

    public function collection(Request $request, Collection $collection)
    {
        $listingQuery = $this->productListingQuery($request, null, $collection);

        $products = (clone $listingQuery)
            ->with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])
            ->paginate(12)
            ->withQueryString();

        return view('storefront.shop.index', [
            'title' => $collection->name,
            'description' => $collection->description,
            'products' => $products,
            'filters' => $this->filters($request),
            'currentCategory' => null,
            'currentCollection' => $collection,
            'heroCollections' => Collection::withCount('products')
                ->where('id', '!=', $collection->id)
                ->where('is_active', true)
                ->take(3)
                ->get(),
            'featuredStrip' => (clone $listingQuery)
                ->with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])
                ->take(3)
                ->get(),
        ]);
    }

    private function productListingQuery(Request $request, ?Category $category = null, ?Collection $collection = null)
    {
        return Product::query()
            ->where('status', 'active')
            ->when($category, fn ($query) => $query->whereBelongsTo($category))
            ->when($collection, fn ($query) => $query->whereHas('collections', fn ($related) => $related->whereKey($collection->id)))
            ->when($request->string('search')->toString(), fn ($query, $search) => $query->where(function ($inner) use ($search): void {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when($request->string('type')->toString(), fn ($query, $type) => $query->where('type', $type))
            ->when($request->string('tag')->toString(), fn ($query, $tag) => $query->whereHas('tags', fn ($related) => $related->where('slug', $tag)))
            ->when($request->string('sort')->toString(), function ($query, $sort): void {
                match ($sort) {
                    'price_low' => $query->orderBy('price'),
                    'price_high' => $query->orderByDesc('price'),
                    'name' => $query->orderBy('name'),
                    default => $query->latest(),
                };
            }, fn ($query) => $query->latest());
    }

    private function filters(Request $request): array
    {
        return [
            'productTypes' => ProductType::options(),
            'tags' => $this->tagsFromCachedIds(
                'storefront.filter.tag_ids',
                fn () => Tag::query()->where('is_active', true)->orderBy('name')->pluck('id')->all()
            ),
            'categories' => $this->categoriesFromCachedIds(
                'storefront.filter.category_ids',
                fn () => Category::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->pluck('id')->all()
            ),
            'collections' => $this->collectionsFromCachedIds(
                'storefront.filter.collection_ids',
                fn () => Collection::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->pluck('id')->all()
            ),
            'applied' => [
                'search' => $request->string('search')->toString(),
                'type' => $request->string('type')->toString(),
                'tag' => $request->string('tag')->toString(),
                'sort' => $request->string('sort')->toString(),
            ],
        ];
    }

    private function productsFromCachedIds(string $cacheKey, callable $resolver): SupportCollection
    {
        $ids = $this->rememberScalarList($cacheKey, $resolver);

        return Product::with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Product $product) => array_search($product->id, $ids, true))
            ->values();
    }

    private function categoriesFromCachedIds(string $cacheKey, callable $resolver): SupportCollection
    {
        $ids = $this->rememberScalarList($cacheKey, $resolver);

        return Category::withCount('products')
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Category $category) => array_search($category->id, $ids, true))
            ->values();
    }

    private function collectionsFromCachedIds(string $cacheKey, callable $resolver): SupportCollection
    {
        $ids = $this->rememberScalarList($cacheKey, $resolver);

        return Collection::withCount('products')
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Collection $collection) => array_search($collection->id, $ids, true))
            ->values();
    }

    private function tagsFromCachedIds(string $cacheKey, callable $resolver): SupportCollection
    {
        $ids = $this->rememberScalarList($cacheKey, $resolver);

        return Tag::query()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Tag $tag) => array_search($tag->id, $ids, true))
            ->values();
    }

    private function faqsFromCachedIds(string $cacheKey, callable $resolver): SupportCollection
    {
        $ids = $this->rememberScalarList($cacheKey, $resolver);

        return Faq::query()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Faq $faq) => array_search($faq->id, $ids, true))
            ->values();
    }

    private function homepageSectionsFromCachedKeys(string $cacheKey, callable $resolver): SupportCollection
    {
        $keys = $this->rememberScalarList($cacheKey, $resolver);

        return HomepageSection::query()
            ->whereIn('section_key', $keys)
            ->get()
            ->sortBy(fn (HomepageSection $section) => array_search($section->section_key, $keys, true))
            ->keyBy('section_key');
    }

    private function rememberScalarList(string $cacheKey, callable $resolver): array
    {
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $fresh = array_values((array) $resolver());
        Cache::put($cacheKey, $fresh, now()->addMinutes(10));

        return $fresh;
    }
}
