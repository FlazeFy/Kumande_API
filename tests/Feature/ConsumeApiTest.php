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

    // Query Test
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

        // Get list key / column for consume detail
        $stringFieldsConsumeDetail = ['provide','main_ing'];
        $intFieldsConsumeDetail = ['calorie'];
        $this->templateTest->templateValidateColumn($data['data']['consume_detail'], $stringFieldsConsumeDetail, 'string', false);
        $this->templateTest->templateValidateColumn($data['data']['consume_detail'], $intFieldsConsumeDetail, 'integer', false);

        // Get list key / column for payment
        if(!is_null($data['data']['payment'])){
            $stringFieldsPayment = ['id_payment','payment_method','created_at'];
            $intFieldsPayment = ['payment_price'];
            $stringNullableFieldsPayment = ['updated_at'];
            $this->templateTest->templateValidateColumn($data['data']['payment'], $stringFieldsPayment, 'string', false);
            $this->templateTest->templateValidateColumn($data['data']['payment'], $intFieldsPayment, 'integer', false);
            $this->templateTest->templateValidateColumn($data['data']['payment'], $stringNullableFieldsPayment, 'string', true);
        }

        if(!is_null($data['data']['schedule'])){
            // Get list key / column for schedule
            $stringFieldsSchedule = ['created_at'];
            $stringNullableFieldsSchedule = ['updated_at'];
            $arrayFieldsSchedule = ['schedule_time'];
            $this->templateTest->templateValidateColumn($data['data']['schedule'], $stringFieldsSchedule, 'string', false);
            $this->templateTest->templateValidateColumn($data['data']['schedule'], $stringNullableFieldsSchedule, 'string', true);
            $this->templateTest->templateValidateColumn($data['data']['schedule'], $arrayFieldsSchedule, 'array', false);
        }

        if(!is_null($data['data']['allergic'])){
            // Get list key / column for allergic
            $stringFieldsAllergic = ['allergic_context'];
            $this->templateTest->templateValidateColumn($data['data']['allergic'], $stringFieldsAllergic, 'string', false);
        }
    }

    public function test_get_consume_by_context(): void
    {
        $ctx = 'provide';
        $target = 'Warteg%20Kembang%20Kuningan';
        $is_paginate = false;

        $response = $this->httpClient->post("by/context/$ctx/$target", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $is_paginate);

        // Get list key / column
        $stringFields = ['id','slug_name','consume_type','consume_name','consume_from'];
        $objectFields = ['consume_detail'];
        $arrayNullableFields = ['consume_tag','schedule'];
        $integerFields = ['is_favorite'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $objectFields, 'object', false);
        $this->templateTest->templateValidateColumn($data['data'], $arrayNullableFields, 'array', true);
        $this->templateTest->templateValidateColumn($data['data'], $integerFields, 'integer', false);

        // Validate contain
        $consumeFromRule = ['GoFood','GrabFood','ShopeeFood','Dine-In','Take Away','Cooking'];
        $isFavoriteRule = [1,0];
        $consumeTypeRule = ['Food','Snack','Drink'];

        $this->templateTest->templateValidateContain($data['data'], $consumeFromRule, 'consume_from');
        $this->templateTest->templateValidateContain($data['data'], $isFavoriteRule, 'is_favorite');
        $this->templateTest->templateValidateContain($data['data'], $consumeTypeRule, 'consume_type');
    }

    // Command Test
    public function test_hard_delete_consume_by_id(): void
    {
        $id = "27dbf1e0-a9e5-11zz-aa95-3216422210e8";

        $response = $this->httpClient->delete("destroy/$id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "permanentaly delete", "consume");
    }

    public function test_soft_delete_consume_by_id(): void
    {
        $id = "05a82ccb-c588-e3f0-2d59-004e882b89f8";

        $response = $this->httpClient->delete("delete/$id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "delete", "consume");
    }

    public function test_put_update_consume_favorite(): void
    {
        $id = "19663b65-1869-e0de-0cc7-62dc026b55e4";
        $data = [
            'is_favorite' => 1
        ];

        $response = $this->httpClient->put("update/favorite/$id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "update", "consume favorite");
    }

    public function test_put_update_consume_data(): void
    {
        $id = "19663b65-1869-e0de-0cc7-62dc026b55e4";
        $data = [
            'consume_type' => 'Food',
            'consume_name' => 'Consume Update A',
            'consume_from' => 'GoFood',
            'consume_tag' => '[{"slug_name":"chocolate","tag_name":"Chocolate"},{"slug_name":"tasty","tag_name":"Tasty"}]',
            'consume_comment' => 'Test comment'
        ];

        $response = $this->httpClient->put("update/data/$id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "update", "consume");
    }

    public function test_post_consume(): void
    {
        $data = [
            'firebase_id' => 'AAA-111',
            'consume_type' => 'Food',
            'consume_name' => 'Consume Create A',
            'consume_detail' => '[{"provide":"Ayam Geprek Crisbar","calorie":240,"main_ing":"Chicken"}]',
            'consume_from' => 'GrabFood',
            'is_favorite' => 0,
            'consume_tag' => '[{"slug_name":"rice", "tag_name":"Rice"},{"slug_name":"spicy", "tag_name":"Spicy"}]',
            'consume_comment' => null,
            'payment_method' => 'Ovo',
            'payment_price' => 36000,
            'is_payment' => 1,      
        ];

        $response = $this->httpClient->post("create", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "create", null, "You have add new payment and consume");
    }

    public function test_put_update_gallery_by_id(): void
    {
        $gallery_id = "0a319135-fec2-697d-335a-fa05832be998";
        $data = [
            'gallery_desc' => 'Testing description',
        ];

        $response = $this->httpClient->put("gallery/$gallery_id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);
        print($response->getBody());

        $this->templateTest->templateCommand($response, "update", "gallery");
    }
}
