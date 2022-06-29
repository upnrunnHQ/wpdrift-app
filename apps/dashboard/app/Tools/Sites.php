<?php
namespace App\Tools;

use Illuminate\Support\Facades\Auth;
use App\Store;
use App\UserDefaultStore;
use App\Company;
use App\Tools\Http;
use Helpers;

class Sites {
	/**
	 * [private description]
	 * @var [type]
	 */
	private $count_sites;
	private $default_site_id;
	private $default_site;

	public function __construct() {
		/**
		 * [$this->count_sites description]
		 * @var [type]
		 */
		$this->count_sites = $this->counts();

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( $this->count_sites ) {
			$this->default_site_id = $this->site_id();
			$this->default_site    = $this->site( $this->default_site_id );
		}
	}

	/**
	 * [get description]
	 * @return [type] [description]
	 */
	public function get() {
		$sites = Store::where( 'user_id', Auth::user()->id )->get();
		if ( ! $sites ) {
			return false;
		}

		return $sites;
	}

	/**
	 * [counts description]
	 * @return [type] [description]
	 */
	public function counts() {
		$counts = Store::where( 'user_id', Auth::user()->id )->count();
		if ( ! $counts ) {
			return false;
		}

		return $counts;
	}

	/**
	 * [default_site_id description]
	 * @return [type] [description]
	 */
	public function site_id() {
		$default_site = UserDefaultStore::where( 'user_id', Auth::user()->id )->first();
		if ( $default_site ) {
			return $default_site->store_id;
		}

		if ( ! $this->count_sites ) {
			return false;
		}

		/**
		 * [$site description]
		 * @var [type]
		 */
		$site = Store::where( 'user_id', Auth::user()->id )->first();
		return $site->id;
	}

	/**
	 * [get description]
	 * @param  [type] $site_id [description]
	 * @return [type]          [description]
	 */
	public function site( $site_id ) {
		if ( ! $site_id ) {
			return false;
		}

		$site = Store::where( 'id', $site_id )->first();
		if ( ! $site ) {
			return false;
		}

		return $site;
	}

	/**
	 * [plugin_status description]
	 * @return [type] [description]
	 */
	public function plugin_installed( $site_url ) {
		$http     = new Http();
		$response = $http->request(
			[
				'base_uri' => $site_url . '/wp-json/wpdriftio/v1/',
				'method'   => 'GET',
				'route'    => 'site/plugin-status/',
			]
		);

		if ( in_array( $response['code'], [ 'exception_response', 'no_response' ], true ) ) {
			return false;
		}

		if ( ! isset( $response['data']->version ) ) {
			return false;
		}

		return true;
	}

	/**
	 * [token_exists description]
	 * @param  [type] $credentials [description]
	 * @return [type]              [description]
	 */
	public function token_exists( $credentials ) {
		if ( ! isset( $credentials['access_token_info']['result']['access_token'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * [token_exists description]
	 * @return [type] [description]
	 */
	public function token_expired( $site_url, $credentials ) {
		if ( empty( $credentials ) ) {
			return;
		}

		$http     = new Http();
		$response = $http->request(
			[
				'base_uri' => $site_url . '/wp-json/wpdriftio/v1/',
				'method'   => 'GET',
				'route'    => 'clients/token',
				'query'    => [
					'token' => $credentials['access_token_info']['result']['access_token'],
				],
			]
		);

		if ( in_array( $response['code'], [ 'exception_response', 'no_response' ] ) ) {
			return true;
		}

		if ( empty( $response['data'] ) ) {
			return true;
		}

		if ( \Carbon\Carbon::now() > $response['data']->expires ) {
			return true;
		}

		return false;
	}

	/**
	 * [dashboard_access description]
	 * @param  [type] $plugin_installed [description]
	 * @param  [type] $token_exists     [description]
	 * @param  [type] $token_expired    [description]
	 * @return [type]                   [description]
	 */
	public function dashboard_access( $plugin_installed, $token_exists, $token_expired ) {
		if ( ! $plugin_installed || ! $token_exists || $token_expired ) {
			return false;
		}

		return true;
	}

	/**
	 * [trim_url description]
	 * @param  [type] $url [description]
	 * @return [type]      [description]
	 */
	public function trim_url( $url ) {
		return rtrim( $url, '/' );
	}

	/**
	 * [settings description]
	 * @return [type] [description]
	 */
	public function settings() {
		$settings                = [];
		$settings['count_sites'] = $this->count_sites;

		if ( $this->count_sites ) {
			$settings['site_id']          = $this->default_site_id;
			$settings['site']             = $this->default_site;
			$settings['token_exists']     = $this->token_exists( unserialize( $this->default_site->companies_store_credentials ) );
			$settings['url']              = $this->default_site->auth_server_url;
			$settings['token_expired']    = $this->token_expired( $this->trim_url( $this->default_site->auth_server_url ), unserialize( $this->default_site->companies_store_credentials ) );
			$settings['plugin_installed'] = $this->plugin_installed( $this->trim_url( $this->default_site->auth_server_url ) );
			// $settings['token_expired']    = false;
			// $settings['plugin_installed'] = true;
			$settings['trimed_url']       = $this->trim_url( 'https://dhakadesk.com/' );
			$settings['company']          = Company::find( $this->default_site->company_id );
			$settings['site_credentials'] = unserialize( $this->default_site->companies_store_credentials );
			$settings['dashboard_access'] = $this->dashboard_access( $settings['plugin_installed'], $settings['token_exists'], $settings['token_expired'] );
		}

		/**
		 * [return description]
		 * @var [type]
		 */
		return $settings;
	}

}
