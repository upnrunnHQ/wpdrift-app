<?php
/**
 * Sync Download Controller
 * This will have all methods to synchronize the downloads/product data with wp site.
 * REST End Points
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Download;
use App\DownloadMeta;
use App\EddStore;
use App\Jobs\ProcessSyncDownloadMeta; // for sync downloads meta
use Illuminate\Support\Facades\Queue;

class SyncDownloadController extends Controller
{
    /**
     * For serving webook of download add call and call rest api for downloads
     * data update like downloads and download meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function download_create(Request $request)
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
                // then update the download
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // send api call to get download details by id
                $response = $this->setup_data_edd($store_url, $store_id, $store_access_token, 'getdownloads', $request->post_id);
                $created_download = $response->edd_downloads;
                if($created_download) {
                    // check for duplicate entry
                    $duplicate_exsts = Download::where('store_id', $store_id)
                                    ->where('post_id', (int) $created_download->ID)
                                    ->first();

                    if(!$duplicate_exsts) {
                        $download = Download::create(
                                            [
                                                'store_id' => $store_id,
                                                'post_id' => $created_download->ID,
                                                'post_author' => $created_download->post_author,
                                                'post_date' => $created_download->post_date,
                                                'post_content' => $created_download->post_content,
                                                'post_title' => $created_download->post_title,
                                                'post_status' => $created_download->post_status,
                                                'ping_status' => $created_download->ping_status,
                                                'post_password' => $created_download->post_password,
                                                'post_name' => $created_download->post_name,
                                                'to_ping' => $created_download->to_ping,
                                                'pinged' => $created_download->pinged,
                                                'post_modified' => $created_download->post_modified,
                                                'post_content_filtered' => $created_download->post_content_filtered,
                                                'post_parent' => $created_download->post_parent,
                                                'guid' => $created_download->guid,
                                                'menu_order' => $created_download->menu_order,
                                                'comment_count' => $created_download->comment_count,
                                            ]
                                        );
                    
                        if($download) {
                            \Log::info("Download Added successfully with ID:".$created_download->ID);
                            // now update the download meta record
                            $job = new ProcessSyncDownloadMeta($store_url, $store_id, $store_access_token, 'getdownloads-metas', $request->post_id);
                            // Add 10 sec delay in job
                            Queue::laterOn('default', '10', $job);
                        } else {
                            \Log::error("Some error in adding download with storeid: {$store_id} & ID:".$created_download->ID);
                        }
                    } else {
                        return;
                    }
                } else {
                    \Log::error("Some error in add download data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }         
        } else {
            \Log::error("Some error in exploding store url data");
        }
    }

    /**
     * For serving webook of download update call and call rest api for downloads
     * data update like downloads and download meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function download_update(Request $request)
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
                // then update the download
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // send api call to get download details by id
                $response = $this->setup_data_edd($store_url, $store_id, 
                $store_access_token, 'getdownloads', $request->post_id);
                $updated_download = $response->edd_downloads;
                if($updated_download) {
                    $download = Download::where('store_id', '=', $store_id)
                                ->where('post_id', '=', $updated_download->ID)
                                ->update(
                                        [
                                            'post_author' => $updated_download->post_author,
                                            'post_date' => $updated_download->post_date,
                                            'post_content' => $updated_download->post_content,
                                            'post_title' => $updated_download->post_title,
                                            'post_status' => $updated_download->post_status,
                                            'ping_status' => $updated_download->ping_status,
                                            'post_password' => $updated_download->post_password,
                                            'post_name' => $updated_download->post_name,
                                            'to_ping' => $updated_download->to_ping,
                                            'pinged' => $updated_download->pinged,
                                            'post_modified' => $updated_download->post_modified,
                                            'post_content_filtered' => $updated_download->post_content_filtered,
                                            'post_parent' => $updated_download->post_parent,
                                            'guid' => $updated_download->guid,
                                            'menu_order' => $updated_download->menu_order,
                                            'comment_count' => $updated_download->comment_count,
                                        ]
                                    );
                    if($download) {
                        \Log::info("Download Updated successfully with ID:".$updated_download->ID);
                        // now update the download meta record
                        $job = new ProcessSyncDownloadMeta($store_url, $store_id, $store_access_token, 'getdownloads-metas', $request->post_id);
                        // Add 10 sec delay in job
                        Queue::laterOn('default', '10', $job);
                    } else {
                        \Log::error("Some error in updating download with storeid: {$store_id} & ID:".$updated_download->ID);
                    }
                } else {
                    \Log::error("Some error in download data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }         
        } else {
            \Log::error("Some error in exploding store url data");
        }
    }

    /**
     * For serving webook of download delete call and call rest api for downloads
     * data update like downloads and download meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function download_delete(Request $request)
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
                // then update the download
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // remove downloads from db
                if($request->post_id) {
                    $download = Download::where('store_id', '=', $store_id)
                                ->where('post_id', '=', (int) $request->post_id)
                                ->delete();
                    if($download) {
                        \Log::info("Download Deleted successfully with ID:".$request->post_id);
                        // delete existing metas for download
                        $deleted_downloads_metas = DownloadMeta::where('store_id', $store_id)
                        ->where('post_id', '=', $request->post_id)
                        ->delete();
                    } else {
                        \Log::error("Some error in deleting download with storeid: {$store_id} & ID:".$request->post_id);
                    }
                } else {
                    \Log::error("Some error in delete download data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }         
        } else {
            \Log::error("Some error in exploding store url data: Download delete call");
        }
    }

    /**
     * For handle call to the setup the edd data call to wp site.
     * @param $store_url - Store site wp url.
     * @param $store_id - Store ID.
     * @param $access_token - OAuth token.
     * @param $edd_wp_endpoint - API Endpoint.
     * @param $post_id - Post ID For download.
     */
    protected function setup_data_edd($store_url, $store_id, $access_token, $edd_wp_endpoint, $post_id)
    {
        $edd_api_end_point = '/wp-json/wpdriftio/v1/'.$edd_wp_endpoint.'/';
        $url = $store_url . $edd_api_end_point . '?task=get_single';
        if($edd_wp_endpoint == 'getdownloads-metas') {
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
