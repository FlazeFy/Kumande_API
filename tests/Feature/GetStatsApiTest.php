<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;
use App\Helpers\Generator;

class GetStatsApiTest extends TestCase
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
            'base_uri' => 'http://127.0.0.1:8000/',
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

    // TC-S001
    public function test_get_today_schedule(): void
    {
        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("/api/v1/schedule/day/Sat", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $arrayFields = ['consume_detail','schedule_time'];
        $arrayNullableFields = ['schedule_tag'];
        $stringFields = ['id','schedule_consume','consume_type','created_at','created_by'];
        $stringNullableFields = ['firebase_id','consume_id','schedule_desc'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $stringNullableFields, 'string', true);
        $this->templateTest->templateValidateColumn($data['data'], $arrayFields, 'array', false);
        $this->templateTest->templateValidateColumn($data['data'], $arrayNullableFields, 'array', true);

        // Validate contain
        $consumeTypeRule = ['Food','Snack','Drink'];

        $this->templateTest->templateValidateContain($data['data'], $consumeTypeRule, 'consume_type');
    }

    // TC-S002
    public function test_get_monthly_payment_analytic(): void
    {
        $randDate = Generator::getRandDate(0);
        $month = date('m',$randDate);
        $year = date('Y',$randDate);

        $token = $this->authTest->test_post_login();
        Audit::auditRecord("Test - Returned Data", "TC-S002", "Month : $month\nYear : $year");

        $response = $this->httpClient->get("/api/v1/analytic/payment/month/$month/year/$year", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $intFields = ['average','max','min','total'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', false);
    }

    // TC-S003
    public function test_get_today_calories(): void
    {
        $randDate = Generator::getRandDate(0);
        $date = date('Y-m-d',$randDate);

        $token = $this->authTest->test_post_login();
        Audit::auditRecord("Test - Returned Data", "TC-S003", "Date : $date");

        $response = $this->httpClient->get("/api/v1/count/calorie/fulfill/$date", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
       
        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $intFields = ['total','target'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', false);
    }

    // TC-S004 && TC-S013
    public function test_get_most_consume_type(): void
    {
        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("/api/v1/consume/total/bytype", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $stringFields = ['context'];
        $intFields = ['total'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', false);

        // Validate contain
        $consumeTypeRule = ['Food','Snack','Drink'];

        $this->templateTest->templateValidateContain($data['data'], $consumeTypeRule, 'context');
    }

    // TC-S005
    public function test_get_most_consume_from(): void
    {
        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("/api/v1/consume/total/byfrom", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $intFields = ['total'];
        $stringFields = ['context'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', false);

        // Validate contain
        $consumeFromRule = ['GoFood','GrabFood','ShopeeFood','Dine-In','Take Away'];

        $this->templateTest->templateValidateContain($data['data'], $consumeFromRule, 'context');
    }

    // TC-S006
    public function test_get_most_consume_provide(): void
    {
        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("/api/v1/consume/total/byprovide", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $intFields = ['total'];
        $stringFields = ['context'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', false);

    }

    // TC-S007
    public function test_get_most_consume_main_ing(): void
    {
        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("/api/v1/consume/total/bymain", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $intFields = ['total'];
        $stringFields = ['context'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', false);
    }

    // TC-S010
    public function test_get_budget_spending_this_year(): void
    {
        $randDate = Generator::getRandDate(0);
        $year = date('Y',$randDate);

        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("/api/v1/budget/by/$year", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $stringFields = ['context'];
        $intFields = ['total'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', false);

        // Validate contain
        $monthNameShort = Generator::getMonthName('all','short');
        $this->templateTest->templateValidateContain($data['data'], $monthNameShort, 'context');
    }

    // TC-S011
    public function test_get_spending_info(): void
    {
        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("/api/v1/count/payment", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $intFields = ['total_days','total_payment'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', false);
    }

    // TC-S012
    public function test_get_body_info(): void
    {
        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("/api/v1/count/calorie", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $intFields = ['weight','height','result'];
        $stringFields = ['created_at'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', false);
    }

    // TC-S008
    public function test_get_total_spending_this_year(): void
    {
        $randDate = Generator::getRandDate(0);
        $month = date('m',$randDate);

        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("/api/v1/payment/total/month/$month", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $intFields = ['total'];
        $stringFields = ['context'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', false);

        // Validate contain
        $monthNameShort = Generator::getMonthName('all','short');
        $this->templateTest->templateValidateContain($data['data'], $monthNameShort, 'context');
    }
 
    // TC-C001
    public function test_get_my_schedule(): void
    {
        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("/api/v1/schedule", [
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
 
    // TC-C002
    public function test_get_total_spend_day(): void
    {
        $randDate = Generator::getRandDate(0);
        $month = date('m',$randDate);
        $year = date('Y',$randDate);

        $token = $this->authTest->test_post_login();
        Audit::auditRecord("Test - Returned Data", "TC-C002", "Month : $month\nYear : $year");

        $response = $this->httpClient->get("/api/v1/payment/total/month/$month/year/$year", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $intFields = ['total'];
        $stringFields = ['context'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', false);        
    }
 
    // TC-C003 && TC-S009
    public function test_get_calendar_daily_calorie(): void
    {
        $randDate = Generator::getRandDate(0);
        $month = date('m',$randDate);
        $year = date('Y',$randDate);

        $token = $this->authTest->test_post_login();
        Audit::auditRecord("Test - Returned Data", "TC-C003", "Month : $month\nYear : $year");

        $response = $this->httpClient->get("/api/v1/consume/total/day/cal/month/$month/year/$year", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $intFields = ['total'];
        $stringFields = ['context'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', false);
    }

    public function test_get_calorie_total_by_consume_type(): void
    {
        $type = "all";

        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("/api/v1/consume/calorie/bytype/$type", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $intFields = ['calorie'];
        $stringFields = ['consume_type'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', false);

        // Validate contain
        $consumeTypeRule = ['Food','Snack','Drink'];

        $this->templateTest->templateValidateContain($data['data'], $consumeTypeRule, 'consume_type');
    }

    public function test_get_calorie_max_min(): void
    {
        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("/api/v1/consume/calorie/maxmin", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $intFields = ['max_calorie','min_calorie','avg_calorie'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', false);
    }
}
