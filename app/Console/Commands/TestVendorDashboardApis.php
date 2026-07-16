<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Provider;
use Illuminate\Support\Facades\Http;

class TestVendorDashboardApis extends Command
{
    protected $signature = 'test:vendor-dashboard';
    protected $description = 'Test Vendor Dashboard APIs';

    public function handle()
    {
        $this->info('Starting Vendor Dashboard API Tests...');

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

        $this->info("Found Vendor User ID: {$user->id}, Provider ID: {$provider->id}");

        // 2. Generate a fresh API token for testing
        $token = $user->createToken('TestDashboardToken')->plainTextToken;
        $this->info("Generated Token: $token");

        // Base URL (Assuming localhost for command line tests)
        $baseUrl = config('app.url') . '/api/v1/vendor';

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];

        // 3. Test Wallet & Earnings
        $this->info('--- Testing Earnings & Wallet ---');
        $response = Http::withHeaders($headers)->get("$baseUrl/earnings/wallet");
        $this->displayResponse('Wallet API', $response);

        // Test Withdraw Request
        $this->info('--- Testing Withdraw Request ---');
        $response = Http::withHeaders($headers)->post("$baseUrl/earnings/withdraw", [
            'amount' => 100
        ]);
        $this->displayResponse('Withdraw API', $response);

        // 4. Test KYC
        $this->info('--- Testing KYC Status ---');
        $response = Http::withHeaders($headers)->get("$baseUrl/kyc");
        $this->displayResponse('KYC Status API', $response);

        // 5. Test Reviews
        $this->info('--- Testing Reviews ---');
        $response = Http::withHeaders($headers)->get("$baseUrl/reviews");
        $this->displayResponse('Reviews API', $response);

        // 6. Test Support Messages
        $this->info('--- Testing Support Messages ---');
        $response = Http::withHeaders($headers)->get("$baseUrl/support/messages");
        $this->displayResponse('Support Messages API', $response);

        // Test Send Support Message
        $this->info('--- Testing Send Support Message ---');
        $response = Http::withHeaders($headers)->post("$baseUrl/support/message", [
            'message' => 'Hello this is a test message from command line!'
        ]);
        $this->displayResponse('Send Message API', $response);

        $this->info('API Testing Completed.');
    }

    private function displayResponse($apiName, $response)
    {
        if ($response->successful()) {
            $this->info("✅ $apiName SUCCESS:");
            // Output pretty JSON
            $this->line(json_encode($response->json(), JSON_PRETTY_PRINT));
        } else {
            $this->error("❌ $apiName FAILED (Status: " . $response->status() . "):");
            $this->line($response->body());
        }
        $this->newLine();
    }
}
