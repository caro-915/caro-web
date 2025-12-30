<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\User;
use Illuminate\Support\Facades\File;

class ProductionDataSeeder extends Seeder
{
    /**
     * Seed the application's database for production.
     */
    public function run(): void
    {
        echo "🔄 Seeding production data...\n";

        // Import CarBrands
        if (File::exists(base_path('export-brands.json'))) {
            $brands = json_decode(File::get(base_path('export-brands.json')), true);
            foreach ($brands as $brand) {
                CarBrand::updateOrCreate(['id' => $brand['id']], $brand);
            }
            echo "✅ CarBrands imported: " . count($brands) . "\n";
        }

        // Import CarModels
        if (File::exists(base_path('export-models.json'))) {
            $models = json_decode(File::get(base_path('export-models.json')), true);
            foreach ($models as $model) {
                CarModel::updateOrCreate(['id' => $model['id']], $model);
            }
            echo "✅ CarModels imported: " . count($models) . "\n";
        }

        // Import Users
        if (File::exists(base_path('export-users.json'))) {
            $users = json_decode(File::get(base_path('export-users.json')), true);
            foreach ($users as $user) {
                User::updateOrCreate(['id' => $user['id']], $user);
            }
            echo "✅ Users imported: " . count($users) . "\n";
        }

        echo "🎉 Production data seeded successfully!\n";
    }
}
