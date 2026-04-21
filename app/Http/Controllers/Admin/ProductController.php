<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Collection;
use App\Models\PersonalizationTemplate;
use App\Models\PersonalizationMockup;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ServiceProductMeta;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $filters = [
            'q' => request('q'),
            'type' => request('type'),
            'category_id' => request('category_id'),
            'customizable' => request()->boolean('customizable'),
            'nikah_only' => request()->boolean('nikah_only'),
            'stock_status' => request('stock_status'),
        ];

        $products = Product::query()
            ->with(['category', 'personalizationTemplate', 'images'])
            ->withCount('images')
            ->when(filled($filters['q']), function (Builder $query) use ($filters): void {
                $query->where(function (Builder $nested) use ($filters): void {
                    $nested
                        ->where('name', 'like', '%'.$filters['q'].'%')
                        ->orWhere('sku', 'like', '%'.$filters['q'].'%')
                        ->orWhere('slug', 'like', '%'.$filters['q'].'%');
                });
            })
            ->when(filled($filters['type']), fn (Builder $query) => $query->where('type', $filters['type']))
            ->when(filled($filters['category_id']), fn (Builder $query) => $query->where('category_id', $filters['category_id']))
            ->when($filters['customizable'], function (Builder $query): void {
                $query->whereIn('type', [
                    ProductType::LightCustomizable->value,
                    ProductType::AdvancedPersonalized->value,
                ]);
            })
            ->when($filters['nikah_only'], function (Builder $query): void {
                $query->where(function (Builder $nested): void {
                    $nested
                        ->where('type', ProductType::AdvancedPersonalized->value)
                        ->orWhereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('slug', 'like', '%nikah%'))
                        ->orWhere('name', 'like', '%nikah%');
                });
            })
            ->when(filled($filters['stock_status']), function (Builder $query) use ($filters): void {
                match ($filters['stock_status']) {
                    'low' => $query->where('manage_stock', true)->whereColumn('stock_quantity', '<=', 'low_stock_threshold'),
                    'out' => $query->where('manage_stock', true)->where('stock_quantity', 0),
                    'made_to_order' => $query->where('manage_stock', false),
                    default => null,
                };
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.catalog.products.index', [
            'products' => $products,
            'productTypes' => ProductType::options(),
            'categories' => Category::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return view('admin.catalog.products.create', $this->formData(new Product([
            'type' => ProductType::Standard,
            'status' => 'draft',
            'manage_stock' => true,
            'proof_notes_enabled' => true,
            'font_presets_enabled' => true,
        ])));
    }

    public function store(ProductRequest $request)
    {
        $product = DB::transaction(function () use ($request) {
            $product = Product::create($this->productPayload($request));
            $this->syncProductRelations($product, $request->validated());

            return $product;
        });

        return redirect()->route('admin.catalog.products.edit', $product)->with('status', 'Product created.');
    }

    public function edit(Product $product)
    {
        $product->load(['collections', 'tags', 'relatedProducts', 'relatedCategories', 'variants', 'bundleItems.childProduct', 'serviceMeta', 'images', 'personalizationTemplate.mockups', 'personalizationMockups']);

        return view('admin.catalog.products.edit', $this->formData($product));
    }

    public function update(ProductRequest $request, Product $product)
    {
        DB::transaction(function () use ($request, $product): void {
            $product->update($this->productPayload($request));
            $this->syncProductRelations($product, $request->validated());
        });

        return redirect()->route('admin.catalog.products.edit', $product)->with('status', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.catalog.products.index')->with('status', 'Product deleted.');
    }

    private function formData(Product $product): array
    {
        $activeTemplateQuery = PersonalizationTemplate::with([
            'product',
            'fields',
            'fonts',
            'mockups.map',
        ])->where('is_active', true);

        if ($product->personalizationTemplate?->id) {
            $activeTemplateQuery->orWhere('id', $product->personalizationTemplate->id);
        }

        return [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'collections' => Collection::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'relatedProducts' => Product::when($product->exists, fn ($query) => $query->whereKeyNot($product->id))->orderBy('name')->get(),
            'relatedCategories' => Category::when($product->exists, fn ($query) => $query->whereKeyNot($product->category_id))->orderBy('name')->get(),
            'personalizationTemplates' => $activeTemplateQuery->orderBy('name')->get(),
            'personalizationMockups' => PersonalizationMockup::with('template')->orderBy('sort_order')->orderBy('title')->get(),
            'productTypes' => ProductType::options(),
        ];
    }

    private function productPayload(ProductRequest $request): array
    {
        $status = $request->input('save_mode') === 'draft'
            ? 'draft'
            : $request->input('status');

        return [
            ...$request->safe()->except([
                'save_mode',
                'collection_ids',
                'tag_ids',
                'related_product_ids',
                'variants',
                'bundle_items',
                'service_meta',
                'assigned_template_id',
                'allowed_mockup_ids',
                'default_mockup_id',
                'related_category_ids',
                'featured_image_upload',
                'gallery_uploads',
                'existing_images',
            ]),
            'status' => $status,
            'manage_stock' => $request->boolean('manage_stock'),
            'is_featured' => $request->boolean('is_featured'),
            'proof_notes_enabled' => $request->boolean('proof_notes_enabled'),
            'font_presets_enabled' => $request->boolean('font_presets_enabled'),
            'gallery_default_source' => $request->input('gallery_default_source', 'manual_featured_image'),
            'show_flat_preview_first' => $request->boolean('show_flat_preview_first', true),
            'include_mockup_gallery' => $request->boolean('include_mockup_gallery', true),
            'live_preview_enabled' => $request->boolean('live_preview_enabled', true),
            'featured_image_url' => $request->route('product')?->featured_image_url,
        ];
    }

    private function syncProductRelations(Product $product, array $data): void
    {
        $product->collections()->sync($data['collection_ids'] ?? []);
        $product->tags()->sync($data['tag_ids'] ?? []);
        $product->relatedProducts()->sync($data['related_product_ids'] ?? []);
        $product->relatedCategories()->sync($data['related_category_ids'] ?? []);

        $this->syncPersonalizationAssignments($product, $data);

        $product->variants()->delete();
        collect($data['variants'] ?? [])
            ->filter(fn (array $variant) => filled($variant['name'] ?? null))
            ->values()
            ->each(fn (array $variant, int $index) => $product->variants()->create([
                'name' => $variant['name'],
                'sku' => $variant['sku'] ?: null,
                'option_values' => filled($variant['option_values'] ?? null)
                    ? array_map('trim', explode(',', $variant['option_values']))
                    : [],
                'price' => $variant['price'] ?? null,
                'compare_at_price' => $variant['compare_at_price'] ?? null,
                'stock_quantity' => $variant['stock_quantity'] ?? 0,
                'is_default' => (bool) ($variant['is_default'] ?? false),
                'position' => $index,
            ]));

        $product->bundleItems()->delete();
        collect($data['bundle_items'] ?? [])
            ->filter(fn (array $item) => filled($item['child_product_id'] ?? null))
            ->values()
            ->each(fn (array $item, int $index) => $product->bundleItems()->create([
                'child_product_id' => $item['child_product_id'],
                'quantity' => $item['quantity'] ?? 1,
                'position' => $index,
            ]));

        $serviceMeta = $data['service_meta'] ?? [];

        if ($product->type === ProductType::Service && collect($serviceMeta)->filter()->isNotEmpty()) {
            $product->serviceMeta()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'service_type' => $serviceMeta['service_type'] ?? null,
                    'duration_label' => $serviceMeta['duration_label'] ?? null,
                    'location_scope' => $serviceMeta['location_scope'] ?? null,
                    'requires_advance_payment' => (bool) ($serviceMeta['requires_advance_payment'] ?? false),
                    'advance_payment_amount' => $serviceMeta['advance_payment_amount'] ?? null,
                    'booking_notes' => $serviceMeta['booking_notes'] ?? null,
                ],
            );
        } else {
            $product->serviceMeta()->delete();
        }

        $this->syncProductMedia($product, $data);
    }

    private function syncPersonalizationAssignments(Product $product, array $data): void
    {
        if (! $this->supportsNikahPersonalization($product, $data)) {
            if ($product->personalizationTemplate) {
                $product->personalizationTemplate()->update(['product_id' => null]);
            }

            $product->personalizationMockups()->sync([]);

            return;
        }

        $selectedTemplateId = filled($data['assigned_template_id'] ?? null)
            ? (int) $data['assigned_template_id']
            : null;

        if ($selectedTemplateId) {
            PersonalizationTemplate::query()
                ->where('product_id', $product->id)
                ->where('id', '!=', $selectedTemplateId)
                ->update(['product_id' => null]);

            PersonalizationTemplate::query()
                ->where('id', $selectedTemplateId)
                ->update(['product_id' => $product->id]);
        } elseif ($product->personalizationTemplate) {
            $product->personalizationTemplate()->update(['product_id' => null]);
        }

        $allowedMockupIds = collect($data['allowed_mockup_ids'] ?? [])
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($selectedTemplateId) {
            $allowedMockupIds = PersonalizationMockup::query()
                ->where('personalization_template_id', $selectedTemplateId)
                ->whereIn('id', $allowedMockupIds)
                ->orderBy('sort_order')
                ->pluck('id')
                ->values();
        } else {
            $allowedMockupIds = collect();
        }

        $defaultMockupId = filled($data['default_mockup_id'] ?? null)
            ? (int) $data['default_mockup_id']
            : null;

        $syncPayload = $allowedMockupIds
            ->values()
            ->mapWithKeys(fn (int $id, int $index) => [
                $id => [
                    'sort_order' => $index,
                    'is_default' => $defaultMockupId ? $id === $defaultMockupId : $index === 0,
                ],
            ])
            ->all();

        $product->personalizationMockups()->sync($syncPayload);
    }

    private function supportsNikahPersonalization(Product $product, array $data): bool
    {
        $type = $product->type instanceof ProductType
            ? $product->type
            : ProductType::tryFrom((string) $product->type);

        if ($type === ProductType::AdvancedPersonalized) {
            return true;
        }

        $categoryId = $data['category_id'] ?? $product->category_id;

        if (! $categoryId) {
            return false;
        }

        $category = Category::query()->find($categoryId);

        if (! $category) {
            return false;
        }

        $label = strtolower(trim($category->name.' '.$category->slug));

        return str_contains($label, 'nikah');
    }

    private function syncProductMedia(Product $product, array $data): void
    {
        $featuredImageUrl = $product->featured_image_url;

        if (request()->hasFile('featured_image_upload')) {
            $featuredImageUrl = $this->storeImage(request()->file('featured_image_upload'));
        }

        $existingImages = collect($data['existing_images'] ?? []);

        $product->images->each(function (ProductImage $image) use ($existingImages): void {
            $row = $existingImages->get((string) $image->id, $existingImages->get($image->id, []));

            if ((bool) ($row['remove'] ?? false)) {
                $image->delete();

                return;
            }

            $image->update([
                'label' => $row['label'] ?? $image->label,
                'alt_text' => $row['alt_text'] ?? $image->alt_text,
                'position' => $row['position'] ?? $image->position,
                'is_primary' => (bool) ($row['is_primary'] ?? false),
                'status' => 'active',
            ]);
        });

        if (request()->hasFile('gallery_uploads')) {
            $basePosition = (int) $product->images()->max('position');

            collect(request()->file('gallery_uploads'))
                ->filter(fn (?UploadedFile $file) => $file instanceof UploadedFile)
                ->values()
                ->each(function (UploadedFile $file, int $index) use ($product, $basePosition): void {
                    $product->images()->create([
                        'label' => 'gallery',
                        'image_url' => $this->storeImage($file),
                        'alt_text' => $product->name.' gallery image',
                        'is_primary' => false,
                        'status' => 'active',
                        'position' => $basePosition + $index + 1,
                    ]);
                });
        }

        $images = $product->images()->orderBy('position')->get();
        $primaryImage = $images->firstWhere('is_primary', true) ?? $images->first();

        $product->images()->whereKey($images->pluck('id'))->update(['is_primary' => false]);

        if ($primaryImage) {
            $primaryImage->update(['is_primary' => true]);
            $featuredImageUrl = $featuredImageUrl ?: $primaryImage->image_url;
        }

        $product->update([
            'featured_image_url' => $featuredImageUrl,
        ]);
    }

    private function storeImage(UploadedFile $file): string
    {
        $path = $file->store('products', 'public');

        return Storage::url($path);
    }
}
