<?php
// Edd Log Metas(EDD)
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class EddLogMeta extends Eloquent
{
    protected $table = "eddlogs_metas";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id',
        'post_id',
        'meta_key',
        'meta_value'
    ];
    /**
     * Get eddlog that have the meta.
     */
    public function eddlogs()
    {
        return $this->belongsTo('App\EddLog');
    }
}
