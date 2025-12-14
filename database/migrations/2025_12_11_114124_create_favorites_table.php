<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('annonce_id')->constrained()->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['user_id', 'annonce_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
