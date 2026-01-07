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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->date('billing_date');
            $table->float('percentage');
            $table->integer('expenses')->nullable();
            $table->integer('grand_total');
            $table->string('notes')->nullable();
            $table->foreignId('truck_id')->unique()->nullable()->references('id')->on('trucks')->nullOnDelete();
            $table->timestamps();

            $table->unique(['billing_date', 'truck_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
