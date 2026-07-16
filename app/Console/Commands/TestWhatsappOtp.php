<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestWhatsappOtp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:whatsapp-otp {phone=917017580125}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sending a WhatsApp OTP using the configured INSIGN_SMS credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = env('INSIGN_SMS_TOKEN');
        $phoneNumberId = env('INSIGN_PHONE_NUMBER_ID');

        if (!$token || !$phoneNumberId) {
            $this->error('Error: INSIGN_SMS_TOKEN or INSIGN_PHONE_NUMBER_ID is missing from .env');
            return 1;
        }

        $phone = $this->argument('phone');
        $otp = rand(100000, 999999);

        $this->info("Sending test OTP ($otp) to $phone via WhatsApp...");

        $payload = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "template",
            "template" => [
                "name" => "verification",
                "language" => [
                    "code" => "en_US"
                ],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => (string) $otp
                            ]
                        ]
                    ],
                    [
                        "type" => "button",
                        "sub_type" => "url",
                        "index" => "0",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => (string) $otp
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $url = "https://multichannel.insignsms.com/api/v1/whatsapp/{$phoneNumberId}/messages"; 
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $token,
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode === 200) {
            $this->info("Success! OTP message sent.");
            $this->line("Response: " . $response);
            return 0;
        } else {
            $this->error("Failed to send WhatsApp message. HTTP Code: $httpcode");
            $this->line("Response: " . $response);
            return 1;
        }
    }
}
