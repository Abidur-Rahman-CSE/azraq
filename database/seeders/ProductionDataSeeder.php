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

        // Insert in FK-safe order
        $tables = [
            'users',
            'categories',
            'collections',
            'tags',
            'products',
            'product_images',
            'collection_products',
            'product_tags',
            'faqs',
            'pages',
            'reviews',
            'coupons',
            'homepage_sections',
            'orders',
            'order_items',
        ];

        foreach ($tables as $table) {
            $rows = $dump[$table] ?? [];
            if (empty($rows)) {
                continue;
            }

            DB::table($table)->truncate();

            // Cast objects → arrays (JSON strings stay as-is, Laravel PDO binds correctly)
            $rows = array_map(fn ($row) => (array) $row, $rows);

            // Chunk to avoid max_allowed_packet limits
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
