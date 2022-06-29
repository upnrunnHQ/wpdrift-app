<?php
// Edd Users
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class EddUser extends Eloquent
{
    protected $table = "eddusers";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id',
        'ID',
        'user_login',
        'user_pass',
        'user_nicename',
        'user_email',
        'user_url',
        'user_registered',
        'user_activation_key',
        'user_status',
        'display_name'
    ];
    /**
    * For adding customers metas relation with customers
    */
    public function eddusersmetas()
    {
        return $this->hasMany('App\EddUserMeta', ['store_id', 'ID'] , ['store_id', 'user_id']);
    }
}
