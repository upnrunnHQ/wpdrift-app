<?php
// Edd Logs(EDD)
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class EddLog extends Eloquent
{
    protected $table = "eddlogs";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id',
        'ID',
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
        'comment_count',
    ];
    /**
    * For adding eddlogs metas relation with eddlog
    */
    public function eddlogmetas()
    {
        return $this->hasMany('App\EddLogMeta', ['store_id', 'ID'] , ['store_id', 'post_id']);
    }
}
