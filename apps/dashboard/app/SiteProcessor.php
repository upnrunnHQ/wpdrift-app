<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use App\Site;

class SiteProcessor {

	protected $payload, $access_token;

	function __construct( $payload ) {
		$this->payload      = $payload;
		$this->access_token = $payload['access_token'];
	}

	/**
	 * [process description]
	 * @param  Site   $site [description]
	 * @return [type]       [description]
	 */
	public function add_site() {
		$query            = $this->site_data( $this->payload['site_id'] );
		$query['edd_key'] = config( 'app.edd_key' );

		$this->send_request(
			[
				'end'   => '/add_site',
				'query' => $query,
			]
		);
	}

	/**
	 * [delete_site description]
	 * @return [type] [description]
	 */
	public function delete_site() {
		$query = [
			'site_id' => $this->payload['site_id'],
			'edd_key' => config( 'app.edd_key' ),
		];

		return $this->send_request(
			[
				'end'   => '/delete_site',
				'query' => $query,
			]
		);
	}

	/**
	 * [site_data description]
	 * @param  [type] $site_id [description]
	 * @return [type]          [description]
	 */
	public function site_data( $site_id ) {
		$site        = Store::where( 'id', $site_id )->first();
		$credentials = unserialize( $site->companies_store_credentials );
		return [
			'site_id'          => $site->id,
			'site_name'        => $site->name,
			'site_description' => $site->description,
			'site_logo'        => $site->photo_url,
			'site_url'         => $site->auth_server_url,
			'credentials'      => $credentials,
			'idToken'          => $credentials['access_token_info']['result']['access_token'],
		];
	}

	/**
	 * [send_request description]
	 * @param  [type] $args [description]
	 * @return [type]       [description]
	 */
	public function send_request( $args = [] ) {
		$defaults = [
			'end'   => '',
			'query' => [],
		];

		$args = array_merge( $defaults, $args );

		try {
			$client   = new Client();
			$response = $client->request(
				$this->request_method(),
				$this->request_url( $args['end'] ),
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
		return 'POST';
	}

	/**
	 * [request_url description]
	 * @param  [type] $route_end [description]
	 * @return [type]            [description]
	 */
	public function request_url( $end = '' ) {
		return config( 'app.edd_server_url' ) . $end;
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
