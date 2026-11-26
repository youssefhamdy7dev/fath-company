
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strawberry_customer_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('amount');
            $table->float('discount')->nullable();
            $table->date('date');
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['customer_id', 'date'], 'strawberry_customer_payments_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strawberry_customer_payments');
    }
};
