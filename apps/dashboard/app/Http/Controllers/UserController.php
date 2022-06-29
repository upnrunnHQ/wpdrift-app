<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\UserDefaultStore;
use App\Store;
use GuzzleHttp\Client;

class UserController extends Controller {
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware( 'auth' );
	}

	/**
	 * [users description]
	 * @return [type] [description]
	 */
	public function users( Request $request ) {
		$default_store = UserDefaultStore::where( 'user_id', Auth::user()->id )->first();

		/**
		 * Exit early.
		 * @var [type]
		 */
		if ( ! $default_store ) {
			return response()->json(
				[
					'code'    => 'default_store_not_set',
					'message' => 'You have to chose current store.',
					'data'    => [ 'status' => 401 ],
				],
				200
			);
		}

		/**
		 * Save store id, continue.
		 * @var [type]
		 */
		$store_id                    = $default_store->store_id;
		$store                       = Store::where( 'id', $store_id )->first();
		$companies_store_credentials = unserialize( $store->companies_store_credentials );
		$auth_token                  = $companies_store_credentials['access_token_info']['result']['access_token'];
		$store_url                   = $store->auth_server_url;
		$api_url                     = $store_url . '/wp-json/wpdriftio/v1/users?response=json';

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( isset( $request['after'] ) && ! empty( $request['after'] ) ) {
			$api_url .= '&after=' . $request['after'];
		}

		if ( isset( $request['before'] ) && ! empty( $request['before'] ) ) {
			$api_url .= '&before=' . $request['before'];
		}

		if ( isset( $request['mode'] ) && ! empty( $request['mode'] ) ) {
			$api_url .= '&mode=' . $request['mode'];
		}

		// Guzzle usage
		$gclient  = new Client();
		$response = $gclient->request(
			'GET',
			$api_url,
			[
				'headers' => [ 'Authorization' => 'Bearer ' . $auth_token ],
			]
		);

		$body        = $response->getBody()->getContents();
		$users       = trim( $body );
		$headers     = $response->getHeaders();
		$status_code = $response->getStatusCode();

		if ( '200' == $status_code ) {
			return response()->json(
				json_decode( $users ),
				200
			);
		} else {
			$error_msg = $this->get_error_message_from_code( $status_code );
			return response()->json(
				[
					'code'    => 'server_error',
					'message' => $error_msg,
					'data'    => [ 'status' => $get_status_code ],
				],
				200
			);
		}
	}
}
