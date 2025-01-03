<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;
use App\Helpers\Generator;

class StatsApiTest extends TestCase
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

    // Query Test
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

    // Command Test
    public function test_put_update_allergic_by_id(): void
    {
        $allergic_id = "72251e48-88e7-0fc9-0114-88872ef787c0";
        $data = [
            'allergic_context' => 'red rice',
            'allergic_desc' => 'Testing description',
        ];

        $response = $this->httpClient->put("/api/v1/analytic/allergic/$allergic_id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);
        print($response->getBody());

        $this->templateTest->templateCommand($response, "update", "allergic");
    }

    public function test_post_allergic(): void
    {
        $data = [
            'allergic_context' => 'wheat test',
            'allergic_desc' => 'Testing description',
        ];

        $response = $this->httpClient->post("/api/v1/analytic/allergic", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);
        print($response->getBody());

        $this->templateTest->templateCommand($response, "create", "allergic");
    }

    public function test_delete_allergic_by_id(): void
    {
        $allergic_id = "435f3fde-a632-0878-0575-8824a247bef7";

        $response = $this->httpClient->delete("/api/v1/analytic/allergic/$allergic_id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
        ]);
        $data = json_decode($response->getBody(), true);
        print($response->getBody());

        $this->templateTest->templateCommand($response, "delete", "allergic");
    }

    public function test_post_count_calorie(): void
    {
        $data = [
            'firebase_id' => '123ABC',
            'weight' => 62,
            'height' => 182,
            'result' => 1800
        ];

        $response = $this->httpClient->post("/api/v1/count/calorie", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "create", "count calorie");
    }

    public function test_delete_count_calorie_by_id(): void
    {
        $id = '206d2949-bd1a-07a7-3b87-c70d6a521da4';

        $response = $this->httpClient->delete("/api/v1/count/calorie/$id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "delete", "1 count calorie");
    }
}
