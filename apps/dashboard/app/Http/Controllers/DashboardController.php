<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\UserDefaultStore;
use App\Store;
use GuzzleHttp\Client;
use App\Tools\Sites;

class DashboardController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}
	/**
	 * Show the application splash screen.
	 *
	 * @return Response
	 */
	public function show()
	{
		return view('dashboard');
	}

	/**
	 * [settings description]
	 * @return [type] [description]
	 */
	public function settings( Request $request ) {
		$sites    = new Sites();
		$settings = $sites->settings();

		if ( 'json' == $request->get( 'response' ) ) {
			return $settings;
		}

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( ! $settings['count_sites'] ) {
			return redirect( '/settings/sites/create' );
		}

		return view( 'spark::settings', $settings );
	}

	/**
	 * [referrals description]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function referrals(Request $request)
	{
		if (isset($_GET['type']) && ! empty($_GET['type'])) {
			$referrals_type = $_GET['type'];
		} else {
			$referrals_type = '';
		}

		$get_default_store = UserDefaultStore::where('user_id', Auth::user()->id)->first();
		if ($get_default_store) {
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

		if ('' != $default_store) {
			$store_info                  = Store::where('id', $default_store)->first();
			$companies_store_credentials = unserialize($store_info->companies_store_credentials);
			$store_url                   = $store_info->auth_server_url;
			$url                         = $store_url . '/wp-json/wpdriftio/v1/hits/' . $referrals_type;

			if (isset($_GET['after']) && ! empty($_GET['after'])) {
				$url .= '&after=' . $_GET['after'];
			}

			if (isset($_GET['before']) && ! empty($_GET['before'])) {
				$url .= '&before=' . $_GET['before'];
			}

			$auth_token = $companies_store_credentials['access_token_info']['result']['access_token'];
			// Guzzle usage
			$gclient = new Client();
			$request_var = $gclient->request(
					'GET',
					$url,
					[
							'headers' =>
									[
											'Authorization' => 'Bearer ' . $auth_token
									]
					]
			);

			$gresponse = $request_var->getBody()->getContents();
			$referrals = trim($gresponse);
			$gheaders = $request_var->getHeaders();
			$get_status_code = $request_var->getStatusCode();

			if ('200' == $get_status_code) {
				return response()->json(
					[ 'referrals' => json_decode($referrals) ],
					200
				);
			} else {
				$error_msg = $this->get_error_message_from_code($get_status_code);
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

	// For showing recent events
	public function recent_events(Request $request)
	{
		$get_default_store = UserDefaultStore::where('user_id', Auth::user()->id)->first();
		if ($get_default_store) {
			$default_store = $get_default_store->store_id;
		} else {
			$default_store = "";
			return response()->json(
		  [
			'code'=> 'default_store_not_set',
			'message' => "You have to chose current store.",
			'data' =>
			[
			  'status' => 401
			  ]
			],
			200
		);
		}

		// setup the store information
		if ($default_store != "") {
			$store_info = Store::where('id', $default_store)
				  ->first();
			$companies_store_credentials = unserialize($store_info->companies_store_credentials);
			$store_url = $store_info->auth_server_url;
			if ($request['type'] != "") {
				$api_end_point = '/wp-json/wpdriftio/v1/events/?type='.$request['type'];
			} else {
				$api_end_point = '/wp-json/wpdriftio/v1/events/';
			}

			// Prepare URL for rest
			$url = $store_url  . $api_end_point;

			// Guzzle usage
			$gclient = new Client();
			$request_var = $gclient->request(
					'GET',
					$url,
					[
							'headers' =>
									[
											'Authorization' => 'Bearer ' . $companies_store_credentials['access_token_info']['result']['access_token']
									]
					]
			);

			$gresponse = $request_var->getBody()->getContents();
			$recent_events = trim($gresponse);
			$gheaders = $request_var->getHeaders();
			$get_status_code = $request_var->getStatusCode();

			if ($get_status_code == "200") {
				return response()->json(
				[
				  'recent_events' => json_decode($recent_events)
				],
				  200
			  );
			} else {
				$error_msg = $this->get_error_message_from_code($get_status_code);
				return response()->json(
				  [
					'code'=> 'server_error',
					'message' => $error_msg,
					'data' =>
					[
					  'status' => $get_status_code
					  ]
					],
					200
				);
			}
		}
	}

	// for serving rest response for dashboard
	public function server_dashboard_api()
	{
		$get_default_store = UserDefaultStore::where('user_id', Auth::user()->id)
				  ->first();
		if ($get_default_store) {
			$default_store = $get_default_store->store_id;
		} else {
			$default_store = "";
			return response()->json(
			[
			  'code'=> 'default_store_not_set',
			  'message' => "You have to chose current store.",
			  'data' =>
			  [
				'status' => 401
				]
			  ],
			  200
		  );
		}

		// setup the store information
		if ($default_store != "") {
			$store_info = Store::where('id', $default_store)
					->first();
			$companies_store_credentials = unserialize($store_info->companies_store_credentials);
			$store_url = $store_info->auth_server_url;
			$api_end_point = '/wp-json/wpdriftio/v1/dashboard/';
			// Prepare URL for rest
			$url = $store_url  . $api_end_point . '?response=json';

			if (isset($_GET['after']) && ! empty($_GET['after'])) {
				$url .= '&after=' . $_GET['after'];
			}

			if (isset($_GET['before']) && ! empty($_GET['before'])) {
				$url .= '&before=' . $_GET['before'];
			}

			// Guzzle usage
			$gclient = new Client();
			$request_var = $gclient->request(
					'GET',
					$url,
					[
							'headers' =>
									[
											'Authorization' => 'Bearer ' . $companies_store_credentials['access_token_info']['result']['access_token']
									]
					]
			);

			$gresponse = $request_var->getBody()->getContents();
			$dashboard_info = trim($gresponse);
			$gheaders = $request_var->getHeaders();
			$get_status_code = $request_var->getStatusCode();

			if ($get_status_code == "200") {
				return response()->json(
				  [
					'dashboard_info' => json_decode($dashboard_info)
				  ],
					200
				);
			} else {
				$error_msg = $this->get_error_message_from_code($get_status_code);
				return response()->json(
					[
					  'code'=> 'server_error',
					  'message' => $error_msg,
					  'data' =>
					  [
						'status' => $get_status_code
						]
					  ],
					  200
				  );
			}
		}
	}
	// to access direct curl response using shared function
	private function get_curl_response($url, $access_token)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $access_token));
		$html = curl_exec($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($html, 0, $header_size);
		curl_close($ch);
		return $html;
	}
	private function get_error_message_from_code($code)
	{
		$error_message = "";
		switch ($code) {
			case '301':
			  $error_message = 'Error: 301 (Moved Permanently)';
			  break;
			case '302':
			  $error_message = 'Error: 302 (Found)';
			  break;
			case '302':
			  $error_message = 'Error: 302 (Found)';
			  break;
			case '303':
			  $error_message = 'Error: 303 (See Other)';
			  break;
			case '303':
			  $error_message = 'Error: 303 (See Other)';
			  break;
			case '307':
			  $error_message = 'Error: 307 (Temporary Redirect)';
			  break;
			case '400':
			  $error_message = 'Error: 400 (Bad Request)';
			  break;
			case '401':
			  $error_message = 'Error: 401 (Unauthorized)';
			  break;
			case '403':
			  $error_message = 'Error: 403 (Forbidden)';
			  break;
			case '404':
			  $error_message = 'Error: 404 (Not Found)';
			  break;
			case '405':
			  $error_message = 'Error: 405 (Method Not Allowed)';
			  break;
			case '406':
			  $error_message = 'Error: 406 (Not Acceptable)';
			  break;
			case '412':
			  $error_message = 'Error: 412 (Precondition Failed)';
			  break;
			case '500':
			  $error_message = 'Error: 500 (Internal Server Error)';
			  break;
			case '501':
			  $error_message = 'Error: 501 (Not Implemented)';
			  break;
	  }
		return $error_message;
	}
}
