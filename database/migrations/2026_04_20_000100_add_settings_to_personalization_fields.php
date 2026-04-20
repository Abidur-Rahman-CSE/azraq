<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personalization_fields', function (Blueprint $table): void {
            $table->json('settings')->nullable()->after('preview_sample_value');
        });
    }

    public function down(): void
    {
        Schema::table('personalization_fields', function (Blueprint $table): void {
            $table->dropColumn('settings');
        });
    }
};
