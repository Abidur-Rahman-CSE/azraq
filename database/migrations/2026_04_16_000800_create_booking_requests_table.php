<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('booking_number')->unique();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->date('preferred_date');
            $table->string('preferred_time');
            $table->string('location_area');
            $table->string('package_details')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->boolean('deposit_required')->default(false);
            $table->decimal('deposit_amount', 12, 2)->nullable();
            $table->string('deposit_status')->default('not_required');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_requests');
    }
};
