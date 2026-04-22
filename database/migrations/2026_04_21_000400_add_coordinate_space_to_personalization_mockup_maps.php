<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personalization_mockup_maps', function (Blueprint $table) {
            $table->string('coordinate_space')->default('stage')->after('normalized_coordinates');
        });
    }

    public function down(): void
    {
        Schema::table('personalization_mockup_maps', function (Blueprint $table) {
            $table->dropColumn('coordinate_space');
        });
    }
};
