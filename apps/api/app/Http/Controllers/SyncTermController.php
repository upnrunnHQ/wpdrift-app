<?php
/**
 * Sync Term Controller
 * This will have all methods to synchronize the terms data with wp site.
 * REST End Points
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\TermTaxonomy;
use App\TermAssigned;
use App\EddStore;

class SyncTermController extends Controller
{
    /**
     * For serving webook of term add call and call rest api for terms
     * data update like term.
     * @param $request - that will have request submitted to this method route.
     */
    public function term_create(Request $request)
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
            $term_id = $request[0];
            $tt_id = $request[1];
            $taxonomy = $request[2];
            if($store_exists && $term_id != "" && $tt_id != "" &&  $taxonomy !="") {
                // then update the term
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // send api call to get term details by id
                $response = $this->setup_data_edd($store_url, $store_id, $store_access_token, 'getterm-taxonomy', $term_id, $tt_id, $taxonomy);
                $term_taxonomy = $response->edd_term_taxonomy;
                if($term_taxonomy) {
                    $term_exists = TermTaxonomy::where('store_id', $store_id)
                                    ->where('term_id', $term_id)
                                    ->first();
                    if(!$term_exists) {
                        TermTaxonomy::create([
                            'store_id' => $store_id,
                            'term_taxonomy_id' => $term_taxonomy->term_taxonomy_id,
                            'term_id' => $term_taxonomy->term_id,
                            'name' => $term_taxonomy->name,
                            'slug' => $term_taxonomy->slug,
                            'taxonomy' => $term_taxonomy->taxonomy,
                            'description' => $term_taxonomy->description,
                            'parent' => $term_taxonomy->parent,
                            'count' => $term_taxonomy->count
                        ]);
                        \Log::info('Successfully Added Term Taxonomy with ID:' . $term_id);
                    } else {
                        \Log::info('Term Taxonomy already exists with ID:' . $term_id);
                    }
                } else {
                    \Log::error("Some error while adding Term ID:".$term_id);
                }
            } else {
                \Log::error("store not exists or with url:".$store_url);
            }         
        } else {
            \Log::error("Some error in exploding store url data");
        }
    }

    /**
     * For serving webook of term update call and call rest api for
     * data update like term information.
     * @param $request - that will have request submitted to this method route.
     */
    public function term_update(Request $request)
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
            $term_id = $request[0];
            $tt_id = $request[1];
            $taxonomy = $request[2];
            if($store_exists && $term_id != "" && $tt_id != "" &&  $taxonomy !="") {
                // then update the term
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // send api call to get term details by id
                $response = $this->setup_data_edd($store_url, $store_id, $store_access_token, 'getterm-taxonomy', $term_id, $tt_id, $taxonomy);
                $term_taxonomy = $response->edd_term_taxonomy;
                if($term_taxonomy) {
                    $term = TermTaxonomy::where('store_id', $store_id)
                        ->where('term_id', $term_id)
                        ->update([
                        'term_taxonomy_id' => $term_taxonomy->term_taxonomy_id,
                        'name' => $term_taxonomy->name,
                        'slug' => $term_taxonomy->slug,
                        'taxonomy' => $term_taxonomy->taxonomy,
                        'description' => $term_taxonomy->description,
                        'parent' => $term_taxonomy->parent,
                        'count' => $term_taxonomy->count
                    ]);
                    if($term) {
                        \Log::info("Term Updated successfully with ID:".$term_id);
                    } else {
                        \Log::error("Some error in updating term id with storeid: {$store_id} & ID:".$term_id);
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
     * For serving webook of term delete call and call rest api for terms
     * data update like term information.
     * @param $request - that will have request submitted to this method route.
     */
    public function term_delete(Request $request)
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
            $term_id = $request[0];
            $tt_id = $request[1];
            $taxonomy = $request[2];
            if($store_exists && $term_id != "" && $tt_id != "" &&  $taxonomy !="") {
                // delete term actions
                $store_id = $store_exists->store_id;
                $store_url = $store_exists->store_url;
                $store_access_token = $store_exists->store_access_token;
                // remove custom from db
                if($term_id) {
                    $term = TermTaxonomy::where('store_id', '=', $store_id)
                                ->where('term_id', '=', $term_id)
                                ->delete();
                    if($term) {
                        \Log::info("Term Deleted successfully with ID:".$term_id);
                        // delete assigned terms
                        TermAssigned::where('store_id', $store_id)
                        ->where('term_taxonomy_id', '=', $term_id)
                        ->delete();
                    } else {
                        \Log::error("Some error in deleting term with storeid: {$store_id} & ID:".$term_id);
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
     * @param $term_id - Term ID.
     * @param $term_id - Term Tax ID.
     * @param $taxonomy - Taxonomy Name.
     */
    protected function setup_data_edd($store_url, $store_id, $access_token, $edd_wp_endpoint, $term_id, $tt_id, $taxonomy)
    {
        $edd_api_end_point = '/wp-json/wpdriftio/v1/'.$edd_wp_endpoint.'/';
        $url = $store_url . $edd_api_end_point . '?task=get_single&term_id=' . $term_id . '&taxonomy=' . $taxonomy;
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
