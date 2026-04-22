<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderPersonalizationReviewRequest;
use App\Http\Requests\Admin\OrderStatusUpdateRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PersonalizationMockup;
use App\Models\PersonalizationTemplate;
use App\Services\MockupRenderService;
use App\Support\NikahRenderPreview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Throwable;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::withCount('items')->latest()->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'events']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(OrderStatusUpdateRequest $request, Order $order)
    {
        $before = [
            'payment_status' => $order->payment_status,
            'fulfillment_status' => $order->fulfillment_status,
            'shipping_status' => $order->shipping_status,
        ];

        $statuses = $request->safe()->except(['note']);

        $order->update($statuses);
        $order->items()->update([
            'payment_status' => $order->payment_status,
            'fulfillment_status' => $order->fulfillment_status,
        ]);

        $order->events()->create([
            'event_type' => 'status_updated',
            'message' => 'Admin updated order statuses.',
            'meta' => [
                'before' => $before,
                'after' => [
                    'payment_status' => $order->payment_status,
                    'fulfillment_status' => $order->fulfillment_status,
                    'shipping_status' => $order->shipping_status,
                ],
                'note' => $request->input('note'),
            ],
        ]);

        return redirect()->route('admin.orders.show', $order)->with('status', 'Order statuses updated.');
    }

    public function showPersonalizationReview(Request $request, Order $order, OrderItem $item)
    {
        abort_unless($item->order_id === $order->id, 404);

        $order->loadMissing('events');
        $item->loadMissing('product.personalizationTemplate.fields', 'product.personalizationTemplate.fonts', 'product.personalizationTemplate.mockups.map');

        $meta = $item->line_item_meta ?? [];
        $productTemplate = $item->product?->personalizationTemplate;
        $selectedTemplateId = $meta['review_template_id'] ?? $productTemplate?->id;
        $templates = PersonalizationTemplate::with(['product', 'mockups.map'])
            ->where(function ($query) use ($item): void {
                $query->where('product_id', $item->product_id)
                    ->orWhere('id', $item->product?->personalizationTemplate?->id);
            })
            ->orderBy('name')
            ->get();
        $currentTemplate = $selectedTemplateId
            ? $templates->firstWhere('id', $selectedTemplateId)
            : null;

        $currentMockup = ($meta['review_mockup_id'] ?? null) && $currentTemplate
            ? $currentTemplate->mockups->firstWhere('id', $meta['review_mockup_id'])
            : $currentTemplate?->mockups->firstWhere('is_active', true) ?? $currentTemplate?->mockups->first();
        $availableMockups = $templates->flatMap(function (PersonalizationTemplate $template) {
            return $template->mockups->map(function ($mockup) use ($template) {
                $mockup->setAttribute('template_label', $template->name);

                return $mockup;
            });
        })->sortBy('sort_order')->values();

        $renderPreview = NikahRenderPreview::buildForOrderItem($item, $currentTemplate, $currentMockup)
            ?? ($meta['render_preview'] ?? null);
        $proofLinkDays = max(1, min(30, $request->integer('proof_link_days', 7)));
        $customerProofExpiresAt = now()->addDays($proofLinkDays);

        return view('admin.orders.personalization-review', [
            'order' => $order,
            'item' => $item,
            'meta' => $meta,
            'productTemplate' => $productTemplate,
            'currentTemplate' => $currentTemplate,
            'currentMockup' => $currentMockup,
            'templates' => $templates,
            'mockups' => $availableMockups,
            'renderPreview' => $renderPreview,
            'proofLinkDays' => $proofLinkDays,
            'customerProofExpiresAt' => $customerProofExpiresAt,
            'customerProofUrl' => URL::temporarySignedRoute(
                'orders.proof.review',
                $customerProofExpiresAt,
                [$order, $item],
            ),
        ]);
    }

    public function updatePersonalizationReview(OrderPersonalizationReviewRequest $request, Order $order, OrderItem $item)
    {
        abort_unless($item->order_id === $order->id, 404);

        $template = $request->filled('template_id')
            ? PersonalizationTemplate::with('mockups')->find($request->integer('template_id'))
            : null;
        $mockup = $request->filled('mockup_id')
            ? PersonalizationMockup::find($request->integer('mockup_id'))
            : null;

        DB::transaction(function () use ($request, $order, $item, $template, $mockup): void {
            $meta = $item->line_item_meta ?? [];
            $renderPreview = NikahRenderPreview::buildForOrderItem($item, $template, $mockup);
            $meta['review_template_id'] = $template?->id;
            $meta['review_template_name'] = $template?->name;
            $meta['review_mockup_id'] = $mockup?->id;
            $meta['review_mockup_title'] = $mockup?->title;
            $meta['internal_note'] = $request->input('internal_note');
            $meta['review_note'] = $request->input('review_note');
            $meta['render_preview'] = $renderPreview;

            $item->update([
                'personalization_status' => $request->input('personalization_status'),
                'line_item_meta' => $meta,
            ]);

            $order->events()->create([
                'event_type' => 'personalization_review_updated',
                'message' => 'Admin updated the personalization review for '.$item->product_name.'.',
                'meta' => [
                    'order_item_id' => $item->id,
                    'personalization_status' => $item->personalization_status,
                    'template' => $template?->name,
                    'mockup' => $mockup?->title,
                    'review_note' => $request->input('review_note'),
                ],
            ]);
        });

        return redirect()
            ->route('admin.orders.personalization.show', [$order, $item])
            ->with('status', 'Personalization review updated.');
    }

    public function exportPersonalizationPreview(Request $request, Order $order, OrderItem $item, string $mode, string $format = 'svg'): Response
    {
        abort_unless($item->order_id === $order->id, 404);
        $isPreviewOnly = $request->boolean('preview');

        $item->loadMissing([
            'product.personalizationTemplate.fields',
            'product.personalizationTemplate.fonts',
            'product.personalizationMockups.map',
        ]);

        $meta = $item->line_item_meta ?? [];
        $selectedTemplate = filled($meta['review_template_id'] ?? null)
            ? PersonalizationTemplate::with(['fields', 'fonts', 'mockups.map'])->find($meta['review_template_id'])
            : $item->product?->personalizationTemplate;
        $selectedMockup = filled($meta['review_mockup_id'] ?? null)
            ? PersonalizationMockup::with('map')->find($meta['review_mockup_id'])
            : null;

        $renderPreview = NikahRenderPreview::buildForOrderItem($item, $selectedTemplate, $selectedMockup)
            ?? ($meta['render_preview'] ?? null);

        abort_unless(is_array($renderPreview), 404);

        $svg = view('admin.orders.personalization-proof-svg', [
            'order' => $order,
            'item' => $item,
            'mode' => $mode,
            'renderPreview' => $renderPreview,
        ])->render();

        if ($format === 'png') {
            try {
                $mockupRenderService = app(MockupRenderService::class);
                $blob = $mode === 'mockup'
                    ? $mockupRenderService->renderMockupProof(
                        $renderPreview,
                        view('admin.orders.personalization-proof-svg', [
                            'order' => $order,
                            'item' => $item,
                            'mode' => 'flat',
                            'renderPreview' => $renderPreview,
                        ])->render(),
                    )
                    : $this->rasterizeSvgToPng($svg);

                if (! $isPreviewOnly) {
                    $this->storeGeneratedProof($item, $order, $mode, 'png', $blob);
                }

                return response($blob, 200, [
                    'Content-Type' => 'image/png',
                    'Content-Disposition' => 'inline; filename="'.$order->order_number.'-'.$item->id.'-'.$mode.'-proof.png"',
                ]);
            } catch (Throwable $exception) {
                $blob = $this->fallbackPngPreview($renderPreview, $mode, $item);
                if (! $isPreviewOnly) {
                    $this->storeGeneratedProof($item, $order, $mode, 'png', $blob);
                }

                return response($blob, 200, [
                    'Content-Type' => 'image/png',
                    'Content-Disposition' => 'inline; filename="'.$order->order_number.'-'.$item->id.'-'.$mode.'-proof.png"',
                ]);
            }
        }

        if (! $isPreviewOnly) {
            $this->storeGeneratedProof($item, $order, $mode, 'svg', $svg);
        }

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="'.$order->order_number.'-'.$item->id.'-'.$mode.'-proof.svg"',
        ]);
    }

    private function rasterizeSvgToPng(string $svg): string
    {
        $image = app(MockupRenderService::class)->renderFlatFromSvg($svg);
        $image->setImageFormat('png32');
        $image->setImageCompressionQuality(100);

        $blob = $image->getImagesBlob();
        $image->clear();
        $image->destroy();

        return $blob;
    }

    private function fallbackPngPreview(array $renderPreview, string $mode, OrderItem $item): string
    {
        $width = $mode === 'flat' ? 900 : 1600;
        $height = $mode === 'flat' ? 1300 : 1200;
        $image = imagecreatetruecolor($width, $height);

        imagealphablending($image, true);
        imagesavealpha($image, true);

        $ivory = imagecolorallocate($image, 247, 241, 229);
        $white = imagecolorallocate($image, 255, 255, 255);
        $navy = imagecolorallocate($image, 0, 48, 73);
        $maroon = imagecolorallocate($image, 120, 0, 0);
        $soft = imagecolorallocate($image, 112, 120, 130);

        imagefilledrectangle($image, 0, 0, $width, $height, $ivory);

        if ($mode === 'flat') {
            imagefilledrectangle($image, 40, 40, $width - 40, $height - 40, $white);
            imagerectangle($image, 40, 40, $width - 40, $height - 40, $maroon);
        } else {
            imagefilledrectangle($image, 70, 70, $width - 70, $height - 70, $white);
            imagerectangle($image, 70, 70, $width - 70, $height - 70, $navy);
            imagestring($image, 5, 90, 92, (string) data_get($renderPreview, 'mockup.title', $item->product_name.' mockup proof'), $navy);
        }

        imagestring($image, 5, 72, 72, $item->product_name, $maroon);
        imagestring($image, 3, 72, $mode === 'flat' ? 102 : 124, strtoupper($mode).' PROOF PREVIEW', $soft);

        $y = $mode === 'flat' ? 180 : 190;
        foreach (data_get($renderPreview, 'flat.text_layers', []) as $layer) {
            $label = (string) data_get($layer, 'label', 'Field');
            $value = (string) data_get($layer, 'value', '');
            imagestring($image, 3, 90, $y, $label, $soft);
            imagestring($image, 5, 90, $y + 24, $value !== '' ? $value : '-', $navy);
            $y += 78;
        }

        ob_start();
        imagepng($image);
        $blob = (string) ob_get_clean();
        imagedestroy($image);

        return $blob;
    }

    private function storeGeneratedProof(OrderItem $item, Order $order, string $mode, string $format, string $contents): void
    {
        $directory = 'proofs/orders/'.$order->order_number.'/items/'.$item->id;
        $meta = $item->line_item_meta ?? [];
        $generatedProofs = $meta['generated_proofs'] ?? [];
        $modeSet = $generatedProofs[$mode] ?? [];
        $history = $modeSet[$format]['history'] ?? [];
        $nextVersion = count($history) + 1;
        $filename = $mode.'-proof-v'.$nextVersion.'.'.$format;
        $path = $directory.'/'.$filename;

        Storage::disk('public')->put($path, $contents);

        $entry = [
            'version' => $nextVersion,
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'generated_at' => now()->toIso8601String(),
        ];
        $history[] = $entry;
        $generatedProofs[$mode][$format] = [
            'latest' => $entry,
            'history' => $history,
        ];
        $meta['generated_proofs'] = $generatedProofs;

        $item->forceFill(['line_item_meta' => $meta])->save();
    }
}
