<?php
/**
 * Sync Term Assigned Controller
 * This will have all methods to synchronize the terms data with wp site.
 * REST End Points
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\TermTaxonomy;
use App\TermAssigned;
use App\EddStore;

class SyncTermAssignedController extends Controller
{
    /**
     * For serving webook of term add call and call rest api for terms
     * data update like term.
     * @param $request - that will have request submitted to this method route.
     */
    public function term_assign(Request $request)
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
            $response = $this->setup_data_edd($store_url, $store_exists->store_id, $store_exists->store_access_token, 'getterm-assigned', $request->object_id);
            $terms_assigned = $response->edd_term_assigned;
            // try catch block to get request and response
            try {
                // first delete old records
                $deleted_records = TermAssigned::where('store_id', $store_exists->store_id)
                                    ->where('object_id', $request->object_id)
                                    ->delete();
                // insert new downloads records.
                foreach ($terms_assigned as $term_assigned) {
                    TermAssigned::create([
                            'store_id' => $store_exists->store_id,
                            'object_id' => $term_assigned->object_id,
                            'term_taxonomy_id' => $term_assigned->term_taxonomy_id,
                            'term_order' => $term_assigned->term_order
                        ]);
                }
                \Log::info('Successfully Added Term Assigned for object id:'.$term_assigned->object_id);
            } catch (\Exception $e) {
                $error = $e->getMessage();
                \Log::error($error);
                return response($error, 500);
            }         
        } else {
            \Log::error("Some error in exploding store url data");
        }
    }

    /**
     * For handle call to the setup the edd data call to wp site.
     * @param $store_url - Store site wp url.
     * @param $store_id - Store ID.
     * @param $access_token - OAuth token.
     * @param $edd_wp_endpoint - API Endpoint.
     * @param $object_id - object_id.
     */
    protected function setup_data_edd($store_url, $store_id, $access_token, $edd_wp_endpoint, $object_id)
    {
        $edd_api_end_point = '/wp-json/wpdriftio/v1/'.$edd_wp_endpoint.'/';
        $url = $store_url . $edd_api_end_point . '?task=get_single&object_id=' . $object_id;
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
