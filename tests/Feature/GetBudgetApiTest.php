<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;
use App\Helpers\Generator;

class GetBudgetApiTest extends TestCase
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

        $this->authTest = new AuthApiTest();
        $this->authTest->setUp();

        $this->token = $this->authTest->test_post_login();
    }

    public function test_get_dashboard_budget(): void
    {
        $response = $this->httpClient->post("/api/v1/budget/dashboard", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }
}
