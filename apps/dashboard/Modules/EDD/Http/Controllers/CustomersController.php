<?php
// Modules/EDD/Http/Controllers/CustomersController.php
/** 
* For setting up the customers so that customer
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

class CustomersController extends Controller
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
     * Get all customers
     */
    public function index(Request $request)
    {
        $per_page =  $request->has('per_page') ? (int) $request->per_page : 10;
        $page =  $request->has('page') ? (int) $request->page : 1;
        $orderby =  $request->has('orderby') ? $request->orderby : "date_created";
        $order =  $request->has('order') ? $request->order : "desc";
        $search =  $request->has('search') ? $request->search : "";
        $startdate =  $request->has('startdate') ? $request->startdate : "";
        $enddate =  $request->has('enddate') ? $request->enddate : "";
        if($startdate != "" && $enddate != "") {
            // extract date from start and end dates
            $explode_startdate = explode("T", $startdate);
            $explode_enddate = explode("T", $enddate);
            $startdate =  $explode_startdate[0];
            $enddate = $explode_enddate[0];
        }
        // get the store information
        $api_end_point = "/api/customers/";
        $query_params = "?per_page={$per_page}&page={$page}&orderby={$orderby}&order={$order}&startdate={$startdate}&enddate={$enddate}";
        if($search != "") {
            $query_params .= "&search={$search}";
        }

        // Prepare URL for rest
        $response = Helpers::get_guzzle_response($api_end_point, '', $query_params);
        return response()->json( json_decode($response) );
    }
    /**
     * Get single customer
     */
    public function show($customer_id)
    {
        // get the store information
        $api_end_point = "/api/customer/";
        // Prepare URL for rest
        $response = Helpers::get_guzzle_response($api_end_point, $customer_id);
        return response()->json( json_decode($response) );
    }
    /**
     * Get Total Number of customers with api call
     */
    public function get_total_customers(Request $request, GeneralController $generalController)
    {
        $startdate =  $request->has('startdate') ? $request->startdate : "";
        $enddate =  $request->has('enddate') ? $request->enddate : "";
        $api_end_point = "/api/total_customers/";
        // Prepare URL for rest
        return response()->json( $generalController->get_general_dashboard_sections_data($api_end_point, $startdate, $enddate) );
    }
    /**
     * For getting customers segment call
     */
    public function get_customers_segment()
    {
        // get the store information
        $api_end_point = "/api/customers_segment/";
        // Prepare URL for rest
        $response = Helpers::get_guzzle_response($api_end_point);
        return response()->json( json_decode($response) );
    }

    /**
     * Get get the recent customers
     */
    public function get_events_customers()
    {
        // get the store information
        $api_end_point = "/api/recent_customers/";
        // Prepare URL for rest
        $response = Helpers::get_guzzle_response($api_end_point);
        return response()->json( json_decode($response) );
    }

}
