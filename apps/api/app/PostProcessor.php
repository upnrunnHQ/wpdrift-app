<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

use App\Site;
use App\SiteMeta;
use App\Download;
use App\DownloadMeta;
use App\Payment;
use App\PaymentMeta;
use App\Discount;
use App\DiscountMeta;

use App\Jobs\ProcessFetch;


/**
 * [PostProcessor description]
 */
class PostProcessor {

	protected $payload, $site_id, $site_url, $access_token, $post_type;

	/**
	 * [__construct description]
	 * @param [type] $payload [description]
	 */
	public function __construct( $payload ) {
		$this->payload = $payload;
		$this->site_id = $payload['site_id'];

		$site = Site::where( 'site_id', $payload['site_id'] )->first();
		if ( $site ) {
			$this->site_url     = $site->site_url;
			$this->access_token = $this->site_metadata( $payload['site_id'], 'access_token' );
			$this->post_type    = $payload['post_type'];
		}
	}

	/**
	 * [process description]
	 * @return [type] [description]
	 */
	public function process() {
		$all        = $this->fetch_all();
		$updated    = $this->fetch_updated();
		$removed    = $this->remove_deleted();
		$queue_list = $this->prepare_queue_list();
		$processed  = $this->process_queue_list();

		return [
			'payload'      => $this->payload,
			'site_id'      => $this->site_id,
			'site_url'     => $this->site_url,
			'access_token' => $this->access_token,
			'post_type'    => $this->post_type,
			'updated'      => $updated,
			'removed'      => $removed,
			'all'          => $all,
			'queue_list'   => $queue_list,
			'processed'    => $processed,
			'synced'       => $this->posts_synced(),
		];
	}

	/**
	 * [fetch_all description]
	 * @param  [type] $site [description]
	 * @return [type]       [description]
	 */
	public function fetch_all() {
		$args = [
			'route_end' => '/all',
			'query'     => [
				'post_type' => $this->post_type,
			],
		];

		$response = $this->send_request( $args );
		$this->add_site_metadata( $this->site_id, $this->post_type . '_all', $response );
		return $response;
	}

	/**
	 * [fetch_updated description]
	 * @return [type] [description]
	 */
	public function fetch_updated() {
		$args = [
			'route_end' => '/updated',
			'query'     => [
				'post_type' => $this->post_type,
			],
		];

		switch ( $this->post_type ) {
			case 'edd_payment':
				$last_updated = Payment::orderBy( 'post_modified', 'desc' )->first();
				break;

			case 'edd_discount':
				$last_updated = Discount::orderBy( 'post_modified', 'desc' )
				->first();
				break;

			default:
				$last_updated = Download::orderBy( 'post_modified', 'desc' )
				->first();
				break;
		}

		if ( $last_updated ) {
			$args['query']['after'] = $last_updated->post_modified;
		}

		$response = $this->send_request( $args );
		$this->add_site_metadata( $this->site_id, $this->post_type . '_updated', $response );
		return $response;
	}

	/**
	 * [remove_deleted description]
	 * @param  [type] $site [description]
	 * @return [type]       [description]
	 */
	public function remove_deleted() {
		$posts        = $this->site_metadata( $this->site_id, $this->post_type . '_all' );
		$posts_synced = $this->posts_synced();

		if ( empty( $posts ) || empty( $posts_synced ) ) {
			return [];
		}

		$deleted = [];
		foreach ( $posts_synced as $post_id ) {
			if ( ! in_array( $post_id, $posts ) ) {
				$deleted[] = $post_id;
				$this->delete( $post_id );
			}
		}

		return $deleted;
	}

	/**
	 * [posts_synced description]
	 * @return [type] [description]
	 */
	public function posts_synced() {
		switch ( $this->post_type ) {
			case 'edd_payment':
				$posts = Payment::where( 'store_id', $this->site_id )->pluck( 'ID' );
				break;
			case 'edd_discount':
				$posts = Discount::where( 'store_id', $this->site_id )
				->pluck( 'ID' );
				break;
			default:
				$posts = Download::where( 'store_id', $this->site_id )
				->pluck( 'post_id' );
				break;
		}

		return $posts->toArray();
	}

	/**
	 * [delete description]
	 * @param  [type] $post_id [description]
	 * @return [type]          [description]
	 */
	public function delete( $post_id ) {
		switch ( $this->post_type ) {
			case 'edd_payment':
				Payment::where( 'store_id', $this->site_id )->where( 'ID', (int) $post_id )->delete();
				PaymentMeta::where( 'store_id', $this->site_id )->where( 'ID', (int) $post_id )->delete();
				break;
			case 'edd_discount':
				Discount::where( 'store_id', $this->site_id )
				->where( 'ID', (int) $post_id )
				->delete();

				DiscountMeta::where( 'store_id', $this->site_id )
				->where( 'ID', (int) $post_id )
				->delete();
				break;
			default:
				Download::where( 'store_id', $this->site_id )
				->where( 'post_id', (int) $post_id )
				->delete();

				DownloadMeta::where( 'store_id', $this->site_id )
				->where( 'post_id', (int) $post_id )
				->delete();
				break;
		}
	}

