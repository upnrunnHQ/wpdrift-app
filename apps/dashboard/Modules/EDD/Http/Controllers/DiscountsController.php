<?php
// Modules/EDD/Http/Controllers/DiscountsController.php
/** 
* For serving products/downloads related end points
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

class DiscountsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Get get the total discounts
     */
    public function get_total_discounts(Request $request, GeneralController $generalController)
    {
        $startdate =  $request->has('startdate') ? $request->startdate : "";
        $enddate =  $request->has('enddate') ? $request->enddate : "";
        // get the store information
        $api_end_point = "/api/total_discounts/";
        return response()->json( $generalController->get_general_dashboard_sections_data($api_end_point, $startdate, $enddate) );
    }
    /**
     * Get Discounts
     */
    public function get_discounts(Request $request)
    {
        $per_page =  $request->has('per_page') ? (int) $request->per_page : 10;
        $page =  $request->has('page') ? (int) $request->page : 1;
        // get the store information
        $api_end_point = "/api/discounts/";
        // Prepare URL for rest
        $response = Helpers::get_guzzle_response($api_end_point, '', "?per_page={$per_page}&page={$page}");
        return response()->json( json_decode($response) );
    }
    /**
     * For get total no. discounts
     */
    public function get_total_number_discounts(Request $request, GeneralController $generalController)
    {
        $startdate =  $request->has('startdate') ? $request->startdate : "";
        $enddate =  $request->has('enddate') ? $request->enddate : "";
        // Prepare URL for rest
        $api_end_point = "/api/total_number_discounts/";
        return response()->json( $generalController->get_general_dashboard_sections_data($api_end_point, $startdate, $enddate) );
    }
}
