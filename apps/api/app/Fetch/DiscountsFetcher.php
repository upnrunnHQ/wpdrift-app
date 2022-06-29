<?php
namespace App\Fetch;

use App\Discount;
use App\DiscountMeta;

class DiscountsFetcher extends Fetcher {
	/**
	 * [protected description]
	 * @var [type]
	 */
	protected $post_type = 'edd_discount';

	/**
	 * [__construct description]
	 * @param integer $site_id [description]
	 */
	public function __construct( $site_id = 0 ) {
		parent::__construct( $site_id );
	}

	public function last_fetched() {
		$last_fetched = Discount::orderBy( 'post_modified', 'desc' )->first();
		if ( ! $last_fetched ) {
			return false;
		}

		return $last_fetched->post_modified;
	}

	/**
	 * [posts_synced description]
	 * @return [type] [description]
	 */
	public function posts_synced() {
		return Discount::where( 'store_id', $this->site_id )
		->pluck( 'ID' )
		->toArray();
	}

	public function delete( $post_id ) {
		$post_id = (int) $post_id;

		Discount::where( 'store_id', $this->site_id )->where( 'ID', (int) $post_id )->delete();
		DiscountMeta::where( 'store_id', $this->site_id )
		->where( 'ID', (int) $post_id )
		->delete();
	}
}
