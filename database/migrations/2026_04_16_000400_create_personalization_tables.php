<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personalization_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('preview_image_url');
            $table->json('preview_rules')->nullable();
            $table->json('render_rules')->nullable();
            $table->text('instructions')->nullable();
            $table->string('proof_note_label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

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
        });

        Schema::create('personalization_fonts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personalization_template_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('css_font_family');
            $table->string('preview_label')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personalization_fonts');
        Schema::dropIfExists('personalization_fields');
        Schema::dropIfExists('personalization_templates');
    }
};
