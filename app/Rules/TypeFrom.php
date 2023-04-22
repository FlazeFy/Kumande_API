<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class TypeFrom implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function passes($attribute, $value)
    {
        $type = ['GoFood','GrabFood','ShopeeFood','Dine-In','Take Away'];

        foreach ($type as $format) {
            if ($format === $value) {
                return true;
            }
        }

        return false;
    }

    public function message()
    {
        return 'Consume from is not available';
    }
}