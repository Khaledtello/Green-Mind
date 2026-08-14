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
        Schema::create('harvested_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('harvest_quantity');
            $table->integer('current_quantity');
            $table->string('storage_location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvested_inventories');
    }
};
