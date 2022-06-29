<?php

namespace App\Jobs;

use App\Discount;
use App\DiscountMeta;
use App\EddSiteTotalJobs; // To save total jobs for site
use App\Http\Controllers\GeneralController;

class ProcessDiscount extends Job
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
        $edd_api_end_point = '/wp-json/wpdriftio/v1/'.$this->edd_wp_endpoint.'/';
        $url = $this->store_url . $edd_api_end_point;
        $url .= "?per_page={$this->per_page}&offset={$this->offset}";
        $response = $general_controller->gclient_request_response($url, $this->access_token);
        $edd_discounts = $response->edd_discounts;
        // try catch block to get request and response
        try {
            // first delete old records
            if($this->page == 1) { // delete only when page = 1
                $deleted_edd_discounts = Discount::where('store_id', $this->store_id)->delete();
                $deleted_edd_discounts_metas = DiscountMeta::where('store_id', $this->store_id)->delete();
            }
            // insert new records.
            foreach ($edd_discounts as $discount) {
                Discount::create([
                        'store_id' => $this->store_id,
                        'ID' => $discount->ID,
                        'post_author' => $discount->post_author,
                        'post_date' => $discount->post_date,
                        'post_content' => $discount->post_content,
                        'post_title' => $discount->post_title,
                        'post_status' => $discount->post_status,
                        'ping_status' => $discount->ping_status,
                        'post_password' => $discount->post_password,
                        'post_name' => $discount->post_name,
                        'to_ping' => $discount->to_ping,
                        'pinged' => $discount->pinged,
                        'post_modified' => $discount->post_modified,
                        'post_content_filtered' => $discount->post_content_filtered,
                        'post_parent' => $discount->post_parent,
                        'guid' => $discount->guid,
                        'menu_order' => $discount->menu_order,
                        'comment_count' => $discount->comment_count,
                    ]);
                    // Save Discount Meta
                    $edd_meta_url = $this->store_url . '/wp-json/wpdriftio/v1/getdiscounts-metas/?post_id='.$discount->ID;
                    $response_e_meta = $general_controller->gclient_request_response($edd_meta_url, $this->access_token);
                    $edd_discounts_metas = $response_e_meta->edd_discounts_metas;
                    foreach ($edd_discounts_metas as $edd_discount_meta) {
                        foreach ($edd_discount_meta as $key => $value) {
                            DiscountMeta::create([
                                'store_id' => $this->store_id,
                                'post_id' => $discount->ID,
                                'meta_key' => $key,
                                'meta_value' => $value
                            ]);
                        }
                    }
            }
            \Log::info('Successfully Added Discount for page:'.$this->page.' store id:'.$this->store_id);
            $general_controller->update_edd_site_total_jobs($this->store_id);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            \Log::error($error);
            return response($error, 500);
        }
    }
}
