<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personalization_templates', function (Blueprint $table): void {
            $table->string('preview_image_url')->nullable()->change();
            $table->string('mask_image_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('personalization_templates', function (Blueprint $table): void {
            $table->string('preview_image_url')->nullable(false)->change();
            $table->string('mask_image_url')->nullable(false)->change();
        });
    }
};
