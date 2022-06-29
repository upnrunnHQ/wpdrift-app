<?php

namespace App\Jobs;

use App\EddUserMeta;
use App\EddSiteTotalJobs; // To save total jobs for site
use App\Http\Controllers\GeneralController;

class ProcessEddUserMeta extends Job
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
        $edd_users_metas = $response->edd_users_metas;
        // try catch block to get request and response
        try {
            // first delete old records
            if($this->page == 1) { // delete only when page = 1
                $deleted_users_metas = EddUserMeta::where('store_id', $this->store_id)->delete();
            }
            // insert new records.
            foreach ($edd_users_metas as $user_meta) {
                EddUserMeta::create([
                    'store_id' => $this->store_id,
                    'umeta_id' => $user_meta->umeta_id,
                    'user_id' => $user_meta->user_id,
                    'meta_key' => $user_meta->meta_key,
                    'meta_value' => $user_meta->meta_value
                ]);
            }
            \Log::info('Successfully Added Edd Users Metas for page:'.$this->page.' store id:'.$this->store_id);
            $general_controller->update_edd_site_total_jobs($this->store_id);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            \Log::error($error);
            return response($error, 500);
        }
    }
}
