<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
{
    Schema::create('employees', function (Blueprint $table) {
        $table->id();

        // Relation to User (Who owns this profile)
        $table->foreignId('user_id')
         ->constrained()
            ->onDelete('cascade');
            $table->uuid('uuid')->unique();

        // Identity (Work specific)
        $table->string('employee_code')->unique(); // EMP001
        
        // Job Details
        $table->string('designation')->nullable();
        $table->string('department')->nullable();
        $table->date('joining_date')->nullable();

        // Bank info
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
