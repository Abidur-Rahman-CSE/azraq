<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $ids = collect($request->session()->get('wishlist.product_ids', []));

        $products = Product::with(['category', 'tags', 'images', 'variants', 'personalizationTemplate', 'personalizationMockups'])
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn ($product) => array_search($product->id, $ids->all()))
            ->values();

        return view('storefront.wishlist.index', compact('products'));
    }

    public function store(Request $request, Product $product)
    {
        $ids = collect($request->session()->get('wishlist.product_ids', []))
            ->prepend($product->id)
            ->unique()
            ->values();

        $request->session()->put('wishlist.product_ids', $ids->all());

        return back()->with('status', 'Product added to wishlist.');
    }

    public function destroy(Request $request, Product $product)
    {
        $ids = collect($request->session()->get('wishlist.product_ids', []))
            ->reject(fn (int $id) => $id === $product->id)
            ->values();

        $request->session()->put('wishlist.product_ids', $ids->all());

        return back()->with('status', 'Product removed from wishlist.');
    }
}
