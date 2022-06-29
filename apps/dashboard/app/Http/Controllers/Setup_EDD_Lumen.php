<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\UserDefaultStore;
use App\Store;
use App\Site;
use App\User;
use App\Jobs\ProcessEddSetup; // for setting up the queue
use App\Events\EddSetup;

class Setup_EDD_Lumen extends Controller {

	public function __construct() {
		$this->middleware(
			'auth',
			[
				'except' => [
					'edd_setup',
				],
			]
		);
	}
	/**
	 * Check the status of EDD on Lumen
	 * So that user can see the exact status of edd.
	 * return response or error
	 */
	public function check_edd_status() {
		$get_default_store = UserDefaultStore::where( 'user_id', Auth::user()->id )->first();
		if ( $get_default_store ) {
			$default_store = $get_default_store->store_id;
		} else {
			$default_store = '';
			return response()->json(
				[
					'code'    => 'default_store_not_set',
					'message' => 'You have to chose current store.',
					'data'    => [ 'status' => 401 ],
				],
				200
			);
		}
		if ( $default_store != '' ) {
			$store_info = Store::where( 'id', $default_store )
						->first();
			// enabled
			if ( $store_info->edd_enabled == 1 ) {
				$edd_status = 'enabled';
			} elseif ( $store_info->edd_enabled == 2 ) {
				$edd_status = 'processing';
			} else {
				$edd_status = 'disabled';
			}

			$message = [ 'edd_status' => $edd_status ];
			if ( isset( $_GET['response'] ) && trim( $_GET['response'] ) == 'json' ) {
				return response()->json( $message, 200 );
			}
			return view( 'companies.stores.eddsetup', $store_info );
		}
	}

	/**
	 * Enable edd for site view.
	 */
	public function enable_edd( Request $request ) {
		$store_id          = $request->input( 'id' );
		$get_default_store = UserDefaultStore::where( 'user_id', Auth::user()->id )->first();
		if ( $store_id == '' ) {
			if ( $get_default_store ) {
				$default_store = $get_default_store->store_id;
			} else {
				$default_store = '';
				return response()->json(
					[
						'code'    => 'default_store_not_set',
						'message' => 'You have to chose current store.',
						'data'    => [ 'status' => 401 ],
					],
					200
				);
			}
		} else {
			$default_store = $store_id;
		}
		if ( $default_store != '' ) {
			$store_info                  = Store::where( 'id', $default_store )->first();
			$companies_store_credentials = unserialize( $store_info->companies_store_credentials );
			$store_url                   = $store_info->auth_server_url;
			$access_token                = $companies_store_credentials['access_token_info']['result']['access_token'];

			$message = 'Edd is already enabled.';

			if ( $store_info->edd_enabled == 0 ) {
				if ( $store_info->has_edd_setup == 1 ) { //if aleready setup then just enable
					// enable edd setup
					Store::where( 'id', $default_store )->update(
						[
							'edd_enabled' => 1, // Enabled
						]
					);
					$message = [ 'message' => 'Edd Enabled' ];
				} else {
					// process edd setup
					$this->setup_data_edd( $store_url, $store_info->id, $access_token );
					Store::where( 'id', $default_store )->update(
						[
							'edd_enabled' => 2, // Processing
						]
					);
					$message = [ 'message' => 'Setup EDD In Process...' ];
				}
			}

			if ( isset( $_GET['response'] ) && trim( $_GET['response'] ) == 'json' ) {
				return response()->json( $message, 200 );
			}

			// redirect user to single store view page.
			return redirect()->route( 'sites.show', [ 'store' => $store_info->id ] );
		}
	}

	/**
	 * Web hook for serving edd setup success from lumen site.
	 */
	public function edd_setup( Request $request ) {
		 $store_id    = $request->input( 'store_id' );
		$store_url    = urldecode( $request->input( 'store_url' ) );
		$access_token = $request->input( 'access_token' );
		$action       = $request->input( 'action' );
		$edd_key      = $request->input( 'edd_key' );
		if ( $store_id != '' && $action == 'success' ) {
			// validate the access token
			$store_info                  = Store::where( 'id', $store_id )
					->first();
			$companies_store_credentials = unserialize( $store_info->companies_store_credentials );
			$store_access_token          = $companies_store_credentials['access_token_info']['result']['access_token'];
			// now update the store to setup for edd
			Store::where( 'id', $store_id )->update(
				[
					'edd_enabled'   => 1,
					'has_edd_setup' => 1,
				]
			);
			// Send en email to admin for edd store setup for the store admin
			event( new EddSetup( $store_info ) );
		}
	}

	/**
	 * Disable edd for site view.
	 */
	public function disable_edd( Request $request ) {
		$store_id          = $request->input( 'id' );
		$get_default_store = UserDefaultStore::where( 'user_id', Auth::user()->id )
					  ->first();
		if ( $store_id == '' ) {
			if ( $get_default_store ) {
				$default_store = $get_default_store->store_id;
			} else {
				$default_store = '';
				return response()->json(
					[
						'code'    => 'default_store_not_set',
						'message' => 'You have to chose current store.',
						'data'    => [ 'status' => 401 ],
					],
					200
				);
			}
		} else {
			$default_store = $store_id;
		}
		if ( $default_store != '' ) {
			$store_info                  = Store::where( 'id', $default_store )
						->first();
			$companies_store_credentials = unserialize( $store_info->companies_store_credentials );
			$store_url                   = $store_info->auth_server_url;
			$access_token                = $companies_store_credentials['access_token_info']['result']['access_token'];
			// update flag store edd lumen
			if ( $store_info->edd_enabled == 1 ) {
				Store::where( 'id', $default_store )->update(
					[
						'edd_enabled' => 0,
					]
				);
			}
			$message = [ 'message' => 'Disabled' ];
			if ( isset( $_GET['response'] ) && trim( $_GET['response'] ) == 'json' ) {
				return response()->json( $message, 200 );
			}
			// redirect user to single store view page.
			return redirect()
						->route( 'sites.show', [ 'store' => $store_info->id ] );
		}
	}

	/**
	 * [enable_edd_sync description]
	 * @return [type] [description]
	 */
	public function sync_edd( Site $site ) {
		$response = $site->start_sync();
		return response()->json( $response );
	}

	/**
	 * Setup the Downloads as products on edd
	 * Get api response from wp edd site and send it to Lumen Edd
	 * @param $store_url, $companies_store_credentials
	 * @param $edd_wp_endpoint - for retrieving data to end point
	 * @param $edd_lumne_endpoint - for sending data to end point
	 * @param $edd_variable_name - name of the data object for edd post call
	 * return response or error
	 */
	protected function setup_data_edd( $store_url, $store_id, $access_token ) {
		ProcessEddSetup::dispatch( $store_url, $store_id, $access_token )->delay( now()->addSeconds( 5 ) );
	}

}
