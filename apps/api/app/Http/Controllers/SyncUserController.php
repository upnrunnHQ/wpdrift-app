<?php
/**
 * Sync User Controller
 * This will have all methods to synchronize the user data with wp site.
 * REST End Points
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\EddUser;
use App\EddUserMeta;
use App\EddStore;

class SyncUserController extends Controller
{
    /**
     * For serving webook of user add call and call rest api for users
     * data update like users and user meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function user_create(Request $request)
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
            if($store_exists && $request->user_id != "") {
                // then update the user
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // send api call to get user details by id
                $response = $this->setup_data_edd($store_url, $store_id, $store_access_token, 'getusers', $request->user_id);
                $created_user = $response->edd_users[0];
                if($created_user) {
                    $user = EddUser::create(
                                        [
                                            'store_id' => $store_id,
                                            'ID' => $created_user->ID,
                                            'user_login' => $created_user->user_login,
                                            'user_pass' => $created_user->user_pass,
                                            'user_nicename' => $created_user->user_nicename,
                                            'user_email' => $created_user->user_email,
                                            'user_url' => $created_user->user_url,
                                            'user_registered' => $created_user->user_registered,
                                            'user_activation_key' => $created_user->user_activation_key,
                                            'user_status' => $created_user->user_status,
                                            'display_name' => $created_user->display_name,
                                        ]
                                    );
                    if($user) {
                        //\Log::info("User Added successfully with ID:".$created_user->ID);
                        // now update the user meta record
                        $response_metas = $this->setup_data_edd($store_url, $store_id, $store_access_token, 'getusers-metas', $request->user_id);
                        if($response_metas) {
                            // add one by one user meta
                            foreach ($response_metas->edd_users_metas as $user_meta) {
                                EddUserMeta::create([
                                    'store_id' => $store_id,
                                    'umeta_id' => $user_meta->umeta_id,
                                    'user_id' => $user_meta->user_id,
                                    'meta_key' => $user_meta->meta_key,
                                    'meta_value' => $user_meta->meta_value
                                ]);
                            }
                        } else {
                            \Log::error("No response with add user meta call for store id:{$store_id} & id:{$created_user->ID}");
                        }
                    } else {
                        \Log::error("Some error in adding user with storeid: {$store_id} & ID:".$created_user->ID);
                    }
                } else {
                    \Log::error("Some error in add user data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }         
        } else {
            \Log::error("Some error in exploding store url data");
        }
    }

    /**
     * For serving webook of user update call and call rest api for users
     * data update like users and user meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function user_update(Request $request)
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
            if($store_exists && $request->user_id != "") {
                // then update the user
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // send api call to get user details by id
                $response = $this->setup_data_edd($store_url, $store_id, $store_access_token, 'getusers', $request->user_id);
                $updated_user = $response->edd_users[0];
                if($updated_user) {
                    $user = EddUser::where('store_id', '=', $store_id)
                                ->where('ID', '=', $updated_user->ID)
                                ->update(
                                        [
                                            'user_login' => $updated_user->user_login,
                                            'user_pass' => $updated_user->user_pass,
                                            'user_nicename' => $updated_user->user_nicename,
                                            'user_email' => $updated_user->user_email,
                                            'user_url' => $updated_user->user_url,
                                            'user_registered' => $updated_user->user_registered,
                                            'user_activation_key' => $updated_user->user_activation_key,
                                            'user_status' => $updated_user->user_status,
                                            'display_name' => $updated_user->display_name,
                                        ]
                                    );
                    if($user) {
                        \Log::info("User Updated successfully with ID:".$updated_user->ID);
                        // now update the user meta record
                        $response_metas = $this->setup_data_edd($store_url, $store_id, $store_access_token, 'getusers-metas', $request->user_id);
                        if($response_metas) {
                            // delete existing metas for user
                            $deleted_users_metas = EddUserMeta::where('store_id', $store_id)
                            ->where('user_id', '=', $updated_user->ID)
                            ->delete();
                            // add one by one user meta
                            foreach ($response_metas->edd_users_metas as $user_meta) {
                                EddUserMeta::create([
                                    'store_id' => $store_id,
                                    'umeta_id' => $user_meta->umeta_id,
                                    'user_id' => $user_meta->user_id,
                                    'meta_key' => $user_meta->meta_key,
                                    'meta_value' => $user_meta->meta_value
                                ]);
                            }
                        } else {
                            \Log::error("No response with user meta call for store id:{$store_id} & id:{$updated_user->ID}");
                        }
                    } else {
                        \Log::error("Some error in updating user with storeid: {$store_id} & ID:".$updated_user->ID);
                    }
                } else {
                    \Log::error("Some error in user data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }         
        } else {
            \Log::error("Some error in exploding store url data");
        }
    }

    /**
     * For serving webook of user delete call and call rest api for users
     * data update like users and user meta information.
     * @param $request - that will have request submitted to this method route.
     */
    public function user_delete(Request $request)
    {
        //$request->user_id;
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
                // then update the user
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // remove custom from db
                if($request->post_id) {
                    $user = EddUser::where('store_id', '=', $store_id)
                                ->where('ID', '=', $request->user_id)
                                ->delete();
                    if($user) {
                        //\Log::info("User Deleted successfully with ID:".$request->user_id);
                        // delete existing metas for user
                        $deleted_users_metas = EddUserMeta::where('store_id', $store_id)
                        ->where('user_id', '=', $request->user_id)
                        ->delete();
                    } else {
                        \Log::error("Some error in deleting user with storeid: {$store_id} & ID:".$request->user_id);
                    }
                } else {
                    \Log::error("Some error in delete user data retrieval for store with ID:".$store_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }         
        } else {
            \Log::error("Some error in exploding store url data: User delete call");
        }
    }

    /**
     * For handle call to the setup the edd data call to wp site.
     * @param $store_url - Store site wp url.
     * @param $store_id - Store ID.
     * @param $access_token - OAuth token.
     * @param $edd_wp_endpoint - API Endpoint.
     * @param $post_id - Post ID For user.
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
