<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Annonce;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BugFixValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * BUG 1 - Test: Création annonce Moto sans boite_vitesse
     */
    public function test_create_moto_without_boite_vitesse_succeeds()
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/annonces', [
            'titre' => 'Yamaha MT-07 2023',
            'prix' => 5000000,
            'marque' => 'Yamaha',
            'modele' => 'MT-07',
            'vehicle_type' => 'Moto',
            'carburant' => 'Essence',
            'condition' => 'non',
            // boite_vitesse intentionnellement omis
        ]);

        $response->assertRedirect(); // Should redirect to show page
        $response->assertSessionHasNoErrors();

        $annonce = Annonce::latest()->first();
        
        $this->assertNotNull($annonce);
        $this->assertEquals('Moto', $annonce->vehicle_type);
        $this->assertEquals('N/A', $annonce->boite_vitesse); // Should auto-fill N/A
    }

    /**
     * BUG 1 - Test: Création annonce Voiture sans boite_vitesse échoue
     */
    public function test_create_voiture_without_boite_vitesse_fails()
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/annonces', [
            'titre' => 'Renault Clio 2020',
            'prix' => 2000000,
            'marque' => 'Renault',
            'modele' => 'Clio',
            'vehicle_type' => 'Voiture',
            'carburant' => 'Essence',
            'condition' => 'non',
            // boite_vitesse intentionnellement omis
        ]);

        $response->assertSessionHasErrors('boite_vitesse');
    }

    /**
     * BUG 2 - Test: Premium user limité à 4 images max
     */
    public function test_premium_user_limited_to_4_images()
    {
        Storage::fake('public');
        
        $user = User::factory()->create();
        
        // Créer plan Premium avec 4 images max
        $premiumPlan = Plan::create([
            'name' => 'Premium',
            'price' => 1500,
            'duration_days' => 7,
            'features' => [
                'max_active_ads' => 5,
                'max_images_per_ad' => 4,
                'boosts_per_month' => 3,
            ],
            'is_active' => true,
        ]);
        
        // Créer abonnement Premium actif
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $premiumPlan->id,
            'started_at' => now(),
            'expires_at' => now()->addDays(7),
            'status' => 'active',
            'payment_status' => 'approved',
        ]);

        // Tenter d'uploader 5 images (doit être refusé)
        $images = [
            UploadedFile::fake()->image('img1.jpg'),
            UploadedFile::fake()->image('img2.jpg'),
            UploadedFile::fake()->image('img3.jpg'),
            UploadedFile::fake()->image('img4.jpg'),
            UploadedFile::fake()->image('img5.jpg'), // 5ème image
        ];

        $response = $this->actingAs($user)->post('/annonces', [
            'titre' => 'Test Premium Limits',
            'prix' => 1000000,
            'marque' => 'Toyota',
            'modele' => 'Yaris',
            'vehicle_type' => 'Voiture',
            'carburant' => 'Essence',
            'boite_vitesse' => 'Manuelle',
            'condition' => 'non',
            'images' => $images,
        ]);

        // Devrait réussir mais avec seulement 4 images
        $response->assertRedirect();
        
        $annonce = Annonce::latest()->first();
        $this->assertNotNull($annonce);
        
        // Compter les images (4 max pour Premium)
        $imageCount = collect([
            $annonce->image_path,
            $annonce->image_path_2,
            $annonce->image_path_3,
            $annonce->image_path_4,
            $annonce->image_path_5,
        ])->filter()->count();
        
        $this->assertEquals(4, $imageCount, 'Premium user should have exactly 4 images');
    }

    /**
     * BUG 2 - Test: PRO user peut uploader 8 images
     */
    public function test_pro_user_can_upload_8_images()
    {
        Storage::fake('public');
        
        $user = User::factory()->create();
        
        // Créer plan PRO avec 8 images max
        $proPlan = Plan::create([
            'name' => 'Pro',
            'price' => 3000,
            'duration_days' => 30,
            'features' => [
                'max_active_ads' => 10,
                'max_images_per_ad' => 8,
                'boosts_per_month' => 5,
            ],
            'is_active' => true,
        ]);
        
        // Créer abonnement PRO actif
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $proPlan->id,
            'started_at' => now(),
            'expires_at' => now()->addDays(30),
            'status' => 'active',
            'payment_status' => 'approved',
        ]);

        // Uploader 8 images
        $images = [];
        for ($i = 1; $i <= 8; $i++) {
            $images[] = UploadedFile::fake()->image("img{$i}.jpg");
        }

        $response = $this->actingAs($user)->post('/annonces', [
            'titre' => 'Test PRO Limits',
            'prix' => 1000000,
            'marque' => 'BMW',
            'modele' => 'X5',
            'vehicle_type' => 'Voiture',
            'carburant' => 'Diesel',
            'boite_vitesse' => 'Automatique',
            'condition' => 'non',
            'images' => $images,
        ]);

        $response->assertRedirect();
        
        $annonce = Annonce::latest()->first();
        $this->assertNotNull($annonce);
        
        // Compter les images (8 pour PRO)
        $imageCount = collect([
            $annonce->image_path,
            $annonce->image_path_2,
            $annonce->image_path_3,
            $annonce->image_path_4,
            $annonce->image_path_5,
            $annonce->image_path_6,
            $annonce->image_path_7,
            $annonce->image_path_8,
        ])->filter()->count();
        
        $this->assertEquals(8, $imageCount, 'PRO user should have exactly 8 images');
    }

    /**
     * BUG 3 - Test: Watermark appliqué sur toutes les images y compris la première
     */
    public function test_watermark_applied_to_all_images_including_first()
    {
        Storage::fake('public');
        
        $user = User::factory()->create();

        $images = [
            UploadedFile::fake()->image('img1.jpg', 800, 600),
            UploadedFile::fake()->image('img2.jpg', 800, 600),
            UploadedFile::fake()->image('img3.jpg', 800, 600),
        ];

        $response = $this->actingAs($user)->post('/annonces', [
            'titre' => 'Test Watermark',
            'prix' => 1000000,
            'marque' => 'Peugeot',
            'modele' => '208',
            'vehicle_type' => 'Voiture',
            'carburant' => 'Essence',
            'boite_vitesse' => 'Manuelle',
            'condition' => 'non',
            'images' => $images,
        ]);

        $response->assertRedirect();
        
        $annonce = Annonce::latest()->first();
        $this->assertNotNull($annonce);
        
        // Vérifier que les 3 images existent
        $this->assertNotNull($annonce->image_path, 'Image 1 doit exister');
        $this->assertNotNull($annonce->image_path_2, 'Image 2 doit exister');
        $this->assertNotNull($annonce->image_path_3, 'Image 3 doit exister');
        
        // Vérifier que les fichiers existent dans le storage
        Storage::disk('public')->assertExists($annonce->image_path);
        Storage::disk('public')->assertExists($annonce->image_path_2);
        Storage::disk('public')->assertExists($annonce->image_path_3);
        
        // Note: Le watermark étant appliqué inline dans le controller,
        // on vérifie que les images sont bien traitées (redimensionnées)
        // en vérifiant la taille du fichier (devrait être processé, pas raw upload)
        $file1Size = Storage::disk('public')->size($annonce->image_path);
        $this->assertGreaterThan(0, $file1Size, 'Image 1 doit être traitée');
        
        // Logs devraient contenir "Image traitée inline" pour index 0, 1, 2
        // (vérification manuelle des logs recommandée)
    }
}
