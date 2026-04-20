<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personalization_fonts', function (Blueprint $table) {
            $table->string('internal_name')->nullable()->after('personalization_template_id');
            $table->string('font_family')->nullable()->after('preview_label');
            $table->string('font_source_type')->default('local')->after('font_family');
            $table->text('font_source_value')->nullable()->after('font_source_type');
            $table->string('category')->nullable()->after('font_source_value');
            $table->string('style_type')->nullable()->after('category');
            $table->string('supported_use')->nullable()->after('style_type');
            $table->string('preview_sample_text')->nullable()->after('supported_use');
            $table->string('font_weight_default')->nullable()->after('preview_sample_text');
            $table->string('font_style_default')->nullable()->after('font_weight_default');
            $table->decimal('letter_spacing_default', 6, 2)->nullable()->after('font_style_default');
            $table->decimal('line_height_default', 4, 2)->nullable()->after('letter_spacing_default');
            $table->string('text_transform_default')->nullable()->after('line_height_default');
            $table->string('recommended_for')->nullable()->after('text_transform_default');
            $table->boolean('is_active')->default(true)->after('is_default');
            $table->unsignedInteger('sort_order')->default(0)->after('position');
        });

        DB::table('personalization_fonts')->orderBy('id')->get()->each(function ($font): void {
            $name = $font->name ?? 'Preset';
            $category = str_contains(strtolower($name), 'script')
                ? 'Classic Script'
                : (str_contains(strtolower($name), 'serif') ? 'Elegant Serif' : 'Minimal Sans');

            DB::table('personalization_fonts')
                ->where('id', $font->id)
                ->update([
                    'internal_name' => str($name)->snake()->toString(),
                    'font_family' => $font->css_font_family,
                    'category' => $category,
                    'style_type' => $category,
                    'supported_use' => 'all',
                    'preview_sample_text' => $font->preview_label ?: 'Amena & Hassan',
                    'font_weight_default' => '600',
                    'font_style_default' => 'normal',
                    'letter_spacing_default' => 0,
                    'line_height_default' => 1.2,
                    'text_transform_default' => 'none',
                    'recommended_for' => 'all',
                    'is_active' => true,
                    'sort_order' => $font->position ?? 0,
                ]);
        });
    }

    public function down(): void
    {
        Schema::table('personalization_fonts', function (Blueprint $table) {
            $table->dropColumn([
                'internal_name',
                'font_family',
                'font_source_type',
                'font_source_value',
                'category',
                'style_type',
                'supported_use',
                'preview_sample_text',
                'font_weight_default',
                'font_style_default',
                'letter_spacing_default',
                'line_height_default',
                'text_transform_default',
                'recommended_for',
                'is_active',
                'sort_order',
            ]);
        });
    }
};
