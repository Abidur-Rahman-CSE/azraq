<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'combo_discount_type')) {
                $table->string('combo_discount_type')->nullable()->after('variant_media_links');
                $table->decimal('combo_discount_value', 12, 2)->nullable()->after('combo_discount_type');
                $table->string('combo_rounding_rule')->default('none')->after('combo_discount_value');
                $table->boolean('show_combo_savings_badge')->default(true)->after('combo_rounding_rule');
                $table->string('combo_promo_headline')->nullable()->after('show_combo_savings_badge');
                $table->text('combo_promo_subtitle')->nullable()->after('combo_promo_headline');
                $table->string('marketing_label')->nullable()->after('combo_promo_subtitle');
                $table->boolean('show_related_combos_on_product')->default(true)->after('marketing_label');
                $table->boolean('show_related_combos_in_cart')->default(true)->after('show_related_combos_on_product');
            }
        });

        Schema::table('bundle_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('bundle_items', 'default_variant_id')) {
                $table->boolean('is_required')->default(true)->after('quantity');
                $table->foreignId('default_variant_id')->nullable()->after('is_required')->constrained('product_variants')->nullOnDelete();
                $table->json('allowed_variant_ids')->nullable()->after('default_variant_id');
                $table->boolean('variant_change_allowed')->default(false)->after('allowed_variant_ids');
                $table->boolean('discount_eligible')->default(true)->after('variant_change_allowed');
                $table->boolean('excluded_upgrade')->default(false)->after('discount_eligible');
                $table->string('price_mode')->default('add_child_price')->after('excluded_upgrade');
                $table->decimal('custom_price', 12, 2)->nullable()->after('price_mode');
                $table->string('display_label')->nullable()->after('custom_price');
                $table->boolean('show_on_hero')->default(true)->after('display_label');
                $table->boolean('show_in_details')->default(true)->after('show_on_hero');
            }
        });

        Schema::table('service_product_meta', function (Blueprint $table): void {
            if (! Schema::hasColumn('service_product_meta', 'confirmation_note')) {
                $table->text('confirmation_note')->nullable()->after('booking_notes');
                $table->text('available_areas')->nullable()->after('confirmation_note');
                $table->text('available_days')->nullable()->after('available_areas');
                $table->text('time_slot_options')->nullable()->after('available_days');
                $table->unsignedInteger('minimum_notice_days')->nullable()->after('time_slot_options');
                $table->unsignedInteger('max_bookings_per_day')->nullable()->after('minimum_notice_days');
                $table->boolean('travel_outside_area_allowed')->default(false)->after('max_bookings_per_day');
                $table->text('extra_charge_note')->nullable()->after('travel_outside_area_allowed');
                $table->json('include_items')->nullable()->after('extra_charge_note');
                $table->json('packages')->nullable()->after('include_items');
                $table->json('booking_flow')->nullable()->after('packages');
                $table->json('before_appointment')->nullable()->after('booking_flow');
                $table->json('pricing_notes')->nullable()->after('before_appointment');
                $table->json('policies')->nullable()->after('pricing_notes');
                $table->json('faqs')->nullable()->after('policies');
                $table->text('gallery_intro_text')->nullable()->after('faqs');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_product_meta', function (Blueprint $table): void {
            foreach ([
                'confirmation_note',
                'available_areas',
                'available_days',
                'time_slot_options',
                'minimum_notice_days',
                'max_bookings_per_day',
                'travel_outside_area_allowed',
                'extra_charge_note',
                'include_items',
                'packages',
                'booking_flow',
                'before_appointment',
                'pricing_notes',
                'policies',
                'faqs',
                'gallery_intro_text',
            ] as $column) {
                if (Schema::hasColumn('service_product_meta', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('bundle_items', function (Blueprint $table): void {
            if (Schema::hasColumn('bundle_items', 'default_variant_id')) {
                $table->dropConstrainedForeignId('default_variant_id');
            }

            foreach ([
                'is_required',
                'allowed_variant_ids',
                'variant_change_allowed',
                'discount_eligible',
                'excluded_upgrade',
                'price_mode',
                'custom_price',
                'display_label',
                'show_on_hero',
                'show_in_details',
            ] as $column) {
                if (Schema::hasColumn('bundle_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            foreach ([
                'combo_discount_type',
                'combo_discount_value',
                'combo_rounding_rule',
                'show_combo_savings_badge',
                'combo_promo_headline',
                'combo_promo_subtitle',
                'marketing_label',
                'show_related_combos_on_product',
                'show_related_combos_in_cart',
            ] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
