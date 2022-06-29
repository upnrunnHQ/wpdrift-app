<?php
// app/Http/Controllers/OAuthController.php
/**
* OAuth2 Controller to handle OAuth related things to communitcate with WP Server.
**/
namespace App\Http\Controllers;

use App\Store;
use URL;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Company;
use App\CompanyUser;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\AddSite;

// For adding OAuth2 Adoy
use OAuth2\Client;
use Mail;
use App\Mail\OAuthError;
use Config;
use Helpers;

class OAuthController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware( 'auth' );
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		//
	}
	// method to connect with REST API Server Store of WP  for popup
	public function driftconnect( Request $request ) {
		$user         = User::find( Auth::user()->id );
		$store_id     = $request->session()->pull( 'store_id' );
		$store_single = Store::find( $store_id );
		// Define the variables
		$auth_client_id           = $store_single->auth_client_id;
		$auth_client_secret       = $store_single->auth_client_secret;
		$auth_server_url          = $store_single->auth_server_url . '/';
		$auth_client_callback_url = URL::to( '/oauth/drift' );
		// create oauth client object
		$client = new \OAuth2\Client( $auth_client_id, $auth_client_secret );
		if ( ! isset( $_GET['code'] ) ) {
			$auth_url = $client->getAuthenticationUrl( $auth_server_url . 'oauth/authorize', $auth_client_callback_url );
			header( 'Location: ' . $auth_url );
			exit;
		} else {
			// Save code to customer credentials if there is code has been there
			$customer_store_credentials = [];
			$params                     = array(
				'code'         => $_GET['code'],
				'redirect_uri' => $auth_client_callback_url,
			);
			$response                   = $client->getAccessToken( $auth_server_url . 'oauth/token', 'authorization_code', $params );
			$client->setAccessToken( $response );
			$customer_store_credentials['code']              = trim( $_GET['code'] );
			$customer_store_credentials['access_token_info'] = $response;
			// added try catch for error handling
			$error = '';
			try {
				if ( $response['result']['access_token'] != '' && $response['code'] == 200 ) {
					$storeUpdate = Store::where( 'id', $store_id )
					->update(
						[
							'companies_store_credentials' => serialize( $customer_store_credentials ),
						]
					);

					$this->process_edd( $store_id );
					return view( 'companies.stores.authorize', [ 'error' => '' ] );
				}
			} catch ( \Exception $e ) {
				$error = $e->getMessage();
				// email error to site admins
				$admin_emails = Config::get( 'app.admin_email' );
				foreach ( $admin_emails as $admin_email ) {
					Mail::to( $admin_email )->send( new OAuthError( $store_single, $error, $response ) );
				}
				return view( 'companies.stores.authorize', [ 'error' => $error ] );
			}
		}
	}

	/**
	 * [process_edd description]
	 * @param  [type] $store_id [description]
	 * @return [type]           [description]
	 */
	public function process_edd( $store_id ) {
		/**
		 * [$store description]
		 * @var [type]
		 */
		$store                       = Store::where( 'id', $store_id )->first();
		$companies_store_credentials = unserialize( $store->companies_store_credentials );
		$access_token                = $companies_store_credentials['access_token_info']['result']['access_token'];

		Store::where( 'id', $store_id )->update(
			[
				'edd_enabled' => 1,
			]
		);

		$payload = [
			'site_id'      => $store_id,
			'access_token' => $access_token,
		];

		AddSite::dispatch( $payload )->delay( now()->addSeconds( 5 ) );
	}

	/**
	 * Re OAuthorize user store upon coupon expire - 25th July 2018
	 */
	public function reoauthstore( Request $request, $store_id ) {
		$error                       = '';
		$user                        = User::find( Auth::user()->id );
		$store_single                = Store::find( $store_id );
		$companies_store_credentials = unserialize( $store_single->companies_store_credentials );

		// Define the variables
		$auth_client_id     = $store_single->auth_client_id;
		$auth_client_secret = $store_single->auth_client_secret;
		$auth_server_url    = $store_single->auth_server_url . '/';

		// Check and validate the token
		$api_end_point         = '/wp-json/wpdriftio/v1/clients/token';
		$url                   = $auth_server_url . $api_end_point . '?token=' . $companies_store_credentials['access_token_info']['result']['access_token'];
		$access_token_lifetime = '';
		if ( $companies_store_credentials['access_token_info']['result']['access_token'] != '' ) {
			$token_call                = Helpers::simple_get_curl_response( $url, $companies_store_credentials['access_token_info']['result']['access_token'] );
			list($token_header, $html) = explode( "\r\n\r\n", $token_call, 2 );
			if ( $html != 'null' ) {
				$html   = json_decode( $html );
				$a_html = (array) $html;
				if ( array_key_exists( 'code', $a_html ) ) {
					if ( $html->code == 'rest_no_route' ) {
						$error = 'No rest route defined';
						return view( 'companies.stores.authorize', [ 'error' => $error ] );
					}
				} else {
					if ( $html != 'Invalid Host' ) {
						$access_token_lifetime = $html->expires;
						if ( \Carbon\Carbon::now() < $access_token_lifetime ) {
							// show message that token is not expiring right now it is expiring on
							$error = 'Token is expiring on ' . \Carbon\Carbon::parse( $access_token_lifetime )->format( 'F j Y h:i:s a' );
							return view( 'companies.stores.authorize', [ 'error' => $error ] );
						}
					} else {
						$error = 'Invalid Host';
						return view( 'companies.stores.authorize', [ 'error' => $error ] );
					}
				}
			}
		} else {
			return view( 'companies.stores.authorize', [ 'error' => 'Access token is not found in our record' ] );
		}

		// create oauth client object
		$client = new Client( $auth_client_id, $auth_client_secret );

		// Save code to customer credentials if there is code has been there
		$customer_store_credentials = [];
		$params                     = [
			'refresh_token' =>
			$companies_store_credentials['access_token_info']['result']['refresh_token'],
		];

		$response                                        = $client->getAccessToken( $auth_server_url . 'oauth/token', 'refresh_token', $params );
		$customer_store_credentials['access_token_info'] = $response;
		// added try catch for error handling

		try {
			if ( $response['result']['access_token'] != '' && $response['code'] == 200 ) {
				$storeUpdate = Store::where( 'id', $store_id )
						->update(
							[
								'companies_store_credentials' => serialize( $customer_store_credentials ),
							]
						);
				// send the token info to edd lumen site
				if ( $store_single->has_edd_setup == 1 ) {
					$edd_server_url = config( 'app.edd_server_url' );
					$edd_key        = config( 'app.edd_key' );
					$gclient        = new Client();
					$response       = $gclient->request(
						'POST',
						$edd_server_url . '/update_store',
						[
							'form_params' =>
							[
								'store_id'     => $store_single->store_id,
								'store_url'    => $store_single->store_url,
								'access_token' => $companies_store_credentials['access_token_info']['result']['access_token'],
								'edd_key'      => $edd_key,
							],
						]
					);
				}
				return view( 'companies.stores.authorize', [ 'error' => '' ] );
			}
		} catch ( \Exception $e ) {
			$error = $e->getMessage();
			//email error to site admins
			$admin_emails = Config::get( 'app.admin_email' );
			foreach ( $admin_emails as $admin_email ) {
				Mail::to( $admin_email )->send( new OAuthError( $store_single, $error, $response ) );
			}
			return view( 'companies.stores.authorize', [ 'error' => $error ] );
		}
	}

	// Get cURL Response.
	private function get_curl_response( $url, $postvars ) {
		 $ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt(
			$ch,
			CURLOPT_POSTFIELDS,
			$postvars
		);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$html = curl_exec( $ch );
		curl_close( $ch );
		return $html;
	}
}
