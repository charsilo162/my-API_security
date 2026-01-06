<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
{
    Schema::create('employees', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->uuid('uuid')->unique();

    // Identity & Job info
    $table->string('employee_code')->unique(); 
    $table->string('designation')->nullable(); // e.g., Bouncer, Armed Guard
    $table->string('department')->nullable();
    $table->date('joining_date')->nullable();

    // Banking info for payroll
    $table->string('account_holder_name')->nullable();
    $table->string('account_number')->nullable();
    $table->string('bank_name')->nullable();
    $table->string('branch_name')->nullable();
    $table->string('routing_number')->nullable();
    $table->string('swift_code')->nullable();

    $table->timestamps();
});
}
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
