<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // The base personalization template migration now creates product_id as nullable.
        // This follow-up migration stays as a no-op so existing local migration history
        // remains consistent without rebuilding SQLite tables and breaking foreign keys.
    }

    public function down(): void
    {
        // Intentionally left blank.
    }
};
