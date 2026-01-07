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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable()->unique();
            $table->enum('location', [
                'ميدان صقر',
                'الكويتية',
                'صقر',
                'العمومى بساتين',
                'البساتين',
                'سوق البساتين',
                'السد العالى',
                'أبو بريك',
                'المطبعة',
                'الجزيرة',
                'دار السلام',
                'البير',
                'المشير وأبو الوفا',
                'عبدالحميد مكى',
                'فايدة كامل',
                'حسنين الدسوقى',
                'المعادى',
                'أخرى'
            ]);
            $table->integer('account');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['name', 'location']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
