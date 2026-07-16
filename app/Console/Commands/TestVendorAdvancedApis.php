<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Provider;
use App\Models\ProviderService;
use Illuminate\Support\Facades\Http;

class TestVendorAdvancedApis extends Command
{
    protected $signature = 'test:vendor-advanced-apis';
    protected $description = 'Test the newly created Vendor Advanced APIs (Services, Calendar, Profile)';

    public function handle()
    {
        $this->info('Starting Vendor Advanced API Tests...');

        // 1. Find or create a Vendor User with a Provider profile
        $user = User::where('email', 'testvendor@example.com')->first();
        if (!$user) {
            $user = User::create([
                'first_name' => 'Test',
                'last_name' => 'Vendor',
                'email' => 'testvendor@example.com',
                'password' => bcrypt('password'),
                'role' => 'vendor'
            ]);
        }

        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) {
            $provider = Provider::create([
                'user_id' => $user->id,
                'name' => 'Test Vendor',
                'business_name' => 'Test Vendor Business',
                'email' => 'testvendor@example.com',
                'kyc_status' => 'pending'
            ]);
        }
        $token = $user->createToken('TestToken')->plainTextToken;

        $this->info('Found Vendor User ID: ' . $user->id . ', Provider ID: ' . $provider->id);
        
        $baseUrl = url('/api/v1/vendor');
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];

        // 1. Profile API
        $this->info('--- Testing Get Profile ---');
        $response = Http::withHeaders($headers)->get($baseUrl . '/profile');
        if ($response->successful() && $response->json('status')) {
            $this->line('✅ Profile API SUCCESS:');
        } else {
            $this->error('❌ Profile API FAILED: ' . $response->body());
        }

        // 2. Create Service API
        $this->info('--- Testing Create Service ---');
        $response = Http::withHeaders($headers)->post($baseUrl . '/my-custom-services/create', [
            'name' => 'Premium Bath',
            'category' => 'Grooming',
            'price' => 500,
            'duration_minutes' => 60
        ]);
        
        $serviceId = null;
        if ($response->successful() && $response->json('status')) {
            $this->line('✅ Create Service API SUCCESS');
            $serviceId = $response->json('data.id');
        } else {
            $this->error('❌ Create Service API FAILED: ' . $response->body());
        }

        // 3. List Services API
        $this->info('--- Testing List Services ---');
        $response = Http::withHeaders($headers)->get($baseUrl . '/my-custom-services');
        if ($response->successful() && $response->json('status')) {
            $this->line('✅ List Services API SUCCESS. Total found: ' . $response->json('data.total_services'));
        } else {
            $this->error('❌ List Services API FAILED: ' . $response->body());
        }

        // 4. Toggle Service API
        if ($serviceId) {
            $this->info('--- Testing Toggle Service ---');
            $response = Http::withHeaders($headers)->post($baseUrl . '/my-custom-services/' . $serviceId . '/toggle');
            if ($response->successful() && $response->json('status')) {
                $this->line('✅ Toggle Service API SUCCESS');
            } else {
                $this->error('❌ Toggle Service API FAILED: ' . $response->body());
            }
        }

        // 5. Update Availability
        $this->info('--- Testing Update Availability ---');
        $response = Http::withHeaders($headers)->post($baseUrl . '/availability/update', [
            'shifts' => [
                'morning' => true,
                'afternoon' => false,
                'evening' => true
            ],
            'blocked_dates' => [
                '2026-07-20',
                '2026-07-21'
            ]
        ]);
        if ($response->successful() && $response->json('status')) {
            $this->line('✅ Update Availability API SUCCESS');
        } else {
            $this->error('❌ Update Availability API FAILED: ' . $response->body());
        }

        // 6. Get Availability
        $this->info('--- Testing Get Availability ---');
        $response = Http::withHeaders($headers)->get($baseUrl . '/availability');
        if ($response->successful() && $response->json('status')) {
            $this->line('✅ Get Availability API SUCCESS');
            $this->line(json_encode($response->json('data'), JSON_PRETTY_PRINT));
        } else {
            $this->error('❌ Get Availability API FAILED: ' . $response->body());
        }

        // 7. Edit Service API
        if ($serviceId) {
            $this->info('--- Testing Edit Service ---');
            $response = Http::withHeaders($headers)->post($baseUrl . '/my-custom-services/' . $serviceId . '/edit', [
                'price' => 600
            ]);
            if ($response->successful() && $response->json('status')) {
                $this->line('✅ Edit Service API SUCCESS');
            } else {
                $this->error('❌ Edit Service API FAILED: ' . $response->body());
            }

            // 8. Delete Service API
            $this->info('--- Testing Delete Service ---');
            $response = Http::withHeaders($headers)->delete($baseUrl . '/my-custom-services/' . $serviceId);
            if ($response->successful() && $response->json('status')) {
                $this->line('✅ Delete Service API SUCCESS');
            } else {
                $this->error('❌ Delete Service API FAILED: ' . $response->body());
            }
        }

        // 9. Notifications API
        $this->info('--- Testing Notifications API ---');
        $response = Http::withHeaders($headers)->get($baseUrl . '/notifications');
        if ($response->successful() && $response->json('status')) {
            $this->line('✅ Get Notifications API SUCCESS');
        } else {
            $this->error('❌ Get Notifications API FAILED: ' . $response->body());
        }

        // 10. Mark Read API
        $this->info('--- Testing Mark Read Notifications API ---');
        $response = Http::withHeaders($headers)->post($baseUrl . '/notifications/mark-read');
        if ($response->successful() && $response->json('status')) {
            $this->line('✅ Mark Read Notifications API SUCCESS');
        } else {
            $this->error('❌ Mark Read Notifications API FAILED: ' . $response->body());
        }

        $this->info('Advanced & Operational API Testing Completed.');
    }
}
