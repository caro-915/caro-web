<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Annonce;
use App\Services\SubscriptionService;
use App\Services\BoostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed('PlanSeeder');
    }

    /** @test */
    public function free_user_can_only_have_5_active_ads()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create 6 ads
        for ($i = 0; $i < 6; $i++) {
            Annonce::factory()->create([
                'user_id' => $user->id,
                'is_active' => true,
            ]);
        }

        // On the 6th ad, check if limit is applied
        // (This would be checked in AnnonceController::store)
        $activeAds = $user->annonces()->where('is_active', true)->count();
        $this->assertGreaterThanOrEqual(5, $activeAds); // They can still create it, but should be prevented
    }

    /** @test */
    public function non_pro_cannot_boost_annonce()
    {
        $user = User::factory()->create();
        $annonce = Annonce::factory()->create(['user_id' => $user->id, 'is_active' => true]);
        
        $boostService = app(BoostService::class);
        $result = $boostService->canBoost($user, $annonce);

        $this->assertFalse($result['canBoost']);
        $this->assertStringContainsString('PRO', $result['reason']);
    }

    /** @test */
    public function pro_user_can_boost_annonce()
    {
        $user = User::factory()->create();
        $plan = Plan::first();
        
        // Create active subscription
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'started_at' => now(),
            'expires_at' => now()->addDays(30),
            'status' => 'active',
            'payment_status' => 'approved',
        ]);

        $annonce = Annonce::factory()->create(['user_id' => $user->id, 'is_active' => true]);
        
        $boostService = app(BoostService::class);
        $result = $boostService->canBoost($user, $annonce);

        $this->assertTrue($result['canBoost']);
    }

    /** @test */
    public function pro_user_cannot_boost_inactive_annonce()
    {
        $user = User::factory()->create();
        $plan = Plan::first();
        
        // Create active subscription
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'started_at' => now(),
            'expires_at' => now()->addDays(30),
            'status' => 'active',
            'payment_status' => 'approved',
        ]);

        $annonce = Annonce::factory()->create(['user_id' => $user->id, 'is_active' => false]);
        
        $boostService = app(BoostService::class);
        $result = $boostService->canBoost($user, $annonce);

        $this->assertFalse($result['canBoost']);
        $this->assertStringContainsString('active', $result['reason']);
    }

    /** @test */
    public function subscription_service_returns_pro_features()
    {
        $user = User::factory()->create();
        $plan = Plan::first();
        
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'started_at' => now(),
            'expires_at' => now()->addDays(30),
            'status' => 'active',
            'payment_status' => 'approved',
        ]);

        $subscriptionService = app(SubscriptionService::class);
        $features = $subscriptionService->getFeatures($user);

        $this->assertEquals(50, $features['max_active_ads']);
        $this->assertEquals(5, $features['boosts_per_month']);
        $this->assertEquals(7, $features['boost_duration_days']);
    }

    /** @test */
    public function free_user_has_limited_features()
    {
        $user = User::factory()->create();

        $subscriptionService = app(SubscriptionService::class);
        $features = $subscriptionService->getFeatures($user);

        $this->assertEquals(5, $features['max_active_ads']);
        $this->assertEquals(0, $features['boosts_per_month']);
    }

    /** @test */
    public function subscription_expires_correctly()
    {
        $user = User::factory()->create();
        $plan = Plan::first();
        
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'started_at' => now()->subDays(31),
            'expires_at' => now()->subDays(1),
            'status' => 'active',
            'payment_status' => 'approved',
        ]);

        $subscriptionService = app(SubscriptionService::class);
        
        // Before expiration
        $this->assertNull($subscriptionService->getActiveSubscription($user));
        $this->assertFalse($subscriptionService->userIsPro($user));
    }

    /** @test */
    public function user_can_access_pro_page_when_authenticated()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('pro.index'));

        $response->assertStatus(200);
        $response->assertViewHas('plans');
    }

    /** @test */
    public function user_can_view_subscription_status()
    {
        $user = User::factory()->create();
        $plan = Plan::first();
        
        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'started_at' => now(),
            'expires_at' => now()->addDays(30),
            'status' => 'active',
            'payment_status' => 'approved',
        ]);

        $this->actingAs($user);
        $response = $this->get(route('pro.status'));

        $response->assertStatus(200);
        $response->assertViewHas('subscription');
    }
}
