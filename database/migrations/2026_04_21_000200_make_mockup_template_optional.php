<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personalization_mockups', function (Blueprint $table) {
            $table->dropForeign(['personalization_template_id']);
        });

        Schema::table('personalization_mockups', function (Blueprint $table) {
            $table->foreignId('personalization_template_id')->nullable()->change();
        });

        Schema::table('personalization_mockups', function (Blueprint $table) {
            $table->foreign('personalization_template_id')
                ->references('id')
                ->on('personalization_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        $fallbackTemplateId = DB::table('personalization_templates')->orderBy('id')->value('id');

        if ($fallbackTemplateId) {
            DB::table('personalization_mockups')
                ->whereNull('personalization_template_id')
                ->update(['personalization_template_id' => $fallbackTemplateId]);
        } else {
            DB::table('personalization_mockups')
                ->whereNull('personalization_template_id')
                ->delete();
        }

        Schema::table('personalization_mockups', function (Blueprint $table) {
            $table->dropForeign(['personalization_template_id']);
        });

        Schema::table('personalization_mockups', function (Blueprint $table) {
            $table->foreignId('personalization_template_id')->nullable(false)->change();
        });

        Schema::table('personalization_mockups', function (Blueprint $table) {
            $table->foreign('personalization_template_id')
                ->references('id')
                ->on('personalization_templates')
                ->cascadeOnDelete();
        });
    }
};
