<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;
use App\Helpers\Generator;

class GetConsumeApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    protected $httpClient;

    private $authTest;
    private $limit;
    private $ord;
    private $token;

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

        $this->limit = 10;
        $this->ord = "DESC";
    }

    public function test_get_all_consume_list(): void
    {
        $is_paginate = true;

        $response = $this->httpClient->get("/api/v1/list/limit/{$this->limit}/order/{$this->ord}", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $is_paginate);

        // Get list key / column
        $stringFields = ['id','slug_name','list_name','created_at'];
        $stringNullableFields = ['list_desc'];
        $arrayNullableFields = ['consume','list_tag'];

        $this->templateTest->templateValidateColumn($data['data']['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data']['data'], $stringNullableFields, 'string', true);
        $this->templateTest->templateValidateColumn($data['data']['data'], $arrayNullableFields, 'array', true);
    }

    public function test_get_all_list_consume(): void
    {
        $is_paginate = false;

        $response = $this->httpClient->get("/api/v1/consume/list/select", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateGet($response, $is_paginate);

        // Get list key / column
        $stringFields = ['slug_name','consume_name','consume_type'];

        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);

        // Validate contain
        $consumeTypeRule = ['Food','Snack','Drink'];

        $this->templateTest->templateValidateContain($data['data'], $consumeTypeRule, 'consume_type');
    }

    public function test_get_all_consume(): void
    {
        $favorite = 'all';
        $type = 'all';
        $provide = 'all';
        $calorie = 'all';
        $is_paginate = true;

        $response = $this->httpClient->get("/api/v1/consume/limit/{$this->limit}/order/{$this->ord}/favorite/$favorite/type/$type/provide/$provide/calorie/$calorie", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $is_paginate);

        // Get list key / column
        $stringFields = ['id','slug_name','consume_from','consume_name','consume_type','created_at'];
        $arrayFields = ['consume_detail','consume_tag'];
        $stringNullableFields = ['consume_comment','payment_method'];
        $integerFields = ['is_favorite','payment_price'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data']['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data']['data'], $stringNullableFields, 'string', true);
        $this->templateTest->templateValidateColumn($data['data']['data'], $arrayFields, 'array', false);
        $this->templateTest->templateValidateColumn($data['data']['data'], $integerFields, 'integer', false);

        // Validate contain
        $consumeFromRule = ['GoFood','GrabFood','ShopeeFood','Dine-In','Take Away'];
        $isFavoriteRule = [1,0];
        $consumeTypeRule = ['Food','Snack','Drink'];

        $this->templateTest->templateValidateContain($data['data']['data'], $consumeFromRule, 'consume_from');
        $this->templateTest->templateValidateContain($data['data']['data'], $isFavoriteRule, 'is_favorite');
        $this->templateTest->templateValidateContain($data['data']['data'], $consumeTypeRule, 'consume_type');
    }
}
