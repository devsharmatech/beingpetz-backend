<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestUsersSeeder extends Seeder
{
    public function run()
    {
        // Create some active users with recent last_login
        User::create([
            'name' => 'Active User 1',
            'email' => 'active1@test.com',
            'password' => Hash::make('password'),
            'role' => 'parent',
            'isComplete' => 1,
            'last_login' => Carbon::now(), // Today - DAU
            'created_at' => Carbon::now()
        ]);

        User::create([
            'name' => 'Active User 2',
            'email' => 'active2@test.com',
            'password' => Hash::make('password'),
            'role' => 'parent',
            'isComplete' => 1,
            'last_login' => Carbon::now()->subDays(2), // This week - WAU
            'created_at' => Carbon::now()->subDays(2)
        ]);

        User::create([
            'name' => 'Active User 3',
            'email' => 'active3@test.com',
            'password' => Hash::make('password'),
            'role' => 'parent',
            'isComplete' => 1,
            'last_login' => Carbon::now()->subDays(10), // This month - MAU
            'created_at' => Carbon::now()->subDays(10)
        ]);

        User::create([
            'name' => 'Active User 4',
            'email' => 'active4@test.com',
            'password' => Hash::make('password'),
            'role' => 'parent',
            'isComplete' => 1,
            'last_login' => Carbon::now()->subDays(15), // This month - MAU
            'created_at' => Carbon::now()->subDays(15)
        ]);

        // Create some inactive users
        User::create([
            'name' => 'Inactive User 1',
            'email' => 'inactive1@test.com',
            'password' => Hash::make('password'),
            'role' => 'parent',
            'isComplete' => 0,
            'last_login' => Carbon::now()->subMonths(2), // Old login
            'created_at' => Carbon::now()->subMonths(2)
        ]);

        // Create some deleted users
        $deletedUser1 = User::create([
            'name' => 'Deleted User 1',
            'email' => 'deleted1@test.com',
            'password' => Hash::make('password'),
            'role' => 'parent',
            'isComplete' => 1,
            'last_login' => Carbon::now()->subMonths(1),
            'created_at' => Carbon::now()->subMonths(3)
        ]);

        $deletedUser2 = User::create([
            'name' => 'Deleted User 2',
            'email' => 'deleted2@test.com',
            'password' => Hash::make('password'),
            'role' => 'parent',
            'isComplete' => 1,
            'last_login' => Carbon::now()->subMonths(2),
            'created_at' => Carbon::now()->subMonths(4)
        ]);

        // Soft delete these users
        $deletedUser1->delete();
        $deletedUser2->delete();

        echo "Test users created successfully!\n";
        echo "- 4 Active users with different login dates\n";
        echo "- 1 Inactive user\n";
        echo "- 2 Deleted users\n";
    }
}