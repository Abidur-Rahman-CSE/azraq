<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        $fieldNeedsRepair = $this->foreignKeyTarget('personalization_fields') === 'personalization_templates_old';
        $fontNeedsRepair = $this->foreignKeyTarget('personalization_fonts') === 'personalization_templates_old';
        $mockupNeedsRepair = $this->foreignKeyTarget('personalization_mockups') === 'personalization_templates_old';

        if (! $fieldNeedsRepair && ! $fontNeedsRepair && ! $mockupNeedsRepair) {
            $this->cleanupLeftovers();
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        if ($fieldNeedsRepair) {
            $this->rebuildPersonalizationFields();
        }

        if ($fontNeedsRepair) {
            $this->rebuildPersonalizationFonts();
        }

        if ($mockupNeedsRepair) {
            $this->rebuildPersonalizationMockups();
        }

        $this->cleanupLeftovers();

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // Repair migration only; no rollback required.
    }

    private function foreignKeyTarget(string $table): ?string
    {
        $foreignKeys = DB::select("PRAGMA foreign_key_list('{$table}')");

        return $foreignKeys[0]->table ?? null;
    }

    private function rebuildPersonalizationFields(): void
    {
        DB::statement('ALTER TABLE personalization_fields RENAME TO personalization_fields_old');

        Schema::create('personalization_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personalization_template_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('field_key');
            $table->string('placeholder')->nullable();
            $table->string('help_text')->nullable();
            $table->string('default_value')->nullable();
            $table->unsignedInteger('max_length')->default(100);
            $table->unsignedInteger('min_length')->default(0);
            $table->unsignedInteger('font_size_min')->default(18);
            $table->unsignedInteger('font_size_max')->default(36);
            $table->decimal('line_height', 4, 2)->default(1.2);
            $table->decimal('letter_spacing', 6, 2)->default(0);
            $table->string('text_align')->default('center');
            $table->string('text_color')->default('#780000');
            $table->decimal('position_x', 5, 2)->default(50);
            $table->decimal('position_y', 5, 2)->default(50);
            $table->decimal('width', 5, 2)->default(70);
            $table->decimal('height', 5, 2)->default(10);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->boolean('is_required')->default(true);
            $table->decimal('rotation', 6, 2)->default(0);
            $table->unsignedInteger('z_index')->default(0);
            $table->string('preview_sample_value')->nullable();
            $table->json('settings')->nullable();
        });

        DB::statement('
            INSERT INTO personalization_fields (
                id, personalization_template_id, label, field_key, placeholder, help_text, default_value,
                max_length, min_length, font_size_min, font_size_max, line_height, letter_spacing,
                text_align, text_color, position_x, position_y, width, height, position, created_at,
                updated_at, is_required, rotation, z_index, preview_sample_value, settings
            )
            SELECT
                id, personalization_template_id, label, field_key, placeholder, help_text, default_value,
                max_length, min_length, font_size_min, font_size_max, line_height, letter_spacing,
                text_align, text_color, position_x, position_y, width, height, position, created_at,
                updated_at, is_required, rotation, z_index, preview_sample_value, settings
            FROM personalization_fields_old
        ');

        DB::statement('DROP TABLE personalization_fields_old');
    }

    private function rebuildPersonalizationFonts(): void
    {
        DB::statement('ALTER TABLE personalization_fonts RENAME TO personalization_fonts_old');

        Schema::create('personalization_fonts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personalization_template_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('css_font_family');
            $table->string('preview_label')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->string('internal_name')->nullable();
            $table->string('font_family')->nullable();
            $table->string('font_source_type')->default('local');
            $table->text('font_source_value')->nullable();
            $table->string('category')->nullable();
            $table->string('style_type')->nullable();
            $table->string('supported_use')->nullable();
            $table->string('preview_sample_text')->nullable();
            $table->string('font_weight_default')->nullable();
            $table->string('font_style_default')->nullable();
            $table->decimal('letter_spacing_default', 6, 2)->nullable();
            $table->decimal('line_height_default', 4, 2)->nullable();
            $table->string('text_transform_default')->nullable();
            $table->string('recommended_for')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
        });

        DB::statement('
            INSERT INTO personalization_fonts (
                id, personalization_template_id, name, css_font_family, preview_label, position,
                is_default, created_at, updated_at, internal_name, font_family, font_source_type,
                font_source_value, category, style_type, supported_use, preview_sample_text,
                font_weight_default, font_style_default, letter_spacing_default, line_height_default,
                text_transform_default, recommended_for, is_active, sort_order
            )
            SELECT
                id, personalization_template_id, name, css_font_family, preview_label, position,
                is_default, created_at, updated_at, internal_name, font_family, font_source_type,
                font_source_value, category, style_type, supported_use, preview_sample_text,
                font_weight_default, font_style_default, letter_spacing_default, line_height_default,
                text_transform_default, recommended_for, is_active, sort_order
            FROM personalization_fonts_old
        ');

        DB::statement('DROP TABLE personalization_fonts_old');
    }

    private function rebuildPersonalizationMockups(): void
    {
        DB::statement('ALTER TABLE personalization_mockups RENAME TO personalization_mockups_old');
        DB::statement('DROP INDEX IF EXISTS personalization_mockups_slug_unique');

        Schema::create('personalization_mockups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personalization_template_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('base_image_url');
            $table->string('overlay_image_url')->nullable();
            $table->string('mask_image_url')->nullable();
            $table->string('thumb_image_url')->nullable();
            $table->string('render_mode')->default('perspective_quad');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::statement('
            INSERT INTO personalization_mockups (
                id, personalization_template_id, title, slug, base_image_url, overlay_image_url,
                mask_image_url, thumb_image_url, render_mode, sort_order, is_active, notes,
                created_at, updated_at
            )
            SELECT
                id, personalization_template_id, title, slug, base_image_url, overlay_image_url,
                mask_image_url, thumb_image_url, render_mode, sort_order, is_active, notes,
                created_at, updated_at
            FROM personalization_mockups_old
        ');

        DB::statement('DROP TABLE personalization_mockups_old');
    }

    private function cleanupLeftovers(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('DROP TABLE IF EXISTS personalization_mockups_old');
        DB::statement('DROP TABLE IF EXISTS personalization_fields_old');
        DB::statement('DROP TABLE IF EXISTS personalization_fonts_old');

        if (! $this->indexExists('personalization_mockups_slug_unique') && Schema::hasTable('personalization_mockups')) {
            DB::statement('CREATE UNIQUE INDEX personalization_mockups_slug_unique ON personalization_mockups (slug)');
        }

        DB::statement('PRAGMA foreign_keys = ON');
    }

    private function indexExists(string $name): bool
    {
        return DB::table('sqlite_master')
            ->where('type', 'index')
            ->where('name', $name)
            ->exists();
    }
};
