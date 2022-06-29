<?php

namespace App\Fetch;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

use Illuminate\Support\Facades\Log;

use App\Site;
use App\SiteMeta;
use App\Download;
use App\DownloadMeta;
use App\Payment;
use App\PaymentMeta;
use App\Discount;
use App\DiscountMeta;
use App\EddLog;
use App\EddLogMeta;
use App\Customer;
use App\CustomerMeta;

use App\Jobs\ProcessFetch;

class Fetch {

	protected $payload, $fetched;

	public function __construct( $payload ) {
		$this->payload = $payload;
	}

	/**
	 * [process description]
	 * @return [type] [description]
	 */
	public function process() {
		$fetched = $this->fetched();
		$saved   = $this->save();

		return [
			'featched' => $fetched,
			'saved'    => $saved,
			'payload'  => $this->payload,
		];
	}

	/**
	 * [fetch description]
	 * @return [type] [description]
	 */
	public function fetched() {
		$response      = $this->send_request();
		$this->fetched = json_decode( json_encode( $response ), true );
		return $this->fetched;
	}

	/**
	 * [save description]
	 * @param  [type] $response [description]
	 * @return [type]           [description]
	 */
	public function save() {
		switch ( $this->payload['fetch_type'] ) {
			case 'download':
			case 'edd_payment':
			case 'edd_discount':
			case 'edd_log':
				$this->save_post();
				$this->fetch_metadata();
				$this->remove_queued();
				break;
			case 'metadata':
				return $this->save_metadata();
				break;
			case 'edd_customer':
				$this->save_customer();
				$this->fetch_metadata();
				$this->remove_queued();
		}

		return [];
	}

	/**
	 * [save_download description]
	 * @return [type] [description]
	 */
	public function save_post() {
		$match = [
			'store_id' => $this->payload['site_id'],
		];

		switch ( $this->payload['fetch_type'] ) {
			case 'edd_payment':
				$match['ID'] = $this->fetched['ID'];
				Payment::updateOrCreate( $match, $this->fetched );
				break;

			case 'edd_discount':
				$match['ID'] = $this->fetched['ID'];
				Discount::updateOrCreate( $match, $this->fetched );
				break;

			case 'edd_log':
				$match['ID'] = $this->fetched['ID'];
				EddLog::updateOrCreate( $match, $this->fetched );
				break;

			default:
				$match['post_id'] = $this->fetched['ID'];
				Download::updateOrCreate( $match, $this->fetched );
				break;
		}
	}

	/**
	 * [save_customer description]
	 * @return [type] [description]
	 */
	public function save_customer() {
		$match = [
			'store_id' => $this->payload['site_id'],
			'id'       => $this->fetched['id'],
		];
		Customer::updateOrCreate( $match, $this->fetched );
	}

	/**
	 * [save_metadata description]
	 * @return [type] [description]
	 */
	public function fetch_metadata() {
		$payload = [
			'fetch_type'   => 'metadata',
			'site_id'      => $this->payload['site_id'],
			'request_url'  => $this->request_url() . '/metadata',
			'access_token' => $this->payload['access_token'],
			'query'        => [],
		];

		if ( isset( $this->payload['data_type'] ) && 'edd_customer' == $this->payload['data_type'] ) {
			$payload['customer_id'] = $this->fetched['id'];
			$payload['data_type']   = $this->payload['data_type'];
		} else {
			$payload['post_id']   = $this->payload['post_id'];
			$payload['post_type'] = $this->payload['post_type'];
		}

		dispatch( new ProcessFetch( $payload ) );
	}

	/**
	 * [save_metadata description]
	 * @return [type] [description]
	 */
	public function save_metadata() {
		$updates = [];
		if ( isset( $this->payload['data_type'] ) && 'edd_customer' == $this->payload['data_type'] ) {
			foreach ( $this->fetched as $metadata ) {
				$match = [
					'store_id' => $this->payload['site_id'],
					'meta_id'  => $metadata['meta_key'],
				];

				$update = [
					'customer_id' => $metadata['customer_id'],
					'meta_key'    => $metadata['meta_key'],
					'meta_value'  => $metadata['meta_value'],
				];

				CustomerMeta::updateOrCreate( $match, $update );
			}
		} else {
			foreach ( $this->fetched as $meta_key => $meta_value ) {
				$match = [
					'store_id' => $this->payload['site_id'],
					'post_id'  => $this->payload['post_id'],
					'meta_key' => $meta_key,
				];

				$update = [
					'meta_value' => $meta_value[0],
				];

				switch ( $this->payload['post_type'] ) {
					case 'edd_payment':
						PaymentMeta::updateOrCreate( $match, $update );
						break;
					case 'edd_discount':
						DiscountMeta::updateOrCreate( $match, $update );
						break;
					case 'edd_log':
						EddLogMeta::updateOrCreate( $match, $update );
						break;
					default:
						DownloadMeta::updateOrCreate( $match, $update );
						break;
				}
			}
		}
	}

	/**
	 * [remove_queued description]
	 * @return [type] [description]
	 */
	public function remove_queued() {
		if ( isset( $this->payload['data_type'] ) && 'edd_customer' == $this->payload['data_type'] ) {
			$data_type = $this->payload['data_type'];
			$data_id   = $this->payload['data_id'];
		} else {
			$data_type = $this->payload['post_type'];
			$data_id   = $this->payload['post_id'];
		}

		$data_queued = $this->get_site_meta( $this->payload['site_id'], $data_type . '_queued' );
		if ( ! empty( $data_queued ) && in_array( $data_id, $data_queued ) ) {
			$queued = [];
			foreach ( $data_queued as $data_queued_id ) {
				if ( $data_queued_id != $data_id ) {
					$queued[] = $data_queued_id;
				}
			}

			$this->add_site_meta( $this->payload['site_id'], $data_type . '_queued', $queued );
		}
	}

	/**
	 * [add_site_meta description]
	 * @param [type] $site_id    [description]
	 * @param [type] $meta_key   [description]
	 * @param [type] $meta_value [description]
	 */
	public function add_site_meta( $site_id, $meta_key, $meta_value ) {
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
	 * [get_site_meta description]
	 * @param  [type] $site_id  [description]
	 * @param  [type] $meta_key [description]
	 * @return [type]           [description]
	 */
	public function get_site_meta( $site_id, $meta_key ) {
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
	public function send_request() {
		try {
			$client   = new Client();
			$response = $client->request(
				$this->request_method(),
				$this->request_url(),
				$this->request_arguments()
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
	public function request_url() {
		return $this->payload['request_url'];
	}

	/**
	 * [request_arguments description]
	 * @param  [type] $query [description]
	 * @return [type]        [description]
	 */
	public function request_arguments() {
		return [
			'headers' => [ 'Authorization' => 'Bearer ' . $this->payload['access_token'] ],
			'query'   => $this->payload['query'],
		];
	}
}
