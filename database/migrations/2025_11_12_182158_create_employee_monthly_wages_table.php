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
        Schema::create('employee_monthly_wages', function (Blueprint $table) {
            $table->id();
            $table->date('employee_start_date');
            // The end date for this calculated wage
            $table->date('end_date');
            // stored final salary after calculations
            $table->float('total_wage');
            $table->foreignId('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_monthly_wages');
    }
};
