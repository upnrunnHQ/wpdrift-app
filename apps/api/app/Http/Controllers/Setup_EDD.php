<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
/**
 * Include all jobs
 */
use App\Jobs\ProcessDownload; // for setting up the queue for downloads
use App\Jobs\ProcessCustomer; // for setting up the queue for Customers
use App\Jobs\ProcessCustomerMeta; // for setting up the queue for Customers Meta
use App\Jobs\ProcessDownloadLog; // for setting up the queue for Download Log
use App\Jobs\ProcessLog; // for setting up the queue for Edd Log
use App\Jobs\ProcessDiscount; // for setting up the queue for Discount
use App\Jobs\ProcessPayment; // for setting up the queue for Payment
use App\Jobs\ProcessTermTaxonomy; // for setting up the queue for Term Taxonomy
use App\Jobs\ProcessTermAssigned; // for setting up the queue for Term Assigned
use App\Jobs\ProcessEddUser; // for setting up the queue for Edd Users
use App\Jobs\ProcessEddUserMeta; // for setting up the queue for Edd Users Meta
use App\EddStore; // To save store details
//use App\EddSiteJobsTrack; // To save site specific job ids.
use App\EddSiteTotalJobs; // To save total jobs for site
use App\Jobs\DeleteEddStore; // Handle store removal process

class Setup_EDD extends Controller {

	public function __construct() {
		// add middlefor edd_app
		$this->middleware( 'edd_app' );
	}
	/**
	 * Actual function that will receive setup request and then create a job queues
	 * @param - $request
	 */
	public function setup_edd_store_data( Request $request ) {
		$store_id     = $request->input( 'store_id' );
		$store_url    = $request->input( 'store_url' );
		$access_token = $request->input( 'access_token' );

		if ( $store_id == '' || $store_url == '' || $access_token == '' ) {
			return response( 'Required information missing.', 401 );
		}

		// save store data to edd stores table
		$store_exists = EddStore::where( 'store_id', '=', $store_id )->first();
		if ( $store_exists === null ) {
			// create store
			EddStore::create(
				[
					'store_id'           => $store_id,
					'store_url'          => $store_url,
					'store_access_token' => $access_token,
					'database_sync'      => time(),
				]
			);
		} else {
			// edit store
			EddStore::where( 'store_id', $store_id )
					->update(
						[
							'store_url'          => $store_url,
							'store_access_token' => $access_token,
							'database_sync'      => time(),
						]
					);
		}
		// add record for site total jobs
		EddSiteTotalJobs::firstOrCreate(
			[
				'site_id'    => $store_id,
				'total_jobs' => 0,
			]
		);
		// 1. setup the edd downloads
		$response_downloads = $this->setup_data_edd( $store_url, $store_id, $access_token, 'getdownloads' );
		// 2. setup edd customers
		$response_customers = $this->setup_data_edd( $store_url, $store_id, $access_token, 'getcustomers' );
		// 3. setup edd customers metas
		$response_customers_metas = $this->setup_data_edd( $store_url, $store_id, $access_token, 'getcustomers-metas' );
		// 4. setup edd download logs
		$response_downloads_logs = $this->setup_data_edd( $store_url, $store_id, $access_token, 'getdownloads-logs' );
		// 5. setup edd logs
		$response_eddlogs = $this->setup_data_edd( $store_url, $store_id, $access_token, 'geteddlogs' );
		// 6. setup edd discounts
		$response_discounts = $this->setup_data_edd( $store_url, $store_id, $access_token, 'getdiscounts' );
		// 7. setup edd payments
		$response_payments = $this->setup_data_edd( $store_url, $store_id, $access_token, 'getpayments' );
		// 8. setup edd term taxonomy
		$response_term_taxonomy = $this->setup_data_edd( $store_url, $store_id, $access_token, 'getterm-taxonomy' );
		// 9. setup edd term assigned
		$response_term_assigned = $this->setup_data_term_assigned( $store_url, $store_id, $access_token, 'getterm-assigned' );
		// 10. setup edd users
		$response_edd_users = $this->setup_data_edd( $store_url, $store_id, $access_token, 'getusers' );
		// 11. setup edd users metas
		$response_edd_users_metas = $this->setup_data_edd( $store_url, $store_id, $access_token, 'getusers-metas' );
	}

