<?php
// Edd Store
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class SiteMeta extends Eloquent {

	protected $table = 'site_metas';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'site_id',
		'meta_key',
		'meta_value',
	];
}
