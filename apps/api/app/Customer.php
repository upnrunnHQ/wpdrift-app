<?php
// Customers
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Customer extends Eloquent {

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'store_id',
		'id',
		'user_id',
		'email',
		'name',
		'purchase_value',
		'purchase_count',
		'payment_ids',
		'notes',
		'date_created',
	];
	/**
	* For adding customers metas relation with customers
	*/
	public function customersmetas() {
		return $this->hasMany( 'App\CustomerMeta', [ 'store_id', 'id' ], [ 'store_id', 'customer_id' ] );
	}
}
