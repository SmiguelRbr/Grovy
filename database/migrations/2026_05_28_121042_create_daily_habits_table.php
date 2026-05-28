<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('daily_habits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date'); // A data do registo (ex: 2024-05-18)
            $table->integer('water_ml')->default(0);
            $table->integer('calories_kcal')->default(0);
            $table->decimal('sleep_hours', 4, 1)->default(0);
            $table->timestamps();

            // Garante que o utilizador só tem 1 registo por dia
            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_habits');
    }
};
