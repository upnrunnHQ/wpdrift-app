<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    //
    protected $fillable = [
      'name',
      'description',
      'user_id'
    ];

    /**
    * For adding company relation with user
    */
    public function users()
    {
        return $this->belongsToMany('App\User');
    }

    /**
    * For adding company relation with store
    */
    public function stores()
    {
        return $this->hasMany('App\Store', 'id', 'company_id');
    }
}
