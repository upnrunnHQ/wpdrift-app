<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\EddStore;
use App\Jobs\ProcessSingleDownload;
use App\Download;

class GetLastDayDownloads extends Command {

	/*
	* The name and signature of the console command.
	*
	* @var string
	*/
	protected $signature = 'GetLastDayDownloads:getdownloads';

	/**
	* The console command description.
	*
	* @var string
	*/
	protected $description = 'Get Last Day Downloads';

	/**
	* Create a new command instance.
	*
	* @return void
	*/
	public function __construct() {
		parent::__construct();
	}

	/**
	* Execute the console command.
	*
	* @return mixed
	*/
	public function handle() {
		$all_stores = EddStore::where( 'store_id', '!=', '' )->get();
		foreach ( $all_stores as $single_store ) {
			// get the downloads from store.
			$url = $single_store->store_url;
			$id  = $single_store->store_id;
			//return;
			$access_token = $single_store->store_access_token;
			$end_point    = 'wp-json/wpdriftio/v1/getdaydownloads';
			try {
				$downloads_id = $this->gclient_request_response( $url . '/' . $end_point, $access_token );
				if ( $downloads_id ) {
					foreach ( $downloads_id->edd_downloads as $download_id ) {
						// first check that is download exits with ID
						$download_exists = Download::where( 'store_id', $id )
						->where( 'post_id', (int) $download_id )->exists();
						if ( ! $download_exists ) {
							$job = new ProcessSingleDownload( $url, $id, $access_token, 'getdownloads', $download_id );
							dispatch( $job->onQueue( 'download' ) );
						}
					}
				}
			} catch ( \Exception $e ) {
				$error = $e->getMessage();
				\Log::error( $error );
			}
		}
	}
	/**
	 * General cURL function
	 */
	protected function gclient_request_response( $url, $access_token ) {
		$gclient          = new Client();
		$request_var      = $gclient->request(
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
