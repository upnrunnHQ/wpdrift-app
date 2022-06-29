<?php
// Edd Users Metas
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class EddUserMeta extends Eloquent
{
    protected $table = "eddusers_metas";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id',
        'umeta_id',
        'user_id',
        'meta_key',
        'meta_value'
    ];
    /**
     * Get edd users that have the metas.
     */
    public function eddusers()
    {
        return $this->belongsTo('App\EddUser');
    }
}
