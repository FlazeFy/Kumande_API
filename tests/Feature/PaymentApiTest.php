<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;
use App\Helpers\Generator;

class PaymentApiTest extends TestCase
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
            'base_uri' => 'http://127.0.0.1:8000/api/v1/payment/',
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
    // TC-S008
    public function test_get_total_spending_this_year(): void
    {
        $randDate = Generator::getRandDate(0);
        $month = date('m',strtotime($randDate));
        $is_paginate = false;

        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("total/month/$month", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $is_paginate);

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

    // TC-C002
    public function test_get_total_spend_day(): void
    {
        $randDate = Generator::getRandDate(0);
        $month = date('m',strtotime($randDate));
        $year = date('Y',strtotime($randDate));
        $is_paginate = false;

        $token = $this->authTest->test_post_login();
        Audit::auditRecord("Test - Returned Data", "TC-C002", "Month : $month\nYear : $year");

        $response = $this->httpClient->get("total/month/$month/year/$year", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $is_paginate);

        // Get list key / column
        $intFields = ['total'];
        $stringFields = ['context'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data'], $intFields, 'integer', false);        
    }

    public function test_get_monthly_spend(): void
    {
        $randDate = Generator::getRandDate(0);
        $month = 'Jul';
        $year = '2024';
        $is_paginate = true;

        $token = $this->authTest->test_post_login();
        Audit::auditRecord("Test - Returned Data", "TC-XXXX", "Month : $month\nYear : $year");

        $response = $this->httpClient->get("detail/month/$month/year/$year", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        
        $this->templateTest->templateGet($response, $is_paginate);

        // Get list key / column
        $intFields = ['payment_price'];
        $stringFields = ['consume_name','consume_type','consume_id','created_at','payment_method'];

        // Validate column
        $this->templateTest->templateValidateColumn($data['data']['data'], $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data['data']['data'], $intFields, 'integer', false);
        
        // Validate contain
        $paymentMethodeRule = ['GoPay','Ovo','Dana','Link Aja','MBanking','Cash','Gift','Cuppon','Free'];
        $consumeTypeRule = ['Food','Snack','Drink'];

        $this->templateTest->templateValidateContain($data['data']['data'], $consumeTypeRule, 'consume_type');
        $this->templateTest->templateValidateContain($data['data']['data'], $paymentMethodeRule, 'payment_method');
    }

    // Command Test
    public function test_put_update_payment_by_id(): void
    {
        $id = "ff795774-4655-3228-07fc-5f733aae71b9";
        $data = [
            'payment_method' => 'Cash',
            'payment_price' => 20000
        ];

        $response = $this->httpClient->put("update/$id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);
        print($response->getBody());

        $this->templateTest->templateCommand($response, "update", "payment");
    }

    public function test_delete_payment_by_id(): void
    {
        $id = "ff795774-4655-3228-07fc-5f733aae71b9";

        $response = $this->httpClient->delete("delete/$id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
        ]);
        $data = json_decode($response->getBody(), true);
        print($response->getBody());

        $this->templateTest->templateCommand($response, "delete", "payment");
    }
}
