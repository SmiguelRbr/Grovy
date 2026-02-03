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
    Schema::create('plans', function (Blueprint $table) {
        $table->id();
        
        $table->foreignId('professional_id')->constrained('users');
        $table->foreignId('student_id')->constrained('users'); 
        
        $table->string('title'); 
        $table->enum('type', ['diet', 'workout']); 
        
        $table->json('content'); 
        
        $table->text('description')->nullable(); 
        
        $table->boolean('active')->default(true); 
        $table->date('expires_at')->nullable();
        
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
