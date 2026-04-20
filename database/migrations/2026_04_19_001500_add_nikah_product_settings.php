<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('gallery_default_source')->default('manual_featured_image')->after('featured_image_url');
            $table->boolean('show_flat_preview_first')->default(true)->after('gallery_default_source');
            $table->boolean('include_mockup_gallery')->default(true)->after('show_flat_preview_first');
            $table->boolean('live_preview_enabled')->default(true)->after('include_mockup_gallery');
        });

        Schema::create('product_personalization_mockup', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('personalization_mockup_id')->constrained('personalization_mockups')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->unique(['product_id', 'personalization_mockup_id'], 'product_mockup_unique');
        });

        Schema::create('product_related_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_related_categories');
        Schema::dropIfExists('product_personalization_mockup');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'gallery_default_source',
                'show_flat_preview_first',
                'include_mockup_gallery',
                'live_preview_enabled',
            ]);
        });
    }
};
