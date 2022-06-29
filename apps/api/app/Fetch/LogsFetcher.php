<?php
namespace App\Fetch;

use App\EddLog;
use App\EddLogMeta;

class LogsFetcher extends Fetcher {

	/**
	 * [protected description]
	 * @var [type]
	 */
	protected $post_type = 'edd_log';

	/**
	 * [__construct description]
	 * @param integer $site_id [description]
	 */
	public function __construct( $site_id = 0 ) {
		parent::__construct( $site_id );
	}

	public function last_fetched() {
		$last_fetched = EddLog::orderBy( 'post_modified', 'desc' )->first();
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
		return EddLog::where( 'store_id', $this->site_id )
		->pluck( 'ID' )
		->toArray();
	}

	public function delete( $post_id ) {
		$post_id = (int) $post_id;

		EddLog::where( 'store_id', $this->site_id )->where( 'ID', $post_id )->delete();
		EddLogMeta::where( 'store_id', $this->site_id )
		->where( 'ID', $post_id )
		->delete();
	}
}
