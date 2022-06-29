<?php
// Edd Site Jobs Track
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class EddSiteJobsTrack extends Eloquent
{
    protected $table = "edd_site_jobs_track";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'site_id',
        'job_id',
        'queue_type'
    ];
}
