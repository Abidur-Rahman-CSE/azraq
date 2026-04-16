<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\BundleItem;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ServiceProductMeta;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(12);

        return view('admin.catalog.products.index', [
            'products' => $products,
            'productTypes' => ProductType::options(),
        ]);
    }

    public function create()
    {
        return view('admin.catalog.products.create', $this->formData(new Product([
            'type' => ProductType::Standard,
            'status' => 'draft',
            'manage_stock' => true,
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
        $product->load(['collections', 'tags', 'relatedProducts', 'variants', 'bundleItems.childProduct', 'serviceMeta']);

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
        return [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'collections' => Collection::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'relatedProducts' => Product::when($product->exists, fn ($query) => $query->whereKeyNot($product->id))->orderBy('name')->get(),
            'productTypes' => ProductType::options(),
        ];
    }

    private function productPayload(ProductRequest $request): array
    {
        return [
            ...$request->safe()->except(['collection_ids', 'tag_ids', 'related_product_ids', 'variants', 'bundle_items', 'service_meta']),
            'manage_stock' => $request->boolean('manage_stock'),
            'is_featured' => $request->boolean('is_featured'),
        ];
    }

    private function syncProductRelations(Product $product, array $data): void
    {
        $product->collections()->sync($data['collection_ids'] ?? []);
        $product->tags()->sync($data['tag_ids'] ?? []);
        $product->relatedProducts()->sync($data['related_product_ids'] ?? []);

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
    }
}
