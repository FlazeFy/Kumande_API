<?php
namespace App\Helpers;

use GuzzleHttp\Client;

class LineMessage
{
    public static function sendMessage($type, $text, $user_id){ 
        $httpClient = new Client();
        $channelAccessToken = env('LINE_BOT_TOKEN'); 
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $channelAccessToken,
        ];

        $message = [
            'to' => $user_id,
            'messages' => []
        ];
    
        if ($type == 'text') {
            $message['messages'][] = [
                'type' => 'text',
                'text' => $text
            ];
        }
        if ($type == 'location') {
            $message['messages'][] = [
                'type' => 'location',
                'title' => $text['title'] ?: 'Location',
                'address' => $text['title'],
                'latitude' => $text['lat'],
                'longitude' => $text['long']
            ];
        }

        $response = $httpClient->post('https://api.line.me/v2/bot/message/push', [
            'headers' => $headers,
            'json' => $message,
        ]); 
    }
}