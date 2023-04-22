<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class TypeConsume implements Rule
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
        $type = ['Food', 'Drink', 'Snack'];

        foreach ($type as $format) {
            if ($format === $value) {
                return true;
            }
        }

        return false;
    }

    public function message()
    {
        return 'Consume type is not available';
    }
}