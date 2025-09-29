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
        Schema::create('nubdha_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nubdha_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->CascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nubdha_views');
    }
};
