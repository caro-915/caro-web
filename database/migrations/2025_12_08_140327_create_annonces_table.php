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
        Schema::create('annonces', function (Blueprint $table) {
             $table->id();

    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

    $table->string('titre');
    $table->text('description');
    $table->integer('prix');

    // 🔹 Infos véhicule
    $table->string('marque');
    $table->string('modele');
    $table->integer('annee');
    $table->integer('kilometrage');
    $table->string('carburant');      // ex: essence, diesel
    $table->string('boite_vitesse');  // ex: manuelle, automatique
    $table->string('ville');

    // 🔹 Image principale
    $table->string('image_path')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annonces');
    }
};
