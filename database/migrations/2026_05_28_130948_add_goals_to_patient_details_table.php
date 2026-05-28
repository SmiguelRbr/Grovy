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
    Schema::table('patient_details', function (Blueprint $table) {
        // Metas definidas pelo Nutricionista
        $table->integer('water_goal_ml')->default(2500)->after('objetivo');
        $table->integer('calorie_goal_kcal')->default(2000)->after('water_goal_ml');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_details', function (Blueprint $table) {
            //
        });
    }
};
