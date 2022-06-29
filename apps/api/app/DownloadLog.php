<?php
// Download Logs(EDD)
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class DownloadLog extends Eloquent
{
    protected $table = "downloads_logs";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'store_id',
        'ID',
        'type',
        'user_id',
        'user_ip',
        'user_agent',
        'download_id',
        'version_id',
        'version',
        'download_date',
        'download_status',
        'download_status_message',
    ];
    /**
     * Get downloads that have the logs.
     */
    public function downloads()
    {
        return $this->belongsTo('App\Download');
    }
}