	/**
	 * [prepare_queue_list description]
	 * @return [type] [description]
	 */
	public function prepare_queue_list() {
		$queue_list    = [];
		$posts         = $this->site_metadata( $this->site_id, $this->post_type . '_all' );
		$posts_updated = $this->site_metadata( $this->site_id, $this->post_type . '_updated' );
		$posts_queued  = $this->site_metadata( $this->site_id, $this->post_type . '_queued' );
		$posts_synced  = $this->posts_synced();

		if ( empty( $posts_queued ) && ! empty( $posts_synced ) && ( count( $posts ) > count( $posts_synced ) ) ) {
			foreach ( $posts as $post_id ) {
				if ( ! in_array( $post_id, $posts_synced ) ) {
					$queue_list[] = $post_id;
				}
			}
		}

		if ( empty( $posts_synced ) && ! empty( $posts ) ) {
			$queue_list = array_merge( $queue_list, $posts );
		}

		if ( ! empty( $posts_updated ) ) {
			$queue_list = array_merge( $queue_list, $posts_updated );
		}

		$queue_list = array_unique( $queue_list );
		$this->add_site_metadata( $this->site_id, $this->post_type . '_queue_list', $queue_list );
		return  $queue_list;
	}

	/**
	 * [process_queue_list description]
	 * @return [type] [description]
	 */
	public function process_queue_list() {
		$payload = [
			'site_id'      => $this->site_id,
			'fetch_type'   => $this->post_type,
			'post_type'    => $this->post_type,
			'access_token' => $this->access_token,
			'query'        => [],
		];

		$queue_list = $this->site_metadata( $this->site_id, $this->post_type . '_queue_list' );
		if ( empty( $queue_list ) ) {
			return [];
		}

		$queued       = [];
		$posts_queued = $this->site_metadata( $this->site_id, $this->post_type . '_queued' );
		if ( ! empty( $posts_queued ) ) {
			$queued = $posts_queued;
		}

		foreach ( $queue_list as $post_id ) {
			if ( ! in_array( $post_id, $queued ) ) {
				$payload['post_id']     = $post_id;
				$payload['request_url'] = $this->request_url( '/' . $post_id );
				dispatch( new ProcessFetch( $payload ) );
				$queued[] = $post_id;
			}
		}

		$this->add_site_metadata( $this->site_id, $this->post_type . '_queued', $queued );
		return $queued;
	}

	/**
	 * [add_site_metadata description]
	 * @param [type] $site_id    [description]
	 * @param [type] $meta_key   [description]
	 * @param [type] $meta_value [description]
	 */
	public function add_site_metadata( $site_id, $meta_key, $meta_value ) {
		if ( ! $meta_key || ! is_numeric( $site_id ) ) {
			return false;
		}

		$match = [
			'site_id'  => $site_id,
			'meta_key' => $meta_key,
		];

		$update = [
			'meta_value' => $meta_value,
		];

		return SiteMeta::updateOrCreate( $match, $update );
	}

	/**
	 * [site_metadata description]
	 * @param  [type] $site_id  [description]
	 * @param  [type] $meta_key [description]
	 * @return [type]           [description]
	 */
	public function site_metadata( $site_id, $meta_key ) {
		$meta = SiteMeta::where( 'site_id', $site_id )
		->where( 'meta_key', $meta_key )
		->first();

		if ( $meta ) {
			return $meta->meta_value;
		}

		return '';
	}

	/**
	 * [send_request description]
	 * @param  [type] $args [description]
	 * @return [type]       [description]
	 */
	public function send_request( $args ) {
		try {
			$client   = new Client();
			$response = $client->request(
				$this->request_method(),
				$this->request_url( $args['route_end'] ),
				$this->request_arguments( $args['query'] )
			);
			return json_decode( $response->getBody()->getContents() );
		} catch ( RequestException | ClientException $e ) {
			if ( $e->hasResponse() ) {
				return [];
			}
		}
	}

	/**
	 * [request_method description]
	 * @return [type] [description]
	 */
	public function request_method() {
		return 'GET';
	}

	/**
	 * [request_url description]
	 * @param  [type] $route_end [description]
	 * @return [type]            [description]
	 */
	public function request_url( $route_end = '' ) {
		return $this->site_url . '/wp-json/wpdriftio/v1/posts' . $route_end;
	}

	/**
	 * [request_arguments description]
	 * @param  [type] $query [description]
	 * @return [type]        [description]
	 */
	public function request_arguments( $query ) {
		return [
			'headers' => [ 'Authorization' => 'Bearer ' . $this->access_token ],
			'query'   => $query,
		];
	}
}
