<?php
// Customers Metas
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class CustomerMeta extends Eloquent {

	protected $table = 'customers_metas';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'store_id',
		'meta_id',
		'customer_id',
		'meta_key',
		'meta_value',
	];
	/**
	 * Get customers that have the metas.
	 */
	public function customers() {
		return $this->belongsTo( 'App\Customer' );
	}
}
