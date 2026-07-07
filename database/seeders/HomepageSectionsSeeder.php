<?php

namespace Database\Seeders;

use App\Models\HomepageSection;
use Illuminate\Database\Seeder;

class HomepageSectionsSeeder extends Seeder
{
    public function run(): void
    {
        // Seed the complete homepage section registry, but only backfill missing
        // rows so existing admin-edited content never gets overwritten.
        $sections = [
            [
                'section_key' => 'hero',
                'title' => 'A premium bridal storefront with configurable homepage sections.',
                'subtitle' => 'Homepage hero',
                'content' => 'Drive the top-of-funnel narrative from admin instead of hardcoding campaign copy in templates.',
                'cta_label' => 'Browse the shop',
                'cta_href' => '/shop',
                'sort_order' => 0,
                'is_enabled' => true,
                'settings' => [
                    'desktop_image_url' => null,
                    'mobile_image_url' => null,
                    'secondary_cta_label' => null,
                    'secondary_cta_href' => null,
                    'featured_product_id' => null,
                    'slides' => [],
                ],
            ],
            [
                'section_key' => 'featured_categories',
                'title' => 'Featured categories',
                'subtitle' => 'Catalog discovery',
                'content' => 'Highlight the primary Azraq catalog groups with clean editorial spacing.',
                'cta_label' => 'View all categories',
                'cta_href' => '/shop',
                'sort_order' => 10,
                'is_enabled' => true,
                'settings' => [
                    'selected_category_ids' => [],
                ],
            ],
            [
                'section_key' => 'featured_products',
                'title' => 'Featured products',
                'subtitle' => 'Merchandising',
                'content' => 'Put bestselling, premium, or campaign-led products into a shared homepage block.',
                'cta_label' => 'See featured products',
                'cta_href' => '/shop',
                'sort_order' => 20,
                'is_enabled' => true,
                'settings' => [
                    'selected_product_ids' => [],
                ],
            ],
            [
                'section_key' => 'featured_collections',
                'title' => 'Featured collections',
                'subtitle' => 'Collection spotlight',
                'content' => 'Promote best sellers, signature Nikah, and gift edits as reusable landing collections.',
                'cta_label' => 'Open collections',
                'cta_href' => '/shop',
                'sort_order' => 30,
                'is_enabled' => true,
                'settings' => [
                    'selected_collection_ids' => [],
                ],
            ],
            [
                'section_key' => 'faq_preview',
                'title' => 'Frequently asked questions',
                'subtitle' => 'Trust and support',
                'content' => 'Bring delivery, personalization, and proof expectations into the homepage for better conversion clarity.',
                'cta_label' => 'Read all FAQs',
                'cta_href' => '/faq',
                'sort_order' => 40,
                'is_enabled' => true,
                'settings' => [],
            ],
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
            HomepageSection::firstOrCreate(
                ['section_key' => $section['section_key']],
                $section
            );
        }
    }
}
