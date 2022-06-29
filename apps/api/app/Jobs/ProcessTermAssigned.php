<?php

namespace App\Jobs;

use App\TermAssigned;
use App\EddSiteTotalJobs; // To save total jobs for site
use App\Http\Controllers\GeneralController;

class ProcessTermAssigned extends Job
{
    public $store_url, $store_id, $access_token, $edd_wp_endpoint, $page, $per_page, $offset, $term_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store_url, $store_id, $access_token, $edd_wp_endpoint, $page, $per_page, $offset, $term_id)
    {
        $this->store_url = $store_url;
        $this->store_id = $store_id; 
        $this->access_token = $access_token; 
        $this->edd_wp_endpoint = $edd_wp_endpoint;
        $this->page = $page;
        $this->per_page = $per_page;
        $this->offset = $offset;
        $this->term_id = $term_id;
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
        $url .= "?per_page={$this->per_page}&offset={$this->offset}&term_id={$this->term_id}";
        $response = $general_controller->gclient_request_response($url, $this->access_token);
        $terms_assigned = $response->edd_term_assigned;
        // try catch block to get request and response
        try {
            // first delete old downloads records
            if($this->page == 1) { // delete only when page = 1
                $deleted_downloads = TermAssigned::where('store_id', $this->store_id)->delete();
            }
            // insert new downloads records.
            foreach ($terms_assigned as $term_assigned) {
                TermAssigned::create([
                        'store_id' => $this->store_id,
                        'object_id' => $term_assigned->object_id,
                        'term_taxonomy_id' => $term_assigned->term_taxonomy_id,
                        'term_order' => $term_assigned->term_order
                    ]);
            }
            \Log::info('Successfully Added Term Assigned for term id:'.$this->term_id.' store id:'.$this->store_id);
            $general_controller->update_edd_site_total_jobs($this->store_id);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            \Log::error($error);
            return response($error, 500);
        }
    }
}
