<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;
use App\Helpers\Generator;

class BudgetApiTest extends TestCase
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
            'base_uri' => 'http://127.0.0.1:8000/api/v1/budget/',
            'http_errors' => false
        ]);

        // Auth API Token
        $this->authTest = new AuthApiTest();
        $this->authTest->setUp();
        $this->token = $this->authTest->test_post_login();

        // Template
        $this->templateTest = new TestTemplate();
    }

    public function test_get_dashboard_budget(): void
    {
        $is_paginate = false;

        $response = $this->httpClient->post("dashboard", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateGet($response, $is_paginate);

        // Validate contain
        $monthNameShort = Generator::getMonthName('all','short');
        $this->templateTest->templateValidateContain($data['data'], $monthNameShort, 'month');

        // Get list key / column
        $stringFields = ['month','year'];
        $intFields = ['budget_total'];
        $objectFields = ['payment_history'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', false);
        $this->templateTest->templateValidateColumn($data['data'], $objectFields, 'object', false);
    }

    public function test_get_all_budget_by_year(): void
    {
        $year = 2024;
        $is_paginate = false;

        $response = $this->httpClient->get("by/$year", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateGet($response, $is_paginate);

        // Validate contain
        $monthNameShort = Generator::getMonthName('all','short');
        $this->templateTest->templateValidateContain($data['data'], $monthNameShort, 'context');

        // Get list key / column
        $stringFields = ['context'];
        $intFields = ['total'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', false);
    }

    public function test_delete_budget_by_id(): void
    {
        $id = "b2f1386d-cd36-bb09-2b4a-d903f6b10faz";

        $response = $this->httpClient->delete("$id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "delete", "budget");
    }

    public function test_post_budget(): void
    {
        $body = [
            "firebase_id" => "1",
            "budget_total" => 2000000,
            "year" => 2024,
            "month" => 'Nov'
        ];

        $response = $this->httpClient->post("create", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $body
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "create", "budget");
    }
}