	/**
	 * Setup the Records as api call
	 * Get api response from wp edd site and send it to Lumen Edd
	 * @param $store_url, $store_id, $access_token
	 * @param $edd_wp_endpoint_new - for retrieving data to end point
	 * return response or error
	 */
	protected function setup_data_edd( $store_url, $store_id, $access_token, $edd_wp_endpoint ) {
		$edd_api_end_point = '/wp-json/wpdriftio/v1/' . $edd_wp_endpoint . '/';
		$per_page          = 100;
		$url               = $store_url . $edd_api_end_point;
		$gclient           = new Client();
		if ( $edd_wp_endpoint != 'getterm-taxonomy' ) {
			$url                .= "?task=get_totals&per_page={$per_page}";
			$request_var         = $gclient->request(
				'GET',
				$url,
				[
					'headers' =>
						[
							'Authorization' => 'Bearer ' . $access_token,
						],
				]
			);
			$gresponse           = $request_var->getBody()->getContents();
			$edd_api_response    = trim( $gresponse );
			$de_edd_api_response = json_decode( $edd_api_response );
		}
		// get total number of jobs
		$edd_total_jobs = $this->get_total_jobs_site( $store_id );
		// switch option for each type of request and job queue.
		switch ( $edd_wp_endpoint ) {
			case 'getdownloads':
				$total_posts = $de_edd_api_response->edd_downloads->found_posts;
				\Log::info( "total-download:{$total_posts} for store id: {$store_id}" );
				$total_pages_count = $de_edd_api_response->edd_downloads->max_num_pages;
				$offset            = 0;
				for ( $i = 1; $i <= $total_pages_count; $i++ ) {
					$job    = new ProcessDownload( $store_url, $store_id, $access_token, $edd_wp_endpoint, $i, $per_page, $offset );
					$job_id = dispatch( $job->onQueue( 'download' ) );
					$offset = $offset + $per_page;
					$this->update_total_job_add_job( $store_id, $job_id, 'download', $edd_total_jobs );
				}
				break;
			case 'getcustomers':
				$total_posts       = $de_edd_api_response->edd_customers->found_posts;
				$total_pages_count = $de_edd_api_response->edd_customers->max_num_pages;
				\Log::info( "total-customer:{$total_posts} for store id: {$store_id}" );
				$offset = 0;
				for ( $i = 1; $i <= $total_pages_count; $i++ ) {
					$job    = new ProcessCustomer( $store_url, $store_id, $access_token, $edd_wp_endpoint, $i, $per_page, $offset );
					$job_id = dispatch( $job->onQueue( 'customer' ) );
					$offset = $offset + $per_page;
					$this->update_total_job_add_job( $store_id, $job_id, 'customer', $edd_total_jobs );
				}
				break;
			case 'getcustomers-metas':
				$total_posts = $de_edd_api_response->edd_customers_metas->found_posts;
				\Log::info( "total-customer-meta:{$total_posts} for store id: {$store_id}" );
				$total_pages_count = $de_edd_api_response->edd_customers_metas->max_num_pages;
				$offset            = 0;
				for ( $i = 1; $i <= $total_pages_count; $i++ ) {
					$job    = new ProcessCustomerMeta( $store_url, $store_id, $access_token, $edd_wp_endpoint, $i, $per_page, $offset );
					$job_id = dispatch( $job->onQueue( 'customer-meta' ) );
					$offset = $offset + $per_page;
					$this->update_total_job_add_job( $store_id, $job_id, 'customer-meta', $edd_total_jobs );
				}
				break;
			//case 'getdownloads-logs':
				// $total_posts = $de_edd_api_response->edd_downloads_logs->found_posts;
				// \Log::info("total-downloads-logs:{$total_posts} for store id: {$store_id}");
				// $total_pages_count = $de_edd_api_response->edd_downloads_logs->max_num_pages;
				// $offset = 0;
				// for ($i=1; $i <= $total_pages_count ; $i++) {
				//     $job = new ProcessDownloadLog($store_url, $store_id, $access_token, $edd_wp_endpoint, $i, $per_page, $offset);
				//     dispatch($job->onQueue('download-log'));
				//     $offset = $offset + $per_page;
				// }
				// break;
			case 'geteddlogs':
				$total_posts = $de_edd_api_response->edd_logs->found_posts;
				\Log::info( "total-edd-logs:{$total_posts} for store id: {$store_id}" );
				$total_pages_count = $de_edd_api_response->edd_logs->max_num_pages;
				$offset            = 0;
				for ( $i = 1; $i <= $total_pages_count; $i++ ) {
					$job    = new ProcessLog( $store_url, $store_id, $access_token, $edd_wp_endpoint, $i, $per_page, $offset );
					$job_id = dispatch( $job->onQueue( 'eddlog' ) );
					$offset = $offset + $per_page;
					$this->update_total_job_add_job( $store_id, $job_id, 'eddlog', $edd_total_jobs );
				}
				break;
			case 'getdiscounts':
				$total_posts = $de_edd_api_response->edd_discounts->found_posts;
				\Log::info( "total-discounts:{$total_posts} for store id: {$store_id}" );
				$total_pages_count = $de_edd_api_response->edd_discounts->max_num_pages;
				$offset            = 0;
				for ( $i = 1; $i <= $total_pages_count; $i++ ) {
					$job    = new ProcessDiscount( $store_url, $store_id, $access_token, $edd_wp_endpoint, $i, $per_page, $offset );
					$job_id = dispatch( $job->onQueue( 'discount' ) );
					$offset = $offset + $per_page;
					$this->update_total_job_add_job( $store_id, $job_id, 'discount', $edd_total_jobs );
				}
				break;
			case 'getpayments':
				$total_posts = $de_edd_api_response->edd_payments->found_posts;
				\Log::info( "total-payment:{$total_posts} for store id: {$store_id}" );
				$total_pages_count = $de_edd_api_response->edd_payments->max_num_pages;
				$offset            = 0;
				for ( $i = 1; $i <= $total_pages_count; $i++ ) {
					$job    = new ProcessPayment( $store_url, $store_id, $access_token, $edd_wp_endpoint, $i, $per_page, $offset );
					$job_id = dispatch( $job->onQueue( 'payment' ) );
					$offset = $offset + $per_page;
					$this->update_total_job_add_job( $store_id, $job_id, 'payment', $edd_total_jobs );
				}
				break;
			case 'getterm-taxonomy':
				$job    = new ProcessTermTaxonomy( $store_url, $store_id, $access_token, $edd_wp_endpoint );
				$job_id = dispatch( $job->onQueue( 'term-taxonomy' ) );
				$this->update_total_job_add_job( $store_id, $job_id, 'term-taxonomy', $edd_total_jobs );
				break;
			case 'getusers':
				$total_posts       = $de_edd_api_response->edd_users->found_posts;
				$total_pages_count = $de_edd_api_response->edd_users->max_num_pages;
				\Log::info( "total-users:{$total_posts} for store id: {$store_id}" );
				$offset = 0;
				for ( $i = 1; $i <= $total_pages_count; $i++ ) {
					$job    = new ProcessEddUser( $store_url, $store_id, $access_token, $edd_wp_endpoint, $i, $per_page, $offset );
					$job_id = dispatch( $job->onQueue( 'edduser' ) );
					$offset = $offset + $per_page;
					$this->update_total_job_add_job( $store_id, $job_id, 'edduser', $edd_total_jobs );
				}
				break;
			case 'getusers-metas':
				$total_posts = $de_edd_api_response->edd_users_metas->found_posts;
				\Log::info( "total-users-meta:{$total_posts} for store id: {$store_id}" );
				$total_pages_count = $de_edd_api_response->edd_users_metas->max_num_pages;
				$offset            = 0;
				for ( $i = 1; $i <= $total_pages_count; $i++ ) {
					$job    = new ProcessEddUserMeta( $store_url, $store_id, $access_token, $edd_wp_endpoint, $i, $per_page, $offset );
					$job_id = dispatch( $job->onQueue( 'edduser-meta' ) );
					$offset = $offset + $per_page;
					$this->update_total_job_add_job( $store_id, $job_id, 'edduser-meta', $edd_total_jobs );
				}
				break;
		}
	}
	/**
	 * Setup the Records as products on edd
	 * Get api response from wp edd site and send it to Lumen Edd
	 * @param $store_url, $store_id, $access_token
	 * @param $edd_wp_endpoint_new - for retrieving data to end point
	 * return response or error
	 */
	protected function setup_data_term_assigned( $store_url, $store_id, $access_token, $edd_wp_endpoint ) {
		 $edd_term_tax_point = '/wp-json/wpdriftio/v1/getterm-taxonomy/';
		$url                 = $store_url . $edd_term_tax_point;
		$per_page            = 100;
		$gclient             = new Client();
		$response            = $this->gclient_request_response( $url, $access_token );
		$edd_term_taxonomy   = $response->edd_term_taxonomy;
		// try catch block to get request and response
		try {
			// insert new records.
			foreach ( $edd_term_taxonomy as $term_taxonomy ) {
				$url_assigned        = $store_url . '/wp-json/wpdriftio/v1/' . $edd_wp_endpoint . '/';
				$url_assigned       .= "?task=get_totals&term_id={$term_taxonomy->term_id}";
				$request_var         = $gclient->request(
					'GET',
					$url_assigned,
					[
						'headers' =>
							[
								'Authorization' => 'Bearer ' . $access_token,
							],
					]
				);
				$gresponse           = $request_var->getBody()->getContents();
				$edd_api_response    = trim( $gresponse );
				$de_edd_api_response = json_decode( $edd_api_response );
				$total_posts         = $de_edd_api_response->edd_term_assigned->found_posts;
				$total_pages_count   = $de_edd_api_response->edd_term_assigned->max_num_pages;
				// get total number of jobs
				$edd_total_jobs = $this->get_total_jobs_site( $store_id );
				$offset         = 0;
				for ( $i = 1; $i <= $total_pages_count; $i++ ) {
					$job    = new ProcessTermAssigned( $store_url, $store_id, $access_token, $edd_wp_endpoint, $i, $per_page, $offset, $term_taxonomy->term_id );
					$job_id = dispatch( $job->onQueue( 'term-assigned' ) );
					$offset = $offset + $per_page;
					$this->update_total_job_add_job( $store_id, $job_id, 'term-assigned', $edd_total_jobs );
				}
			}
		} catch ( \Exception $e ) {
			$error = $e->getMessage();
			\Log::error( $error );
			return response( $error, 500 );
		}
	}

