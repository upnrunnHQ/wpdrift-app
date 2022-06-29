<?php
// app/Providers/StoreManageCustomServiceProvider.php
/**
* Added store manage custom provide to add composer view to pass it to navigation menus
* header
**/
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\UserDefaultStore;
use App\Store;
use Symfony\Component\HttpFoundation\Session\Session;
use Helpers;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

class StoreManageCustomServiceProvider extends ServiceProvider {
	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot() {
		// To set as defailt store as menu for top menu
		$auth = $this->app['auth'];
		view()->composer(
			'spark::nav.user',
			function ( $view ) use ( $auth ) {
				$this->get_default_store( $view, $auth );
			}
		);
		view()->composer(
			'dashboard',
			function ( $view ) use ( $auth ) {
				$this->get_default_store( $view, $auth );
			}
		);
		view()->composer(
			'partials.side-nav',
			function ( $view ) use ( $auth ) {
				$this->get_default_store( $view, $auth );
			}
		);
		// add logo to brand blade
		view()->composer(
			'spark::nav.brand',
			function ( $view ) use ( $auth ) {
				$this->get_default_store_details( $view, $auth );
			}
		);
		// Add default customers listing page variables
		view()->composer(
			'spark::layouts.app',
			function ( $view ) use ( $auth ) {
				$this->get_default_customers_page_vars( $view, $auth );
				$view->with( 'timezone', $this->get_timezone() );
				$view->with( 'settings', $this->get_settings() );
			}
		);
	}

	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register() {
		//
	}

	protected function get_default_store( $view, $auth ) {
		$user_default_store = '';
		$view->with( 'user_default_store', $user_default_store );
		$view->with( 'store_setup', 'N' );
		$chkdftstr = UserDefaultStore::where( 'user_id', $auth->user()->getattributes()['id'] )
		->first();
		if ( $chkdftstr ) {
			$user_default_store = $chkdftstr->store_id;
			if ( $user_default_store ) {
				// add status of plugin is installed or not
				$default_store = $chkdftstr->store_id;
				$store_info    = Store::where( 'id', $default_store )->first();
				if ( $store_info ) {
					$companies_store_credentials = unserialize( $store_info->companies_store_credentials );
					if ( $companies_store_credentials != '' ) {
						$store_url = $store_info->auth_server_url;
						// first check plugin is installed at server or not, If not then redirect to store single page.
						$plgn_chk_api_end_point = '/wp-json/wpdriftio/v1/site/plugin-status/';
						$plgn_chk_url           = $store_url . $plgn_chk_api_end_point;
						try {
							$plgn_chk_html                = Helpers::simple_get_curl_response( $plgn_chk_url );
							list($header, $plgn_chk_html) = explode( "\r\n\r\n", $plgn_chk_html, 2 );
							$decode_plugin_chk            = json_decode( $plgn_chk_html );
							if ( $decode_plugin_chk->version != '' ) {
								$view->with( 'plugin_version', $decode_plugin_chk->version );
							} else {
								$view->with( 'plugin_version', '' );
							}
						} catch ( \Exception $e ) {
							//echo $error = $e->getMessage();
							$view->with( 'plugin_version', '' );
						}
						$view->with( 'user_default_store', $user_default_store );
						$view->with( 'store_setup', 'Y' );
					} else {
						$view->with( 'user_default_store', $user_default_store );
						$view->with( 'plugin_version', '' );
					}
				}
			} else {
				$view->with( 'user_default_store', '' );
				$view->with( 'plugin_version', '' );
			}
		} else {
			$view->with( 'plugin_version', '' );
			$view->with( 'user_default_store', '' );
		}
	}
	// get default store information
	protected function get_default_store_details( $view, $auth ) {
		$user_default_store_details = '';
		$view->with( 'user_default_store_details', $user_default_store_details );
		$chkdftstr = UserDefaultStore::where( 'user_id', $auth->user()->getattributes()['id'] )
		  ->first();
		if ( $chkdftstr ) {
			$user_default_store_details = Store::where( 'id', $chkdftstr->store_id )
			->first();
			if ( $user_default_store_details ) {
				$view->with( 'user_default_store_details', $user_default_store_details );
			}
		}
	}
	// get default customers page listing variables from sessions
	protected function get_default_customers_page_vars( $view, $auth ) {
		$session       = new Session();
		$per_page_sess = $session->get( 'per_page_sess' );
		if ( $per_page_sess == '' ) {
			$per_page_sess = 10;
		}
		$page_sess = $session->get( 'page_sess' );
		if ( $page_sess == '' ) {
			$page_sess = 1;
		}
		$orderby_sess = $session->get( 'orderby_sess' );
		if ( $orderby_sess == '' ) {
			$orderby_sess = 'name';
		}
		$search_sess = $session->get( 'search_sess' );
		if ( $search_sess == '' ) {
			$search_sess = '';
		}
		$customers_page_sess = [
			'customersPageSess' =>
			  [
				  'per_page' => $per_page_sess,
				  'page'     => $page_sess,
				  'orderby'  => $orderby_sess,
				  'search'   => $search_sess,
			  ],
		];
		if ( $customers_page_sess ) {
			$view->with( 'customers_page_sess', $customers_page_sess );
		}
	}

	public function get_timezone() {
		$timezone = 'UTC';
		if ( ! Auth::user() ) {
			return $timezone;
		}

		$default_store = UserDefaultStore::where( 'user_id', Auth::user()->id )->first();
		if ( ! $default_store ) {
			return $timezone;
		}

		$store                       = Store::where( 'id', $default_store->store_id )->first();
		$companies_store_credentials = unserialize( $store->companies_store_credentials );
		$bearer_token                = $companies_store_credentials['access_token_info']['result']['access_token'];
		$request_url                 = $store->auth_server_url . '/wp-json/wp/v2/settings';

		try {
			$client   = new Client();
			$response = $client->request(
				'GET',
				$request_url,
				[ 'headers' => [ 'Authorization' => 'Bearer ' . $bearer_token ] ]
			);

			$response_body = json_decode( $response->getBody()->getContents() );
			$timezone      = $response_body->timezone;
		} catch ( ConnectException | RequestException | ClientException $e ) {
		}

		return $timezone;
	}

	public function get_settings() {
		$settings = [];
		if ( ! Auth::user() ) {
			return $settings;
		}

		$default_store = UserDefaultStore::where( 'user_id', Auth::user()->id )->first();
		if ( ! $default_store ) {
			return $settings;
		}

		$store                       = Store::where( 'id', $default_store->store_id )->first();
		$companies_store_credentials = unserialize( $store->companies_store_credentials );
		$bearer_token                = $companies_store_credentials['access_token_info']['result']['access_token'];
		$request_url                 = $store->auth_server_url . '/wp-json/wpdriftio/v1/site';

		try {
			$client   = new Client();
			$response = $client->request( 'GET', $request_url );
			$settings = json_decode( $response->getBody()->getContents() );
		} catch ( ConnectException | RequestException | ClientException $e ) {
		}

		return $settings;
	}
}
