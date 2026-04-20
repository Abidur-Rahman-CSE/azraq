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

        $mapNeedsRepair = $this->foreignKeyTarget('personalization_mockup_maps') === 'personalization_mockups_old';
        $pivotNeedsRepair = $this->foreignKeyTarget('product_personalization_mockup') === 'personalization_mockups_old';

        if (! $mapNeedsRepair && ! $pivotNeedsRepair) {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        if ($mapNeedsRepair) {
            $this->rebuildMockupMaps();
        }

        if ($pivotNeedsRepair) {
            $this->rebuildProductMockupPivot();
        }

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

    private function rebuildMockupMaps(): void
    {
        DB::statement('ALTER TABLE personalization_mockup_maps RENAME TO personalization_mockup_maps_old');
        DB::statement('DROP INDEX IF EXISTS personalization_mockup_maps_personalization_mockup_id_unique');

        Schema::create('personalization_mockup_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personalization_mockup_id')->constrained()->cascadeOnDelete();
            $table->string('map_type')->default('quad');
            $table->decimal('top_left_x', 5, 2)->default(0.20);
            $table->decimal('top_left_y', 5, 2)->default(0.18);
            $table->decimal('top_right_x', 5, 2)->default(0.80);
            $table->decimal('top_right_y', 5, 2)->default(0.18);
            $table->decimal('bottom_right_x', 5, 2)->default(0.80);
            $table->decimal('bottom_right_y', 5, 2)->default(0.82);
            $table->decimal('bottom_left_x', 5, 2)->default(0.20);
            $table->decimal('bottom_left_y', 5, 2)->default(0.82);
            $table->boolean('normalized_coordinates')->default(true);
            $table->string('fit_mode')->default('contain');
            $table->decimal('object_position_x', 5, 2)->nullable();
            $table->decimal('object_position_y', 5, 2)->nullable();
            $table->decimal('manual_rotation', 6, 2)->nullable();
            $table->decimal('shadow_strength', 5, 2)->nullable();
            $table->decimal('highlight_strength', 5, 2)->nullable();
            $table->decimal('opacity', 5, 2)->nullable();
            $table->timestamps();
        });

        DB::statement('
            INSERT INTO personalization_mockup_maps (
                id, personalization_mockup_id, map_type, top_left_x, top_left_y, top_right_x, top_right_y,
                bottom_right_x, bottom_right_y, bottom_left_x, bottom_left_y, normalized_coordinates,
                fit_mode, object_position_x, object_position_y, manual_rotation, shadow_strength,
                highlight_strength, opacity, created_at, updated_at
            )
            SELECT
                id, personalization_mockup_id, map_type, top_left_x, top_left_y, top_right_x, top_right_y,
                bottom_right_x, bottom_right_y, bottom_left_x, bottom_left_y, normalized_coordinates,
                fit_mode, object_position_x, object_position_y, manual_rotation, shadow_strength,
                highlight_strength, opacity, created_at, updated_at
            FROM personalization_mockup_maps_old
        ');

        DB::statement('DROP TABLE personalization_mockup_maps_old');
    }

    private function rebuildProductMockupPivot(): void
    {
        DB::statement('ALTER TABLE product_personalization_mockup RENAME TO product_personalization_mockup_old');
        DB::statement('DROP INDEX IF EXISTS product_mockup_unique');

        Schema::create('product_personalization_mockup', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('personalization_mockup_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['product_id', 'personalization_mockup_id'], 'product_mockup_unique');
        });

        DB::statement('
            INSERT INTO product_personalization_mockup (
                id, product_id, personalization_mockup_id, sort_order, is_default, created_at, updated_at
            )
            SELECT
                id, product_id, personalization_mockup_id, sort_order, is_default, created_at, updated_at
            FROM product_personalization_mockup_old
        ');

        DB::statement('DROP TABLE product_personalization_mockup_old');
    }
};
