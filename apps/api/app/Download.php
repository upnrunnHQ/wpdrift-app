<?php
// Downloads(EDD Products)
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Download extends Eloquent
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id',
        'post_id',
        'post_author',
        'post_date',
        'post_content',
        'post_title',
        'post_status',
        'ping_status',
        'post_password',
        'post_name',
        'to_ping',
        'pinged',
        'post_modified',
        'post_content_filtered',
        'post_parent',
        'guid',
        'menu_order',
        'comment_count'
    ];
    
    /**
    * For adding download metas relation with downloads
    */
    public function downloadsmetas()
    {
        return $this->hasMany('App\DownloadMeta', ['store_id', 'post_id'] , ['store_id', 'post_id']);
    }
    /**
    * For adding download logs relation with downloads
    */
    public function downloadslogs()
    {
        return $this->hasMany('App\DownloadLog', ['store_id', 'post_id'] , ['store_id', 'download_id']);
    }
}
