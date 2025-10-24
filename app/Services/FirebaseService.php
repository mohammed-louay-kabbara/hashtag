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
            'base_uri' => 'https://fcm.googleapis.com/v1/projects/hashtag-b1a81/messages:send',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ]
        ]);
    }

public function sendNotification($token, $title, $body, $type, $target_id = null)
{
    $response = $this->client->post('', [
        'json' => [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => [
                    'type' => $type,
                    'target_id' => $target_id,
                ],
            ],
        ],
    ]);

    return json_decode($response->getBody(), true);
}
}
