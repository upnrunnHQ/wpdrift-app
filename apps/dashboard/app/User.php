<?php

namespace App;

use Soved\Laravel\Gdpr\Portable;
use Illuminate\Notifications\Notifiable;
use Laravel\Spark\User as SparkUser;
use App\Events\UserSignUp;

class User extends SparkUser
{
    use Portable, Notifiable;

    /**
    * The attributes that should be visible in the downloadable data.
    *
    * @var array
    */
    protected $gdprVisible = ['name', 'email'];

    /**
    * The attributes that should be hidden for the downloadable data.
    *
    * @var array
    */
    protected $gdprHidden = ['password'];

    /**
     * Get the GDPR compliant data portability array for the model.
     *
     * @return array
     */
    public function toPortableArray()
    {
        $array = $this->toArray();

        // Customize array...

        return $array;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
    ];

    protected $dispatchesEvents = [
      'created' => UserSignUp::class
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'authy_id',
        'country_code',
        'phone',
        'two_factor_reset_code',
        'card_brand',
        'card_last_four',
        'card_country',
        'billing_address',
        'billing_address_line_2',
        'billing_city',
        'billing_zip',
        'billing_country',
        'extra_billing_information'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'uses_two_factor_auth' => 'boolean',
    ];

    public function getId()
    {
        return $this->id;
    }

    /**
    * For adding user relation with store
    */
    public function stores()
    {
        return $this->hasMany('App\Store', 'id', 'user_id');
    }

    /**
    * User Has Only One company for now
    */
    public function companies()
    {
        return $this->hasMany('\App\Company', 'id', 'user_id');
    }
}
