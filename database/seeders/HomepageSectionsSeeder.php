<?php

namespace Database\Seeders;

use App\Models\HomepageSection;
use Illuminate\Database\Seeder;

class HomepageSectionsSeeder extends Seeder
{
    public function run(): void
    {
        // Existing keys are seeded by CatalogSeeder. This seeder upserts the 6 new
        // section_keys introduced in the v3 home page redesign so they appear in
        // /admin/homepage-sections and become editable.
        $sections = [
            [
                'section_key' => 'stats_strip',
                'title' => 'Built on craft, trusted by hundreds.',
                'subtitle' => 'Trust strip',
                'content' => null,
                'cta_label' => null,
                'cta_href' => null,
                'sort_order' => 5,
                'is_enabled' => true,
                'settings' => [
                    'stats' => [
                        ['num' => '350+', 'label' => 'Brides served'],
                        ['num' => '48 hr', 'label' => 'Proof turnaround'],
                        ['num' => '12 yrs', 'label' => 'Atelier in Dhaka'],
                        ['num' => '100%', 'label' => 'Hand finished'],
                    ],
                ],
            ],
            [
                'section_key' => 'signature_nikah_spotlight',
                'title' => 'Signature Nikah Nama',
                'subtitle' => 'Signature Nikah',
                'content' => 'Premium Nikah personalization with structured details, curated typography, and proof-aware production.',
                'cta_label' => 'Customize your Nikah',
                'cta_href' => null,
                'sort_order' => 15,
                'is_enabled' => true,
                'settings' => [
                    'image_url' => null,
                    'product_id' => null,
                    'process_steps' => [
                        '01 Fill details',
                        '02 Choose typography',
                        '03 Approve proof',
                    ],
                    'secondary_cta_label' => 'Explore Nikah Collection',
                    'secondary_cta_href' => '/categories/nikah-collection',
                ],
            ],
            [
                'section_key' => 'atelier_services',
                'title' => 'Bookings handled with the same care as our pieces.',
                'subtitle' => 'Atelier services',
                'content' => 'Bridal makeup, mehendi, and ceremony consultations — inquiry-first, never stock-first.',
                'cta_label' => null,
                'cta_href' => null,
                'sort_order' => 35,
                'is_enabled' => true,
                'settings' => [
                    'service_ids' => [],
                ],
            ],
            [
                'section_key' => 'finale_cta',
                'title' => 'Crafted for moments that last forever.',
                'subtitle' => 'Begin the journey',
                'content' => 'A 15-minute consultation. Zero obligation. Visit our atelier or chat on WhatsApp.',
                'cta_label' => 'Book a consultation',
                'cta_href' => '/shop?type=service',
                'sort_order' => 45,
                'is_enabled' => true,
                'settings' => [
                    'background_image_url' => null,
                    'secondary_cta_label' => 'Browse the shop',
                    'secondary_cta_href' => '/shop',
                ],
            ],
            [
                'section_key' => 'instagram_strip',
                'title' => 'From our atelier',
                'subtitle' => 'Instagram',
                'content' => 'Recent bridal moments and behind-the-scenes from the studio.',
                'cta_label' => 'Follow on Instagram',
                'cta_href' => 'https://instagram.com/',
                'sort_order' => 32,
                'is_enabled' => false,
                'settings' => [
                    'posts' => [],
                ],
            ],
            [
                'section_key' => 'trust_strip',
                'title' => 'Trust signals',
                'subtitle' => 'Why Azraq',
                'content' => null,
                'cta_label' => null,
                'cta_href' => null,
                'sort_order' => 50,
                'is_enabled' => true,
                'settings' => [
                    'signals' => [
                        ['icon' => '✦', 'label' => 'Hand-finished in Dhaka'],
                        ['icon' => '◈', 'label' => '48 hr proof turnaround'],
                        ['icon' => '❋', 'label' => 'WhatsApp support'],
                        ['icon' => '✧', 'label' => 'Nationwide delivery'],
                    ],
                ],
            ],
        ];

        foreach ($sections as $section) {
            HomepageSection::updateOrCreate(
                ['section_key' => $section['section_key']],
                $section
            );
        }
    }
}
