<?php
// Edd Site Total Jobs
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class EddSiteTotalJobs extends Eloquent {

	protected $table = 'edd_site_total_jobs';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'site_id',
		'total_jobs',
	];
}
