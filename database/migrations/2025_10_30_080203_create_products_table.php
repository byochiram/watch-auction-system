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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('brand', 100)->index();
            $table->string('model', 100)->index();
            $table->string('category', 255)->nullable();
            $table->smallInteger('year')->nullable();
            $table->unsignedInteger('weight_grams')->nullable();
            $table->enum('condition',['NEW','USED'])->default('USED');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
