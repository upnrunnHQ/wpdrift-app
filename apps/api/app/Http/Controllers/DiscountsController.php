<?php
/**
 * Discount Controller
 * This will have all the discounts controller related methods that will serv
 * REST End Points
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Discount;
use App\DiscountMeta;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DiscountsController extends Controller {

	public function __construct() {
		// add middlefor edd_app
		//$this->middleware('edd_app');
	}
	/**
	 * For getting total number of discounts rest call
	 * @param $request - that will have request submitted to this method route.
	 * @param $store_id - for specifying store
	 */
	public function get_total_discounts( Request $request, $store_id ) {
		$discounts_set = Discount::orderBy( 'name', 'desc' )->where( 'store_id', $store_id );

		if ( $request->has( [ 'startdate', 'enddate' ] ) ) {
			$after  = $this->prepare_after( $request->startdate );
			$before = $this->prepare_before( $request->enddate );

			$discounts_set->where( 'post_date', '>=', $after )->where( 'post_date', '<=', $before );
		}

		return $discounts_set->count();
	}

	/**
	 * For getting all discounts rest call
	 * @param $request - that will have request submitted to this method route.
	 * @param $store_id - for specifying store
	 */
	public function get_discounts( Request $request, $store_id ) {
		$per_page      = $request->has( 'per_page' ) ? (int) $request->per_page : 10;
		$page          = $request->has( 'page' ) ? (int) $request->page : 1;
		$discounts_set = Discount::where( 'store_id', $store_id );
		if ( $request->has( [ 'startdate', 'enddate' ] ) ) {
			$discounts_set->where( 'post_date', '>=', $request->startdate )
						->where( 'post_date', '<=', $request->enddate );
		}
		if ( $request->has( 'search' ) ) {
			$discounts_set->orWhere( 'post_title', 'like', '%' . $request->search . '%' );
		}
		$discounts_set->orderBy( 'post_title', 'desc' );

		return $discounts_set->paginate( $per_page );
	}

	/**
	 * Get total no. of discounts
	 */
	/**
	 * For getting discounts listing rest call
	 * @param $request - that will have request submitted to this method route.
	 * @param $store_id - for specifying store
	 */
	public function get_total_number_discounts( Request $request, $store_id ) {
		$discounts_set = Discount::where( 'store_id', $store_id );

		if ( $request->has( [ 'startdate', 'enddate' ] ) ) {
			if ( $request->startdate != '' && $request->enddate != '' ) {
				// to check if items exists in this period if not then return all products
				$discounts_set->where( 'post_date', '>=', $request->startdate )
							->where( 'post_date', '<=', $request->enddate );
			}
		}

		return $discounts_set->count();
	}

	/**
	 * [prepare_after description]
	 * @param  [type] $date_string [description]
	 * @return [type]              [description]
	 */
	public function prepare_after( $date_string ) {
		$after = new Carbon( $date_string );
		return $after->startOfDay()->toDateTimeString();
	}

	/**
	 * [prepare_before description]
	 * @param  [type] $date_string [description]
	 * @return [type]              [description]
	 */
	public function prepare_before( $date_string ) {
		$before = new Carbon( $date_string );
		return $before->endOfDay()->toDateTimeString();
	}
}
