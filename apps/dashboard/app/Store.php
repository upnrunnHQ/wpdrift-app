<?php

namespace App;

//use App\Events\StoreCreated;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    //
    protected $fillable = [
      'name',
      'description',
      'company_id',
      'user_id',
      'auth_client_id',
      'auth_client_secret',
      'auth_server_url',
      'auth_client_callback_url',
      'companies_store_credentials'
    ];

    // protected $dispatchesEvents = [
    //   'updated' => StoreCreated::class
    // ];

    /**
    * For adding store relation with company
    */
    public function companies()
    {
        return $this->belongsTo('App\Company');
    }
}
