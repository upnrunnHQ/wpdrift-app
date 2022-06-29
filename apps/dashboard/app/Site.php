<?php

namespace App;

use Illuminate\Support\Facades\Auth;
use App\UserDefaultStore;
use App\Store;

class Site {


	function __construct() {
		// code...
	}

	public function start_sync() {
		return [
			'sync_started',
			'default' => $this->get_default(),
		];
	}

	public function get() {
		$default = $this->get_default();
		if ( $default ) {
			return $default;
		}

		return $this->get_first();
	}

	public function get_default() {
		$default = UserDefaultStore::where( 'user_id', Auth::user()->id )->first();
		if ( ! $default ) {
			return false;
		}

		return $default->store_id;
	}

	public function get_first() {
		$site = UserDefaultStore::where( 'user_id', Auth::user()->id )->first();
	}

	/**
	 * [site_details description]
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function site_details( $id ) {
		$site        = Store::where( 'id', $id )->first();
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

}
