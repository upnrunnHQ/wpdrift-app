<?php
// app/Rules/ValidDuplicateStoreURL.php
/**
* Validate the Store URL
**/
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Store;

class ValidDuplicateStoreURL implements Rule
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
            $explode_url = explode("//", $value);
            $url = rtrim($explode_url[1],"/");
            $store = Store::Where('auth_server_url', 'LIKE', '%'.$url )->first();
            if($store) {
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
        return 'The URL has already been taken.';
    }
}
