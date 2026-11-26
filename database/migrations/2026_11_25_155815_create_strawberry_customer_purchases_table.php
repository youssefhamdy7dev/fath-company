
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strawberry_customer_purchases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('strawberry_truck_fruit_id')->constrained('strawberry_truck_fruits')->cascadeOnDelete();

            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();

            $table->enum('box_class', ['first', 'second', 'third',]);

            $table->integer('number_of_boxes');

            $table->float('total_weight')->nullable();
            $table->float('unique_unit_price')->nullable();
            $table->integer('unique_box_price')->nullable();

            $table->date('date');
            $table->timestamps();

            $table->unique(['strawberry_truck_fruit_id', 'customer_id', 'box_class', 'date',], 'strawberry_customer_purchases_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strawberry_customer_purchases');
    }
};
