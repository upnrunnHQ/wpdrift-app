<?php

namespace App;

use Carbon\Carbon;
use App\Site;
use App\SiteMeta;
use App\Download;
use App\DownloadMeta;
use App\Payment;
use App\PaymentMeta;
use App\Discount;
use App\DiscountMeta;
use App\Jobs\ProcessSite;
use App\Jobs\ProcessPosts;
use App\Jobs\ProcessCustomers;
use App\Fetch\ProductsFetcher;
use App\Fetch\PaymentsFetcher;
use App\Fetch\DiscountsFetcher;
use App\Fetch\LogsFetcher;

/**
 * [DownloadController description]
 */
class SiteProcessor {
	/**
	 * [process description]
	 * @return [type] [description]
	 */
	public function process_sites() {
		$sites = Site::pluck( 'site_id' )->toArray();
		if ( ! $sites ) {
			return [];
		}

		foreach ( $sites as $payload ) {
			dispatch( new ProcessSite( $payload ) );
		}

		return $sites;
	}

	/**
	 * [process_site description]
	 * @param  [type] $payload [description]
	 * @return [type]          [description]
	 */
	public function process_site( $payload ) {
		dispatch( new ProcessPosts( new LogsFetcher( $payload ) ) );
		dispatch( new ProcessPosts( new ProductsFetcher( $payload ) ) );
		dispatch( new ProcessPosts( new PaymentsFetcher( $payload ) ) );
		dispatch( new ProcessPosts( new DiscountsFetcher( $payload ) ) );

		dispatch(
			new ProcessCustomers(
				[
					'site_id'   => $payload,
					'data_type' => 'edd_customer',
				]
			)
		);

		return [];
	}

	/**
	 * [add_site description]
	 * @param [type] $payload [description]
	 */
	public function add_site( $payload ) {
		$defaults = [
			'site_id'          => '',
			'site_name'        => '',
			'site_description' => '',
			'site_url'         => '',
			'site_logo'        => '',
			'site_status'      => 'publish',
			'site_last_synced' => Carbon::today(),
		];

		$match = [
			'site_id' => $payload['site_id'],
		];

		$update = array_merge( $defaults, $payload );

		Site::updateOrCreate( $match, $update );

		if ( ! empty( $payload['idToken'] ) ) {
			$this->add_metadata( $payload['site_id'], 'access_token', $payload['idToken'] );
		}

		return [
			'status'  => 'ok',
			'site_id' => $payload['site_id'],
		];
	}

	/**
	 * [add_metadata description]
	 * @param [type] $site_id    [description]
	 * @param [type] $meta_key   [description]
	 * @param [type] $meta_value [description]
	 */
	public function add_metadata( $site_id, $meta_key, $meta_value ) {
		if ( ! $meta_key || ! is_numeric( $site_id ) ) {
			return false;
		}

		$match = [
			'site_id'  => $site_id,
			'meta_key' => $meta_key,
		];

		$update = [
			'meta_value' => $meta_value,
		];

		return SiteMeta::updateOrCreate( $match, $update );
	}

	/**
	 * [delete_site description]
	 * @param  [type] $site_id [description]
	 * @return [type]          [description]
	 */
	public function delete_site( $site_id ) {
		// $site = Site::where( 'site_id', $site_id )->first();
		// if ( ! $site ) {
		// 	return;
		// }

		Site::where( 'site_id', $site_id )->delete();
		SiteMeta::where( 'site_id', $site_id )->delete();
		Download::where( 'store_id', $site_id )->delete();
		DownloadMeta::where( 'store_id', $site_id )->delete();
		Discount::where( 'store_id', $site_id )->delete();
		DiscountMeta::where( 'store_id', $site_id )->delete();
		Payment::where( 'store_id', $site_id )->delete();
		PaymentMeta::where( 'store_id', $site_id )->delete();
	}
}
