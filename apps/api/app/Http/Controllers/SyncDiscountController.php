<?php
/**
 * Sync Discount Controller
 * This will have all methods to synchronize the discounts/product data with wp site.
 * REST End Points
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Discount;
use App\DiscountMeta;
use App\EddStore;
use App\Jobs\ProcessSyncDiscountMeta; // for setting up the queue for Sync. Discount
use Illuminate\Support\Facades\Queue;

class SyncDiscountController extends Controller
{
    /**
     * For serving webook of discount add call and call rest api for discounts
     * data update like discounts and discount meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function discount_create(Request $request)
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
                // then update the discount
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // send api call to get discount details by id
                $response = $this->setup_data_edd($store_url, $store_id, $store_access_token, 'getdiscounts', $request->post_id);
                $created_discount = $response->edd_discounts;
                if($created_discount) {
                    // check for duplicate entry
                    $duplicate_exsts = Discount::where('store_id', $store_id)
                                    ->where('post_id', (int) $created_discount->ID)
                                    ->first();

                    if(!$duplicate_exsts) {
                        $discount = Discount::create(
                                            [
                                                'store_id' => $store_id,
                                                'ID' => $created_discount->ID,
                                                'post_author' => $created_discount->post_author,
                                                'post_date' => $created_discount->post_date,
                                                'post_content' => $created_discount->post_content,
                                                'post_title' => $created_discount->post_title,
                                                'post_status' => $created_discount->post_status,
                                                'ping_status' => $created_discount->ping_status,
                                                'post_password' => $created_discount->post_password,
                                                'post_name' => $created_discount->post_name,
                                                'to_ping' => $created_discount->to_ping,
                                                'pinged' => $created_discount->pinged,
                                                'post_modified' => $created_discount->post_modified,
                                                'post_content_filtered' => $created_discount->post_content_filtered,
                                                'post_parent' => $created_discount->post_parent,
                                                'guid' => $created_discount->guid,
                                                'menu_order' => $created_discount->menu_order,
                                                'comment_count' => $created_discount->comment_count,
                                            ]
                                        );
                    
                        if($discount) {
                            \Log::info("Discount Added successfully with ID:".$created_discount->ID);
                            // now update the discount meta record
                            $job = new ProcessSyncDiscountMeta($store_url, $store_id, $store_access_token, 'getdiscounts-metas', $created_discount->ID);
                            // Add 10 sec delay in job
                            Queue::laterOn('default', '10', $job);
                            
                        } else {
                            \Log::error("Some error in adding discount with storeid: {$store_id} & ID:".$created_discount->ID);
                        }
                    } else {
                        return;
                    }
                } else {
                    \Log::error("Some error in add discount data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }         
        } else {
            \Log::error("Some error in exploding store url data");
        }
    }

    /**
     * For serving webook of discount update call and call rest api for discounts
     * data update like discounts and discount meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function discount_update(Request $request)
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
                // then update the discount
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // send api call to get discount details by id
                $response = $this->setup_data_edd($store_url, $store_id, 
                $store_access_token, 'getdiscounts', $request->post_id);
                $updated_discount = $response->edd_discounts;
                if($updated_discount) {
                    $discount = Discount::where('store_id', '=', $store_id)
                                ->where('ID', '=', $updated_discount->ID)
                                ->update(
                                        [
                                            'post_author' => $updated_discount->post_author,
                                            'post_date' => $updated_discount->post_date,
                                            'post_content' => $updated_discount->post_content,
                                            'post_title' => $updated_discount->post_title,
                                            'post_status' => $updated_discount->post_status,
                                            'ping_status' => $updated_discount->ping_status,
                                            'post_password' => $updated_discount->post_password,
                                            'post_name' => $updated_discount->post_name,
                                            'to_ping' => $updated_discount->to_ping,
                                            'pinged' => $updated_discount->pinged,
                                            'post_modified' => $updated_discount->post_modified,
                                            'post_content_filtered' => $updated_discount->post_content_filtered,
                                            'post_parent' => $updated_discount->post_parent,
                                            'guid' => $updated_discount->guid,
                                            'menu_order' => $updated_discount->menu_order,
                                            'comment_count' => $updated_discount->comment_count,
                                        ]
                                    );
                    if($discount) {
                        \Log::info("Discount Updated successfully with ID:".$updated_discount->ID);
                        // now update the discount meta record
                        $job = new ProcessSyncDiscountMeta($store_url, $store_id, $store_access_token, 'getdiscounts-metas', $updated_discount->ID);
                        // Add 10 sec delay in job
                        Queue::laterOn('default', '10', $job);
                    } else {
                        \Log::error("Some error in updating discount with storeid: {$store_id} & ID:".$updated_discount->ID);
                    }
                } else {
                    \Log::error("Some error in discount data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }         
        } else {
            \Log::error("Some error in exploding store url data");
        }
    }

    /**
     * For serving webook of discount delete call and call rest api for discounts
     * data update like discounts and discount meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function discount_delete(Request $request)
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
                // then update the discount
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // remove discounts from db
                if($request->post_id) {
                    $discount = Discount::where('store_id', '=', $store_id)
                                ->where('ID', '=', (int) $request->post_id)
                                ->delete();
                    if($discount) {
                        \Log::info("Discount Deleted successfully with ID:".$request->post_id);
                        // delete existing metas for discount
                        $deleted_discounts_metas = DiscountMeta::where('store_id', $store_id)
                        ->where('post_id', '=', (int) $request->post_id)
                        ->delete();
                    } else {
                        \Log::error("Some error in deleting Discount with storeid: {$store_id} & ID:".$request->post_id);
                    }
                } else {
                    \Log::error("Some error in delete Discount data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }         
        } else {
            \Log::error("Some error in exploding store url data: Discount delete call");
        }
    }

    /**
     * For handle call to the setup the edd data call to wp site.
     * @param $store_url - Store site wp url.
     * @param $store_id - Store ID.
     * @param $access_token - OAuth token.
     * @param $edd_wp_endpoint - API Endpoint.
     * @param $post_id - Post ID For discount.
     */
    protected function setup_data_edd($store_url, $store_id, $access_token, $edd_wp_endpoint, $post_id)
    {
        $edd_api_end_point = '/wp-json/wpdriftio/v1/'.$edd_wp_endpoint.'/';
        $url = $store_url . $edd_api_end_point . '?task=get_single';
        if($edd_wp_endpoint == 'getdiscounts-metas') {
            $url .= '&post_id=' . $post_id;
        } else {
            $url .= '&id=' . $post_id;
        }
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
