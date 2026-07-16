<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Provider;
use App\Models\Service;
use App\Models\ServiceBooking;
use App\Models\ProviderEarning;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VendorApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_vendor_apis()
    {
        // 1. Setup Test Data
        $rand = rand(10000, 99999);
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Vendor',
            'email' => "testvendor{$rand}@example.com",
            'phone' => "12345{$rand}",
            'password' => bcrypt('password123'),
            'role' => 'vendor',
        ]);

        $service = Service::create([
            'name' => 'Test Service',
            'description' => 'A service for testing',
            'is_active' => true,
        ]);

        $provider = Provider::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'name' => 'Test Provider Profile',
            'email' => 'provider@example.com',
            'phone' => '0987654321',
            'address' => '123 Test St',
        ]);

        // Create some bookings
        $booking = ServiceBooking::create([
            'user_id' => $user->id, // just using the same user as customer for testing
            'provider_id' => $provider->id,
            'service_id' => $service->id,
            'scheduled_at' => Carbon::now()->addDays(2),
            'status' => 'accepted',
            'total_amount' => 150.00
        ]);

        // Create an earning
        ProviderEarning::create([
            'provider_id' => $provider->id,
            'service_booking_id' => $booking->id,
            'amount' => 150.00,
            'status' => 'cleared'
        ]);

        // 2. Test Login API
        $response = $this->postJson('/api/v1/vendor/login', [
            'email' => "testvendor{$rand}@example.com",
            'password' => 'password123'
        ]);
        
        $response->assertStatus(200);
        $this->assertTrue($response->json('status'));
        $token = $response->json('token');
        $this->assertNotEmpty($token);

        $headers = ['Authorization' => "Bearer $token"];

        // 3. Test Dashboard API
        $response = $this->getJson('/api/v1/vendor/dashboard', $headers);
        $response->assertStatus(200);
        $this->assertTrue($response->json('status'));
        $this->assertArrayHasKey('monthly_earnings', $response->json('data'));

        // 4. Test Earnings API
        $response = $this->getJson('/api/v1/vendor/earnings', $headers);
        $response->assertStatus(200);
        $this->assertTrue($response->json('status'));

        // 5. Test Chart API
        $response = $this->getJson('/api/v1/vendor/chart', $headers);
        $response->assertStatus(200);
        $this->assertTrue($response->json('status'));

        // 6. Test Upcoming Bookings API
        $response = $this->getJson('/api/v1/vendor/bookings/upcoming', $headers);
        $response->assertStatus(200);
        $this->assertTrue($response->json('status'));

        // 7. Test All Bookings API
        $response = $this->getJson('/api/v1/vendor/bookings', $headers);
        $response->assertStatus(200);
        $this->assertTrue($response->json('status'));

        // 8. Test Services API
        $response = $this->getJson('/api/v1/vendor/services', $headers);
        $response->assertStatus(200);
        $this->assertTrue($response->json('status'));

        // 9. Test Messages API
        $response = $this->getJson('/api/v1/vendor/messages', $headers);
        $response->assertStatus(200);
        $this->assertTrue($response->json('status'));

        // 10. Test Profile Update API
        $response = $this->postJson('/api/v1/vendor/profile', [
            'name' => 'Updated Provider Name',
            'phone' => '1111111111'
        ], $headers);
        $response->assertStatus(200);
        $this->assertTrue($response->json('status'));
        $this->assertEquals('Updated Provider Name', $response->json('data.provider.name'));

        // Output success if all assertions pass
        echo "\n[SUCCESS] All Vendor APIs tested successfully!\n";
    }
}
