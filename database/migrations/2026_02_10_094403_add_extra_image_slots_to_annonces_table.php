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
        Schema::table('annonces', function (Blueprint $table) {
            $table->string('image_path_6', 500)->nullable()->after('image_path_5');
            $table->string('image_path_7', 500)->nullable()->after('image_path_6');
            $table->string('image_path_8', 500)->nullable()->after('image_path_7');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            $table->dropColumn(['image_path_6', 'image_path_7', 'image_path_8']);
        });
    }
};
