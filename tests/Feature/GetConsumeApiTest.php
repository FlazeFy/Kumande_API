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

        $this->authTest = new AuthApiTest();
        $this->authTest->setUp();

        $this->limit = 10;
        $this->ord = "DESC";
        $this->token = $this->authTest->test_post_login();
    }

    public function test_get_all_consume_list(): void
    {
        $response = $this->httpClient->get("/api/v1/list/limit/{$this->limit}/order/{$this->ord}", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }

    public function test_get_all_list_consume(): void
    {
        $response = $this->httpClient->get("/api/v1/consume/list/select", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }

    public function test_get_all_consume(): void
    {
        $favorite = 'all';
        $type = 'all';
        $provide = 'all';
        $calorie = 'all';

        $response = $this->httpClient->get("/api/v1/consume/limit/{$this->limit}/order/{$this->ord}/favorite/$favorite/type/$type/provide/$provide/calorie/$calorie", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }
}
