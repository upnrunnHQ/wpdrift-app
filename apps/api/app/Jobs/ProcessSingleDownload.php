<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use App\Download;
use App\DownloadMeta;

class ProcessSingleDownload extends Job
{
    public $store_url, $store_id, $access_token, $edd_wp_endpoint, $download_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store_url, $store_id, $access_token, $edd_wp_endpoint, $download_id)
    {
        $this->store_url = $store_url;
        $this->store_id = $store_id;
        $this->access_token = $access_token;
        $this->edd_wp_endpoint = $edd_wp_endpoint;
        $this->download_id = $download_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * handling the edd setup job that will setup edd data on lumen db
         */
        try {
            $this->save_edd_database();
        } catch(\Exception $e) {
            \Log::error($e->getMessage());
            $error = $e->getMessage();
            return $error;
        }
    }

    /**
     * Execure the process to add data into mongo db
     */
    protected function save_edd_database()
    {
        $edd_dwnloads_api_end_point = '/wp-json/wpdriftio/v1/'.$this->edd_wp_endpoint.'/';
        $url = $this->store_url . $edd_dwnloads_api_end_point;
        $url .= "?task=get_single&id={$this->download_id}";
        // Guzzle usage
        $gclient = new Client();
        $response = $this->gclient_request_response($url);
        $edd_download = $response->edd_downloads;
        // try catch block to get request and response
        try {
            // insert new downloads records.
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
            $response_d_meta = $this->gclient_request_response($download_meta_url);
            $edd_downloads_metas = $response_d_meta->edd_downloads_metas;
            //\Log::info(print_r($edd_downloads_metas,1));
            foreach ($edd_downloads_metas as $edd_download_meta) {
                foreach ($edd_download_meta as $key => $value) {
                    DownloadMeta::create([
                        'store_id' => $this->store_id,
                        'post_id' => (string) $edd_download->ID,
                        'meta_key' => $key,
                        'meta_value' => $value
                    ]);
                }
            }
            \Log::info('Successfully Added Download using 30 mins sync with ID:'.$edd_download->ID);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            \Log::error($error);
            return response($error, 500);
        }
    }
    /**
     * General cURL function
     */
    protected function gclient_request_response($url)
    {
        $gclient = new Client();
        $request_var = $gclient->request(
            'GET',
            $url,
            [
                'headers' =>
                    [
                        'Authorization' => 'Bearer ' . $this->access_token
                    ]
            ]
        );

        $gresponse = $request_var->getBody()->getContents();
        $edd_api_response = trim($gresponse);
        $req_jsn_decode = json_decode($edd_api_response);
        return $req_jsn_decode;
    }
}
