
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strawberry_truck_fruits', function (Blueprint $table) {
            $table->id();
            $table->enum('fruit_type', ['فراولة', 'إستفالة', 'فرتونة', 'فلوريدا',])->default('فراولة');
            $table->enum('box_type', ['big_box', 'normal_box', 'small_box', 'small_net',])->default('normal_box');

            $table->integer('number_of_boxes');
            $table->integer('second_class_boxes')->nullable();
            $table->integer('third_class_boxes')->nullable();

            $table->float('unified_weight')->nullable();
            $table->float('unified_unit_price')->nullable();
            $table->integer('unified_box_price')->nullable();

            $table->foreignId('truck_id')->constrained('strawberry_trucks')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->timestamps();

            $table->unique(['truck_id', 'fruit_type', 'box_type'], 'strawberry_truck_fruits_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strawberry_truck_fruits');
    }
};
