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
        Schema::create('diagnosis_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plant_id')->nullable()->constrained()->nullOnDelete();

            $table->string('disease_name_technical');
            $table->string('disease_name_arabic');
            $table->decimal('confidence_percentage', 5, 2);
            $table->string('original_image_path');
            $table->string('grad_cam_image_path');
            $table->text('treatment');

            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnosis_history');
    }
};
