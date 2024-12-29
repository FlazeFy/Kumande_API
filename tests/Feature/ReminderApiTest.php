<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;
use App\Helpers\Generator;

class ReminderApiTest extends TestCase
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
            'base_uri' => 'http://127.0.0.1:8000/api/v1/reminder/',
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
    public function test_get_my_reminder(): void
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
        $stringFields = ['reminder_id','reminder_name','reminder_type','reminder_body'];
        $stringNullableFields = ['id_rel_reminder'];
        $arrayNullableFields = ['reminder_context','reminder_attachment'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $stringNullableFields, 'string', true);
        $this->templateTest->templateValidateColumn($data['data'], $arrayNullableFields, 'array', true);

        // Validate contain
        $reminderTypeRule = ['Every Day','Every Week','Every Month','Every Year','Custom'];

        $this->templateTest->templateValidateContain($data['data'], $reminderTypeRule, 'reminder_type');
    }

    // Command Test
    public function test_post_reminder(): void
    {
        $data = [
            'reminder_name' => 'Testing reminder A',
            'reminder_context' => '[{"time":"06 July"},{"time":"04 July"}]',
            'reminder_attachment' => '[{"attachment_type":"location","attachment_context":"-6.22686285578315, 106.82139153159926","attachment_name":"Alfamidi"},{"attachment_type":"location","attachment_context":"-6.089146220510728, 106.74184781763985","attachment_name":"Jus Pancoran PIK 2"}]',
            'reminder_type' => 'Every Year',
            'reminder_body' => 'Hello, this is just for testing',   
        ];

        $response = $this->httpClient->post("add", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "create", "reminder");
    }

    // public function test_post_reminder_rel(): void
    // {
    //     $data = [
    //         'reminder_id' => '8d175756-0faa-c458-202d-29ef671143bc', 
    //     ];

    //     $response = $this->httpClient->post("rel", [
    //         'headers' => [
    //             'Authorization' => "Bearer {$this->token}"
    //         ],
    //         'json' => $data
    //     ]);
    //     $data = json_decode($response->getBody(), true);

    //     $this->templateTest->templateCommand($response, "create", null, "reminder turned on!");
    // }

    public function test_delete_reminder_rel_by_rel_id(): void
    {
        $relation_id = "69124d74-d434-a3ae-3d2f-dc9694db903e";

        $response = $this->httpClient->delete("rel/$relation_id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "delete", null, "reminder turned off!");
    }
    public function test_delete_reminder_by_id(): void
    {
        $id = "027d824b-5120-e60b-288b-54ca45e6977b";
        $data = [
            'reminder_name' => 'Reminder : Testing reminder A'
        ];

        $response = $this->httpClient->delete("delete/$id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "delete", "reminder");
    }
}
