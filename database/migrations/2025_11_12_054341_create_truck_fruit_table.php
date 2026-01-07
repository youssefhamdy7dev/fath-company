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
        Schema::create('truck_fruit', function (Blueprint $table) {
            $table->id();
            $table->enum('box_type', ['big_box', 'normal_box', 'small_box', 'small_net'])->default('normal_box');
            $table->integer('number_of_boxes');
            $table->integer('second_class_boxes')->nullable();
            $table->integer('third_class_boxes')->nullable();
            $table->float('unified_weight')->nullable();
            $table->float('unified_unit_price')->nullable();
            $table->integer('unified_box_price')->nullable();
            $table->foreignId('truck_id')->references('id')->on('trucks')->cascadeOnDelete();
            $table->foreignId('fruit_id')->references('id')->on('fruits')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->references('id')->on('clients')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['truck_id', 'fruit_id', 'box_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('truck_fruit');
    }
};
