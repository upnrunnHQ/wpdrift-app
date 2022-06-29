<?php

namespace App\Jobs;

use App\Payment;
use App\PaymentMeta;
use App\EddSiteTotalJobs; // To save total jobs for site
use App\Http\Controllers\GeneralController;

class ProcessPayment extends Job {

	public $store_url, $store_id, $access_token, $edd_wp_endpoint, $page, $per_page, $offset;
	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct( $store_url, $store_id, $access_token, $edd_wp_endpoint, $page, $per_page, $offset ) {
		$this->store_url       = $store_url;
		$this->store_id        = $store_id;
		$this->access_token    = $access_token;
		$this->edd_wp_endpoint = $edd_wp_endpoint;
		$this->page            = $page;
		$this->per_page        = $per_page;
		$this->offset          = $offset;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle( GeneralController $general_controller ) {
		/**
		  * handling the edd setup job that will setup edd data on lumen db
		  */
		try {
			$this->save_edd_database( $general_controller );
		} catch ( \Exception $e ) {
			\Log::error( $e->getMessage() );
			$error = $e->getMessage();
			return $error;
		}
	}

	/**
	 * Execure the process to add data into mongo db
	 */
	protected function save_edd_database( $general_controller ) {
		$edd_api_end_point = '/wp-json/wpdriftio/v1/' . $this->edd_wp_endpoint . '/';
		$url               = $this->store_url . $edd_api_end_point;
		$url              .= "?per_page={$this->per_page}&offset={$this->offset}";
		$response          = $general_controller->gclient_request_response( $url, $this->access_token );
		$edd_payments      = $response->edd_payments;
		// try catch block to get request and response
		try {
			// first delete old records
			if ( $this->page == 1 ) { // delete only when page = 1
				$deleted_edd_payments       = Payment::where( 'store_id', $this->store_id )->delete();
				$deleted_edd_payments_metas = PaymentMeta::where( 'store_id', $this->store_id )->delete();
			}
			// insert new records.
			foreach ( $edd_payments as $payment ) {
				Payment::create(
					[
						'store_id'              => $this->store_id,
						'ID'                    => $payment->ID,
						'post_author'           => $payment->post_author,
						'post_date'             => $payment->post_date,
						'post_content'          => $payment->post_content,
						'post_title'            => $payment->post_title,
						'post_status'           => $payment->post_status,
						'ping_status'           => $payment->ping_status,
						'post_password'         => $payment->post_password,
						'post_name'             => $payment->post_name,
						'to_ping'               => $payment->to_ping,
						'pinged'                => $payment->pinged,
						'post_modified'         => $payment->post_modified,
						'post_content_filtered' => $payment->post_content_filtered,
						'post_parent'           => $payment->post_parent,
						'guid'                  => $payment->guid,
						'menu_order'            => $payment->menu_order,
						'comment_count'         => $payment->comment_count,
					]
				);
					// Save Discount Meta
					$edd_meta_url       = $this->store_url . '/wp-json/wpdriftio/v1/getpayments-metas/?post_id=' . $payment->ID;
					$response_e_meta    = $general_controller->gclient_request_response( $edd_meta_url, $this->access_token );
					$edd_payments_metas = $response_e_meta->edd_payments_metas;
				foreach ( $edd_payments_metas as $edd_payment_meta ) {
					foreach ( $edd_payment_meta as $key => $value ) {
						PaymentMeta::create(
							[
								'store_id'   => $this->store_id,
								'post_id'    => $payment->ID,
								'meta_key'   => $key,
								'meta_value' => $value,
							]
						);
					}
				}
			}
			\Log::info( 'Successfully Added Payment for page:' . $this->page . ' store id:' . $this->store_id );
			$general_controller->update_edd_site_total_jobs( $this->store_id );
		} catch ( \Exception $e ) {
			$error = $e->getMessage();
			\Log::error( $error );
			return response( $error, 500 );
		}
	}
}
