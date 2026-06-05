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
        $homepageSections = $this->homepageSectionsFromCachedKeys(
            'storefront.home.section_keys',
            fn () => HomepageSection::query()
                ->where('is_enabled', true)
                ->orderBy('sort_order')
                ->pluck('section_key')
                ->all()
        );

        $featuredCategoryIds = collect(data_get($homepageSections->get('featured_categories'), 'settings.selected_category_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        $featuredProductIds = collect(data_get($homepageSections->get('featured_products'), 'settings.selected_product_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        $featuredCollectionIds = collect(data_get($homepageSections->get('featured_collections'), 'settings.selected_collection_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $featuredProducts = $this->productsFromCachedIds(
            'storefront.home.featured_product_ids',
            fn () => $featuredProductIds !== []
                ? $featuredProductIds
                : Product::query()
                    ->where('status', 'active')
                    ->where('is_featured', true)
                    ->latest()
                    ->take(4)
                    ->pluck('id')
                    ->all()
        );

        $featuredCategories = $this->categoriesFromCachedIds(
            'storefront.home.featured_category_ids',
            fn () => $featuredCategoryIds !== []
                ? $featuredCategoryIds
                : Category::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->take(4)
                    ->pluck('id')
                    ->all()
        );

        $featuredCollections = $this->collectionsFromCachedIds(
            'storefront.home.featured_collection_ids',
            fn () => $featuredCollectionIds !== []
                ? $featuredCollectionIds
                : Collection::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->take(3)
                    ->pluck('id')
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

        // Signature Nikah spotlight — admin can override via signature_nikah_spotlight.settings.product_id
        $spotlightOverrideId = (int) data_get($homepageSections->get('signature_nikah_spotlight'), 'settings.product_id');
        $signatureNikah = Product::with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])
            ->when($spotlightOverrideId > 0, fn ($q) => $q->where('id', $spotlightOverrideId))
            ->when($spotlightOverrideId === 0, fn ($q) => $q->where('slug', 'signature-nikah-nama'))
            ->first();

        $comboSpotlight = Product::with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])
            ->where('type', ProductType::Bundle->value)
            ->where('status', 'active')
            ->latest()
            ->take(3)
            ->get();

        // Atelier services — admin can pin specific service products via atelier_services.settings.service_ids
        $atelierServiceIds = collect(data_get($homepageSections->get('atelier_services'), 'settings.service_ids', []))
            ->filter()->map(fn ($id) => (int) $id)->values()->all();
        $bookingHighlights = Product::with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])
            ->where('status', 'active')
            ->when($atelierServiceIds !== [], fn ($q) => $q->whereIn('id', $atelierServiceIds))
            ->when($atelierServiceIds === [], fn ($q) => $q->where('type', ProductType::Service->value))
            ->latest()
            ->take(6)
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
        $category->loadMissing('parent');

        $listingQuery = $this->productListingQuery($request, $category);

        $products = (clone $listingQuery)
            ->with(['category', 'tags', 'images', 'personalizationTemplate', 'personalizationMockups'])
            ->paginate(12)
            ->withQueryString();

        return view('storefront.shop.index', [
            'title' => $category->name,
            'description' => $category->description,
            'products' => $products,
            'filters' => $this->filters($request, $category),
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
            ->when(! $category ? $request->string('category')->toString() : null, fn ($query, $categorySlug) => $query->whereHas('category', fn ($related) => $related->where('slug', (string) $categorySlug)))
            ->when($request->filled('min_price'), fn ($query) => $query->where('price', '>=', max(0, (float) $request->input('min_price'))))
            ->when($request->filled('max_price'), fn ($query) => $query->where('price', '<=', max(0, (float) $request->input('max_price'))))
            ->when(collect((array) $request->input('availability', []))->filter()->isNotEmpty(), function ($query) use ($request): void {
                $availability = collect((array) $request->input('availability', []))->filter()->values();

                $query->where(function ($inner) use ($availability): void {
                    if ($availability->contains('in_stock')) {
                        $inner->orWhere(fn ($stockQuery) => $stockQuery->where('manage_stock', true)->where('stock_quantity', '>', 0));
                    }

                    if ($availability->contains('made_to_order')) {
                        $inner->orWhere('manage_stock', false);
                    }
                });
            })
            ->when($request->string('sort')->toString(), function ($query, $sort): void {
                match ($sort) {
                    'price_low' => $query->orderBy('price'),
                    'price_high' => $query->orderByDesc('price'),
                    'name' => $query->orderBy('name'),
                    default => $query->latest(),
                };
            }, fn ($query) => $query->latest());
    }

    private function filters(Request $request, ?Category $currentCategory = null): array
    {
        $priceBounds = Product::query()
            ->where('status', 'active')
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();
        $selectedCategorySlug = $currentCategory?->slug ?: $request->string('category')->toString();
        $selectedCategory = $currentCategory
            ?: (filled($selectedCategorySlug) ? Category::query()
                ->where('is_active', true)
                ->with(['parent.parent'])
                ->with(['children' => fn ($query) => $query
                    ->where('is_active', true)
                    ->withCount('products')
                    ->with(['children' => fn ($childQuery) => $childQuery->where('is_active', true)->withCount('products')])])
                ->where('slug', $selectedCategorySlug)
                ->first() : null);
        $selectedCategory?->loadMissing([
            'parent.parent',
            'children' => fn ($query) => $query
                ->where('is_active', true)
                ->withCount('products')
                ->with(['children' => fn ($childQuery) => $childQuery->where('is_active', true)->withCount('products')]),
        ]);
        $selectedCategory?->parent?->loadMissing([
            'children' => fn ($query) => $query
                ->where('is_active', true)
                ->withCount('products')
                ->with(['children' => fn ($childQuery) => $childQuery->where('is_active', true)->withCount('products')]),
        ]);
        $selectedCategory?->parent?->parent?->loadMissing([
            'children' => fn ($query) => $query
                ->where('is_active', true)
                ->withCount('products')
                ->with(['children' => fn ($childQuery) => $childQuery->where('is_active', true)->withCount('products')]),
        ]);

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
            'parentCategories' => Category::query()
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->withCount('products')
                ->with(['children' => fn ($query) => $query
                    ->where('is_active', true)
                    ->withCount('products')
                    ->with(['children' => fn ($childQuery) => $childQuery
                        ->where('is_active', true)
                        ->withCount('products')
                        ->orderBy('sort_order')
                        ->orderBy('name')])
                    ->orderBy('sort_order')
                    ->orderBy('name')])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'selectedCategory' => $selectedCategory,
            'collections' => $this->collectionsFromCachedIds(
                'storefront.filter.collection_ids',
                fn () => Collection::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->pluck('id')->all()
            ),
            'applied' => [
                'search' => $request->string('search')->toString(),
                'type' => $request->string('type')->toString(),
                'tag' => $request->string('tag')->toString(),
                'category' => $selectedCategorySlug,
                'min_price' => $request->input('min_price'),
                'max_price' => $request->input('max_price'),
                'availability' => collect((array) $request->input('availability', []))->filter()->values()->all(),
                'sort' => $request->string('sort')->toString(),
            ],
            'priceBounds' => [
                'min' => (int) floor((float) ($priceBounds?->min_price ?? 0)),
                'max' => (int) ceil((float) ($priceBounds?->max_price ?? 0)),
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
