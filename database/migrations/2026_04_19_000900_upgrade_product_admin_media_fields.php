<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('featured_image_url')->nullable()->after('is_featured');
            $table->string('video_url')->nullable()->after('featured_image_url');
            $table->boolean('proof_notes_enabled')->default(false)->after('video_url');
            $table->boolean('font_presets_enabled')->default(false)->after('proof_notes_enabled');
            $table->text('personalization_help_text')->nullable()->after('font_presets_enabled');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->string('alt_text')->nullable()->after('image_url');
            $table->string('status')->default('active')->after('is_primary');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn(['alt_text', 'status']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'featured_image_url',
                'video_url',
                'proof_notes_enabled',
                'font_presets_enabled',
                'personalization_help_text',
            ]);
        });
    }
};
