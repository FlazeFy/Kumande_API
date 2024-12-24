<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;
use App\Helpers\Generator;

class ConsumeApiTest extends TestCase
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
            'base_uri' => 'http://127.0.0.1:8000/api/v1/consume/',
            'http_errors' => false
        ]);

        // Auth API Token
        $this->authTest = new AuthApiTest();
        $this->authTest->setUp();
        $this->token = $this->authTest->test_post_login();
        $this->slug = "nasi-warteg-tahu-bacem-sayur-pare-sayur-nangka";

        // Template
        $this->templateTest = new TestTemplate();

        $this->limit = 10;
        $this->ord = "DESC";
    }

    public function test_get_all_my_gallery(): void
    {
        $is_paginate = true;

        $response = $this->httpClient->get("gallery", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        $this->templateTest->templateGet($response, $is_paginate);

        // Get list key / column
        $stringFields = ['consume_name','consume_type','consume_from','created_at','gallery_url'];
        $stringNullableFields = ['gallery_desc'];
        $integerFields = ['is_favorite'];

        $this->templateTest->templateValidateColumn($data['data']['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data']['data'], $stringNullableFields, 'string', true);
        $this->templateTest->templateValidateColumn($data['data']['data'], $integerFields, 'integer', false);

        // Validate contain
        $consumeFromRule = ['GoFood','GrabFood','ShopeeFood','Dine-In','Take Away','Cooking'];
        $isFavoriteRule = [1,0];
        $consumeTypeRule = ['Food','Snack','Drink'];

        $this->templateTest->templateValidateContain($data['data']['data'], $consumeFromRule, 'consume_from');
        $this->templateTest->templateValidateContain($data['data']['data'], $isFavoriteRule, 'is_favorite');
        $this->templateTest->templateValidateContain($data['data']['data'], $consumeTypeRule, 'consume_type');
    }

    public function test_get_gallery_by_consume(): void
    {
        $is_paginate = false;

        $response = $this->httpClient->get("gallery/$this->slug", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        $this->templateTest->templateGet($response, $is_paginate);

        // Get list key / column
        $stringFields = ['id','gallery_url','created_at'];
        $stringNullableFields = ['gallery_desc'];

        $this->templateTest->templateValidateColumn($data['data']['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data']['data'], $stringNullableFields, 'string', true);
    }

    public function test_get_all_list_consume(): void
    {
        $is_paginate = false;

        $response = $this->httpClient->get("list/select", [
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

        $response = $this->httpClient->get("limit/{$this->limit}/order/{$this->ord}/favorite/$favorite/type/$type/provide/$provide/calorie/$calorie", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $is_paginate);

        // Get list key / column
        $stringFields = ['id','slug_name','consume_from','consume_name','consume_type','created_at'];
        $arrayFields = ['consume_detail'];
        $arrayNullableFields = ['consume_tag'];
        $stringNullableFields = ['consume_comment','payment_method'];
        $integerFields = ['is_favorite'];
        $integerNullableFields = ['payment_price'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data']['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data']['data'], $stringNullableFields, 'string', true);
        $this->templateTest->templateValidateColumn($data['data']['data'], $arrayFields, 'array', false);
        $this->templateTest->templateValidateColumn($data['data']['data'], $arrayNullableFields, 'array', true);
        $this->templateTest->templateValidateColumn($data['data']['data'], $integerFields, 'integer', false);
        $this->templateTest->templateValidateColumn($data['data']['data'], $integerNullableFields, 'integer', true);

        // Validate contain
        $consumeFromRule = ['GoFood','GrabFood','ShopeeFood','Dine-In','Take Away','Cooking'];
        $isFavoriteRule = [1,0];
        $consumeTypeRule = ['Food','Snack','Drink'];

        $this->templateTest->templateValidateContain($data['data']['data'], $consumeFromRule, 'consume_from');
        $this->templateTest->templateValidateContain($data['data']['data'], $isFavoriteRule, 'is_favorite');
        $this->templateTest->templateValidateContain($data['data']['data'], $consumeTypeRule, 'consume_type');
    }

    public function test_get_consume_detail_by_slug(): void
    {
        $is_paginate = false;
        $response = $this->httpClient->get("detail/$this->slug", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $is_paginate);

        // Get list key / column
        $stringFields = ['id','firebase_id','slug_name','consume_type','consume_name','consume_from','created_at'];
        $stringNullableFields = ['consume_comment','updated_at','deleted_at'];
        $arrayFields = ['consume_detail'];
        $arrayNullableFields = ['payment','schedule','allergic','consume_tag'];
        $integerFields = ['is_favorite'];

        $data_arr = [$data['data']];

        // Validate column
        $this->templateTest->templateValidateColumn($data_arr, $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data_arr, $stringNullableFields, 'string', true);
        $this->templateTest->templateValidateColumn($data_arr, $arrayFields, 'array', false);
        $this->templateTest->templateValidateColumn($data_arr, $arrayNullableFields, 'array', true);
        $this->templateTest->templateValidateColumn($data_arr, $integerFields, 'integer', false);

        // Validate contain
        $consumeFromRule = ['GoFood','GrabFood','ShopeeFood','Dine-In','Take Away','Cooking'];
        $isFavoriteRule = [1,0];
        $consumeTypeRule = ['Food','Snack','Drink'];

        $this->templateTest->templateValidateContain($data_arr, $consumeFromRule, 'consume_from');
        $this->templateTest->templateValidateContain($data_arr, $isFavoriteRule, 'is_favorite');
        $this->templateTest->templateValidateContain($data_arr, $consumeTypeRule, 'consume_type');

        foreach ($data['data']['consume_detail'] as $dt) {
            // Get list key / column
            $stringFields = ['provide','main_ing'];
            $intFields = ['calorie'];

            // Validate column
            $this->templateTest->templateValidateColumn($dt, $stringFields, 'string', false);
            $this->templateTest->templateValidateColumn($dt, $intFields, 'integer', false);
        }

        if(!is_null($data['data']['payment'])){
            foreach ($data['data']['payment'] as $dt) {
                // Get list key / column
                $stringFields = ['id_payment','payment_method','created_at'];
                $intFields = ['payment_price'];
                $stringNullableFields = ['updated_at'];

                // Validate column
                $this->templateTest->templateValidateColumn($dt, $stringFields, 'string', false);
                $this->templateTest->templateValidateColumn($dt, $intFields, 'integer', false);
                $this->templateTest->templateValidateColumn($dt, $stringNullableFields, 'string', true);
            }
        }

        if(!is_null($data['data']['schedule'])){
            foreach ($data['data']['schedule'] as $dt) {
                // Get list key / column
                $stringFields = ['created_at'];
                $stringNullableFields = ['updated_at'];
                $arrayFields = ['schedule_time'];

                // Validate column
                $this->templateTest->templateValidateColumn($dt, $stringFields, 'string', false);
                $this->templateTest->templateValidateColumn($dt, $stringNullableFields, 'string', true);
                $this->templateTest->templateValidateColumn($dt, $arrayFields, 'array', false);
            }
        }

        if(!is_null($data['data']['allergic'])){
            foreach ($data['data']['allergic'] as $dt) {
                // Get list key / column
                $stringFields = ['allergic_context'];

                // Validate column
                $this->templateTest->templateValidateColumn($dt, $stringFields, 'string', false);
            }
        }
    }
}
