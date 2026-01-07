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
        Schema::create('customer_purchase', function (Blueprint $table) {
            $table->id();
            $table->integer('number_of_boxes');
            $table->float('total_weight')->nullable();
            $table->float('unique_unit_price')->nullable();
            $table->integer('unique_box_price')->nullable();
            $table->enum('box_class', [
                'first',
                'second',
                'third'
            ]);
            $table->date('date');
            $table->foreignId('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->foreignId('truck_fruit_id')->references('id')->on('truck_fruit')->cascadeOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['truck_fruit_id', 'customer_id', 'box_class', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_purchase');
    }
};
