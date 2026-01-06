<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('service_requests', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('client_id')->constrained()->onDelete('cascade');
    
    // Request Details
    $table->string('title'); 
    $table->enum('category', ['personal', 'event', 'business', 'vip']);
    $table->text('description');
    $table->dateTime('start_date');
    $table->dateTime('end_date');
    $table->integer('required_staff_count')->default(1);
    
    // Status Management
    $table->enum('status', ['pending', 'approved', 'assigned', 'active', 'completed', 'cancelled'])->default('pending');
    $table->text('admin_remarks')->nullable();
    
    $table->timestamps();
});
 }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
