<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class TypePayment implements Rule
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
        $type = ['GoPay','Ovo','Dana','Link Aja','MBanking','Cash','Gift','Cuppon','Free'];

        foreach ($type as $format) {
            if ($format === $value) {
                return true;
            }
        }

        return false;
    }

    public function message()
    {
        return 'Payment type is not available';
    }
}