<?php
/**
 * General Controller
 * This will have all the general tasks controller related methods that will serv
 * REST End Points
 */
namespace Modules\EDD\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\EDD\Http\Helpers;

class GeneralController extends Controller {
	/**
	 * Public static function to get the all types of range for dashboard page.
	 */
	public function get_general_dashboard_sections_data( $api_end_point, $query_params ) {
		return [
			'net_item'             => 14,
			'item_prev_percentage' => 100,
		];

		$dates = '';
		if ( $startdate != '' && $enddate != '' ) {
			// extract date from start and end dates
			$explode_startdate = explode( 'T', $startdate );
			$explode_enddate   = explode( 'T', $enddate );
			$startdate         = $explode_startdate[0];
			$enddate           = $explode_enddate[0];
			$dates             = "?startdate={$startdate}&enddate={$enddate}";
		}
		// Prepare URL for rest
		$net_item_value      = Helpers::get_guzzle_response( $api_end_point, '', $dates );
		$net_plus_percentage = 0;

		// return [ $net_item_value ];
		if ( $dates != '' ) {
			/**
			 * Get that last duration payment
			 */
			if ( $startdate == $enddate ) { // Same day so get prev date
				$start_date_prev = date( 'Y-m-d', strtotime( $startdate . ' -1 day' ) );
				$end_date_prev   = $start_date_prev;
			} else {
				$endd            = strtotime( $enddate ); // or your date as well
				$startd          = strtotime( $startdate );
				$datediff        = $endd - $startd;
				$days_dif        = round( $datediff / ( 60 * 60 * 24 ) );
				$prev_end_day    = $startdate;
				$start_date_prev = date( 'Y-m-d', strtotime( $startdate . '-' . ( $days_dif ) . ' days' ) );
				$end_date_prev   = date( 'Y-m-d', strtotime( $startdate . ' -1 day' ) );
			}
			$dates_prev = "?startdate={$start_date_prev}&enddate={$end_date_prev}";
			// $net_item_prev_value = Helpers::get_guzzle_response( $api_end_point, '', $dates_prev );
			// $net_item_prev_value = intval( $net_item_prev_value );

			// calculate the percentage for showing in box.
			// ex: (40-30)/30 * 100 = 33%, The percentage increase from 30 to 40 is:
			if ( ( $net_item_value - $net_item_prev_value ) === 0 ) {
				$net_plus_percentage = 0;
			} elseif ( $net_item_value > $net_item_prev_value ) {
				$net_plus_percentage = ( $net_item_value - $net_item_prev_value ) / $net_item_value * 100;
			} elseif ( $net_item_value < $net_item_prev_value ) {
				$net_plus_percentage = ( $net_item_value - $net_item_prev_value ) / $net_item_prev_value * 100;
			}
		}

		return [
			'net_item'             => $net_item_value,
			'item_prev_percentage' => $net_plus_percentage,
		];
	}
}
