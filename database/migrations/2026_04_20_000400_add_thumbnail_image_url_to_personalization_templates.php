<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personalization_templates', function (Blueprint $table) {
            $table->string('thumbnail_image_url')->nullable()->after('mask_image_url');
        });
    }

    public function down(): void
    {
        Schema::table('personalization_templates', function (Blueprint $table) {
            $table->dropColumn('thumbnail_image_url');
        });
    }
};
