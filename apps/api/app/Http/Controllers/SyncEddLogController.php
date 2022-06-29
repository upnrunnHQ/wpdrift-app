<?php
/**
 * Sync Edd Log Controller
 * This will have all methods to synchronize the EddLog data with wp site.
 * REST End Points
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\EddLog;
use App\EddLogMeta;
use App\EddStore;
use App\Jobs\ProcessSyncEddLogMeta; // for sync edd log meta
use Illuminate\Support\Facades\Queue;

class SyncEddLogController extends Controller
{
    /**
     * For serving webook of eddlog add call and call rest api for eddlogs
     * data update like eddlogs and eddlog meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function eddlog_create(Request $request)
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
                // then update the eddlog
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // send api call to get eddlog details by id
                $response = $this->setup_data_edd($store_url, $store_id, $store_access_token, 'geteddlogs', $request->post_id);
                $created_eddlog = $response->edd_logs;
                if($created_eddlog) {
                    // check for duplicate entry
                    $duplicate_exsts = EddLog::where('store_id', $store_id)
                                    ->where('ID', (int) $created_eddlog->ID)
                                    ->first();

                    if(!$duplicate_exsts) {
                        $eddlog = EddLog::create(
                                            [
                                                'store_id' => $store_id,
                                                'ID' => $created_eddlog->ID,
                                                'post_author' => $created_eddlog->post_author,
                                                'post_date' => $created_eddlog->post_date,
                                                'post_content' => $created_eddlog->post_content,
                                                'post_title' => $created_eddlog->post_title,
                                                'post_status' => $created_eddlog->post_status,
                                                'ping_status' => $created_eddlog->ping_status,
                                                'post_password' => $created_eddlog->post_password,
                                                'post_name' => $created_eddlog->post_name,
                                                'to_ping' => $created_eddlog->to_ping,
                                                'pinged' => $created_eddlog->pinged,
                                                'post_modified' => $created_eddlog->post_modified,
                                                'post_content_filtered' => $created_eddlog->post_content_filtered,
                                                'post_parent' => $created_eddlog->post_parent,
                                                'guid' => $created_eddlog->guid,
                                                'menu_order' => $created_eddlog->menu_order,
                                                'comment_count' => $created_eddlog->comment_count,
                                            ]
                                        );
                    
                        if($eddlog) {
                            \Log::info("Edd Log Added successfully with ID:".$created_eddlog->ID);
                            // now update the eddlog meta record
                            $job = new ProcessSyncEddLogMeta($store_url, $store_id, $store_access_token, 'edd_logs_metas', $request->post_id);
                            // Add 10 sec delay in job
                            Queue::laterOn('default', '10', $job);
                        } else {
                            \Log::error("Some error in adding eddlog with storeid: {$store_id} & ID:".$created_eddlog->ID);
                        }
                    } else {
                        return;
                    }
                } else {
                    \Log::error("Some error in add eddlog data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }         
        } else {
            \Log::error("Some error in exploding store url data");
        }
    }

    /**
     * For serving webook of eddlog update call and call rest api for eddlogs
     * data update like eddlogs and eddlog meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function eddlog_update(Request $request)
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
                // then update the eddlog
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // send api call to get eddlog details by id
                $response = $this->setup_data_edd($store_url, $store_id, 
                $store_access_token, 'geteddlogs', $request->post_id);
                $updated_eddlog = $response->edd_logs;
                if($updated_eddlog) {
                    $eddlog = EddLog::where('store_id', '=', $store_id)
                                ->where('ID', '=', (int) $updated_eddlog->ID)
                                ->update(
                                        [
                                            'post_author' => $updated_eddlog->post_author,
                                            'post_date' => $updated_eddlog->post_date,
                                            'post_content' => $updated_eddlog->post_content,
                                            'post_title' => $updated_eddlog->post_title,
                                            'post_status' => $updated_eddlog->post_status,
                                            'ping_status' => $updated_eddlog->ping_status,
                                            'post_password' => $updated_eddlog->post_password,
                                            'post_name' => $updated_eddlog->post_name,
                                            'to_ping' => $updated_eddlog->to_ping,
                                            'pinged' => $updated_eddlog->pinged,
                                            'post_modified' => $updated_eddlog->post_modified,
                                            'post_content_filtered' => $updated_eddlog->post_content_filtered,
                                            'post_parent' => $updated_eddlog->post_parent,
                                            'guid' => $updated_eddlog->guid,
                                            'menu_order' => $updated_eddlog->menu_order,
                                            'comment_count' => $updated_eddlog->comment_count,
                                        ]
                                    );
                    if($eddlog) {
                        \Log::info("Edd Log Updated successfully with ID:".$updated_eddlog->ID);
                        // now update the EddLog meta record
                        $job = new ProcessSyncEddLogMeta($store_url, $store_id, $store_access_token, 'edd_logs_metas', $request->post_id);
                        // Add 10 sec delay in job
                        Queue::laterOn('default', '10', $job);
                    } else {
                        \Log::error("Some error in updating eddlog with storeid: {$store_id} & ID:".$updated_eddlog->ID);
                    }
                } else {
                    \Log::error("Some error in eddlog data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }         
        } else {
            \Log::error("Some error in exploding store url data");
        }
    }

    /**
     * For serving webook of eddlog delete call and call rest api for eddlogs
     * data update like eddlogs and eddlog meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function eddlog_delete(Request $request)
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
                // then update the eddlog
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // remove eddlogs from db
                if($request->post_id) {
                    $eddlog = EddLog::where('store_id', '=', $store_id)
                                ->where('ID', '=', (int) $request->post_id)
                                ->delete();
                    if($eddlog) {
                        \Log::info("Edd Log Deleted successfully with ID:".$request->post_id);
                        // delete existing metas for eddlog
                        $deleted_eddlogs_metas = EddLogMeta::where('store_id', $store_id)
                        ->where('post_id', '=', (int) $request->post_id)
                        ->delete();
                    } else {
                        \Log::error("Some error in deleting Edd Log with storeid: {$store_id} & ID:".$request->post_id);
                    }
                } else {
                    \Log::error("Some error in delete Edd Log data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }         
        } else {
            \Log::error("Some error in exploding store url data: Edd Log delete call");
        }
    }

    /**
     * For handle call to the setup the edd data call to wp site.
     * @param $store_url - Store site wp url.
     * @param $store_id - Store ID.
     * @param $access_token - OAuth token.
     * @param $edd_wp_endpoint - API Endpoint.
     * @param $post_id - Post ID For eddlog.
     */
    protected function setup_data_edd($store_url, $store_id, $access_token, $edd_wp_endpoint, $post_id)
    {
        $edd_api_end_point = '/wp-json/wpdriftio/v1/'.$edd_wp_endpoint.'/';
        $url = $store_url . $edd_api_end_point . '?task=get_single';
        if($edd_wp_endpoint == 'geteddlogs-metas') {
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
