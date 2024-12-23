<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;
use App\Helpers\Generator;

class HistoryApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    protected $httpClient;

    private $authTest;
    private $templateGet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/history/',
            'http_errors' => false
        ]);

        // Auth API Token
        $this->authTest = new AuthApiTest();
        $this->authTest->setUp();
        $this->token = $this->authTest->test_post_login();

        // Template
        $this->templateTest = new TestTemplate();
    }

    public function test_get_all_history(): void
    {
        $is_paginate = true;

        $response = $this->httpClient->get("", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateGet($response, $is_paginate);

        // Get list key / column
        $stringFields = ['id','history_type','history_context','created_at'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data']['data'], $stringFields, 'string', false);
    }

    public function test_hard_delete_history_by_id(): void
    {
        $id = "2d98f524-de02-11ed-b5ea-0242ac120002";

        $response = $this->httpClient->delete("destroy/$id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "permentally delete", "history");
    }
}
