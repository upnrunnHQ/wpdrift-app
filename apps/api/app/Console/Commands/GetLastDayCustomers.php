<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\EddStore;
use App\Jobs\ProcessSingleCustomer;
use App\Customer;

class GetLastDayCustomers extends Command {

	/*
	* The name and signature of the console command.
	*
	* @var string
	*/
	protected $signature = 'GetLastDayCustomers:getcustomers';

	/**
	* The console command description.
	*
	* @var string
	*/
	protected $description = 'Get Last Day Customers';

	/**
	* Create a new command instance.
	*
	* @return void
	*/
	public function __construct() {
		 parent::__construct();
	}

	/**
	* Execute the console command.
	*
	* @return mixed
	*/
	public function handle() {
		$all_stores = EddStore::where( 'store_id', '!=', '' )->get();
		foreach ( $all_stores as $single_store ) {
			// get the customers from store.
			$url          = $single_store->store_url;
			$id           = $single_store->store_id;
			$access_token = $single_store->store_access_token;
			$end_point    = 'wp-json/wpdriftio/v1/getdaycustomers';
			try {
				$customers_id = $this->gclient_request_response( $url . '/' . $end_point, $access_token );
				if ( $customers_id ) {
					foreach ( $customers_id->edd_customers as $customer_id ) {
						// first check that is customer exits with id
						$customer_exists = Customer::where( 'store_id', $id )
						->where( 'id', $customer_id->id )->exists();
						if ( ! $customer_exists ) {
							$job = new ProcessSingleCustomer( $url, $id, $access_token, 'getcustomers', $customer_id->id );
							dispatch( $job->onQueue( 'customer' ) );
						}
					}
				}
			} catch ( \Exception $e ) {
				$error = $e->getMessage();
				\Log::error( $error );
			}
		}
	}
	/**
	 * General cURL function
	 */
	protected function gclient_request_response( $url, $access_token ) {
		$gclient          = new Client();
		$request_var      = $gclient->request(
			'GET',
			$url,
			[
				'headers' =>
					[
						'Authorization' => 'Bearer ' . $access_token,
					],
			]
		);
		$gresponse        = $request_var->getBody()->getContents();
		$edd_api_response = trim( $gresponse );
		$req_jsn_decode   = json_decode( $edd_api_response );
		return $req_jsn_decode;
	}
}
