<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    public function faq()
    {
        $faqs = Cache::remember('storefront.faqs', now()->addMinutes(10), fn () => Faq::where('is_published', true)->orderBy('sort_order')->get());

        return view('storefront.pages.faq', [
            'faqs' => $faqs,
            'faqGroups' => $this->faqGroups($faqs),
        ]);
    }

    public function show(Page $page)
    {
        abort_unless($page->is_published, 404);

        return view('storefront.pages.show', [
            'page' => $page,
            'pageKind' => $this->pageKind($page),
        ]);
    }

    private function pageKind(Page $page): string
    {
        return match ($page->slug) {
            'about' => 'about',
            'contact' => 'contact',
            'shipping-policy', 'return-policy', 'privacy-policy', 'terms-and-conditions' => 'policy',
            default => 'general',
        };
    }

    private function faqGroups(Collection $faqs): array
    {
        $groups = [
            'Shipping' => collect(),
            'Personalization' => collect(),
            'Proof Process' => collect(),
            'Returns' => collect(),
            'Combos' => collect(),
            'Bookings' => collect(),
        ];

        foreach ($faqs as $faq) {
            $haystack = str($faq->question.' '.$faq->answer)->lower()->value();

            $group = match (true) {
                str_contains($haystack, 'proof') => 'Proof Process',
                str_contains($haystack, 'personal') || str_contains($haystack, 'nikah') => 'Personalization',
                str_contains($haystack, 'combo') || str_contains($haystack, 'bundle') => 'Combos',
                str_contains($haystack, 'booking') || str_contains($haystack, 'service') => 'Bookings',
                str_contains($haystack, 'return') || str_contains($haystack, 'refund') => 'Returns',
                default => 'Shipping',
            };

            $groups[$group]->push($faq);
        }

        return collect($groups)
            ->filter(fn (Collection $items) => $items->isNotEmpty())
            ->map(fn (Collection $items, string $label) => [
                'label' => $label,
                'items' => $items->values(),
            ])
            ->values()
            ->all();
    }
}
