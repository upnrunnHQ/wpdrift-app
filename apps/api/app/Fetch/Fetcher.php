<?php
namespace App\Fetch;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use App\Site;
use App\SiteMeta;
use App\Jobs\ProcessFetch;

abstract class Fetcher {

	/**
	 * [protected description]
	 * @var [type]
	 */
	protected $site_id, $site_url, $access_token;

	/**
	 * [__construct description]
	 * @param [type] $site_id [description]
	 */
	public function __construct( $site_id ) {
		$this->site_id = $site_id;
		$site          = Site::where( 'site_id', $site_id )->first();
		if ( $site ) {
			$this->site_url     = $site->site_url;
			$this->access_token = $this->site_metadata( $site_id, 'access_token' );
		}
	}

	/**
	 * [fetch description]
	 * @return [type] [description]
	 */
	final public function fetch() {
		// $this->fetch_all();
		// $this->fetch_updated();
		// $this->remove_deleted();
		// $this->prepare_queue_list();
		// $this->process_queue_list();

		return [
			'site_id'      => $this->site_id,
			'site_url'     => $this->site_url,
			'access_token' => $this->access_token,
			'post_type'    => $this->post_type,
			'updated'      => $this->fetch_updated(),
			'removed'      => $this->remove_deleted(),
			'all'          => $this->fetch_all(),
			'queue_list'   => $this->prepare_queue_list(),
			'processed'    => $this->process_queue_list(),
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

		$response = $this->request( $args );
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

		$last_fetched = $this->last_fetched();
		if ( ! empty( $last_fetched ) ) {
			$args['query']['after'] = $last_fetched;
		}

		$response = $this->request( $args );
		$this->add_site_metadata( $this->site_id, $this->post_type . '_updated', $response );
		return $response;
	}

	/**
	 * [remove_deleted description]
	 * @param  [type] $site [description]
	 * @return [type]       [description]
	 */
	public function remove_deleted() {
		$posts        = $this->posts_all();
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
	 * [prepare_queue_list description]
	 * @return [type] [description]
	 */
	public function prepare_queue_list() {
		$queue_list    = [];
		$posts         = $this->posts_all();
		$posts_updated = $this->posts_updated();
		$posts_queued  = $this->posts_queued();
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

		$queue_list = $this->posts_queue_list();
		if ( empty( $queue_list ) ) {
			return [];
		}

		$queued       = [];
		$posts_queued = $this->posts_queued();
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
	 * [posts_all description]
	 * @return [type] [description]
	 */
	public function posts_all() {
		return $this->site_metadata( $this->site_id, $this->post_type . '_all' );
	}

	/**
	 * [posts_updated description]
	 * @return [type] [description]
	 */
	public function posts_updated() {
		return $this->site_metadata( $this->site_id, $this->post_type . '_updated' );
	}

	/**
	 * [posts_queued description]
	 * @return [type] [description]
	 */
	public function posts_queued() {
		return $this->site_metadata( $this->site_id, $this->post_type . '_queued' );
	}

	/**
	 * [posts_queue_list description]
	 * @return [type] [description]
	 */
	public function posts_queue_list() {
		return $this->site_metadata( $this->site_id, $this->post_type . '_queue_list' );
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

		$match  = [
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
	 * [request description]
	 * @param  [type] $args [description]
	 * @return [type]       [description]
	 */
	public function request( $args ) {
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
