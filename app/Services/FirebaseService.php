<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FirebaseService
{
    private $projectId;
    private $keyFilePath;

    public function __construct(string $projectId, string $keyFilePath)
    {
        $this->projectId = $projectId;
        $this->keyFilePath = $keyFilePath;
    }

    private function getAccessToken()
    {
        $key = json_decode(file_get_contents($this->keyFilePath), true);

        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $claims = json_encode([
            'iss'   => $key['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => time(),
            'exp'   => time() + 3600,
        ]);

        $base64UrlHeader = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $base64UrlClaims = rtrim(strtr(base64_encode($claims), '+/', '-_'), '=');

        $signature = '';
        openssl_sign($base64UrlHeader . '.' . $base64UrlClaims, $signature, $key['private_key'], OPENSSL_ALGO_SHA256);
        $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        $jwt = $base64UrlHeader . '.' . $base64UrlClaims . '.' . $base64UrlSignature;

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);

        return $response->json('access_token') ?? null;
    }

    public function sendNotification(array $deviceIds, array $message)
    {
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return 'Error obtaining access token.';
        }

        foreach ($deviceIds as $deviceId) {
            $fields = [
                'message' => [
                    'token'        => $deviceId,
                    'notification' => [
                        'title' => $message['title'],
                        'body'  => $message['body'],
                    ],
                    'data' => $message,
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])->post($url, $fields);

            if (!$response->successful()) {
                return 'FCM Send Error: ' . $response->body();
            }
        }

        return true;
    }
}
