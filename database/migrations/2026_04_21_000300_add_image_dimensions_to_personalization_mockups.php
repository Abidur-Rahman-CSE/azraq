<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personalization_mockups', function (Blueprint $table) {
            $table->unsignedInteger('image_width')->nullable()->after('thumb_image_url');
            $table->unsignedInteger('image_height')->nullable()->after('image_width');
        });
    }

    public function down(): void
    {
        Schema::table('personalization_mockups', function (Blueprint $table) {
            $table->dropColumn(['image_width', 'image_height']);
        });
    }
};
