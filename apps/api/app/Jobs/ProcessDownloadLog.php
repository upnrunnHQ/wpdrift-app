<?php

namespace App\Jobs;

use App\DownloadLog;
use App\EddSiteTotalJobs; // To save total jobs for site
use App\Http\Controllers\GeneralController;

class ProcessDownloadLog extends Job
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
        $edd_downloads_logs = $response->edd_downloads_logs;
        // try catch block to get request and response
        try {
            // first delete old records
            if($this->page == 1) { // delete only when page = 1
                $deleted_downloads_logs = DownloadLog::where('store_id', $this->store_id)->delete();
            }
            // insert new records.
            foreach ($edd_downloads_logs as $download_log) {
                DownloadLog::create([
                    'store_id' => $this->store_id,
                    'ID' => $download_log->ID,
                    'type' => $download_log->type,
                    'user_id' => $download_log->user_id,
                    'user_ip' => $download_log->user_ip,
                    'user_agent' => $download_log->user_agent,
                    'download_id' => $download_log->download_id,
                    'version_id' => $download_log->version_id,
                    'version' => $download_log->version,
                    'download_date' => $download_log->download_date,
                    'download_status' => $download_log->download_status,
                    'download_status_message' => $download_log->download_status_message,
                ]);
            }
            \Log::info('Successfully Added Download Log for page:'.$this->page.' store id:'.$this->store_id);
            $general_controller->update_edd_site_total_jobs($this->store_id);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            \Log::error($error);
            return response($error, 500);
        }
    }
}
