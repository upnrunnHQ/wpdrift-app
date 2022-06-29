<?php
namespace App\Utilities;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class Reader {
	protected $request_url, $request_method, $request_arguments;

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
