<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add extra image paths for up to 4 photos per ad.
     */
    public function up(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            // Additional optional image paths
            $table->string('image_path_2')->nullable()->after('image_path');
            $table->string('image_path_3')->nullable()->after('image_path_2');
            $table->string('image_path_4')->nullable()->after('image_path_3');
        });
    }

    /**
     * Rollback extra image paths.
     */
    public function down(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            $table->dropColumn(['image_path_2', 'image_path_3', 'image_path_4']);
        });
    }
};
