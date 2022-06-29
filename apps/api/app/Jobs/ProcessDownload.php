<?php

namespace App\Jobs;

use App\Download;
use App\DownloadMeta;
use App\EddSiteTotalJobs; // To save total jobs for site
use App\Http\Controllers\GeneralController;

class ProcessDownload extends Job
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
        // Get API Reponse
        $response = $general_controller->gclient_request_response($url, $this->access_token);
        $edd_downloads = $response->edd_downloads;
        // try catch block to get request and response
        try {
            // first delete old downloads records
            if($this->page == 1) { // delete only when page = 1
                $deleted_downloads = Download::where('store_id', $this->store_id)->delete();
                $deleted_downloads_metas = DownloadMeta::where('store_id', $this->store_id)->delete();
            }
            // insert new downloads records.
            foreach ($edd_downloads as $edd_download) {
                Download::create([
                        'store_id' => $this->store_id,
                        'post_id' => $edd_download->ID,
                        'post_author' => $edd_download->post_author,
                        'post_date' => $edd_download->post_date,
                        'post_content' => $edd_download->post_content,
                        'post_title' => $edd_download->post_title,
                        'post_status' => $edd_download->post_status,
                        'ping_status' => $edd_download->ping_status,
                        'post_password' => $edd_download->post_password,
                        'post_name' => $edd_download->post_name,
                        'to_ping' => $edd_download->to_ping,
                        'pinged' => $edd_download->pinged,
                        'post_modified' => $edd_download->post_modified,
                        'post_content_filtered' => $edd_download->post_content_filtered,
                        'post_parent' => $edd_download->post_parent,
                        'guid' => $edd_download->guid,
                        'menu_order' => $edd_download->menu_order,
                        'comment_count' => $edd_download->comment_count,
                    ]);
                    // Save Download Meta
                    $download_meta_url = $this->store_url . '/wp-json/wpdriftio/v1/getdownloads-metas/?post_id='.$edd_download->ID;
                    $response_d_meta = $general_controller->gclient_request_response($download_meta_url, $this->access_token);
                    $edd_downloads_metas = $response_d_meta->edd_downloads_metas;
                    foreach ($edd_downloads_metas as $edd_download_meta) {
                        foreach ($edd_download_meta as $key => $value) {
                            DownloadMeta::create([
                                'store_id' => $this->store_id,
                                'post_id' => $edd_download->ID,
                                'meta_key' => $key,
                                'meta_value' => $value
                            ]);
                        }
                    }
            }
            \Log::info('Successfully Added Downloads for page:'.$this->page.' store id:'.$this->store_id);
            $general_controller->update_edd_site_total_jobs($this->store_id);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            \Log::error($error);
            return response($error, 500);
        }
    }
}
