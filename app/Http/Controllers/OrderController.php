<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Support\NikahRenderPreview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function success(Order $order)
    {
        $order->load('items');

        return view('storefront.orders.success', compact('order'));
    }

    public function index(Request $request)
    {
        $ids = collect($request->session()->get('recent_order_ids', []));

        $orders = Order::withCount('items')
            ->whereIn('id', $ids)
            ->latest()
            ->get()
            ->sortBy(fn ($order) => array_search($order->id, $ids->all()))
            ->values();

        return view('storefront.orders.index', compact('orders'));
    }

    public function trackForm()
    {
        return view('storefront.orders.track');
    }

    public function track(Request $request)
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string'],
            'customer_email' => ['required', 'email'],
        ]);

        $order = Order::with(['items.product'])
            ->where('order_number', $validated['order_number'])
            ->where('customer_email', $validated['customer_email'])
            ->first();

        if (! $order) {
            return back()->withErrors([
                'order_number' => 'No order matched that order number and email combination.',
            ])->withInput();
        }

        return view('storefront.orders.track-result', [
            'order' => $order,
            'customerEmail' => $validated['customer_email'],
        ]);
    }

    public function proofReview(Request $request, Order $order, OrderItem $item)
    {
        abort_unless($item->order_id === $order->id, 404);

        $order->loadMissing(['items.product']);
        $item->loadMissing([
            'product.personalizationTemplate.fields',
            'product.personalizationTemplate.fonts',
            'product.personalizationMockups.map',
        ]);

        $generatedProofs = data_get($item->line_item_meta, 'generated_proofs', []);
        abort_if(empty($generatedProofs), 404);

        return view('storefront.orders.proof-review', [
            'order' => $order,
            'item' => $item,
            'renderPreview' => data_get($item->line_item_meta, 'render_preview')
                ?? NikahRenderPreview::buildForOrderItem($item),
        ]);
    }

    public function updateProofDecision(Request $request, Order $order, OrderItem $item)
    {
        abort_unless($item->order_id === $order->id, 404);

        $validated = $request->validate([
            'customer_email' => ['required', 'email'],
            'decision' => ['required', 'in:approve,changes_requested'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        abort_unless($order->customer_email === $validated['customer_email'], 403);

        $generatedProofs = data_get($item->line_item_meta, 'generated_proofs', []);
        abort_if(empty($generatedProofs), 404);

        $this->applyProofDecision($order, $item, $validated['decision'], $validated['note'] ?? null);

        $order->load(['items.product']);
        $request->session()->flash('status', 'Your proof response has been recorded.');

        return response()
            ->view('storefront.orders.track-result', [
                'order' => $order,
                'customerEmail' => $validated['customer_email'],
            ])
            ->withHeaders([
                'X-Azraq-Status' => 'proof-response-recorded',
            ]);
    }

    public function updateProofDecisionSigned(Request $request, Order $order, OrderItem $item)
    {
        abort_unless($item->order_id === $order->id, 404);

        $validated = $request->validate([
            'decision' => ['required', 'in:approve,changes_requested'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $generatedProofs = data_get($item->line_item_meta, 'generated_proofs', []);
        abort_if(empty($generatedProofs), 404);

        $this->applyProofDecision($order, $item, $validated['decision'], $validated['note'] ?? null);

        $order->loadMissing(['items.product']);
        $item->refresh();
        $item->loadMissing([
            'product.personalizationTemplate.fields',
            'product.personalizationTemplate.fonts',
            'product.personalizationMockups.map',
        ]);
        session()->flash('status', 'Your proof response has been recorded.');

        return view('storefront.orders.proof-review', [
            'order' => $order,
            'item' => $item,
            'renderPreview' => data_get($item->line_item_meta, 'render_preview')
                ?? NikahRenderPreview::buildForOrderItem($item),
        ]);
    }

    private function applyProofDecision(Order $order, OrderItem $item, string $decision, ?string $note): void
    {
        DB::transaction(function () use ($order, $item, $decision, $note): void {
            $meta = $item->line_item_meta ?? [];
            $meta['customer_proof_decision'] = $decision;
            $meta['customer_proof_note'] = $note;
            $meta['customer_proof_responded_at'] = now()->toIso8601String();

            $item->update([
                'personalization_status' => $decision === 'approve' ? 'proof_approved' : 'changes_requested',
                'line_item_meta' => $meta,
            ]);

            $order->events()->create([
                'event_type' => 'customer_proof_response',
                'message' => 'Customer responded to proof for '.$item->product_name.'.',
                'meta' => [
                    'order_item_id' => $item->id,
                    'decision' => $decision,
                    'note' => $note,
                ],
            ]);
        });
    }
}
