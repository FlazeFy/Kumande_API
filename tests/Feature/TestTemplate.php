<?php
namespace Tests\Feature;

use Tests\TestCase;
use GuzzleHttp\Client;

class TestTemplate extends TestCase
{
    public function templateGet($obj, $is_paginate)
    {
        $this->assertEquals(200, $obj->getStatusCode(), "Expected status code 200, Result : {$obj->getStatusCode()}");
        $data = json_decode($obj->getBody(), true);
        $this->assertIsString($data['message'], "Expected 'message' to be a string");

        if (!$is_paginate) {
            $this->assertIsArray($data['data'], "Expected 'data' to be an array when not paginated");
        }

        if ($is_paginate) {
            $this->assertIsArray($data['data']['data'], "Expected 'data.data' to be an array when paginated");
        }
    }

    public function templateCommand($obj, $type, $ctx, $custom_message = null)
    {
        $status_code = $type != "create" ? 200 : 201;
        $this->assertEquals($status_code, $obj->getStatusCode(), "Expected status code $status_code, Result : {$obj->getStatusCode()}");
        $data = json_decode($obj->getBody(), true);
        $this->assertIsString($data['message'], "Expected 'message' to be a string");

        if($custom_message == null){
            $this->assertEquals($data['message'], ucfirst($ctx)." is ".$type."d", "Expected 'message' to be a string");
        } else {
            // Using api custom message if context empty
            $this->assertEquals($data['message'], $custom_message);
        }
    }

    public function templateValidateContain($data, $list, $target)
    {
        foreach ($data as $idx => $dt) {
            $this->assertIsObject((object)$dt, "Expected the item to be an object");
            $this->assertContains($dt[$target], 
                $list, 
                "Column $target with value = {$dt[$target]} must contain in list. Index Data: $idx"
            );
        }
    }

    public function templateValidateColumn($data, $obj, $dataType, $nullable)
    {
        $dataArray = is_array($data) ? $data : [$data];

        foreach ($dataArray as $item) {
            $this->assertIsObject((object)$item, 'Item should be an object');
            foreach ($obj as $field) {
                $this->assertArrayHasKey($field, $item, "Item should have the property $field");
                if ($nullable && $item[$field] === null) {
                    $this->assertNull($item[$field], "The property $field should be null");
                } else {
                    switch ($dataType) {
                        case 'integer':
                            $this->assertIsInt($item[$field], "The property $field should be an integer");
                            break;
                        case 'string':
                            $this->assertIsString($item[$field], "The property $field should be a string");
                            break;
                        case 'boolean':
                            $this->assertIsBool($item[$field], "The property $field should be a boolean");
                            break;
                        case 'array':
                            $this->assertIsArray($item[$field], "The property $field should be an array");
                            break;
                        case 'object':
                            $this->assertIsObject((object)$item[$field], "The property $field should be an object");
                            break;
                        case 'float':
                            $this->assertIsFloat($item[$field], "The property $field should be a float");
                            break;
                    }

                    if ($dataType === 'integer' || $dataType === 'float') {
                        if (is_int($item[$field])) {
                            $this->assertEquals($item[$field] % 1, 0, "The property $field should be an integer");
                        } else {
                            $this->assertNotEquals($item[$field] % 1, 0, "The property $field should be a float");
                        }
                    }
                }
            }
        }
    }
}