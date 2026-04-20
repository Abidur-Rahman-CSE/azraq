<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->string('cover_image_url')->nullable()->after('description');
            $table->string('collection_mode')->default('manual')->after('cover_image_url');
            $table->boolean('is_featured')->default(false)->after('sort_order');
            $table->string('cta_label')->nullable()->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn([
                'cover_image_url',
                'collection_mode',
                'is_featured',
                'cta_label',
            ]);
        });
    }
};
