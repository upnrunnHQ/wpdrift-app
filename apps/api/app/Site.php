<?php
// Edd Store
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Site extends Eloquent {

	protected $table = 'sites';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'site_id',
		'site_name',
		'site_description',
		'site_url',
		'site_logo',
		'site_status',
		'site_last_synced',
	];
}
