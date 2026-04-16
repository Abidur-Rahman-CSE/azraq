<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Faq;
use App\Models\HomepageSection;
use App\Models\Collection;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\PersonalizationTemplate;
use App\Models\Tag;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $wear = Category::firstOrCreate(['slug' => 'customized-bridal-wear'], [
            'name' => 'Customized Bridal Wear',
            'description' => 'Dupattas and key bridal wear pieces.',
            'is_active' => true,
        ]);

        $nikah = Category::firstOrCreate(['slug' => 'nikah-collection'], [
            'name' => 'Nikah Collection',
            'description' => 'Nikah Nama and complementary personalized pieces.',
            'is_active' => true,
        ]);

        $booking = Category::firstOrCreate(['slug' => 'mehendi-event'], [
            'name' => 'Mehendi & Event',
            'description' => 'Bridal and mehendi service-led offerings.',
            'is_active' => true,
        ]);

        $accessories = Category::firstOrCreate(['slug' => 'bridal-accessories'], [
            'name' => 'Bridal Accessories',
            'description' => 'Mirror sets, badges, pillows, and finishing accessories.',
            'is_active' => true,
        ]);

        $bundles = Category::firstOrCreate(['slug' => 'packages-combos'], [
            'name' => 'Packages / Combos',
            'description' => 'Bundled bridal gifting and ceremony sets.',
            'is_active' => true,
        ]);

        $collection = Collection::firstOrCreate(['slug' => 'signature-nikah'], [
            'name' => 'Signature Nikah',
            'description' => 'Featured premium Nikah essentials.',
            'is_active' => true,
        ]);

        $bestSellers = Collection::firstOrCreate(['slug' => 'best-sellers'], [
            'name' => 'Best Sellers',
            'description' => 'Popular products across gifting and bridal essentials.',
            'is_active' => true,
        ]);

        $giftPicks = Collection::firstOrCreate(['slug' => 'gift-picks'], [
            'name' => 'Gift Picks',
            'description' => 'Giftable items and curated finishing pieces.',
            'is_active' => true,
        ]);

        $tag = Tag::firstOrCreate(['slug' => 'customizable'], [
            'name' => 'Customizable',
            'description' => 'Products with customization flows.',
            'is_active' => true,
        ]);

        $premium = Tag::firstOrCreate(['slug' => 'premium'], [
            'name' => 'Premium',
            'description' => 'Premium bridal or gift-ready pieces.',
            'is_active' => true,
        ]);

        $giftable = Tag::firstOrCreate(['slug' => 'giftable'], [
            'name' => 'Giftable',
            'description' => 'Products ideal for bridal gifting.',
            'is_active' => true,
        ]);

        $standard = Product::firstOrCreate(['slug' => 'bridal-dupatta'], [
            'category_id' => $wear->id,
            'name' => 'Bridal Dupatta',
            'sku' => 'AZR-DUP-001',
            'type' => ProductType::Standard,
            'status' => 'active',
            'excerpt' => 'A premium handcrafted bridal dupatta.',
            'description' => 'Foundation sample product for standard inventory-based merchandising.',
            'price' => 3200,
            'compare_at_price' => 3600,
            'manage_stock' => true,
            'stock_quantity' => 12,
            'low_stock_threshold' => 3,
            'is_featured' => true,
        ]);

        $advanced = Product::firstOrCreate(['slug' => 'signature-nikah-nama'], [
            'category_id' => $nikah->id,
            'name' => 'Signature Nikah Nama',
            'sku' => 'AZR-NIK-001',
            'type' => ProductType::AdvancedPersonalized,
            'status' => 'active',
            'excerpt' => 'Premium Nikah Nama with structured personalization flow.',
            'description' => 'Phase 1 sample for advanced personalized product architecture.',
            'price' => 2500,
            'manage_stock' => false,
            'stock_quantity' => 0,
            'low_stock_threshold' => 0,
            'is_featured' => true,
        ]);

        $service = Product::firstOrCreate(['slug' => 'mehendi-booking'], [
            'category_id' => $booking->id,
            'name' => 'Mehendi Booking',
            'sku' => 'AZR-SVC-001',
            'type' => ProductType::Service,
            'status' => 'active',
            'excerpt' => 'Service-led booking product with date and area preferences.',
            'description' => 'Phase 1 sample for service product metadata.',
            'price' => 5000,
            'manage_stock' => false,
            'stock_quantity' => 0,
            'low_stock_threshold' => 0,
            'is_featured' => false,
        ]);

        $bridalService = Product::firstOrCreate(['slug' => 'bridal-booking'], [
            'category_id' => $booking->id,
            'name' => 'Bridal Booking',
            'sku' => 'AZR-SVC-002',
            'type' => ProductType::Service,
            'status' => 'active',
            'excerpt' => 'Service-led bridal booking with package and area preferences.',
            'description' => 'Dedicated bridal service workflow separate from product inventory logic.',
            'price' => 8500,
            'manage_stock' => false,
            'stock_quantity' => 0,
            'low_stock_threshold' => 0,
            'is_featured' => true,
        ]);

        $nonBridalService = Product::firstOrCreate(['slug' => 'non-bridal-booking'], [
            'category_id' => $booking->id,
            'name' => 'Non-Bridal Booking',
            'sku' => 'AZR-SVC-003',
            'type' => ProductType::Service,
            'status' => 'active',
            'excerpt' => 'Event-ready non-bridal service booking.',
            'description' => 'Supports service-only conversion without inventory deduction.',
            'price' => 4500,
            'manage_stock' => false,
            'stock_quantity' => 0,
            'low_stock_threshold' => 0,
            'is_featured' => false,
        ]);

        $light = Product::firstOrCreate(['slug' => 'customized-pen'], [
            'category_id' => $nikah->id,
            'name' => 'Customized Pen',
            'sku' => 'AZR-PEN-001',
            'type' => ProductType::LightCustomizable,
            'status' => 'active',
            'excerpt' => 'Lightweight personalization for signatures and gifting.',
            'description' => 'Simple custom text product suited for compact personalization without the advanced Nikah builder.',
            'price' => 650,
            'manage_stock' => true,
            'stock_quantity' => 18,
            'low_stock_threshold' => 4,
            'is_featured' => true,
        ]);

        $mirror = Product::firstOrCreate(['slug' => 'bridal-mirror-ayna'], [
            'category_id' => $accessories->id,
            'name' => 'Bridal Mirror / Ayna',
            'sku' => 'AZR-MIR-001',
            'type' => ProductType::Standard,
            'status' => 'active',
            'excerpt' => 'Elegant handcrafted ayna for gifting and photography.',
            'description' => 'A premium accessory-led product for category and collection merchandising.',
            'price' => 1400,
            'compare_at_price' => 1650,
            'manage_stock' => true,
            'stock_quantity' => 9,
            'low_stock_threshold' => 2,
            'is_featured' => true,
        ]);

        $bundle = Product::firstOrCreate(['slug' => 'nikkah-combo'], [
            'category_id' => $bundles->id,
            'name' => 'Nikkah Combo',
            'sku' => 'AZR-BND-001',
            'type' => ProductType::Bundle,
            'status' => 'active',
            'excerpt' => 'A ready-made bundle for the Nikkah ceremony.',
            'description' => 'Bundle-ready product seeded for storefront discovery and combo spotlight sections.',
            'price' => 4200,
            'compare_at_price' => 4800,
            'manage_stock' => false,
            'stock_quantity' => 0,
            'low_stock_threshold' => 0,
            'is_featured' => true,
        ]);

        $collection->products()->syncWithoutDetaching([$advanced->id]);
        $bestSellers->products()->syncWithoutDetaching([$advanced->id, $standard->id, $mirror->id]);
        $giftPicks->products()->syncWithoutDetaching([$mirror->id, $light->id, $bundle->id]);

        $advanced->tags()->syncWithoutDetaching([$tag->id, $premium->id]);
        $light->tags()->syncWithoutDetaching([$tag->id, $giftable->id]);
        $mirror->tags()->syncWithoutDetaching([$premium->id, $giftable->id]);
        $bundle->tags()->syncWithoutDetaching([$premium->id, $giftable->id]);

        $advanced->relatedProducts()->syncWithoutDetaching([$standard->id, $light->id, $bundle->id]);
        $standard->relatedProducts()->syncWithoutDetaching([$mirror->id, $bundle->id]);
        $bundle->relatedProducts()->syncWithoutDetaching([$advanced->id, $light->id]);

        $bundle->bundleItems()->updateOrCreate([
            'bundle_product_id' => $bundle->id,
            'child_product_id' => $advanced->id,
        ], [
            'quantity' => 1,
            'position' => 0,
        ]);

        $bundle->bundleItems()->updateOrCreate([
            'bundle_product_id' => $bundle->id,
            'child_product_id' => $light->id,
        ], [
            'quantity' => 1,
            'position' => 1,
        ]);

        $standard->variants()->updateOrCreate([
            'product_id' => $standard->id,
            'sku' => 'AZR-DUP-001-RD',
        ], [
            'name' => 'Ruby Finish',
            'option_values' => ['ruby', 'standard'],
            'price' => 3200,
            'stock_quantity' => 7,
            'is_default' => true,
            'position' => 0,
        ]);

        $standard->variants()->updateOrCreate([
            'product_id' => $standard->id,
            'sku' => 'AZR-DUP-001-GL',
        ], [
            'name' => 'Gold Finish',
            'option_values' => ['gold', 'premium'],
            'price' => 3450,
            'stock_quantity' => 5,
            'is_default' => false,
            'position' => 1,
        ]);

        $light->variants()->updateOrCreate([
            'product_id' => $light->id,
            'sku' => 'AZR-PEN-001-GD',
        ], [
            'name' => 'Gold Barrel',
            'option_values' => ['gold'],
            'price' => 650,
            'stock_quantity' => 10,
            'is_default' => true,
            'position' => 0,
        ]);

        $light->variants()->updateOrCreate([
            'product_id' => $light->id,
            'sku' => 'AZR-PEN-001-SL',
        ], [
            'name' => 'Silver Barrel',
            'option_values' => ['silver'],
            'price' => 700,
            'stock_quantity' => 8,
            'is_default' => false,
            'position' => 1,
        ]);

        foreach ([
            [$standard, 'Hero detail', 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=900&q=80', true, 0],
            [$standard, 'Styling closeup', 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=900&q=80', false, 1],
            [$light, 'Signature pen', 'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=900&q=80', true, 0],
            [$light, 'Gift packaging', 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?auto=format&fit=crop&w=900&q=80', false, 1],
            [$advanced, 'Certificate preview', 'https://images.unsplash.com/photo-1516542076529-1ea3854896e1?auto=format&fit=crop&w=1200&q=80', true, 0],
        ] as [$product, $label, $url, $isPrimary, $position]) {
            ProductImage::updateOrCreate([
                'product_id' => $product->id,
                'image_url' => $url,
            ], [
                'label' => $label,
                'is_primary' => $isPrimary,
                'position' => $position,
            ]);
        }

        foreach ([
            [$standard, 'Amena Rahman', 5, 'Beautiful finishing', 'The finishing quality felt premium and the color looked gorgeous in person.'],
            [$standard, 'Sara Noor', 4, 'Perfect for gifting', 'It photographed beautifully and arrived in secure packaging.'],
            [$light, 'Mahira Islam', 5, 'Lovely add-on item', 'A small product, but it elevated the full Nikah set and the custom text felt polished.'],
        ] as [$product, $author, $rating, $title, $body]) {
            Review::updateOrCreate([
                'product_id' => $product->id,
                'author_name' => $author,
                'title' => $title,
            ], [
                'rating' => $rating,
                'body' => $body,
                'is_approved' => true,
            ]);
        }

        $template = PersonalizationTemplate::updateOrCreate([
            'product_id' => $advanced->id,
        ], [
            'name' => 'Signature Nikah Template',
            'preview_image_url' => 'https://images.unsplash.com/photo-1516542076529-1ea3854896e1?auto=format&fit=crop&w=1400&q=80',
            'preview_rules' => [
                'safe_scale' => true,
                'allow_multiline' => true,
            ],
            'render_rules' => [
                'export_format' => 'png',
                'proof_required' => true,
            ],
            'instructions' => 'Provide the bride and groom names, ceremony date, and venue details. Preview scaling is safe, but final proof will still be reviewed before fulfillment.',
            'proof_note_label' => 'Proof notes for the designer',
            'is_active' => true,
        ]);

        $template->fields()->delete();
        foreach ([
            ['label' => 'Bride Name', 'field_key' => 'bride_name', 'placeholder' => 'Enter bride name', 'help_text' => 'Use the final name spelling for the certificate.', 'max_length' => 28, 'font_size_min' => 22, 'font_size_max' => 40, 'position_x' => 50, 'position_y' => 30, 'width' => 60, 'height' => 10, 'text_align' => 'center'],
            ['label' => 'Groom Name', 'field_key' => 'groom_name', 'placeholder' => 'Enter groom name', 'help_text' => 'This appears directly under the bride name.', 'max_length' => 28, 'font_size_min' => 22, 'font_size_max' => 40, 'position_x' => 50, 'position_y' => 42, 'width' => 60, 'height' => 10, 'text_align' => 'center'],
            ['label' => 'Ceremony Date', 'field_key' => 'ceremony_date', 'placeholder' => '12 December 2026', 'help_text' => 'Written format looks best for the certificate.', 'max_length' => 32, 'font_size_min' => 16, 'font_size_max' => 26, 'position_x' => 50, 'position_y' => 60, 'width' => 45, 'height' => 8, 'text_align' => 'center'],
            ['label' => 'Venue', 'field_key' => 'venue', 'placeholder' => 'Dhaka, Bangladesh', 'help_text' => 'Keep this concise for the preview.', 'max_length' => 36, 'font_size_min' => 14, 'font_size_max' => 24, 'position_x' => 50, 'position_y' => 70, 'width' => 50, 'height' => 8, 'text_align' => 'center'],
        ] as $index => $field) {
            $template->fields()->create([
                ...$field,
                'min_length' => 0,
                'default_value' => null,
                'line_height' => 1.2,
                'letter_spacing' => 0,
                'text_color' => '#780000',
                'position' => $index,
            ]);
        }

        $template->fonts()->delete();
        foreach ([
            ['name' => 'Classic Script', 'css_font_family' => '"Times New Roman", serif', 'preview_label' => 'Classic Script', 'is_default' => true],
            ['name' => 'Modern Serif', 'css_font_family' => 'Georgia, serif', 'preview_label' => 'Modern Serif', 'is_default' => false],
            ['name' => 'Elegant Sans', 'css_font_family' => '"Poppins", sans-serif', 'preview_label' => 'Elegant Sans', 'is_default' => false],
        ] as $index => $font) {
            $template->fonts()->create([
                ...$font,
                'position' => $index,
            ]);
        }

        $service->serviceMeta()->updateOrCreate(['product_id' => $service->id], [
            'service_type' => 'mehendi',
            'duration_label' => 'Half day session',
            'location_scope' => 'Dhaka city',
            'requires_advance_payment' => true,
            'advance_payment_amount' => 1000,
            'booking_notes' => 'Used as demo booking metadata for the admin flow.',
        ]);

        $bridalService->serviceMeta()->updateOrCreate(['product_id' => $bridalService->id], [
            'service_type' => 'bridal',
            'duration_label' => 'Full day coverage',
            'location_scope' => 'Dhaka and nearby areas',
            'requires_advance_payment' => true,
            'advance_payment_amount' => 2000,
            'booking_notes' => 'Premium bridal slot with schedule confirmation and package review.',
        ]);

        $nonBridalService->serviceMeta()->updateOrCreate(['product_id' => $nonBridalService->id], [
            'service_type' => 'non_bridal',
            'duration_label' => 'Event session',
            'location_scope' => 'Dhaka city',
            'requires_advance_payment' => false,
            'advance_payment_amount' => null,
            'booking_notes' => 'Flexible event-ready booking flow without mandatory deposit.',
        ]);

        foreach ([
            ['group' => 'storefront', 'key' => 'announcement_text', 'value' => 'Free Dhaka delivery on premium personalized orders above BDT 3,000.'],
            ['group' => 'storefront', 'key' => 'announcement_cta_label', 'value' => 'Explore Nikah Collection'],
            ['group' => 'storefront', 'key' => 'announcement_cta_href', 'value' => '/collections/signature-nikah'],
            ['group' => 'storefront', 'key' => 'support_phone', 'value' => '+880 1700-000000'],
            ['group' => 'storefront', 'key' => 'support_email', 'value' => 'hello@azraqbridal.com'],
        ] as $setting) {
            Setting::updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }

        foreach ([
            ['section_key' => 'hero', 'title' => 'A premium bridal storefront with configurable homepage sections.', 'subtitle' => 'Homepage hero', 'content' => 'Drive the top-of-funnel narrative from admin instead of hardcoding campaign copy in templates.', 'cta_label' => 'Browse the shop', 'cta_href' => '/shop', 'sort_order' => 0, 'is_enabled' => true],
            ['section_key' => 'featured_categories', 'title' => 'Featured categories', 'subtitle' => 'Catalog discovery', 'content' => 'Highlight the primary Azraq catalog groups with clean editorial spacing.', 'cta_label' => 'View all categories', 'cta_href' => '/shop', 'sort_order' => 10, 'is_enabled' => true],
            ['section_key' => 'featured_products', 'title' => 'Featured products', 'subtitle' => 'Merchandising', 'content' => 'Put bestselling, premium, or campaign-led products into a shared homepage block.', 'cta_label' => 'See featured products', 'cta_href' => '/shop', 'sort_order' => 20, 'is_enabled' => true],
            ['section_key' => 'featured_collections', 'title' => 'Featured collections', 'subtitle' => 'Collection spotlight', 'content' => 'Promote best sellers, signature Nikah, and gift edits as reusable landing collections.', 'cta_label' => 'Open collections', 'cta_href' => '/shop', 'sort_order' => 30, 'is_enabled' => true],
            ['section_key' => 'faq_preview', 'title' => 'Frequently asked questions', 'subtitle' => 'Trust and support', 'content' => 'Bring delivery, personalization, and proof expectations into the homepage for better conversion clarity.', 'cta_label' => 'Read all FAQs', 'cta_href' => '/faq', 'sort_order' => 40, 'is_enabled' => true],
        ] as $section) {
            HomepageSection::updateOrCreate(
                ['section_key' => $section['section_key']],
                $section
            );
        }

        foreach ([
            ['question' => 'How long does a Nikah Nama proof take?', 'answer' => 'Proof-ready personalized orders usually receive a design review update within 2 to 4 business days.', 'sort_order' => 0, 'is_published' => true],
            ['question' => 'Can I order standard and personalized items together?', 'answer' => 'Yes. The checkout and order model support both standard products and advanced personalized products in the same order.', 'sort_order' => 10, 'is_published' => true],
            ['question' => 'Do you offer cash on delivery?', 'answer' => 'Yes. Cash on delivery is available on supported orders while online payment remains modeled as a separate status flow.', 'sort_order' => 20, 'is_published' => true],
        ] as $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }

        foreach ([
            ['title' => 'About Azraq Bridal', 'slug' => 'about', 'body' => 'Azraq Bridal focuses on premium bridal keepsakes, personalized Nikah essentials, and a calm, polished shopping experience.', 'meta_title' => 'About Azraq Bridal', 'meta_description' => 'Learn about Azraq Bridal and the design philosophy behind the storefront.', 'is_published' => true],
            ['title' => 'Contact', 'slug' => 'contact', 'body' => 'Reach out through WhatsApp, email, or the booking workflow for bridal and personalized order questions.', 'meta_title' => 'Contact Azraq Bridal', 'meta_description' => 'Contact Azraq Bridal for orders, personalization questions, and booking details.', 'is_published' => true],
            ['title' => 'Shipping Policy', 'slug' => 'shipping-policy', 'body' => 'Shipping timelines vary by product type. Personalized items may include proof review before fulfillment.', 'meta_title' => 'Shipping Policy', 'meta_description' => 'Azraq Bridal shipping policy and estimated delivery guidance.', 'is_published' => true],
            ['title' => 'Return Policy', 'slug' => 'return-policy', 'body' => 'Return eligibility depends on product condition and whether the order includes personalization or proof-approved work.', 'meta_title' => 'Return Policy', 'meta_description' => 'Azraq Bridal return and exchange guidance.', 'is_published' => true],
            ['title' => 'Privacy Policy', 'slug' => 'privacy-policy', 'body' => 'We collect only the customer, delivery, and personalization details required to process orders, proofs, and support conversations.', 'meta_title' => 'Privacy Policy', 'meta_description' => 'Azraq Bridal privacy and customer data handling policy.', 'is_published' => true],
            ['title' => 'Terms & Conditions', 'slug' => 'terms-and-conditions', 'body' => 'By placing an order, you agree to Azraq Bridal pricing, fulfillment, proof, and support terms across standard, personalized, combo, and booking products.', 'meta_title' => 'Terms & Conditions', 'meta_description' => 'Azraq Bridal storefront terms and conditions.', 'is_published' => true],
        ] as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }

        Coupon::updateOrCreate(
            ['code' => 'AZRAQ10'],
            ['type' => 'percent', 'value' => 10, 'minimum_order_amount' => 2000, 'is_active' => true]
        );
    }
}
