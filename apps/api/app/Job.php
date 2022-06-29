<?php
// Edd Store
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Job extends Eloquent {

	protected $table = 'jobs';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [];
}
