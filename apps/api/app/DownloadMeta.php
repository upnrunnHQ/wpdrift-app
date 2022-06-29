<?php
// Downloads Metas(EDD Products Metas)
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class DownloadMeta extends Eloquent
{
    protected $table = 'downloads_metas';
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
     * Get downloads that have the metas.
     */
    public function downloads()
    {
        return $this->belongsTo('App\Download');
    }
}
