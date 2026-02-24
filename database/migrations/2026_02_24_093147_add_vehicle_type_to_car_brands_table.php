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
        Schema::table('car_brands', function (Blueprint $table) {
            $table->string('vehicle_type', 20)->default('Voiture')->after('region');
        });

        // Mark existing moto brands
        $motoBrands = [
            'Yamaha', 'Kawasaki', 'Ducati', 'Harley-Davidson', 'KTM',
            'Aprilia', 'Triumph', 'BMW Motorrad', 'Royal Enfield',
            'Piaggio', 'Vespa',
        ];

        \App\Models\CarBrand::whereIn('name', $motoBrands)
            ->update(['vehicle_type' => 'Moto']);
    }

    public function down(): void
    {
        Schema::table('car_brands', function (Blueprint $table) {
            $table->dropColumn('vehicle_type');
        });
    }
};
