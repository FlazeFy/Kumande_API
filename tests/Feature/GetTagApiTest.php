<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;
use App\Helpers\Generator;

class GetTagApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    protected $httpClient;

    private $authTest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/',
            'http_errors' => false
        ]);

        // Auth API Token
        $this->authTest = new AuthApiTest();
        $this->authTest->setUp();
        $this->token = $this->authTest->test_post_login();

        // Template
        $this->templateTest = new TestTemplate();
    }

    public function test_get_all_tag(): void
    {
        $is_paginate = false;
        
        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("/api/v1/tag", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
       
        $this->templateTest->templateGet($response, $is_paginate);

        // Get list key / column
        $stringFields = ['tag_slug','tag_name'];
        $stringNullableFields = ['created_by'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $stringNullableFields, 'string', true);
    }
}
