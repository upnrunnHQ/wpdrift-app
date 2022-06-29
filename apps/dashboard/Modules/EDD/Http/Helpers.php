<?php
// Modules/EDD/Http/Helpers.php

namespace Modules\EDD\Http;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client;
use App\UserDefaultStore;
use App\Store;
use Illuminate\Support\Facades\Auth;

class Helpers {

	/**
	 * For getting guzzle response direct from lumen edd
	 * @param - api end point to lumen
	 * @param - $entity_id is for adding single page entity id it can be, customer id/order *
	 * id/product id
	 */
	public static function get_guzzle_response( $api_end_point, $entity_id = '', $other_params = '' ) {
		// Get default store.
		$default_store = UserDefaultStore::where( 'user_id', Auth::user()->id )->first();
		if ( ! $default_store ) {
			return [
				'code'    => 'default_store_not_set',
				'message' => 'You have to chose current store.',
			];
		}

		$edd_server_url = config( 'app.edd_server_url' );
		$edd_key        = config( 'app.edd_key' );

		$store_id                    = $default_store->store_id;
		$store_info                  = Store::where( 'id', $store_id )->first();
		$companies_store_credentials = unserialize( $store_info->companies_store_credentials );
		$access_token                = $companies_store_credentials['access_token_info']['result']['access_token'];
		$url                         = $edd_server_url . $api_end_point . $store_id;

		if ( $entity_id != '' ) {
			$url .= '/' . $entity_id;
		}

		if ( $other_params != '' ) {
			$url .= $other_params;
		}

		$gclient = new Client();
		try {
			// Guzzle usage
			$request_var     = $gclient->request(
				'GET',
				$url,
				[
					'headers' =>
						[
							'Authorization' => 'Bearer ' . $access_token,
						],
				]
			);
			$gresponse       = $request_var->getBody()->getContents();
			$total_customers = trim( $gresponse );
			return $total_customers;
		} catch ( \Exception $e ) {
			$error = $e->getMessage();
			return response()->json(
				[
					'code'    => 'some_error',
					'message' => $error,
					'data'    => [ 'status' => 500 ],
				],
				200
			);
		}
	}

	/**
	 * [remote_get description]
	 * @param  [type] $route [description]
	 * @param  [type] $query_params [description]
	 * @return [type]        [description]
	 */
	public static function remote_get( $route = '', $query_params = [] ) {
		$default_store = UserDefaultStore::where( 'user_id', Auth::user()->id )->first();
		if ( ! $default_store ) {
			return [
				'code'    => 'invalid_default_store',
				'message' => __( 'Plese set up a default store to get details.' ),
			];
		}

		$store                       = Store::where( 'id', $default_store->store_id )->first();
		$companies_store_credentials = unserialize( $store->companies_store_credentials );
		$bearer_token                = $companies_store_credentials['access_token_info']['result']['access_token'];

		$edd_server_url = config( 'app.edd_server_url' );
		$request_url    = $edd_server_url . '/' . $route . '/' . $store->id;

		try {
			$client   = new Client();
			$response = $client->request(
				'GET',
				$request_url,
				[
					'headers' => [ 'Authorization' => 'Bearer ' . $bearer_token ],
					'query'   => $query_params,
				]
			);

			$response_body = json_decode( $response->getBody()->getContents() );
			return [
				'success' => true,
				'data'    => (array) $response_body,
			];
		} catch ( ConnectException | RequestException | ClientException $e ) {
			return [
				'code'    => $e->getCode(),
				'message' => $e->getMessage(),
			];
		}
	}
}
