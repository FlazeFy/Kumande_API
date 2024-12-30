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

    public function test_get_check_consume_by_slug(): void
    {
        $is_paginate = false;
        $slug = "bakso-campur";
        $list_id = "27dbf1e0-a9e5-11ee-aa95-3216422210e8";

        $response = $this->httpClient->get("check/$slug/$list_id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        $this->templateTest->templateGet($response, $is_paginate);

        // Get list key / column
        $intFields = ['calorie','average_price'];
        $stringFields = ['consume_name','consume_from','provide'];

        $data = [$data['data']];
        $this->templateTest->templateValidateColumn($data, $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data, $intFields, 'integer', false);

        // Validate contain
        $consumeFromRule = ['GoFood','GrabFood','ShopeeFood','Dine-In','Take Away','Cooking'];

        $this->templateTest->templateValidateContain($data, $consumeFromRule, 'consume_from');
    }

    // Command Test
    public function test_post_list(): void
    {
        $data = [
            'list_name' => 'Testing New List A',
            'list_tag' => '[{"slug_name":"chocolate","tag_name":"Chocolate"},{"slug_name":"tasty","tag_name":"Tasty"}]',
            'list_desc' => 'Testing Description',
            'firebase_id' => '123ABC'
        ];

        $response = $this->httpClient->post("create", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "create", "list");
    }

    public function test_post_list_relation(): void
    {
        $data = [
            'list_id' => '4044df51-1e7e-1f5d-1cdd-c89b42ce0f22',
            'consume_slug' => 'bakso-campur',
        ];

        $response = $this->httpClient->post("create_rel", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "create", null, "consume is added to list");
    }

    public function test_delete_list_relation_by_relation_id(): void
    {
        $relation_id = "dccbb0d3-394d-6b16-3c16-c35608bccf5e";

        $response = $this->httpClient->delete("delete_rel/$relation_id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "delete", null, "consume removed from list");
    }

    public function test_delete_list_by_id(): void
    {
        $id = "4044df51-1e7e-1f5d-1cdd-c89b42ce0f22";

        $response = $this->httpClient->delete("delete/$id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "delete", "consume list");
    }

    public function test_put_update_list_data_by_id(): void
    {
        $id = "27dbf1e0-a9e5-11ee-aa95-3216422210e8";
        $data = [
            'list_name' => 'Testing Update List A',
            'list_tag' => '[{"slug_name":"chocolate","tag_name":"Chocolate"},{"slug_name":"tasty","tag_name":"Tasty"}]',
            'list_desc' => 'Testing Description',
        ];

        $response = $this->httpClient->put("update/data/$id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "update", "consume list");
    }
}
