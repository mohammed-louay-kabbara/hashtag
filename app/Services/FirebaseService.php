<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;

class FirebaseService
{
    protected $client;
    protected $accessToken;

    public function __construct()
    {
        $credentialsPath = base_path(env('FIREBASE_CREDENTIALS'));

        $credentials = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            $credentialsPath
        );

        $this->accessToken = $credentials->fetchAuthToken()['access_token'];

        $this->client = new Client([
            'base_uri' => 'https://fcm.googleapis.com/v1/projects/YOUR_PROJECT_ID/messages:send',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    public function sendNotification($token, $title, $body)
    {
        $response = $this->client->post('', [
            'json' => [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                ],
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
