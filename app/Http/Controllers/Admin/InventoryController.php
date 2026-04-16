<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InventoryAdjustmentRequest;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;

class InventoryController extends Controller
{
    public function index()
    {
        $products = Product::with('variants')
            ->where('manage_stock', true)
            ->orderBy('name')
            ->get();

        $lowStockProducts = $products->filter(function (Product $product) {
            return $product->stock_quantity <= max(1, $product->low_stock_threshold);
        })->values();

        $lowStockVariants = ProductVariant::with('product')
            ->whereHas('product', fn ($query) => $query->where('manage_stock', true))
            ->get()
            ->filter(fn (ProductVariant $variant) => $variant->stock_quantity <= 2)
            ->values();

        $movements = InventoryMovement::with(['product', 'variant', 'order'])->latest()->paginate(20);

        return view('admin.inventory.index', compact('products', 'lowStockProducts', 'lowStockVariants', 'movements'));
    }

    public function store(InventoryAdjustmentRequest $request)
    {
        $product = Product::findOrFail($request->integer('product_id'));
        $variant = $request->input('target') === 'variant' && $request->filled('variant_id')
            ? ProductVariant::findOrFail($request->integer('variant_id'))
            : null;

        $before = $variant?->stock_quantity ?? $product->stock_quantity;
        $after = max(0, $before + $request->integer('quantity_change'));

        if ($variant) {
            $variant->update(['stock_quantity' => $after]);
        } else {
            $product->update(['stock_quantity' => $after]);
        }

        InventoryMovement::create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'type' => 'manual_adjustment',
            'quantity_change' => $request->integer('quantity_change'),
            'quantity_before' => $before,
            'quantity_after' => $after,
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('admin.inventory.index')->with('status', 'Inventory adjusted.');
    }
}
