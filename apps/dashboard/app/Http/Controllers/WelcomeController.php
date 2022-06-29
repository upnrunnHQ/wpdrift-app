<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Store;
use App\Tools\Sites;

class WelcomeController extends Controller {
	/**
	 * Create a new controller instance.
	 */
	public function __construct() {
		$this->middleware( 'auth' );
	}

	/**
	 * Show the application splash screen.
	 *
	 * @return Response
	 */
	public function show() {
		$sites    = new Sites();
		$settings = $sites->settings();

		if ( ! $settings['count_sites'] ) {
			return redirect( '/settings/sites/create' );
		}

		if ( ! $settings['dashboard_access'] ) {
			return redirect( '/settings' );
		}

		return view( 'dashboard' );
	}
}
