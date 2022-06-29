<?php

namespace App\Jobs;

use App\EddUser;
use App\EddSiteTotalJobs; // To save total jobs for site
use App\Http\Controllers\GeneralController;

class ProcessEddUser extends Job
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
        $edd_users = $response->edd_users;
        // try catch block to get request and response
        try {
            // first delete old records
            if($this->page == 1) { // delete only when page = 1
                $deleted_users = EddUser::where('store_id', $this->store_id)->delete();
            }
            // insert new records.
            foreach ($edd_users as $edd_user) {
                EddUser::create([
                    'store_id' => $this->store_id,
                    'ID' => $edd_user->ID,
                    'user_login' => $edd_user->user_login,
                    'user_pass' => $edd_user->user_pass,
                    'user_nicename' => $edd_user->user_nicename,
                    'user_email' => $edd_user->user_email,
                    'user_url' => $edd_user->user_url,
                    'user_registered' => $edd_user->user_registered,
                    'user_activation_key' => $edd_user->user_activation_key,
                    'user_status' => $edd_user->user_status,
                    'display_name' => $edd_user->display_name
                ]);
            }
            \Log::info('Successfully Added Edd Users for page:'.$this->page.' store id:'.$this->store_id);
            $general_controller->update_edd_site_total_jobs($this->store_id);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            \Log::error($error);
            return response($error, 500);
        }
    }
}
