<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;
use App\Helpers\Generator;

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

    // ============================== Auth Module ==============================

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
    public function test_post_sign_out(): void
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

    // ============================== Stats Module ==============================

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
        $randDate = Generator::getRandDate();
        $month = date('m',$randDate);
        $year = date('Y',$randDate);

        $token = $this->test_post_login();
        Audit::auditRecord("Test - Returned Data", "TC-S002", "Month : $month\nYear : $year");

        $response = $this->httpClient->get("/api/v1/analytic/payment/month/$month/year/$year", [
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
        $randDate = Generator::getRandDate();
        $date = date('Y-m-d',$randDate);

        $token = $this->test_post_login();
        Audit::auditRecord("Test - Returned Data", "TC-S003", "Date : $date");

        $response = $this->httpClient->get("/api/v1/count/calorie/fulfill/$date", [
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

    // TC-S007
    public function test_get_most_consume_main_ing(): void
    {
        $token = $this->test_post_login();
        $response = $this->httpClient->get("/api/v1/consume/total/bymain", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }

    // TC-S008
    public function test_get_total_spending_this_year(): void
    {
        $randDate = Generator::getRandDate();
        $month = date('m',$randDate);

        $token = $this->test_post_login();
        $response = $this->httpClient->get("/api/v1/payment/total/month/$month", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }

    // TC-S009
    public function test_get_total_daily_calorie_this_month(): void
    {
        // Notes : Same api with TC-C003
        ApiTest::test_get_calendar_daily_calorie(); 
    }

    // TC-S010
    public function test_get_budget_spending_this_year(): void
    {
        $randDate = Generator::getRandDate();
        $year = date('Y',$randDate);

        $token = $this->test_post_login();
        $response = $this->httpClient->get("/api/v1/budget/$year", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }

    // TC-S011
    public function test_get_spending_info(): void
    {
        $token = $this->test_post_login();
        $response = $this->httpClient->get("/api/v1/count/payment", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }

    // TC-S012
    public function test_get_body_info(): void
    {
        $token = $this->test_post_login();
        $response = $this->httpClient->get("/api/v1/count/calorie", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }

    // TC-S013
    public function test_get_consume_total(): void
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

    // ============================== Schedule Module ==============================

    // TC-C001
    public function test_get_my_schedule(): void
    {
        $token = $this->test_post_login();
        $response = $this->httpClient->get("/api/v1/schedule", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }

    // TC-C002
    public function test_get_total_spend_day(): void
    {
        $randDate = Generator::getRandDate();
        $month = date('m',$randDate);
        $year = date('Y',$randDate);

        $token = $this->test_post_login();
        Audit::auditRecord("Test - Returned Data", "TC-C002", "Month : $month\nYear : $year");

        $response = $this->httpClient->get("/api/v1/payment/total/month/$month/year/$year", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }

    // TC-C003
    public function test_get_calendar_daily_calorie(): void
    {
        $randDate = Generator::getRandDate();
        $month = date('m',$randDate);
        $year = date('Y',$randDate);

        $token = $this->test_post_login();
        Audit::auditRecord("Test - Returned Data", "TC-C003", "Month : $month\nYear : $year");

        $response = $this->httpClient->get("/api/v1/consume/total/day/cal/month/$month/year/$year", [
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }
}
