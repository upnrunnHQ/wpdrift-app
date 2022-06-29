<?php
// Modules/EDD/Http/Controllers/ProductsController.php
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
use Modules\EDD\Http\Helpers;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class ProductsController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware( 'auth' );
	}
	/**
	 * Get single product
	 */
	public function show( $product_id ) {
		// get the store information
		$api_end_point = '/api/product/';
		// Prepare URL for rest
		$response = Helpers::get_guzzle_response( $api_end_point, $product_id );
		return response()->json( json_decode( $response ) );
	}
	/**
	 * Get get the total products
	 */
	public function get_total_products() {
		// get the store information
		$api_end_point = '/api/total_products/';
		// Prepare URL for rest
		$response = Helpers::get_guzzle_response( $api_end_point );
		return response()->json( json_decode( $response ) );
	}
	/**
	 * Get Products
	 */
	public function get_products( Request $request ) {
		$per_page  = $request->has( 'per_page' ) ? (int) $request->per_page : 10;
		$page      = $request->has( 'page' ) ? (int) $request->page : 1;
		$search    = $request->has( 'search' ) ? $request->search : '';
		$startdate = $request->has( 'startdate' ) ? $request->startdate : '';
		$enddate   = $request->has( 'enddate' ) ? $request->enddate : '';
		$orderby   = $request->has( 'orderby' ) ? $request->orderby : 'post_id';
		$order     = $request->has( 'order' ) ? $request->order : 'desc';
		if ( $startdate != '' && $enddate != '' ) {
			// extract date from start and end dates
			$explode_startdate = explode( 'T', $startdate );
			$explode_enddate   = explode( 'T', $enddate );
			$startdate         = $explode_startdate[0];
			$enddate           = $explode_enddate[0];
		}

		// get the store information
		$api_end_point = '/api/products/';
		// Prepare URL for rest
		$response = Helpers::get_guzzle_response( $api_end_point, '', "?per_page={$per_page}&page={$page}&search={$search}&startdate={$startdate}&enddate={$enddate}&orderby={$orderby}&order={$order}" );
		return response()->json( json_decode( $response ) );
	}

	/**
	 * Get Product's Payments
	 */
	public function get_products_orders( $product_id ) {
		// get the store information
		$api_end_point = '/api/product_payments/';
		// Prepare URL for rest
		$response = Helpers::get_guzzle_response( $api_end_point, $product_id );
		return response()->json( json_decode( $response ) );
	}

	/**
	 * For get total no. products
	 */
	public function get_total_number_products( Request $request, GeneralController $generalController ) {
		$startdate = $request->has( 'startdate' ) ? $request->startdate : '';
		$enddate   = $request->has( 'enddate' ) ? $request->enddate : '';
		// Prepare URL for rest
		$api_end_point = '/api/total_number_products/';
		return response()->json( $generalController->get_general_dashboard_sections_data( $api_end_point, $startdate, $enddate ) );
	}

	/**
	 * For get top purchased products
	 */
	public function get_top_purchased_products( Request $request, GeneralController $generalController ) {
		$per_page = 5;
		// Prepare URL for rest
		$api_end_point = '/api/top-purchased-products/';
		// Prepare URL for rest
		$response = Helpers::get_guzzle_response( $api_end_point, '', "?per_page={$per_page}" );
		return response()->json( json_decode( $response ) );
	}

	/**
	 * Get payments.
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function get_payments( Request $request ) {
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
		$request_url    = $edd_server_url . '/api/payments/' . $store->id;

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
