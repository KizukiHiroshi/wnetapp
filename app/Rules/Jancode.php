<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Jancode implements Rule
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

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (preg_match("/^[0-9]+$/", $value) == false) { return false; }
        if (strlen(trim($value)) > 13) { 
            return false; 
        } else {
            $value = substr('0000000000000'.trim($value), -13);
        }
        $checkdigit = (10 - ((intval(Substr($value, 2, 1)) + intval(Substr($value, 4, 1)) + 
                intval(Substr($value, 6, 1)) + intval(Substr($value, 8, 1)) + 
                intval(Substr($value, 10, 1)) + intval(Substr($value, 12, 1))) * 3 + 
                intval(Substr($value, 1, 1)) + intval(Substr($value, 3, 1)) + 
                intval(Substr($value, 5, 1)) + intval(Substr($value, 7, 1)) + 
                intval(Substr($value, 9, 1)) + intval(Substr($value, 11, 1))) % 10) % 10;    
        if (intval(substr($value, 13, 1)) <> $checkdigit) { return false; }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'JANコードが不正です。';
    }
}
