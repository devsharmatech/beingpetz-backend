<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Api\VendorAuthController;
use App\Models\Vendor;

class TestVendorRegistration extends Command
{
    protected $signature = 'test:vendor-register';
    protected $description = 'Test the Vendor Registration API payload and response.';

    public function handle()
    {
        $this->info('Starting Vendor Registration Test...');

        // 1. Create a dummy file for the primary_gov_doc requirement
        $file = UploadedFile::fake()->image('gov_doc.jpg');

        // 2. Prepare the payload based on the UI images
        $data = [
            'business_name' => 'Happy Paws Grooming Studio',
            'legal_name' => 'John Doe',
            'email' => 'vendor_test_' . time() . '@example.com',
            'phone' => '917017580125', // Use a valid number to test OTP
            'area' => 'Koramangala, HSR Layout, Indiranagar',
            'experience_years' => '5-10',
            'services' => ['grooming', 'boarding'],
            'dpdp_consent' => 1,
            
            // Logistics
            'weekly_schedule' => [
                'Mon' => ['from' => '09:00', 'to' => '18:00'],
                'Sun' => ['from' => '09:00', 'to' => '18:00'],
            ],
            'accepted_pet_types' => ['dogs', 'cats'],
            'accepted_pet_sizes' => ['small', 'medium', 'large'],
            'emergency_contact_number' => '9876543210',
            
            // Grooming specific
            'facility_capacity' => '20',
            'cage_free_facility' => 1,
            'trade_licence_number' => 'TL-98765',
            'breed_specialization' => ['poodles', 'golden retrievers'],
            
            // Boarding specific
            'property_type' => 'house_with_yard',
            'household_composition' => '2 adults',
            'supervision_level' => '24_7',
            'max_pet_capacity' => '5',
            'pet_size_restrictions' => 'none',
            'separation_anxiety_accepted' => 1,
            'potty_breaks' => 'every_3_hours',
            'update_frequency' => 'twice_daily',
        ];

        // 3. Create a mock Request
        $request = Request::create('/api/v1/vendor/register', 'POST', $data);
        $request->files->set('primary_gov_doc', $file);

        // 4. Resolve the controller and call register
        try {
            $controller = app(VendorAuthController::class);
            $response = $controller->register($request);
            
            $this->info('Response Status Code: ' . $response->getStatusCode());
            $this->line('Response Body: ' . $response->getContent());

            if ($response->getStatusCode() === 200) {
                $this->info("\nSUCCESS: Vendor account created and OTP triggered!");
            } else {
                $this->error("\nFAILED: Received non-200 status code.");
            }
        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
        }
    }
}
