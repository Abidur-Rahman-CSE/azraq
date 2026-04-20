<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personalization_templates', function (Blueprint $table) {
            $table->unsignedInteger('export_ratio_width')->default(9)->after('mask_image_url');
            $table->unsignedInteger('export_ratio_height')->default(13)->after('export_ratio_width');
        });

        Schema::table('personalization_fields', function (Blueprint $table) {
            $table->unsignedInteger('z_index')->default(0)->after('rotation');
            $table->string('preview_sample_value')->nullable()->after('z_index');
        });
    }

    public function down(): void
    {
        Schema::table('personalization_fields', function (Blueprint $table) {
            $table->dropColumn(['z_index', 'preview_sample_value']);
        });

        Schema::table('personalization_templates', function (Blueprint $table) {
            $table->dropColumn(['export_ratio_width', 'export_ratio_height']);
        });
    }
};
