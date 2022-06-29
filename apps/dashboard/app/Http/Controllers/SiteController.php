<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\UserDefaultStore;
use App\Store;
use App\Site;
use App\User;
use App\Jobs\ProcessSite;
use App\SiteProcessor;

class SiteController extends Controller {

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
	 * [enable_edd description]
	 * @return [type] [description]
	 */
	public function enable_edd( Site $site ) {
		$default_site = UserDefaultStore::where( 'user_id', Auth::user()->id )->first();
		if ( ! $default_site ) {
			return response()->json(
				[
					'code'    => 'no_default_site',
					'message' => 'You have to chose current store.',
					'data'    => [],
				],
				200
			);
		}

		Store::where( 'id', $default_site->store_id )->update(
			[
				'edd_enabled' => 1,
			]
		);

		ProcessSite::dispatch( $default_site->store_id )->delay( now()->addSeconds( 1 ) );

		return [
			'site_id' => $default_site->store_id,
			'status'  => 'ok',
		];
	}

	/**
	 * [debug description]
	 * @return [type] [description]
	 */
	public function debug() {
		$default_site = UserDefaultStore::where( 'user_id', Auth::user()->id )->first();
		if ( ! $default_site ) {
			return response()->json(
				[
					'code'    => 'no_default_site',
					'message' => 'You have to chose current store.',
					'data'    => [],
				],
				200
			);
		}

		return [
			'site_id' => $default_site->store_id,
		];
	}
}
