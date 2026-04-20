<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        Schema::create('personalization_mockup_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personalization_mockup_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('map_type')->default('quad');
            $table->decimal('top_left_x', 6, 4)->default(0.20);
            $table->decimal('top_left_y', 6, 4)->default(0.18);
            $table->decimal('top_right_x', 6, 4)->default(0.80);
            $table->decimal('top_right_y', 6, 4)->default(0.18);
            $table->decimal('bottom_right_x', 6, 4)->default(0.80);
            $table->decimal('bottom_right_y', 6, 4)->default(0.82);
            $table->decimal('bottom_left_x', 6, 4)->default(0.20);
            $table->decimal('bottom_left_y', 6, 4)->default(0.82);
            $table->boolean('normalized_coordinates')->default(true);
            $table->string('fit_mode')->default('contain');
            $table->decimal('object_position_x', 6, 4)->nullable();
            $table->decimal('object_position_y', 6, 4)->nullable();
            $table->decimal('manual_rotation', 6, 2)->nullable();
            $table->decimal('shadow_strength', 4, 2)->nullable();
            $table->decimal('highlight_strength', 4, 2)->nullable();
            $table->decimal('opacity', 4, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personalization_mockup_maps');
        Schema::dropIfExists('personalization_mockups');
    }
};
