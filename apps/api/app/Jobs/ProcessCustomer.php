<?php

namespace App\Jobs;

use App\Customer;
use App\EddSiteTotalJobs; // To save total jobs for site
use App\Http\Controllers\GeneralController;

class ProcessCustomer extends Job
{
    public $store_url, $store_id, $access_token, $edd_wp_endpoint, $page, $per_page, $offset;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store_url, $store_id, $access_token, $edd_wp_endpoint, $page, $per_page, $offset)
    {
        $this->store_url = $store_url;
        $this->store_id = $store_id; 
        $this->access_token = $access_token; 
        $this->edd_wp_endpoint = $edd_wp_endpoint;
        $this->page = $page;
        $this->per_page = $per_page;
        $this->offset = $offset;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(GeneralController $general_controller)
    {
        /**
         * handling the edd setup job that will setup edd data on lumen db
         */
        try {
            $this->save_edd_database($general_controller);
        } catch(\Exception $e) {
            \Log::error($e->getMessage());
            $error = $e->getMessage();
            return $error;
        }
    }

    /**
     * Execure the process to add data into mongo db
     */
    protected function save_edd_database($general_controller)
    {
        $edd_dwnloads_api_end_point = '/wp-json/wpdriftio/v1/'.$this->edd_wp_endpoint.'/';
        $url = $this->store_url . $edd_dwnloads_api_end_point;
        $url .= "?per_page={$this->per_page}&offset={$this->offset}";
        $response = $general_controller->gclient_request_response($url, $this->access_token);
        $edd_customers = $response->edd_customers;
        // try catch block to get request and response
        try {
            // first delete old records
            if($this->page == 1) { // delete only when page = 1
                $deleted_customers = Customer::where('store_id', $this->store_id)->delete();
            }
            // insert new records.
            foreach ($edd_customers as $edd_customer) {
                Customer::create([
                    'store_id' => $this->store_id,
                    'id' => $edd_customer->id,
                    'user_id' => $edd_customer->user_id,
                    'email' => $edd_customer->email,
                    'name' => $edd_customer->name,
                    'purchase_value' => $edd_customer->purchase_value,
                    'purchase_count' => $edd_customer->purchase_count,
                    'payment_ids' => $edd_customer->payment_ids,
                    'notes' => $edd_customer->notes,
                    'date_created' => $edd_customer->date_created
                ]);
            }
            \Log::info('Successfully Added Customers for page:'.$this->page.' store id:'.$this->store_id);
            $general_controller->update_edd_site_total_jobs($this->store_id);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            \Log::error($error);
            return response($error, 500);
        }
    }
}
