<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $templateId = DB::table('personalization_templates')
            ->join('products', 'personalization_templates.product_id', '=', 'products.id')
            ->where('products.sku', 'SIGNATURE-NIKAH-PREMIUM')
            ->value('personalization_templates.id');

        if (! $templateId) {
            return;
        }

        $field = DB::table('personalization_fields')
            ->where('personalization_template_id', $templateId)
            ->where('field_key', 'quotation')
            ->first();

        if (! $field) {
            return;
        }

        $settings = json_decode($field->settings ?: '[]', true) ?: [];
        $settings['auto_fit'] = true;
        $settings['allow_multiline'] = true;
        $settings['max_lines'] = max(3, (int) ($settings['max_lines'] ?? 3));
        $settings['overflow_behavior'] = 'shrink_then_wrap';

        DB::table('personalization_fields')
            ->where('id', $field->id)
            ->update([
                'font_size_min' => min((int) $field->font_size_min ?: 14, 14),
                'font_size_max' => min((int) $field->font_size_max ?: 22, 22),
                'settings' => json_encode($settings),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        $templateId = DB::table('personalization_templates')
            ->join('products', 'personalization_templates.product_id', '=', 'products.id')
            ->where('products.sku', 'SIGNATURE-NIKAH-PREMIUM')
            ->value('personalization_templates.id');

        if (! $templateId) {
            return;
        }

        $field = DB::table('personalization_fields')
            ->where('personalization_template_id', $templateId)
            ->where('field_key', 'quotation')
            ->first();

        if (! $field) {
            return;
        }

        $settings = json_decode($field->settings ?: '[]', true) ?: [];
        $settings['auto_fit'] = true;
        $settings['allow_multiline'] = true;
        $settings['max_lines'] = 3;
        $settings['overflow_behavior'] = 'shrink_only';

        DB::table('personalization_fields')
            ->where('id', $field->id)
            ->update([
                'font_size_min' => 14,
                'font_size_max' => 35,
                'settings' => json_encode($settings),
                'updated_at' => now(),
            ]);
    }
};
