<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use App\Discount;
use App\DiscountMeta;

class ProcessSingleDiscount extends Job
{
    public $store_url, $store_id, $access_token, $edd_wp_endpoint, $discount_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store_url, $store_id, $access_token, $edd_wp_endpoint, $discount_id)
    {
        $this->store_url = $store_url;
        $this->store_id = $store_id; 
        $this->access_token = $access_token; 
        $this->edd_wp_endpoint = $edd_wp_endpoint;
        $this->discount_id = $discount_id;
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
        $edd_discnts_api_end_point = '/wp-json/wpdriftio/v1/'.$this->edd_wp_endpoint.'/';
        $url = $this->store_url . $edd_discnts_api_end_point;
        $url .= "?task=get_single&id={$this->discount_id}";
        // Guzzle usage
        $gclient = new Client();
        $response = $this->gclient_request_response($url);
        $edd_discount = $response->edd_discounts;
        // try catch block to get request and response
        try {
            // insert new discounts records.
            Discount::create([
                'store_id' => $this->store_id,
                'ID' => $edd_discount->ID,
                'post_author' => $edd_discount->post_author,
                'post_date' => $edd_discount->post_date,
                'post_content' => $edd_discount->post_content,
                'post_title' => $edd_discount->post_title,
                'post_status' => $edd_discount->post_status,
                'ping_status' => $edd_discount->ping_status,
                'post_password' => $edd_discount->post_password,
                'post_name' => $edd_discount->post_name,
                'to_ping' => $edd_discount->to_ping,
                'pinged' => $edd_discount->pinged,
                'post_modified' => $edd_discount->post_modified,
                'post_content_filtered' => $edd_discount->post_content_filtered,
                'post_parent' => $edd_discount->post_parent,
                'guid' => $edd_discount->guid,
                'menu_order' => $edd_discount->menu_order,
                'comment_count' => $edd_discount->comment_count,
                ]);
            // Save Discount Meta
            $discount_meta_url = $this->store_url . '/wp-json/wpdriftio/v1/getdiscounts-metas/?post_id='.$edd_discount->ID;
            $response_d_meta = $this->gclient_request_response($discount_meta_url);
            $edd_discounts_metas = $response_d_meta->edd_discounts_metas;
            foreach ($edd_discounts_metas as $edd_discount_meta) {
                foreach ($edd_discount_meta as $key => $value) {
                    DiscountMeta::create([
                        'store_id' => $this->store_id,
                        'post_id' => $edd_discount->ID,
                        'meta_key' => $key,
                        'meta_value' => $value
                    ]);
                }
            }
            \Log::info('Successfully Added Discount using 1 Hour sync with ID:'.$edd_discount->ID);
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
