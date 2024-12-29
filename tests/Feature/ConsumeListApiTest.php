<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;
use App\Helpers\Generator;

class ConsumeListApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    protected $httpClient;

    private $authTest;
    private $limit;
    private $ord;
    private $token;
    private $slug;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/list/',
            'http_errors' => false
        ]);

        // Auth API Token
        $this->authTest = new AuthApiTest();
        $this->authTest->setUp();
        $this->token = $this->authTest->test_post_login();

        // Template
        $this->templateTest = new TestTemplate();

        $this->ord = "DESC";
    }

    // Query Test
    public function test_get_all_list(): void
    {
        $is_paginate = true;
        $page = 1;

        $response = $this->httpClient->get("limit/$page/order/$this->ord", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        $this->templateTest->templateGet($response, $is_paginate);

        // Get list key / column
        $stringFields = ['id','slug_name','list_name','created_at'];
        $stringNullableFields = ['list_desc'];
        $arrayNullableFields = ['list_tag','consume'];

        $this->templateTest->templateValidateColumn($data['data']['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data']['data'], $stringNullableFields, 'string', true);
        $this->templateTest->templateValidateColumn($data['data']['data'], $arrayNullableFields, 'array', true);
    }

    public function test_get_list_detail_by_id(): void
    {
        $is_paginate = false;
        $id = "27dbf1e0-a9e5-11ee-aa95-3216422210e8";

        $response = $this->httpClient->get("detail/$id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        $this->templateTest->templateGet($response, $is_paginate);

        // Get list key / column
        $intFields = ['whole_avg_calorie','whole_avg_price'];
        $stringFields = ['id','slug_name','list_name','created_at'];
        $stringNullableFields = ['list_desc'];
        $stringNullableFields = ['list_desc'];
        $arrayNullableFields = ['list_tag','consume'];

        $data = [$data['data']];
        $this->templateTest->templateValidateColumn($data, $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data, $stringNullableFields, 'string', true);
        $this->templateTest->templateValidateColumn($data, $arrayNullableFields, 'array', true);
        $this->templateTest->templateValidateColumn($data, $intFields, 'integer', false);
    }
}
