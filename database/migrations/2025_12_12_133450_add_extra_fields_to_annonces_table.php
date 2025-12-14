<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            $table->string('couleur')->nullable()->after('boite_vitesse');
            $table->string('document_type')->nullable()->after('couleur'); // carte_grise | procuration
            $table->string('finition')->nullable()->after('document_type');
        });
    }

    public function down(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            $table->dropColumn(['couleur', 'document_type', 'finition']);
        });
    }
};
