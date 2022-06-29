<?php
// Edd Store
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class EddStore extends Eloquent {

	protected $table = 'eddstores';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'store_id',
		'store_url',
		'store_access_token',
		'database_sync',
	];
}
