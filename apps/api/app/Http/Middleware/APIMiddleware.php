<?php

namespace App\Http\Middleware;

use Closure;
use App\SiteMeta;

class APIMiddleware {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle( $request, Closure $next ) {
		$autho_header = $request->header( 'Authorization' );
		$auth_epld    = explode( ' ', $autho_header );
		$oauth_token  = $auth_epld['1'];
		// get provided store details
		$store_id   = $request->route()['2']['store_id'];
		$store_info = SiteMeta::where( 'site_id', $store_id )
		->where( 'meta_key', 'access_token' )
		->first();

		if ( $store_info->meta_value != $oauth_token ) {
			return response( 'Unauthorized.', 401 );
		}
		return $next( $request );
	}
}
