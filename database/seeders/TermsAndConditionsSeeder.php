<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class TermsAndConditionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::updateOrCreate(
            ['key' => 'terms_and_conditions'],
            [
                'group' => 'legal',
                'value' => '<p>Please read these terms and conditions carefully before using our application.</p><p>By accessing or using the Service you agree to be bound by these Terms. If you disagree with any part of the terms then you may not access the Service.</p><p><strong>1. Accounts</strong></p><p>When you create an account with us, you must provide us information that is accurate, complete, and current at all times. Failure to do so constitutes a breach of the Terms, which may result in immediate termination of your account on our Service.</p>',
                'type' => 'richtext',
                'label' => 'Terms & Conditions',
                'description' => 'The main terms and conditions content for the application.',
                'sort_order' => 10,
                'is_active' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'privacy_policy'],
            [
                'group' => 'legal',
                'value' => '<p>This is the privacy policy.</p>',
                'type' => 'richtext',
                'label' => 'Privacy Policy',
                'description' => 'The privacy policy content for the application.',
                'sort_order' => 20,
                'is_active' => true,
            ]
        );
    }
}
