<?php

$token = "195|qI44UYCab3iSeKZqjDsLR66i1xfu0yKX8QgMqS6954d95770";
$phoneNumber = "917017580125"; // The user's number with country code

$payload = [
    "messaging_product" => "whatsapp",
    "recipient_type" => "individual",
    "to" => $phoneNumber,
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
                        "text" => "123456"
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
                        "text" => "123456"
                    ]
                ]
            ]
        ]
    ]
];

$url = "https://multichannel.insignsms.com/api/v1/whatsapp/1/messages"; // Using 1 as a placeholder phone_number_id

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

echo "HTTP Code: $httpcode\n";
echo "Response: $response\n";
