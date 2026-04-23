<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Models\Coupon;
use App\Models\PersonalizationMockup;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\CartSession;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $items = CartSession::items($request);
        $coupon = $request->session()->get('cart.coupon_id')
            ? Coupon::find($request->session()->get('cart.coupon_id'))
            : null;

        return view('storefront.cart.index', [
            'items' => $items,
            'coupon' => $coupon,
            'summary' => CartSession::summary($items, 'standard', $coupon),
        ]);
    }

    public function store(AddToCartRequest $request, Product $product)
    {
        $validated = $request->validated();
        $items = collect($request->session()->get('cart.items', []));
        $variant = filled($validated['variant_id'] ?? null)
            ? ProductVariant::query()->find($validated['variant_id'])
            : null;
        $mockup = filled($validated['mockup_id'] ?? null)
            ? PersonalizationMockup::query()->find($validated['mockup_id'])
            : null;
        $personalization = collect($validated['personalization'] ?? [])
            ->filter(fn ($value) => filled($value))
            ->all();
        $fontSelection = collect($validated['font_selection'] ?? [])
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => (int) $value)
            ->all();

        $key = implode(':', [
            $product->id,
            $validated['variant_id'] ?? 'base',
            md5((string) ($validated['custom_text'] ?? '')),
            md5(json_encode($personalization)),
            $validated['font_id'] ?? 'no-font',
            md5(json_encode($fontSelection)),
            $validated['mockup_id'] ?? 'no-mockup',
        ]);

        $existingIndex = $items->search(fn (array $item) => $item['key'] === $key);
        $requestedQuantity = (int) $validated['quantity'];

        if ($existingIndex !== false) {
            $requestedQuantity += (int) ($items->get($existingIndex)['quantity'] ?? 0);
        }

        if (! CartSession::hasSufficientStock($product, $variant, $requestedQuantity)) {
            $available = CartSession::availableStock($product, $variant);

            return redirect()
                ->route('products.show', $product)
                ->withErrors([
                    'cart' => 'Only '.$available.' unit(s) of '.$product->name.' are currently available.',
                ]);
        }

        if ($existingIndex !== false) {
            $current = $items->get($existingIndex);
            $current['quantity'] += (int) $validated['quantity'];
            $items->put($existingIndex, $current);
        } else {
            $items->push([
                'key' => $key,
                'product_id' => $product->id,
                'variant_id' => $validated['variant_id'] ?? null,
                'quantity' => (int) $validated['quantity'],
                'custom_text' => $validated['custom_text'] ?? null,
                'font_id' => $validated['font_id'] ?? null,
                'font_selection' => $fontSelection,
                'mockup_id' => $validated['mockup_id'] ?? null,
                'mockup_title' => $mockup?->title,
                'proof_note' => $validated['proof_note'] ?? null,
                'personalization' => $personalization,
            ]);
        }

        $request->session()->put('cart.items', $items->values()->all());

        return redirect()
            ->route($request->boolean('buy_now') ? 'checkout.show' : 'cart.index')
            ->with('status', $request->boolean('buy_now') ? 'Product added to cart. Continue to checkout.' : 'Product added to cart.');
    }

    public function update(Request $request, string $key)
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $items = collect($request->session()->get('cart.items', []));
        $target = $items->firstWhere('key', $key);

        if (! $target) {
            return redirect()->route('cart.index')->withErrors([
                'cart' => 'We could not find that cart item.',
            ]);
        }

        $product = Product::query()->find($target['product_id']);
        $variant = filled($target['variant_id'] ?? null)
            ? ProductVariant::query()->find($target['variant_id'])
            : null;
        $requestedQuantity = (int) $request->integer('quantity');

        if ($product && ! CartSession::hasSufficientStock($product, $variant, $requestedQuantity)) {
            $available = CartSession::availableStock($product, $variant);

            return redirect()->route('cart.index')->withErrors([
                'cart' => 'Only '.$available.' unit(s) of '.$product->name.' are currently available.',
            ]);
        }

        $items = $items->map(function (array $item) use ($key, $requestedQuantity) {
            if ($item['key'] === $key) {
                $item['quantity'] = $requestedQuantity;
            }

            return $item;
        });

        $request->session()->put('cart.items', $items->all());

        return redirect()->route('cart.index')->with('status', 'Cart updated.');
    }

    public function destroy(Request $request, string $key)
    {
        $items = collect($request->session()->get('cart.items', []))
            ->reject(fn (array $item) => $item['key'] === $key)
            ->values();

        $request->session()->put('cart.items', $items->all());

        return redirect()->route('cart.index')->with('status', 'Item removed from cart.');
    }
}
