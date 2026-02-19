<?php
// database/seeders/SettingsTableSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsTableSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            // Website Settings
            [
                'group' => 'website',
                'key' => 'site_name',
                'value' => 'Pet Social',
                'type' => 'text',
                'label' => 'Site Name',
                'description' => 'The name of your website',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'group' => 'website',
                'key' => 'site_email',
                'value' => 'admin@petsocial.com',
                'type' => 'email',
                'label' => 'Site Email',
                'description' => 'Default email address for the website',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'group' => 'website',
                'key' => 'site_description',
                'value' => 'A social platform for pet lovers',
                'type' => 'textarea',
                'label' => 'Site Description',
                'description' => 'Brief description of your website',
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'group' => 'website',
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'label' => 'Maintenance Mode',
                'description' => 'Put the website in maintenance mode',
                'sort_order' => 4,
                'is_active' => true
            ],

            // Email Settings
            [
                'group' => 'email',
                'key' => 'mail_driver',
                'value' => 'smtp',
                'type' => 'select',
                'options' => ['smtp' => 'SMTP', 'mail' => 'Mail', 'sendmail' => 'Sendmail'],
                'label' => 'Mail Driver',
                'description' => 'Email driver to use for sending emails',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'group' => 'email',
                'key' => 'mail_host',
                'value' => 'smtp.mailtrap.io',
                'type' => 'text',
                'label' => 'Mail Host',
                'description' => 'SMTP server host',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'group' => 'email',
                'key' => 'mail_port',
                'value' => '2525',
                'type' => 'number',
                'label' => 'Mail Port',
                'description' => 'SMTP server port',
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'group' => 'email',
                'key' => 'mail_username',
                'value' => '',
                'type' => 'text',
                'label' => 'Mail Username',
                'description' => 'SMTP username',
                'sort_order' => 4,
                'is_active' => true
            ],
            [
                'group' => 'email',
                'key' => 'mail_password',
                'value' => '',
                'type' => 'password',
                'label' => 'Mail Password',
                'description' => 'SMTP password',
                'sort_order' => 5,
                'is_active' => true
            ],
            [
                'group' => 'email',
                'key' => 'mail_encryption',
                'value' => 'tls',
                'type' => 'select',
                'options' => ['tls' => 'TLS', 'ssl' => 'SSL', '' => 'None'],
                'label' => 'Mail Encryption',
                'description' => 'Email encryption type',
                'sort_order' => 6,
                'is_active' => true
            ],

            // Notification Settings
            [
                'group' => 'notification',
                'key' => 'push_notifications',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Push Notifications',
                'description' => 'Enable push notifications',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'group' => 'notification',
                'key' => 'email_notifications',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Email Notifications',
                'description' => 'Enable email notifications',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'group' => 'notification',
                'key' => 'sms_notifications',
                'value' => '0',
                'type' => 'boolean',
                'label' => 'SMS Notifications',
                'description' => 'Enable SMS notifications',
                'sort_order' => 3,
                'is_active' => true
            ],

            // Page Settings
            [
                'group' => 'page',
                'key' => 'privacy_policy',
                'value' => '<h1>Privacy Policy</h1><p>Your privacy policy content here...</p>',
                'type' => 'textarea',
                'label' => 'Privacy Policy',
                'description' => 'Privacy policy content',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'group' => 'page',
                'key' => 'terms_conditions',
                'value' => '<h1>Terms & Conditions</h1><p>Your terms and conditions content here...</p>',
                'type' => 'textarea',
                'label' => 'Terms & Conditions',
                'description' => 'Terms and conditions content',
                'sort_order' => 2,
                'is_active' => true
            ],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}