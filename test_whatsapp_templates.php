<?php
$token = "195|qI44UYCab3iSeKZqjDsLR66i1xfu0yKX8QgMqS6954d95770";
$url = "https://multichannel.insignsms.com/api/v1/whatsapp/rayarainnovations/message_templates";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $token
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpcode\n";
echo "Response: $response\n";
