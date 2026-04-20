<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('description');
            $table->string('banner_image_url')->nullable()->after('image_url');
            $table->string('mobile_banner_image_url')->nullable()->after('banner_image_url');
            $table->string('icon_image_url')->nullable()->after('mobile_banner_image_url');
            $table->string('alt_text')->nullable()->after('icon_image_url');
            $table->boolean('is_featured')->default(false)->after('sort_order');
            $table->boolean('show_on_homepage')->default(false)->after('is_featured');
            $table->string('seo_image_url')->nullable()->after('show_on_homepage');
        });

        Schema::create('category_related', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('related_category_id')->constrained('categories')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['category_id', 'related_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_related');

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'image_url',
                'banner_image_url',
                'mobile_banner_image_url',
                'icon_image_url',
                'alt_text',
                'is_featured',
                'show_on_homepage',
                'seo_image_url',
            ]);
        });
    }
};
