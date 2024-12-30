<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use GuzzleHttp\Client;
use Tests\TestCase;
use App\Helpers\Audit;
use App\Helpers\Generator;

class UserApiTest extends TestCase
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
            'base_uri' => 'http://127.0.0.1:8000/api/v1/user/',
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
    public function test_get_my_profile(): void
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
        $stringFields = ['id','username','fullname','email','gender','born_at','created_at'];
        $stringNullableFields = ['updated_at'];

        // Validate column
        $data = [$data['data']];
        $this->templateTest->templateValidateColumn($data, $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data, $stringNullableFields, 'string', true);
    }

    public function test_get_my_latest_body_info(): void
    {
        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("body_info", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateGet($response, $this->is_paginate);

        // Get list key / column
        $stringFields = ['born_at','gender'];
        $stringNullableFields = ['blood_pressure','created_at','calorie_updated'];
        $intFields = ['age'];
        $intNullableFields = ['blood_glucose','cholesterol','weight','height','result'];
        $doubleNullableFields = ['gout','bmi'];

        // Validate column
        $data = [$data['data']];
        $this->templateTest->templateValidateColumn($data, $stringFields, 'string', false);
        $this->templateTest->templateValidateColumn($data, $stringNullableFields, 'string', true);
        $this->templateTest->templateValidateColumn($data, $intFields, 'integer', false);
        $this->templateTest->templateValidateColumn($data, $intNullableFields, 'integer', true);
        $this->templateTest->templateValidateColumn($data, $doubleNullableFields, 'double', true);
    }

    public function test_get_my_body_history(): void
    {
        $token = $this->authTest->test_post_login();
        $response = $this->httpClient->get("my_body_history", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ]
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateGet($response, $this->is_paginate);

        // Body Info
        $body_info = $data['data']['body_info'];
        if(!is_null($body_info)){
            // Get list key / column
            $stringBodyInfoFields = ['id','blood_pressure','created_at'];
            $doubleBodyInfoFields = ['blood_glucose','gout','cholesterol'];

            // Validate column
            $this->templateTest->templateValidateColumn($body_info, $stringBodyInfoFields, 'string', false);
            $this->templateTest->templateValidateColumn($body_info, $doubleBodyInfoFields, 'double', false);
        }

        // Calorie
        $calorie = $data['data']['calorie'];
        if(!is_null($calorie)){
            // Get list key / column
            $stringCalorieFields = ['id','created_at'];
            $intCalorieFields = ['weight','height','result'];

            // Validate column
            $this->templateTest->templateValidateColumn($calorie, $stringCalorieFields, 'string', false);
            $this->templateTest->templateValidateColumn($calorie, $intCalorieFields, 'integer', false);
        }

        // Dashboard
        $dashboard = [$data['data']['dashboard']];
        if(!is_null($dashboard)){
            // Get list key / column
            $doubleDashboardFields = ['max_gout','min_gout'];
            $intDashboardFields = ['max_blood_glucose','min_blood_glucose','max_cholesterol','min_cholesterol','max_weight','min_weight','max_height','min_height'];

            // Validate column
            $this->templateTest->templateValidateColumn($dashboard, $doubleDashboardFields, 'double', false);
            $this->templateTest->templateValidateColumn($dashboard, $intDashboardFields, 'integer', false);
        }
    }

    // Command Test
    public function test_put_update_user(): void
    {
        $data = [
            'fullname' => 'Leonardho Rante Sitanggang',
            'email' => 'flazen.edu2@gmail.com',
            'gender' => 'female',
            'born_at' => '2001-10-10',
        ];

        $response = $this->httpClient->put("edit", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "update", "account");
    }

    public function test_put_update_telegram_id(): void
    {
        $data = [
            'telegram_user_id' => '1317625977',
        ];

        $response = $this->httpClient->put("edit_telegram_id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "update", "telegram ID");
    }

    public function test_put_update_timezone(): void
    {
        $data = [
            'timezone' => '+07:00',
        ];

        $response = $this->httpClient->put("edit_timezone", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "update", "timezone");
    }

    public function test_post_create_body_info(): void
    {
        $data = [
            'blood_pressure' => "126/90", 
            'blood_glucose' => 82, 
            'gout' => 5.8,  
            'cholesterol' => 178, 
        ];

        $response = $this->httpClient->post("body_info/create", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "create", "body info");
    }

    public function test_delete_body_info_by_id(): void
    {
        $id = "8aa2b7cf-5230-813c-3ddb-cd2a38eb4544";

        $response = $this->httpClient->delete("body_info/delete/$id", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
        ]);
        $data = json_decode($response->getBody(), true);

        $this->templateTest->templateCommand($response, "delete", "1 body info");
    }

    public function test_post_create_user(): void
    {
        $data = [
            'firebase_id' => 'aBcDeFgHiJkLmNoPqRsTuVwXyZ123',
            'fullname' => 'Leonardho Test',
            'username'  => 'flazentest',
            'email' => 'flazen.test@gmail.com',
            'password' => 'testpassword',
            'gender' => 'male',
            'image_url' => null,
            'born_at' => '2000-10-11',
        ];

        $response = $this->httpClient->post("create", [
            'headers' => [
                'Authorization' => "Bearer {$this->token}"
            ],
            'json' => $data
        ]);
        $data = json_decode($response->getBody(), true);
        print($response->getBody());

        $this->templateTest->templateCommand($response, "create", null, "account is registered");
    }
}