	/**
	 * Update the store information like access token
	 */
	public function update_edd_store( Request $request ) {
		$store_id     = $request->input( 'store_id' );
		$store_url    = $request->input( 'store_url' );
		$access_token = $request->input( 'access_token' );

		if ( $store_id == '' || $store_url == '' || $access_token == '' ) {
			return response( 'Required information missing.', 401 );
		}

		// save store data to edd stores table
		$store_exists = EddStore::where( 'store_id', '=', $store_id )
						->first();
		if ( $store_exists ) {
			// edit store
			EddStore::where( 'store_id', $store_id )
					->update(
						[
							'store_url'          => $store_url,
							'store_access_token' => $access_token,
						]
					);
		}
	}

	/**
	 * Delete store data upon store deletion from app
	 */
	public function store_delete( Request $request ) {
		$store_id     = $request->input( 'store_id' );
		$store_url    = $request->input( 'store_url' );
		$access_token = $request->input( 'access_token' );
		if ( $store_id == '' || $store_url == '' || $access_token == '' ) {
			return response( 'Required information missing.', 401 );
		}

		// save store data to edd stores table
		$store_exists = EddStore::where( 'store_id', '=', $store_id )
						->first();
		if ( $store_exists ) {
			// remove all datarecords from table for this site.
			$job = new DeleteEddStore( $store_url, $store_id, $access_token );
			dispatch( $job->onQueue( 'default' ) );
		}
	}

	/**
	 * Get total jobs for particular site or store
	 */
	protected function get_total_jobs_site( $store_id ) {
		// get total number of jobs
		$edd_total_jobs_obj = EddSiteTotalJobs::where( 'site_id', $store_id )->first();
		if ( $edd_total_jobs_obj ) {
			$edd_total_jobs = $edd_total_jobs_obj->total_jobs;
		} else {
			$edd_total_jobs = 0;
		}
		return $edd_total_jobs;
	}

	/**
	 * Add Job record and update the total job for site or store
	 */
	protected function update_total_job_add_job( $store_id, $job_id, $queue_type, $edd_total_jobs ) {
		// add queue job record
		// EddSiteJobsTrack::create([
		//     'site_id' => $store_id,
		//     'job_id' => $job_id,
		//     'queue_type' => $queue_type
		// ]);
		// update the total jobs count for site
		EddSiteTotalJobs::where( 'site_id', $store_id )->update(
			[
				'total_jobs' => $edd_total_jobs + 1,
			]
		);
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
