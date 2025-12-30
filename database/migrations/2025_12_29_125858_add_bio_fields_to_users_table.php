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
    Schema::table('users', function (Blueprint $table) {
        $table->string('phone')->nullable()->after('email');
        $table->text('address')->nullable()->after('phone');
        $table->text('bio')->nullable()->after('address');
        $table->date('date_of_birth')->nullable()->after('bio');
        $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['phone', 'address', 'bio', 'date_of_birth', 'gender']);
    });
}
};
