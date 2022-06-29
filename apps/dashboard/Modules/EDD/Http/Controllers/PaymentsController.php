<?php
// Modules/EDD/Http/Controllers/PaymentsController.php
/**
* For setting up the serving api end points related to Orders
* Rest Calls
*/
namespace Modules\EDD\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\EDD\Http\Helpers;
use Modules\EDD\Http\Controllers\GeneralController;

class PaymentsController extends Controller {


	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		 $this->middleware( 'auth' );
	}
	/**
	 * For getting single payment rest call
	 * @param $order_id - for specifying order id
	 */
	public function show( $order_id ) {
		 // get the store information
		$api_end_point = '/api/order/';
		// Prepare URL for rest
		$response = Helpers::get_guzzle_response( $api_end_point, $order_id );
		return response()->json( json_decode( $response ) );
	}
	/**
	 * Get get the recent orders
	 */
	public function get_events_orders() {
		// get the store information
		$api_end_point = '/api/recent_orders/';
		// Prepare URL for rest
		$response = Helpers::get_guzzle_response( $api_end_point );
		return response()->json( json_decode( $response ) );
	}
	/**
	 * Get Net revenue
	 */
	public function get_net_revenue( Request $request, GeneralController $generalController ) {
		 $startdate = $request->has( 'startdate' ) ? $request->startdate : '';
		$enddate    = $request->has( 'enddate' ) ? $request->enddate : '';
		// get the store information
		$api_end_point = '/api/net_revenue/';
		return response()->json( $generalController->get_general_dashboard_sections_data( $api_end_point, $startdate, $enddate ) );
	}
	/**
	 * For gross sales
	 */
	public function get_gross_sales() {
		  // get the store information
		$api_end_point = '/api/gross_sales/';
		// Prepare URL for rest
		$response = Helpers::get_guzzle_response( $api_end_point );
		return response()->json( json_decode( $response ) );
	}
	/**
	 * For gross refunds
	 */
	public function get_gross_refunds() {
		// get the store information
		$api_end_point = '/api/gross_refunds/';
		// Prepare URL for rest
		$response = Helpers::get_guzzle_response( $api_end_point );
		return response()->json( json_decode( $response ) );
	}
	/**
	 * For gross taxes
	 */
	public function get_gross_taxes() {
		  // get the store information
		$api_end_point = '/api/gross_taxes/';
		// Prepare URL for rest
		$response = Helpers::get_guzzle_response( $api_end_point );
		return response()->json( json_decode( $response ) );
	}
	/**
	 * For get total items sold
	 */
	public function get_total_items_sold( Request $request, GeneralController $generalController ) {
		$startdate = $request->has( 'startdate' ) ? $request->startdate : '';
		$enddate   = $request->has( 'enddate' ) ? $request->enddate : '';
		// get the store information
		$api_end_point = '/api/total_items_sold/';
		return response()->json( $generalController->get_general_dashboard_sections_data( $api_end_point, $startdate, $enddate ) );
	}
	/**
	 * For get total number of refunds
	 */
	public function get_total_number_refunds( Request $request, GeneralController $generalController ) {
		$startdate = $request->has( 'startdate' ) ? $request->startdate : '';
		$enddate   = $request->has( 'enddate' ) ? $request->enddate : '';
		// get the store information
		$api_end_point = '/api/total_number_refunds/';
		return response()->json( $generalController->get_general_dashboard_sections_data( $api_end_point, $startdate, $enddate ) );
	}
	/**
	 * For get refunded amount
	 */
	public function get_refunded_amounts( Request $request, GeneralController $generalController ) {
		$startdate = $request->has( 'startdate' ) ? $request->startdate : '';
		$enddate   = $request->has( 'enddate' ) ? $request->enddate : '';
		// get the store information
		$api_end_point = '/api/refunded_amounts/';
		return response()->json( $generalController->get_general_dashboard_sections_data( $api_end_point, $startdate, $enddate ) );
	}
	/**
	 * For get orders
	 */
	public function get_orders( Request $request ) {
		$per_page  = $request->has( 'per_page' ) ? (int) $request->per_page : 10;
		$page      = $request->has( 'page' ) ? (int) $request->page : 1;
		$search    = $request->has( 'search' ) ? $request->search : '';
		$orderby   = $request->has( 'orderby' ) ? $request->orderby : 'order_id';
		$order     = $request->has( 'order' ) ? $request->order : 'desc';
		$startdate = $request->has( 'startdate' ) ? $request->startdate : '';
		$enddate   = $request->has( 'enddate' ) ? $request->enddate : '';

		// if ( $startdate != '' && $enddate != '' ) {
		//  // extract date from start and end dates
		//  $explode_startdate = explode( 'T', $startdate );
		//  $explode_enddate   = explode( 'T', $enddate );
		//  $startdate         = $explode_startdate[0];
		//  $enddate           = $explode_enddate[0];
		// }

		// get the store information
		$api_end_point = '/api/orders/';
		// query params
		$query_params = "?per_page={$per_page}&page={$page}&startdate={$startdate}&enddate={$enddate}&orderby={$orderby}&order={$order}";
		if ( $search != '' ) {
			$query_params .= "&search={$search}";
		}
		// Prepare URL for rest
		$response = Helpers::get_guzzle_response( $api_end_point, '', $query_params );
		return response()->json( json_decode( $response ) );
	}
	/**
	 * For get refunds
	 */
	public function get_refunds( Request $request ) {
		$per_page = $request->has( 'per_page' ) ? (int) $request->per_page : 10;
		$page     = $request->has( 'page' ) ? (int) $request->page : 1;
		// get the store information
		$api_end_point = '/api/refunds/';
		// Prepare URL for rest
		$response = Helpers::get_guzzle_response( $api_end_point, '', "?per_page={$per_page}&page={$page}" );
		return response()->json( json_decode( $response ) );
	}
	/**
	 * For get orders segment
	 */
	public function get_orders_segment() {
		// get the store information
		$api_end_point = '/api/orders_segment/';
		// Prepare URL for rest
		$response = Helpers::get_guzzle_response( $api_end_point );
		return response()->json( json_decode( $response ) );
	}

	/**
	 * For get total no. orders
	 */
	public function get_total_number_orders( Request $request, GeneralController $generalController ) {
		$startdate = $request->has( 'startdate' ) ? $request->startdate : '';
		$enddate    = $request->has( 'enddate' ) ? $request->enddate : '';
		// Prepare URL for rest
		$api_end_point = '/api/total_number_orders/';
		return response()->json( $generalController->get_general_dashboard_sections_data( $api_end_point, $startdate, $enddate ) );
	}

	/**
	 * [subscriptions_earnings description]
	 * @param  Request           $request           [description]
	 * @param  GeneralController $generalController [description]
	 * @return [type]                               [description]
	 */
	public function subscriptions_earnings( Request $request, GeneralController $generalController ) {
		$startdate = $request->has( 'startdate' ) ? $request->startdate : '';
		$enddate   = $request->has( 'enddate' ) ? $request->enddate : '';
		// Prepare URL for rest
		$api_end_point = '/api/subscriptions_earnings/';
		return response()->json( $generalController->get_general_dashboard_sections_data( $api_end_point, $startdate, $enddate ) );
	}

	/**
	 * [subscriptions_refunded description]
	 * @param  Request           $request           [description]
	 * @param  GeneralController $generalController [description]
	 * @return [type]                               [description]
	 */
	public function subscriptions_refunded( Request $request, GeneralController $generalController ) {
		return Helpers::remote_get(
			'/api/subscriptions_refunded/',
			$request->all()
		);
	}

	/**
	 * [subscriptions_count description]
	 * @param  Request           $request           [description]
	 * @param  GeneralController $generalController [description]
	 * @return [type]                               [description]
	 */
	public function subscriptions_count( Request $request, GeneralController $generalController ) {
		$startdate = $request->has( 'startdate' ) ? $request->startdate : '';
		$enddate   = $request->has( 'enddate' ) ? $request->enddate : '';
		// Prepare URL for rest
		$api_end_point = '/api/subscriptions_count/';
		return response()->json( $generalController->get_general_dashboard_sections_data( $api_end_point, $startdate, $enddate ) );
	}

	/**
	 * [subscriptions_refunded_count description]
	 * @param  Request           $request           [description]
	 * @param  GeneralController $generalController [description]
	 * @return [type]                               [description]
	 */
	public function subscriptions_refunded_count( Request $request, GeneralController $generalController ) {
		$startdate = $request->has( 'startdate' ) ? $request->startdate : '';
		$enddate   = $request->has( 'enddate' ) ? $request->enddate : '';
		// Prepare URL for rest
		$api_end_point = '/api/subscriptions_refunded_count/';
		return response()->json( $generalController->get_general_dashboard_sections_data( $api_end_point, $startdate, $enddate ) );
	}
}
