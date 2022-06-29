<?php
namespace App\Fetch;

use App\Download;
use App\DownloadMeta;

class ProductsFetcher extends Fetcher {

	/**
	 * [protected description]
	 * @var [type]
	 */
	protected $post_type = 'download';

	/**
	 * [__construct description]
	 * @param integer $site_id [description]
	 */
	public function __construct( $site_id = 0 ) {
		parent::__construct( $site_id );
	}

	/**
	 * [last_fetched description]
	 * @return [type] [description]
	 */
	public function last_fetched() {
		$last_fetched = Download::where( 'store_id', $this->site_id )
		->orderBy( 'post_modified', 'desc' )
		->first();

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
		return Download::where( 'store_id', $this->site_id )
		->pluck( 'post_id' )
		->toArray();
	}

	/**
	 * [delete description]
	 * @param  [type] $post_id [description]
	 * @return [type]          [description]
	 */
	public function delete( $post_id ) {
		$post_id = (int) $post_id;

		Download::where( 'store_id', $this->site_id )->where( 'post_id', $post_id )->delete();
		DownloadMeta::where( 'store_id', $this->site_id )
		->where( 'post_id', $post_id )
		->delete();
	}
}
