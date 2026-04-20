<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('personalization_templates')
            ->where('base_template_url', '/images/nikahnama/Mockup-1.png')
            ->update(['base_template_url' => '/images/nikahnama/Template-Base.png']);

        DB::table('personalization_templates')
            ->where('preview_image_url', '/images/nikahnama/Mockup-1.png')
            ->update(['preview_image_url' => '/images/nikahnama/Template-Preview.png']);

        DB::table('personalization_templates')
            ->where('mask_image_url', '/images/nikahnama/Mockup-2.png')
            ->update(['mask_image_url' => '/images/nikahnama/Template-Mask.png']);
    }

    public function down(): void
    {
        DB::table('personalization_templates')
            ->where('base_template_url', '/images/nikahnama/Template-Base.png')
            ->update(['base_template_url' => '/images/nikahnama/Mockup-1.png']);

        DB::table('personalization_templates')
            ->where('preview_image_url', '/images/nikahnama/Template-Preview.png')
            ->update(['preview_image_url' => '/images/nikahnama/Mockup-1.png']);

        DB::table('personalization_templates')
            ->where('mask_image_url', '/images/nikahnama/Template-Mask.png')
            ->update(['mask_image_url' => '/images/nikahnama/Mockup-2.png']);
    }
};
