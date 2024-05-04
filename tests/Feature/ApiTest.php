<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;

class ApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    protected $httpClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000/',
            'http_errors' => false
        ]);
    }

    // TC-001
    public function test_post_login()
    {
        // Post login
        $response = $this->httpClient->post("/api/v1/login", [
            'json' => [
                'email' => 'flazen.edu@gmail.com',
                'password' => 'nopass123',
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('token', $data);

        $token = $data['token'];
        Audit::auditRecord("Test - Returned Data", "TC-001", "Token : ".$token);

        // View dashboard for test auth
        $response = $this->httpClient->get("/api/v1/consume/total/byfrom", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());

        return $token;
    }

    // TC-002
    public function test_get_sign_out(): void
    {
        $token = $this->test_post_login();
        $response = $this->httpClient->post("/api/v1/logout", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }

    // TC-S001
    public function test_get_today_schedule(): void
    {
        $token = $this->test_post_login();
        $response = $this->httpClient->get("/api/v1/schedule/day/Sat", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }

    // TC-S002
    public function test_get_monthly_payment_analytic(): void
    {
        $token = $this->test_post_login();
        $response = $this->httpClient->get("/api/v1/analytic/payment/month/5/year/2024", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }

    // TC-S003
    public function test_get_today_calories(): void
    {
        $token = $this->test_post_login();
        $response = $this->httpClient->get("/api/v1/count/calorie/fulfill/2024-05-04", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }

    // TC-S004
    public function test_get_most_consume_type(): void
    {
        $token = $this->test_post_login();
        $response = $this->httpClient->get("/api/v1/consume/total/bytype", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }

    // TC-S005
    public function test_get_most_consume_from(): void
    {
        $token = $this->test_post_login();
        $response = $this->httpClient->get("/api/v1/consume/total/byfrom", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }

    // TC-S006
    public function test_get_most_consume_provide(): void
    {
        $token = $this->test_post_login();
        $response = $this->httpClient->get("/api/v1/consume/total/byprovide", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }
}
