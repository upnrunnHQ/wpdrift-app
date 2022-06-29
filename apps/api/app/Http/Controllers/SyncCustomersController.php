<?php
/**
 * Sync Customers Controller
 * This will have all methods to synchronize the customers data with wp site.
 * REST End Points
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Customer;
use App\CustomerMeta;
use App\EddStore;

class SyncCustomersController extends Controller
{
    public function __construct()
    {
        // add middlefor edd_app
        //$this->middleware('edd_app');
    }

    /**
     * For serving webook of customer add call and call rest api for customers
     * data update like customers and customer meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function customer_create(Request $request)
    {
        $user_agent = $request->header('user-agent');
        $expld_user_agent = explode(" ", $user_agent);
        if(!empty($expld_user_agent)) {
            $store_url = $expld_user_agent[1];
            
            if($store_url == "") {
                return response('Required information missing.', 401);
            }

            // save store data to edd stores table
            $store_exists = EddStore::where('store_url', 'like', $store_url)
                        ->first();
            if($store_exists && $request->post_id != "") {
                // then update the customer
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // send api call to get customer details by id
                $response = $this->setup_data_edd($store_url, $store_id, $store_access_token, 'getcustomers', $request->post_id);
                $created_customer = $response->edd_customers[0];
                if($created_customer) {
                    $customer = Customer::where('store_id', '=', $store_id)
                                ->create(
                                        [
                                            'id' => $created_customer->id,
                                            'user_id' => $created_customer->user_id,
                                            'email' => $created_customer->email,
                                            'name' => $created_customer->name,
                                            'purchase_value' => $created_customer->purchase_value,
                                            'purchase_count' => $created_customer->purchase_count,
                                            'payment_ids' => $created_customer->payment_ids,
                                            'notes' => $created_customer->notes,
                                        ]
                                    );
                    if($customer) {
                        \Log::info("Customer Added successfully with ID:".$created_customer->id);
                        // now update the customer meta record
                        $response_metas = $this->setup_data_edd($store_url, $store_id, $store_access_token, 'getcustomers-metas', $request->post_id);
                        if($response_metas) {
                            // add one by one customer meta
                            foreach ($response_metas->edd_customers_metas as $customer_meta) {
                                CustomerMeta::create([
                                    'store_id' => $store_id,
                                    'meta_id' => $customer_meta->meta_id,
                                    'customer_id' => $customer_meta->customer_id,
                                    'meta_key' => $customer_meta->meta_key,
                                    'meta_value' => $customer_meta->meta_value
                                ]);
                            }
                        } else {
                            \Log::error("No response with add customers meta call for store id:{$store_id} & id:{$created_customer->id}");
                        }
                    } else {
                        \Log::error("Some error in adding customer with storeid: {$store_id} & ID:".$created_customer->id);
                    }
                } else {
                    \Log::error("Some error in add customer data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }         
        } else {
            \Log::error("Some error in exploding store url data");
        }
    }

    /**
     * For serving webook of customer update call and call rest api for customers
     * data update like customers and customer meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function customer_update(Request $request)
    {
        $user_agent = $request->header('user-agent');
        $expld_user_agent = explode(" ", $user_agent);
        if(!empty($expld_user_agent)) {
            $store_url = $expld_user_agent[1];
            
            if($store_url == "") {
                return response('Required information missing.', 401);
            }

            // save store data to edd stores table
            $store_exists = EddStore::where('store_url', 'like', $store_url)
                        ->first();
            if($store_exists && $request->post_id != "") {
                // then update the customer
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // send api call to get customer details by id
                $response = $this->setup_data_edd($store_url, $store_id, $store_access_token, 'getcustomers', $request->post_id);
                $updated_customer = $response->edd_customers[0];
                if($updated_customer) {
                    $customer = Customer::where('store_id', '=', $store_id)
                                ->where('id', '=', $updated_customer->id)
                                ->update(
                                        [
                                            'user_id' => $updated_customer->user_id,
                                            'email' => $updated_customer->email,
                                            'name' => $updated_customer->name,
                                            'purchase_value' => $updated_customer->purchase_value,
                                            'purchase_count' => $updated_customer->purchase_count,
                                            'payment_ids' => $updated_customer->payment_ids,
                                            'notes' => $updated_customer->notes,
                                        ]
                                    );
                    if($customer) {
                        \Log::info("Customer Updated successfully with ID:".$updated_customer->id);
                        // now update the customer meta record
                        $response_metas = $this->setup_data_edd($store_url, $store_id, $store_access_token, 'getcustomers-metas', $request->post_id);
                        if($response_metas) {
                            // delete existing metas for customer
                            $deleted_customers_metas = CustomerMeta::where('store_id', $store_id)
                            ->where('customer_id', '=', $updated_customer->id)
                            ->delete();
                            // add one by one customer meta
                            foreach ($response_metas->edd_customers_metas as $customer_meta) {
                                CustomerMeta::create([
                                    'store_id' => $store_id,
                                    'meta_id' => $customer_meta->meta_id,
                                    'customer_id' => $customer_meta->customer_id,
                                    'meta_key' => $customer_meta->meta_key,
                                    'meta_value' => $customer_meta->meta_value
                                ]);
                            }
                        } else {
                            \Log::error("No response with customers meta call for store id:{$store_id} & id:{$updated_customer->id}");
                        }
                    } else {
                        \Log::error("Some error in updating customer with storeid: {$store_id} & ID:".$updated_customer->id);
                    }
                } else {
                    \Log::error("Some error in customer data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }         
        } else {
            \Log::error("Some error in exploding store url data");
        }
    }

    /**
     * For serving webook of customer delete call and call rest api for customers
     * data update like customers and customer meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function customer_delete(Request $request)
    {
        $user_agent = $request->header('user-agent');
        $expld_user_agent = explode(" ", $user_agent);
        if(!empty($expld_user_agent)) {
            $store_url = $expld_user_agent[1];
            
            if($store_url == "") {
                return response('Required information missing.', 401);
            }

            // save store data to edd stores table
            $store_exists = EddStore::where('store_url', 'like', $store_url)
                        ->first();
            if($store_exists && $request->post_id != "") {
                // then update the customer
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // remove custom from db
                if($request->post_id) {
                    $customer = Customer::where('store_id', '=', $store_id)
                                ->where('id', '=', $request->post_id)
                                ->delete();
                    if($customer) {
                        \Log::info("Customer Deleted successfully with ID:".$request->post_id);
                        // delete existing metas for customer
                        $deleted_customers_metas = CustomerMeta::where('store_id', $store_id)
                        ->where('customer_id', '=', $request->post_id)
                        ->delete();
                    } else {
                        \Log::error("Some error in deleting customer with storeid: {$store_id} & ID:".$request->post_id);
                    }
                } else {
                    \Log::error("Some error in delete customer data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }         
        } else {
            \Log::error("Some error in exploding store url data: Customer delete call");
        }
    }

    /**
     * For handle call to the setup the edd data call to wp site.
     * @param $store_url - Store site wp url.
     * @param $store_id - Store ID.
     * @param $access_token - OAuth token.
     * @param $edd_wp_endpoint - API Endpoint.
     * @param $post_id - Post ID For customer.
     */
    protected function setup_data_edd($store_url, $store_id, $access_token, $edd_wp_endpoint, $post_id)
    {
        $edd_api_end_point = '/wp-json/wpdriftio/v1/'.$edd_wp_endpoint.'/';
        $url = $store_url . $edd_api_end_point . '?task=get_single&id=' . $post_id;
        $gclient = new Client();
        $request_var = $gclient->request(
                            'GET',
                            $url,
                            [
                                'headers' => 
                                    [
                                        'Authorization' => 'Bearer ' . $access_token
                                    ]
                            ]
                        );
        $gresponse = $request_var->getBody()->getContents();
        $edd_api_response = trim($gresponse);
        $de_edd_api_response = json_decode($edd_api_response);
        return $de_edd_api_response;
    }
}
