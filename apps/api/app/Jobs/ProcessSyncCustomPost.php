<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use App\Discount;
use App\DiscountMeta;

class ProcessSyncCustomPostMeta extends Job
{
    public $store_url, $store_id, $access_token, $edd_wp_endpoint, $post_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store_url, $store_id, $access_token, $edd_wp_endpoint, $post_id)
    {
        $this->store_url = $store_url;
        $this->store_id = $store_id; 
        $this->access_token = $access_token; 
        $this->edd_wp_endpoint = $edd_wp_endpoint;
        $this->post_id = $post_id;
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
        // // Guzzle usage
        $gclient = new Client();
        // try catch block to get request and response
        try {
            // first delete old records
            $deleted_edd_discounts_metas = DiscountMeta::where('store_id', $this->store_id)->delete();
            
            // Save Discount Meta
            $edd_meta_url = $this->store_url . '/wp-json/wpdriftio/v1/'.$this->edd_wp_endpoint.'/?post_id='.$this->post_id;
            $response_e_meta = $this->gclient_request_response($edd_meta_url);
            $edd_discounts_metas = $response_e_meta->edd_discounts_metas;
            foreach ($edd_discounts_metas as $edd_discount_meta) {
                foreach ($edd_discount_meta as $key => $value) {
                    DiscountMeta::create([
                        'store_id' => $this->store_id,
                        'post_id' => $this->post_id,
                        'meta_key' => $key,
                        'meta_value' => $value
                    ]);
                }
            }
            \Log::info('Successfully Added Discount Meta for Discount ID: '.$this->post_id);
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
