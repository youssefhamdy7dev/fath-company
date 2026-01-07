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
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('amount');
            $table->float('discount')->nullable();
            $table->date('date');
            $table->foreignId('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
    }
};
