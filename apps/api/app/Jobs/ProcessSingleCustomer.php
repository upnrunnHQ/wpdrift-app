<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use App\Customer;
use App\CustomerMeta;

class ProcessSingleCustomer extends Job
{
    public $store_url, $store_id, $access_token, $edd_wp_endpoint, $customer_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store_url, $store_id, $access_token, $edd_wp_endpoint, $customer_id)
    {
        $this->store_url = $store_url;
        $this->store_id = $store_id;
        $this->access_token = $access_token;
        $this->edd_wp_endpoint = $edd_wp_endpoint;
        $this->customer_id = $customer_id;
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
        $edd_custs_api_end_point = '/wp-json/wpdriftio/v1/'.$this->edd_wp_endpoint.'/';
        $url = $this->store_url . $edd_custs_api_end_point;
        $url .= "?task=get_single&id={$this->customer_id}";
        // Guzzle usage
        $gclient = new Client();
        $response = $this->gclient_request_response($url);
        $edd_customer = $response->edd_customers;
        // try catch block to get request and response
        try {
            // insert new customer records.
            $customer = Customer::create(
                [
                    'store_id' => $this->store_id,
                    'id' => $edd_customer[0]->id,
                    'user_id' => $edd_customer[0]->user_id,
                    'email' => $edd_customer[0]->email,
                    'name' => $edd_customer[0]->name,
                    'purchase_value' => $edd_customer[0]->purchase_value,
                    'purchase_count' => $edd_customer[0]->purchase_count,
                    'payment_ids' => $edd_customer[0]->payment_ids,
                    'notes' => $edd_customer[0]->notes,
                    'date_created' => $edd_customer[0]->date_created
                ]
            );
            if($customer) {
                // Save Customer Meta
                $customer_meta_url = $this->store_url . '/wp-json/wpdriftio/v1/getcustomers-metas/?task=get_single&id='.$edd_customer[0]->id;
                $response_c_meta = $this->gclient_request_response($customer_meta_url);
                $edd_customers_metas = $response_c_meta->edd_customers_metas;
                foreach ($edd_customers_metas as $customer_meta) {
                    CustomerMeta::create([
                        'store_id' => $this->store_id,
                        'meta_id' => $customer_meta->meta_id,
                        'customer_id' => $customer_meta->customer_id,
                        'meta_key' => $customer_meta->meta_key,
                        'meta_value' => $customer_meta->meta_value
                    ]);
                }
            }
            \Log::info('Successfully Added Customer using 30 mins sync with ID:'.$edd_customer[0]->id);
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
