<?php
/**
 * General Controller
 * This will have all the general tasks controller related methods that will serv
 * REST End Points
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\EddSiteTotalJobs;
use GuzzleHttp\Client;

class GeneralController extends Controller {

	// get user avatar by applied $email_address
	public function get_user_avatar_by_email( $email_address ) {
		if ( $email_address != '' ) {
			$user_avatar = 'https://www.gravatar.com/avatar/' . md5( strtolower( trim( $email_address ) ) ) . '?s=48';
		} else {
			$user_avatar = '';
		}
		return $user_avatar;
	}


	// update the job total number and track it for store update
	public function update_edd_site_total_jobs( $site_id ) {
		$edd_total_jobs_obj = EddSiteTotalJobs::where( 'site_id', $site_id )->first();
		$edd_total_jobs     = $edd_total_jobs_obj->total_jobs;
		// Send post hook to edd site that edd has been setup.
		if ( $edd_total_jobs <= 2 ) { // last two jobs are remaning then send notification
			$app_server_url = config( 'app.app_wpdrift_url' );
			$edd_key        = config( 'app.edd_key' );
			$query_string   = '?store_id=' . $site_id . '&action=success';
			$gclient        = new Client();
			$response       = $gclient->request( 'GET', $app_server_url . '/edd_setup' . $query_string, [] );
		}
		// update the job total for tracking, decrement it.
		EddSiteTotalJobs::where( 'site_id', $site_id )->update(
			[
				'total_jobs' => $edd_total_jobs - 1,
			]
		);
		if ( $edd_total_jobs <= 0 ) {
			// delete total job record for this table
			EddSiteTotalJobs::where( 'site_id', $site_id )->delete();
		}
	}

	/**
	 * General cURL function
	 */
	public function gclient_request_response( $url, $access_token ) {
		$gclient     = new Client();
		$request_var = $gclient->request(
			'GET',
			$url,
			[
				'headers' =>
					[
						'Authorization' => 'Bearer ' . $access_token,
					],
			]
		);

		$gresponse        = $request_var->getBody()->getContents();
		$edd_api_response = trim( $gresponse );
		$req_jsn_decode   = json_decode( $edd_api_response );
		return $req_jsn_decode;
	}
}
