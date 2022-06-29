<?php
// Modules/EDD/Http/Controllers/CustomersController.php
/**
* For setting up the customers so that customer
* Rest Calls
*/
namespace Modules\EDD\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\EDD\Http\Helpers;

class DashboardController extends Controller {
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware( 'auth' );
	}

	public function stats( Request $request ) {
		return Helpers::remote_get( 'api/dashboard-stats', $request->all() );
	}
}
