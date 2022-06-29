<?php
/**
 * Sync Payment Controller
 * This will have all methods to synchronize the payment data with wp site.
 * REST End Points
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Payment;
use App\PaymentMeta;
use App\EddStore;
use App\Jobs\ProcessSyncPaymentMeta; // for sync payments meta
use Illuminate\Support\Facades\Queue;

class SyncPaymentController extends Controller
{
    /**
     * For serving webook of payment add call and call rest api for payments
     * data update like payments and payment meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function payment_create(Request $request)
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
                // then update the payment
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // send api call to get payment details by id
                $response = $this->setup_data_edd($store_url, $store_id, $store_access_token, 'getpayments', $request->post_id);
                $created_payment = $response->edd_payments;
                if($created_payment) {
                    // check for duplicate entry
                    $duplicate_exsts = Payment::where('store_id', $store_id)
                                    ->where('ID', (int) $created_payment->ID)
                                    ->first();

                    if(!$duplicate_exsts) {
                        $payment = Payment::create(
                                            [
                                                'store_id' => $store_id,
                                                'ID' => $created_payment->ID,
                                                'post_author' => $created_payment->post_author,
                                                'post_date' => $created_payment->post_date,
                                                'post_content' => $created_payment->post_content,
                                                'post_title' => $created_payment->post_title,
                                                'post_status' => $created_payment->post_status,
                                                'ping_status' => $created_payment->ping_status,
                                                'post_password' => $created_payment->post_password,
                                                'post_name' => $created_payment->post_name,
                                                'to_ping' => $created_payment->to_ping,
                                                'pinged' => $created_payment->pinged,
                                                'post_modified' => $created_payment->post_modified,
                                                'post_content_filtered' => $created_payment->post_content_filtered,
                                                'post_parent' => $created_payment->post_parent,
                                                'guid' => $created_payment->guid,
                                                'menu_order' => $created_payment->menu_order,
                                                'comment_count' => $created_payment->comment_count,
                                            ]
                                        );
                        if($payment) {
                            \Log::info("Payment Added successfully with storeid: {$store_id} ID:".$created_payment->ID);
                            // now update the payment meta record
                            $job = new ProcessSyncPaymentMeta($store_url, $store_id, $store_access_token, 'getpayments-metas', $request->post_id);
                            // Add 10 sec delay in job
                            Queue::laterOn('default', '50', $job);
                        } else {
                            \Log::error("Some error in adding payment with storeid: {$store_id} & ID:".$created_payment->ID);
                        }
                    } else {
                        return;
                    }
                } else {
                    \Log::error("Some error in add payment data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }
        } else {
            \Log::error("Some error in exploding store url data");
        }
    }

    /**
     * For serving webook of payment update call and call rest api for payments
     * data update like payments and payment meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function payment_update(Request $request)
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
                // then update the payment
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // send api call to get payment details by id
                $response = $this->setup_data_edd($store_url, $store_id,
                $store_access_token, 'getpayments', $request->post_id);
                $updated_payment = $response->edd_payments;
                if($updated_payment) {
                    $payment = Payment::where('store_id', '=', $store_id)
                                ->where('ID', '=', (int) $updated_payment->ID)
                                ->update(
                                        [
                                            'post_author' => $updated_payment->post_author,
                                            'post_date' => $updated_payment->post_date,
                                            'post_content' => $updated_payment->post_content,
                                            'post_title' => $updated_payment->post_title,
                                            'post_status' => $updated_payment->post_status,
                                            'ping_status' => $updated_payment->ping_status,
                                            'post_password' => $updated_payment->post_password,
                                            'post_name' => $updated_payment->post_name,
                                            'to_ping' => $updated_payment->to_ping,
                                            'pinged' => $updated_payment->pinged,
                                            'post_modified' => $updated_payment->post_modified,
                                            'post_content_filtered' => $updated_payment->post_content_filtered,
                                            'post_parent' => $updated_payment->post_parent,
                                            'guid' => $updated_payment->guid,
                                            'menu_order' => $updated_payment->menu_order,
                                            'comment_count' => $updated_payment->comment_count,
                                        ]
                                    );
                    if($payment) {
                        \Log::info("Payment Updated successfully with ID:".$updated_payment->ID);
                        // now update the Payment meta record
                        $job = new ProcessSyncPaymentMeta($store_url, $store_id, $store_access_token, 'getpayments-metas', $request->post_id);
                        // Add 10 sec delay in job
                        Queue::laterOn('default', '50', $job);
                    } else {
                        \Log::error("Some error in updating Payment with storeid: {$store_id} & ID:".$updated_payment->ID);
                    }
                } else {
                    \Log::error("Some error in Payment data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }
        } else {
            \Log::error("Some error in exploding store url data");
        }
    }

    /**
     * For serving webook of payment delete call and call rest api for payments
     * data update like payments and payment meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function payment_delete(Request $request)
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
                // then update the payment
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // remove payments from db
                if($request->post_id) {
                    $payment = Payment::where('store_id', '=', $store_id)
                                ->where('ID', '=', (int) $request->post_id)
                                ->delete();
                    if($payment) {
                        \Log::info("Payment Deleted successfully with ID:".$request->post_id);
                        // delete existing metas for payment
                        $deleted_payments_metas = PaymentMeta::where('store_id', $store_id)
                        ->where('post_id', '=', (int) $request->post_id)
                        ->delete();
                    } else {
                        \Log::error("Some error in deleting payment with storeid: {$store_id} & ID:".$request->post_id);
                    }
                } else {
                    \Log::error("Some error in delete payment data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }
        } else {
            \Log::error("Some error in exploding store url data: Payment delete call");
        }
    }

    /**
     * For handle call to the setup the edd data call to wp site.
     * @param $store_url - Store site wp url.
     * @param $store_id - Store ID.
     * @param $access_token - OAuth token.
     * @param $edd_wp_endpoint - API Endpoint.
     * @param $post_id - Post ID For payment.
     */
    protected function setup_data_edd($store_url, $store_id, $access_token, $edd_wp_endpoint, $post_id)
    {
        $edd_api_end_point = '/wp-json/wpdriftio/v1/'.$edd_wp_endpoint.'/';
        $url = $store_url . $edd_api_end_point . '?task=get_single';
        if($edd_wp_endpoint == 'getpayments-metas') {
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
