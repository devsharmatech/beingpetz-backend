<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the first user as sender, or create one if none exists
        $senderId = DB::table('users')->value('id');
        
        if (!$senderId) {
            // Create a default admin user if no users exist
            $senderId = DB::table('users')->insertGetId([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $notifications = [
            [
                'title' => 'Welcome to Our App!',
                'message' => 'Thank you for installing our application. We hope you have a great experience!',
                'type' => 'info',
                'image' => 'notifications/welcome.jpg',
                'audience' => json_encode(['locations' => ['New York', 'Los Angeles', 'Chicago']]),
                'status' => 1,
                'sender_id' => $senderId,
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(10),
            ],
            [
                'title' => 'Special Discount Offer',
                'message' => 'Get 20% off on all premium features for a limited time only!',
                'type' => 'promo',
                'image' => 'notifications/discount.jpg',
                'audience' => json_encode(['locations' => ['New York', 'Miami', 'San Francisco']]),
                'status' => 1,
                'sender_id' => $senderId,
                'created_at' => Carbon::now()->subDays(8),
                'updated_at' => Carbon::now()->subDays(8),
            ],
            [
                'title' => 'System Maintenance Alert',
                'message' => 'The app will be undergoing maintenance from 2 AM to 4 AM. Some features may be temporarily unavailable.',
                'type' => 'alert',
                'image' => 'notifications/maintenance.jpg',
                'audience' => json_encode(['locations' => ['Chicago', 'Houston', 'Phoenix']]),
                'status' => 0,
                'sender_id' => $senderId,
                'created_at' => Carbon::now()->subDays(6),
                'updated_at' => Carbon::now()->subDays(6),
            ],
            [
                'title' => 'New Features Available',
                'message' => 'Check out the latest features we have added to improve your experience!',
                'type' => 'update',
                'image' => 'notifications/features.jpg',
                'audience' => null, // All users
                'status' => 1,
                'sender_id' => $senderId,
                'created_at' => Carbon::now()->subDays(4),
                'updated_at' => Carbon::now()->subDays(4),
            ],
            [
                'title' => 'Security Update Required',
                'message' => 'Please update your app to the latest version for important security patches.',
                'type' => 'alert',
                'image' => 'notifications/security.jpg',
                'audience' => json_encode(['locations' => ['Los Angeles', 'San Diego', 'Dallas']]),
                'status' => 1,
                'sender_id' => $senderId,
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'title' => 'Weekend Special Promotion',
                'message' => 'Enjoy exclusive deals this weekend only! Limited time offer.',
                'type' => 'promo',
                'image' => 'notifications/weekend.jpg',
                'audience' => json_encode(['locations' => ['New York', 'Chicago', 'Miami', 'Los Angeles']]),
                'status' => 1,
                'sender_id' => $senderId,
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'title' => 'App Performance Improvements',
                'message' => 'We have made significant performance improvements to make the app faster and more responsive.',
                'type' => 'update',
                'image' => 'notifications/performance.jpg',
                'audience' => null, // All users
                'status' => 1,
                'sender_id' => $senderId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Emergency Weather Alert',
                'message' => 'Severe weather conditions expected in your area. Please take necessary precautions.',
                'type' => 'alert',
                'image' => 'notifications/weather.jpg',
                'audience' => json_encode(['locations' => ['Houston', 'Dallas', 'San Antonio']]),
                'status' => 1,
                'sender_id' => $senderId,
                'created_at' => Carbon::now()->subHours(6),
                'updated_at' => Carbon::now()->subHours(6),
            ],
            [
                'title' => 'New Year Special Offer',
                'message' => 'Start the new year with amazing discounts and offers! Valid until January 15th.',
                'type' => 'promo',
                'image' => 'notifications/newyear.jpg',
                'audience' => json_encode(['locations' => ['New York', 'Los Angeles', 'Chicago', 'Miami', 'San Francisco']]),
                'status' => 1,
                'sender_id' => $senderId,
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(15),
            ],
            [
                'title' => 'Server Maintenance Completed',
                'message' => 'The scheduled server maintenance has been completed successfully. All services are now running normally.',
                'type' => 'info',
                'image' => 'notifications/server.jpg',
                'audience' => null, // All users
                'status' => 0,
                'sender_id' => $senderId,
                'created_at' => Carbon::now()->subHours(2),
                'updated_at' => Carbon::now()->subHours(2),
            ]
        ];

        // Insert notifications
        DB::table('notifications')->insert($notifications);

        $this->command->info('✅ 10 dummy notifications created successfully!');
        $this->command->info('👤 Sender ID: ' . $senderId);
        $this->command->info('📊 Notification Types:');
        $this->command->info('   - Info: 2 notifications');
        $this->command->info('   - Alert: 3 notifications');
        $this->command->info('   - Promo: 3 notifications');
        $this->command->info('   - Update: 2 notifications');
        $this->command->info('   - Active: 8 notifications');
        $this->command->info('   - Inactive: 2 notifications');
    }
}