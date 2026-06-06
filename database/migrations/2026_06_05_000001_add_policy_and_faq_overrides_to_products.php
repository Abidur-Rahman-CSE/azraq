<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->json('shipping_care_policy')->nullable()->after('variant_media_links');
            $table->json('product_faqs')->nullable()->after('shipping_care_policy');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['shipping_care_policy', 'product_faqs']);
        });
    }
};
