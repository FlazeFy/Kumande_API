<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;
use App\Helpers\Generator;

class ScheduleApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    protected $httpClient;

    private $authTest;
    private $is_paginate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/api/v1/schedule/',
            'http_errors' => false
        ]);

        // Auth API Token
        $this->authTest = new AuthApiTest();
        $this->authTest->setUp();
        $this->token = $this->authTest->test_post_login();

        // Template
        $this->templateTest = new TestTemplate();

        $this->is_paginate = false;
    }

    // Query Test
    // TC-S001
    public function test_get_today_schedule(): void
    {
        $token = $this->authTest->test_post_login();
        $day = "Fri";
        $response = $this->httpClient->get("day/$day", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $stringFields = ['id','consume_name'];
        $stringNullableFields = ['schedule_desc'];
        $objectFields = ['schedule_time'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $stringNullableFields, 'string', true);
        $this->templateTest->templateValidateColumn($data['data'], $objectFields, 'array', false);
    }
 
    // TC-C001
    public function test_get_my_schedule(): void
    {
        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $stringFields = ['day','time','schedule_consume'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);

        // Validate contain
        $timeRule = ['Breakfast','Lunch','Dinner'];
        $dayName = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

        $this->templateTest->templateValidateContain($data['data'], $timeRule, 'time');
        $this->templateTest->templateValidateContain($data['data'], $dayName, 'day');
    }

    // Command Test
    public function test_delete_schedule_by_id(): void
    {
        $id = '1ac36d07-b43e-4zz7-34c8-c49bc217f650';

        $response = $this->httpClient->delete("delete/$id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "delete", "schedule");
    }

    public function test_put_update_schedule_data_by_id(): void
    {
        $id = '1ac36d07-b43e-4257-34c8-c49bc217f650';
        $data = [
            'consume_id' => '05a82ccb-c588-e3f0-2d59-004e882b89f8',
            'schedule_desc' => 'Testing Update Description',
            'schedule_time' => '[{"day":"Sat","category":"Dinner","time":"20:00"}]',
        ];

        $response = $this->httpClient->put("update/data/$id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "update", "schedule");
    }

    public function test_post_create_schedule(): void
    {
        $data = [
            'firebase_id' => '123ABC',
            'consume_id' => '05a82ccb-c588-e3f0-2d59-004e882b89f8',
            'schedule_desc' => 'Testing Create Description',
            'schedule_time' => '[{"day":"Sun","category":"Dinner","time":"22:00"}]',
        ];

        $response = $this->httpClient->post("create", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "create", "schedule");
    }
}
