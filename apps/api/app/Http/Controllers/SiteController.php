<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GeoIp2\Database\Reader;
use Carbon\Carbon;
use App\Site;
use App\SiteMeta;
use App\Download;
use App\DownloadMeta;
use App\Payment;
use App\PaymentMeta;
use App\Discount;
use App\DiscountMeta;
use App\EddLog;
use App\EddLogMeta;
use App\Customer;
use App\CustomerMeta;
use App\SiteProcessor;
use App\Fetch\ProductsFetcher;
use App\Fetch\PaymentsFetcher;
use App\Fetch\DiscountsFetcher;
use App\Fetch\LogsFetcher;
use App\CustomerProcessor;
use App\Fetch\Fetch;
use App\Job;
use App\Jobs\AddSite;
use App\Jobs\DeleteSite;


class SiteController extends Controller {

	/**
	 * Retrieve the user for the given ID.
	 * @return [type] [description]
	 */
	public function show() {
		$reader = new Reader( storage_path( 'app/GeoLite2-City.mmdb' ) );
		$record = $reader->city( '42.0.4.229' );
		return [
			'today' => Carbon::today(),
		];
	}

	/**
	 * [add_site description]
	 * @param Request $request [description]
	 */
	public function add_site( Request $request ) {
		if ( empty( $request->input( 'site_url' ) ) || ( filter_var( $request->input( 'site_url' ), FILTER_VALIDATE_URL ) === false ) ) {
			return [
				'code'    => 'site_url_not_valid',
				'message' => 'Site URL is not valid.',
				'data'    => [],
			];
		}

		if ( empty( $request->input( 'site_id' ) ) ) {
			return [
				'code'    => 'site_id_not_valid',
				'message' => 'Site id is not valid.',
				'data'    => [],
			];
		}

		dispatch( new AddSite( $request->all() ) );

		return [];
	}

	/**
	 * [delete_site description]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function delete_site( Request $request ) {
		Job::where( 'queue', 'default' )->delete();
		foreach ( [ 'download', 'edd_customer', 'edd_payment', 'edd_discount' ] as $data_type ) {
			SiteMeta::where( 'meta_key', $data_type . '_queue_list' )
			->delete();

			SiteMeta::where( 'meta_key', $data_type . '_queued' )
			->delete();
		}
		dispatch( new DeleteSite( $request->input( 'site_id' ) ) );
		return [];
	}

	/**
	 * [process_posts description]
	 * @return [type] [description]
	 */
	public function process_posts() {
		$site    = Site::first();
		$fetcher = new ProductsFetcher( $site->site_id );
		// $fetcher = new PaymentsFetcher( $site->site_id );
		// $fetcher = new DiscountsFetcher( $site->site_id );
		// $fetcher = new LogsFetcher( $site->site_id );
		return $fetcher->fetch();
	}

	/**
	 * [process_customers description]
	 * @return [type] [description]
	 */
	public function process_customers() {
		$site    = Site::first();
		$payload = [
			'site_id'   => $site->site_id,
			'data_type' => 'edd_customer',
		];

		$processor = new CustomerProcessor( $payload );
		return $processor->process();
	}

	/**
	 * [process_sites description]
	 * @param  SiteProcessor $processor [description]
	 * @return [type]                   [description]
	 */
	public function process_sites( SiteProcessor $processor ) {
		return $processor->process_sites();
	}

	public function process_site( SiteProcessor $processor ) {
		$site = Site::first();
		return $processor->process_site( $site->site_id );
	}

	public function fetch() {
		$site_id      = 155;
		$access_token = '9b0cbe60a6511d20c77828898b2f731a5e88230d';
		$site_url     = 'https://wpdrift.com';
		$end          = 'posts';
		$data_id      = 3077;

		$payload = [
			'site_id'      => $site_id,
			'fetch_type'   => 'metadata',
			'post_type'    => 'download',
			'access_token' => $access_token,
			'query'        => [],
			'request_url'  => $site_url . '/wp-json/wpdriftio/v1/' . $end . '/' . $data_id . '/metadata',
			'post_id'      => $data_id,
		];

		// $payload['request_url'] = $site->site_url . '/wp-json/wpdriftio/v1/posts/' . $post_id . '/metadata';
		// $payload['fetch_type']  = 'metadata';

		$fetch = new Fetch( $payload );
		return [
			'fetched' => $fetch->process(),
			'$payload' => $payload,
		];
	}

	/**
	 * [clear description]
	 * @return [type] [description]
	 */
	public function clear() {
		$site_id = (string) $_GET['site_id'];
		Job::where( 'queue', 'default' )->delete();
		Site::where( 'site_id', $site_id )->delete();
		SiteMeta::where( 'site_id', $site_id )->delete();
		Download::where( 'store_id', $site_id )->delete();
		DownloadMeta::where( 'store_id', $site_id )->delete();
		Payment::where( 'store_id', $site_id )->delete();
		PaymentMeta::where( 'store_id', $site_id )->delete();
		Discount::where( 'store_id', $site_id )->delete();
		DiscountMeta::where( 'store_id', $site_id )->delete();
		EddLog::where( 'store_id', $site_id )->delete();
		EddLogMeta::where( 'store_id', $site_id )->delete();
		Customer::where( 'store_id', $site_id )->delete();
		CustomerMeta::where( 'store_id', $site_id )->delete();
		return [];
	}

	/**
	 * [debug description]
	 * @return [type] [description]
	 */
	public function debug() {
		$metadata = DownloadMeta::where( 'meta_key', '_thumbnail_url' )
		->where( 'post_id', '3077' )
		->first();
		// $metadata->delete();
		if ( isset( $_GET['testing'] ) ) {
			return [
				'metadata'     => $metadata,
				'jobs_all'     => Job::count(),
				'jobs'         => Job::where( 'queue', 'default' )->count(),
				'Download'     => Download::count(),
				'DownloadMeta' => DownloadMeta::count(),
				'Payment'      => Payment::count(),
				'PaymentMeta'  => PaymentMeta::count(),
				'Discount'     => Discount::count(),
				'DiscountMeta' => DiscountMeta::count(),
				'EddLog'       => EddLog::count(),
				// 'EddLogCount'  => EddLog::take( 1 )->get(),
				'EddLogMeta'   => EddLogMeta::count(),
				'Customer'     => Customer::count(),
				'CustomerMeta' => CustomerMeta::count(),
				'sites'        => Site::all(),
				'meta'         => SiteMeta::all(),
				// 'Customer'     => Customer::get(),
			];
		}
		return [];
	}
}
