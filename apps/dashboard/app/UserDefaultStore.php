<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDefaultStore extends Model
{
    //
    public $timestamps = false;
    protected $table = "user_default_store";

    protected $fillable = [
        'user_id',
        'store_id'
    ];
}
