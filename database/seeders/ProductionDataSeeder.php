<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductionDataSeeder extends Seeder
{
    public function run(): void
    {
        $dump = json_decode(
            file_get_contents(database_path('seeders/data/production.json')),
            true
        );

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Order matters: parent tables before child tables
        $tables = [
            // Auth / base
            'users',
            'settings',
            'pages',

            // Catalog
            'categories',
            'collections',
            'tags',
            'products',
            'product_images',
            'product_variants',
            'bundle_items',
            'service_product_meta',

            // Pivot / relations
            'collection_product',
            'product_tag',
            'product_related',
            'product_related_categories',
            'category_related',

            // Personalization
            'personalization_templates',
            'personalization_mockups',
            'personalization_mockup_maps',
            'personalization_fields',
            'personalization_fonts',
            'product_personalization_mockup',

            // Content
            'faqs',
            'homepage_sections',
            'reviews',
            'coupons',

            // Commerce
            'orders',
            'order_items',
            'order_events',
            'inventory_movements',
            'booking_requests',
        ];

        foreach ($tables as $table) {
            $rows = $dump[$table] ?? [];

            DB::table($table)->truncate();

            if (empty($rows)) {
                $this->command->getOutput()->writeln("  <comment>–</comment> $table: empty");
                continue;
            }

            $rows = array_map(fn ($row) => (array) $row, $rows);

            foreach (array_chunk($rows, 50) as $chunk) {
                DB::table($table)->insert($chunk);
            }

            $this->command->getOutput()->writeln(
                "  <info>✓</info> $table: " . count($rows) . ' rows'
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
