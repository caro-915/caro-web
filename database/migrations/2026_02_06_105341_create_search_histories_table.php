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
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('marque')->nullable();
            $table->string('modele')->nullable();
            $table->integer('price_max')->nullable();
            $table->integer('annee_min')->nullable();
            $table->integer('annee_max')->nullable();
            $table->integer('km_min')->nullable();
            $table->integer('km_max')->nullable();
            $table->string('carburant')->nullable();
            $table->string('wilaya')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_histories');
    }
};
