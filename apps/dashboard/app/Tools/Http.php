<?php
namespace App\Tools;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class Http {
	/**
	 * [get description]
	 * @return [type] [description]
	 */
	public function request( $args ) {
		// Guzzle usage
		$client = new Client( [ 'base_uri' => $args['base_uri'] ] );

		/**
		 * [$query description]
		 * @var array
		 */
		$query = [];
		if ( isset( $args['query'] ) ) {
			$query = $args['query'];
		}

		/**
		 * [try description]
		 * @var [type]
		 */
		try {
			/**
			 * [$response description]
			 * @var [type]
			 */
			$response = $client->request(
				$args['method'],
				$args['route'],
				[
					'query' => $query,
				]
			);

			/**
			 * [return description]
			 * @var [type]
			 */
			return [
				'code'    => 'normal_response',
				'message' => __( 'All good.' ),
				'data'    => json_decode( $response->getBody()->getContents() ),
			];

		} catch ( RequestException | ClientException $e ) {
			/**
			 * [if description]
			 * @var [type]
			 */
			if ( $e->hasResponse() ) {
				return [
					'code'    => 'exception_response',
					'message' => __( 'Exception.' ),
					'data'    => json_decode( $e->getResponse()->getBody()->getContents() ),
				];
			}
		}

		/**
		 * [return description]
		 * @var [type]
		 */
		return [
			'code'    => 'no_response',
			'message' => __( 'We did not get any response form your site.' ),
			'data'    => [ 'status' => 404 ],
		];
	}
}
