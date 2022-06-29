<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use App\Payment;
use App\PaymentMeta;

class ProcessSinglePayment extends Job
{
    public $store_url, $store_id, $access_token, $edd_wp_endpoint, $payment_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store_url, $store_id, $access_token, $edd_wp_endpoint, $payment_id)
    {
        $this->store_url = $store_url;
        $this->store_id = $store_id;
        $this->access_token = $access_token;
        $this->edd_wp_endpoint = $edd_wp_endpoint;
        $this->payment_id = $payment_id;
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
        $edd_payments_api_end_point = '/wp-json/wpdriftio/v1/'.$this->edd_wp_endpoint.'/';
        $url = $this->store_url . $edd_payments_api_end_point;
        $url .= "?task=get_single&id={$this->payment_id}";
        // Guzzle usage
        $gclient = new Client();
        $response = $this->gclient_request_response($url);
        $edd_payment = $response->edd_payments;
        // try catch block to get request and response
        try {
            // insert new payments records.
            Payment::create([
                'store_id' => $this->store_id,
                'ID' => $edd_payment->ID,
                'post_author' => $edd_payment->post_author,
                'post_date' => $edd_payment->post_date,
                'post_content' => $edd_payment->post_content,
                'post_title' => $edd_payment->post_title,
                'post_status' => $edd_payment->post_status,
                'ping_status' => $edd_payment->ping_status,
                'post_password' => $edd_payment->post_password,
                'post_name' => $edd_payment->post_name,
                'to_ping' => $edd_payment->to_ping,
                'pinged' => $edd_payment->pinged,
                'post_modified' => $edd_payment->post_modified,
                'post_content_filtered' => $edd_payment->post_content_filtered,
                'post_parent' => $edd_payment->post_parent,
                'guid' => $edd_payment->guid,
                'menu_order' => $edd_payment->menu_order,
                'comment_count' => $edd_payment->comment_count,
                ]);
            // Save payment Meta
            $payment_meta_url = $this->store_url . '/wp-json/wpdriftio/v1/getpayments-metas/?post_id='.$edd_payment->ID;
            $response_d_meta = $this->gclient_request_response($payment_meta_url);
            $edd_payments_metas = $response_d_meta->edd_payments_metas;
            foreach ($edd_payments_metas as $edd_payment_meta) {
                foreach ($edd_payment_meta as $key => $value) {
                    PaymentMeta::create([
                        'store_id' => $this->store_id,
                        'post_id' => $edd_payment->ID,
                        'meta_key' => $key,
                        'meta_value' => $value
                    ]);
                }
            }
            \Log::info('Successfully Added Payment using 1 Hour sync with ID:'.$edd_payment->ID.' for store id: '.$this->store_id);
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
