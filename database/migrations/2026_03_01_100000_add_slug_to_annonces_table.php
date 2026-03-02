<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Annonce;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only add column if it doesn't exist
        if (!Schema::hasColumn('annonces', 'slug')) {
            Schema::table('annonces', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('titre')->index();
            });
        }

        // Generate slugs for existing annonces that don't have one
        Annonce::whereNull('slug')->orWhere('slug', '')->chunk(100, function ($annonces) {
            foreach ($annonces as $annonce) {
                $baseSlug = Str::slug($annonce->titre ?: ($annonce->marque . ' ' . $annonce->modele));
                if (empty($baseSlug)) {
                    $baseSlug = 'annonce';
                }
                $slug = $baseSlug;
                $counter = 1;
                
                while (Annonce::where('slug', $slug)->where('id', '!=', $annonce->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                
                $annonce->slug = $slug;
                $annonce->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('annonces', 'slug')) {
            Schema::table('annonces', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};
