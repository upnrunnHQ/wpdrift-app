<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

use App\Customer;
use App\CustomerMeta;
use App\EddUser;
use App\EddUserMeta;
use App\Jobs\ProcessFetch;


/**
 * [PostProcessor description]
 */
class CustomerProcessor {

	protected $payload, $site_id, $site_url, $access_token, $data_type;

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
			$this->data_type    = $payload['data_type'];
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
			'data_type'    => $this->data_type,
			'all'          => $all,
			'updated'      => $updated,
			'removed'      => $removed,
			'queue_list'   => $queue_list,
			'processed'    => $processed,
			'synced'       => $this->data_synced(),
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
			'query'     => [],
		];

		$response = $this->send_request( $args );
		$this->add_site_metadata( $this->site_id, $this->data_type . '_all', $response );
		return $response;
	}

	/**
	 * [fetch_updated description]
	 * @return [type] [description]
	 */
	public function fetch_updated() {
		$args = [
			'route_end' => '/updated',
			'query'     => [],
		];

		$response = $this->send_request( $args );
		$this->add_site_metadata( $this->site_id, $this->data_type . '_updated', $response );
		return $response;
	}

	/**
	 * [remove_deleted description]
	 * @param  [type] $site [description]
	 * @return [type]       [description]
	 */
	public function remove_deleted() {
		$data_all    = $this->site_metadata( $this->site_id, $this->data_type . '_all' );
		$data_synced = $this->data_synced();

		if ( empty( $data_all ) || empty( $data_synced ) ) {
			return [];
		}

		$deleted = [];
		foreach ( $data_synced as $data_id ) {
			if ( ! in_array( $data_id, $data_all ) ) {
				$deleted[] = $data_id;
				$this->delete( $data_id );
			}
		}

		return $deleted;
	}

	/**
	 * [data_synced description]
	 * @return [type] [description]
	 */
	public function data_synced() {
		return Customer::where( 'store_id', $this->site_id )->pluck( 'id' )->toArray();
	}

	/**
	 * [delete description]
	 * @param  [type] $data_id [description]
	 * @return [type]          [description]
	 */
	public function delete( $data_id ) {
		Customer::where( 'store_id', $this->site_id )->where( 'id', (int) $data_id )->delete();

		CustomerMeta::where( 'store_id', $this->site_id )
		->where( 'customer_id', (int) $data_id )
		->delete();
	}

	/**
	 * [prepare_queue_list description]
	 * @return [type] [description]
	 */
	public function prepare_queue_list() {
		$queue_list   = [];
		$data_all     = $this->site_metadata( $this->site_id, $this->data_type . '_all' );
		$data_updated = $this->site_metadata( $this->site_id, $this->data_type . '_updated' );
		$data_queued  = $this->site_metadata( $this->site_id, $this->data_type . '_queued' );
		$data_synced  = $this->data_synced();

		if ( empty( $data_queued ) && ! empty( $data_synced ) && ( count( $data_all ) > count( $data_synced ) ) ) {
			foreach ( $data_all as $data_id ) {
				if ( ! in_array( $data_id, $data_synced ) ) {
					$queue_list[] = $data_id;
				}
			}
		}

		if ( empty( $data_synced ) && ! empty( $data_all ) ) {
			$queue_list = array_merge( $queue_list, $data_all );
		}

		if ( ! empty( $data_updated ) ) {
			$queue_list = array_merge( $queue_list, $data_updated );
		}

		$queue_list = array_unique( $queue_list );
		$this->add_site_metadata( $this->site_id, $this->data_type . '_queue_list', $queue_list );
		return  $queue_list;
	}

	/**
	 * [process_queue_list description]
	 * @return [type] [description]
	 */
	public function process_queue_list() {
		$payload = [
			'site_id'      => $this->site_id,
			'fetch_type'   => $this->data_type,
			'data_type'    => $this->data_type,
			'access_token' => $this->access_token,
			'query'        => [],
		];

		$queue_list = $this->site_metadata( $this->site_id, $this->data_type . '_queue_list' );
		if ( empty( $queue_list ) ) {
			return [];
		}

		$queued      = [];
		$data_queued = $this->site_metadata( $this->site_id, $this->data_type . '_queued' );
		if ( ! empty( $data_queued ) ) {
			$queued = $data_queued;
		}

		foreach ( $queue_list as $data_id ) {
			if ( ! in_array( $data_id, $queued ) ) {
				$payload['data_id']     = $data_id;
				$payload['request_url'] = $this->request_url( '/' . $data_id );
				dispatch( new ProcessFetch( $payload ) );
				$queued[] = $data_id;
			}
		}

		// $queued = [];
		$this->add_site_metadata( $this->site_id, $this->data_type . '_queued', $queued );
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
		return $this->site_url . '/wp-json/wpdriftio/v1/customers' . $route_end;
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
