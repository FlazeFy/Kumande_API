<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;
use App\Helpers\Generator;

class TagApiTest extends TestCase
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
            'base_uri' => 'http://127.0.0.1:8000/api/v1/tag/',
            'http_errors' => false
        ]);

        // Auth API Token
        $this->authTest = new AuthApiTest();
        $this->authTest->setUp();
        $this->token = $this->authTest->test_post_login();

        // Template
        $this->templateTest = new TestTemplate();
    }

    // Query Test
    public function test_get_all_tag(): void
    {
        $is_paginate = false;
        
        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("", [
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

    public function test_get_my_tag(): void
    {
        $is_paginate = false;
        
        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("my", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
       
        $this->templateTest->templateGet($response, $is_paginate);

        // Get list key / column
        $stringFields = ['id','tag_slug','tag_name'];
        $intFields = ['total_used'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', true);
    }

    public function test_get_analyze_my_tag(): void
    {
        $slug = "low_fat";
        $is_paginate = false;
        
        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("analyze/$slug", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
       
        $this->templateTest->templateGet($response, $is_paginate);

        // Get list key / column
        $stringFields = ['last_used','last_used_consume_name','last_used_consume_type','last_used_consume_slug'];
        $intFields = ['total_item','total_price','average_calorie','max_calorie','min_calorie'];

        // Validate column
        $data_arr = [$data['data']];
        $this->templateTest->templateValidateColumn($data_arr, $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data_arr, $intFields, 'integer', false);
    }

    // Command Test
    public function test_post_tag(): void
    {
        $body = [
            "tag_name" => "tagtests",
        ];

        $response = $this->httpClient->post("add", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $body
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "create", "tag");
    }

    public function test_delete_tag_by_id(): void
    {
        $id = "40c56a4f-ef93-20a5-2a22-57e286e53053"; 

        $response = $this->httpClient->delete("$id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "delete", "tag");
    }
}
