<?php

namespace App\Jobs;

use App\TermTaxonomy;
use App\EddSiteTotalJobs; // To save total jobs for site
use App\Http\Controllers\GeneralController;

class ProcessTermTaxonomy extends Job
{
    public $store_url, $store_id, $access_token, $edd_wp_endpoint;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store_url, $store_id, $access_token, $edd_wp_endpoint)
    {
        $this->store_url = $store_url;
        $this->store_id = $store_id; 
        $this->access_token = $access_token; 
        $this->edd_wp_endpoint = $edd_wp_endpoint;
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
        $edd_api_end_point = '/wp-json/wpdriftio/v1/'.$this->edd_wp_endpoint.'/';
        $url = $this->store_url . $edd_api_end_point;
        $response = $general_controller->gclient_request_response($url, $this->access_token);
        $edd_term_taxonomy = $response->edd_term_taxonomy;
        // try catch block to get request and response
        try {
            // first delete old records
            $deleted_term_taxonomy = TermTaxonomy::where('store_id', $this->store_id)->delete();
            // insert new records.
            foreach ($edd_term_taxonomy as $term_taxonomy) {
                TermTaxonomy::create([
                    'store_id' => $this->store_id,
                    'term_taxonomy_id' => $term_taxonomy->term_taxonomy_id,
                    'term_id' => $term_taxonomy->term_id,
                    'name' => $term_taxonomy->name,
                    'slug' => $term_taxonomy->slug,
                    'taxonomy' => $term_taxonomy->taxonomy,
                    'description' => $term_taxonomy->description,
                    'parent' => $term_taxonomy->parent,
                    'count' => $term_taxonomy->count
                ]);
            }
            \Log::info('Successfully Added All Term Taxonomies for store id:'.$this->store_id);
            $general_controller->update_edd_site_total_jobs($this->store_id);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            \Log::error($error);
            return response($error, 500);
        }
    }
}
