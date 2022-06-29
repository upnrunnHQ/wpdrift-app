<?php
// app/Rules/ValidStoreURL.php
/**
* Validate the Store URL
**/
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidStoreURL implements Rule
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
        if (!empty($value)) {
            $is_url = filter_var($value, FILTER_VALIDATE_URL) !== false;
            if (!$is_url) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Not valid URL.';
    }
}
