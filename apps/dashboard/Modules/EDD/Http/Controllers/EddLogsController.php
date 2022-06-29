<?php
/**
* For serving products/downloads related end points
* Rest Calls
*/
namespace Modules\EDD\Http\Controllers;

use App\User;
use App\Store;
use App\UserDefaultStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class EddLogsController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware( 'auth' );
	}

	/**
	 * Get Discounts
	 */
	public function get_logs( Request $request ) {
		$default_store = UserDefaultStore::where( 'user_id', Auth::user()->id )->first();
		if ( ! $default_store ) {
			return [
				'code'    => 'default_store_not_set',
				'message' => __( 'Plese set up a default store to get details.' ),
				'data'    => [ 'status' => 401 ],
			];
		}

		$store                       = Store::where( 'id', $default_store->store_id )->first();
		$companies_store_credentials = unserialize( $store->companies_store_credentials );
		$store_data                  = [
			'store_id'    => $store->id,
			'api_uri'     => $store->auth_server_url,
			'credentials' => $companies_store_credentials,
			'idToken'     => $companies_store_credentials['access_token_info']['result']['access_token'],
		];

		$edd_server_url = config( 'app.edd_server_url' );
		$request_url    = $edd_server_url . '/api/logs/' . $store->id;

		try {
			$client   = new Client();
			$response = $client->request(
				'GET',
				$request_url,
				[
					'headers' => [ 'Authorization' => 'Bearer ' . $store_data['idToken'] ],
					'query'   => $request->all(),
				]
			);

			$response_body = json_decode( $response->getBody()->getContents() );
			return (array) $response_body;
		} catch ( RequestException | ClientException $e ) {
			if ( $e->hasResponse() ) {
				return [];
			}
		}
	}
}
