<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personalization_templates', function (Blueprint $table) {
            $table->string('base_template_url')->nullable()->after('name');
            $table->string('mask_image_url')->nullable()->after('preview_image_url');
            $table->json('preview_data_presets')->nullable()->after('render_rules');
            $table->text('safe_zone_notes')->nullable()->after('instructions');
        });

        Schema::table('personalization_fields', function (Blueprint $table) {
            $table->boolean('is_required')->default(true)->after('default_value');
            $table->decimal('rotation', 6, 2)->default(0)->after('height');
        });
    }

    public function down(): void
    {
        Schema::table('personalization_fields', function (Blueprint $table) {
            $table->dropColumn(['is_required', 'rotation']);
        });

        Schema::table('personalization_templates', function (Blueprint $table) {
            $table->dropColumn([
                'base_template_url',
                'mask_image_url',
                'preview_data_presets',
                'safe_zone_notes',
            ]);
        });
    }
};
