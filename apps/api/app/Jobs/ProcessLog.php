<?php

namespace App\Jobs;

use App\EddLog;
use App\EddLogMeta;
use App\EddSiteTotalJobs; // To save total jobs for site
use App\Http\Controllers\GeneralController;

class ProcessLog extends Job
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
        $edd_logs = $response->edd_logs;
        // try catch block to get request and response
        try {
            // first delete old downloads records
            if($this->page == 1) { // delete only when page = 1
                $deleted_edd_logs = EddLog::where('store_id', $this->store_id)->delete();
                $deleted_edd_logs_metas = EddLogMeta::where('store_id', $this->store_id)->delete();
            }
            // insert new downloads records.
            foreach ($edd_logs as $eddlog) {
                EddLog::create([
                        'store_id' => $this->store_id,
                        'ID' => $eddlog->ID,
                        'post_author' => $eddlog->post_author,
                        'post_date' => $eddlog->post_date,
                        'post_content' => $eddlog->post_content,
                        'post_title' => $eddlog->post_title,
                        'post_status' => $eddlog->post_status,
                        'ping_status' => $eddlog->ping_status,
                        'post_password' => $eddlog->post_password,
                        'post_name' => $eddlog->post_name,
                        'to_ping' => $eddlog->to_ping,
                        'pinged' => $eddlog->pinged,
                        'post_modified' => $eddlog->post_modified,
                        'post_content_filtered' => $eddlog->post_content_filtered,
                        'post_parent' => $eddlog->post_parent,
                        'guid' => $eddlog->guid,
                        'menu_order' => $eddlog->menu_order,
                        'comment_count' => $eddlog->comment_count,
                    ]);
                    // Save Edd Log Meta
                    $edd_meta_url = $this->store_url . '/wp-json/wpdriftio/v1/geteddlogs-metas/?post_id='.$eddlog->ID;
                    $response_e_meta = $general_controller->gclient_request_response($edd_meta_url, $this->access_token);
                    $edd_logs_metas = $response_e_meta->edd_logs_metas;
                    foreach ($edd_logs_metas as $edd_download_meta) {
                        foreach ($edd_download_meta as $key => $value) {
                            EddLogMeta::create([
                                'store_id' => $this->store_id,
                                'post_id' => $eddlog->ID,
                                'meta_key' => $key,
                                'meta_value' => $value
                            ]);
                        }
                    }
            }
            \Log::info('Successfully Added Edd Log for page:'.$this->page.' store id:'.$this->store_id);
            $general_controller->update_edd_site_total_jobs($this->store_id);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            \Log::error($error);
            return response($error, 500);
        }
    }
}
