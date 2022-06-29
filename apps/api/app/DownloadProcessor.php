<?php

namespace App;

use App\Site;
use App\SiteMeta;
use App\Download;
use App\DownloadMeta;
use App\Jobs\ProcessFetch;
use GuzzleHttp\Client;

/**
 * [DownloadProcessor description]
 */
class DownloadProcessor {

	protected $payload, $site, $access_token;

	/**
	 * [process description]
	 * @param  [type] $payload [description]
	 * @return [type]          [description]
	 */
	public function process( $payload ) {
		$this->prepare( $payload );

		$count_downloads = $this->count_downloads();
		$all_ids         = $this->fetch_all();
		$updated_ids     = $this->fetch_updated();
		$remove          = $this->remove_deleted();
		$queue           = $this->prepare_queue();
		$jobs            = $this->add_queue_jobs();
		// $this->update_status();

		return [
			'payload'         => $payload,
			'count_downloads' => $count_downloads,
			'jobs'            => $jobs,
			'site'            => $this->site,
			'remove'          => $remove,
			// 'all_ids'         => $all_ids,
			'updated_ids'     => $updated_ids,
			'synced_ids'      => $this->get_synced(),
			'queue'           => $queue,
		];
	}

	/**
	 * [__construct description]
	 * @param [type] $payload [description]
	 */
	public function prepare( $payload ) {
		$this->payload = $payload;

		$site = Site::where( 'site_id', $payload )->first();
		if ( $site ) {
			$this->site         = $site;
			$this->access_token = $this->get_site_meta( $payload, 'access_token' );
		}
	}

	/**
	 * [count_downloads description]
	 * @return [type] [description]
	 */
	public function count_downloads() {
		$client = new Client();
		$query  = [];

		/**
		 * [try description]
		 * @var [type]
		 */
		try {
			$response = $client->request(
				'GET',
				$this->site->site_url . '/wp-json/wpdriftio/v1/getdownloads/counts',
				[
					'headers' => [ 'Authorization' => 'Bearer ' . $this->access_token ],
					'query'   => $query,
				]
			);

			$count_downloads = json_decode( $response->getBody()->getContents() );
			$this->add_site_meta( $this->site->site_id, 'download_counts', $count_downloads );
			return $count_downloads;
		} catch ( RequestException | ClientException $e ) {
			if ( $e->hasResponse() ) {
			}
		}
	}

	/**
	 * [fetch_all description]
	 * @param  [type] $site [description]
	 * @return [type]       [description]
	 */
	public function fetch_all() {
		$client = new Client();
		$query  = [];

		/**
		 * [try description]
		 * @var [type]
		 */
		try {
			$response = $client->request(
				'GET',
				$this->site->site_url . '/wp-json/wpdriftio/v1/getdownloads/ids',
				[
					'headers' => [ 'Authorization' => 'Bearer ' . $this->access_token ],
					'query'   => $query,
				]
			);

			$download_ids = json_decode( $response->getBody()->getContents() );
			$this->add_site_meta( $this->site->site_id, 'download', $download_ids );
			return $download_ids;
		} catch ( RequestException | ClientException $e ) {
			if ( $e->hasResponse() ) {

			}
		}
	}

	/**
	 * [fetch_updated description]
	 * @param  [type] $site [description]
	 * @return [type]       [description]
	 */
	public function fetch_updated() {
		$client = new Client();
		$query  = [];

		$download_synced = $this->get_site_meta( $this->site->site_id, 'download_synced' );
		if ( empty( $download_synced ) ) {
			return;
		}

		// $download_counts = $this->get_site_meta( $this->site->site_id, 'download_counts' );
		// if ( count( $download_synced ) != $download_counts ) {
		// 	return;
		// }

		$last_download = Download::orderBy( 'post_modified', 'desc' )
		->first();
		if ( ! $last_download ) {
			return;
		}

		$query['after'] = $last_download->post_modified;

		/**
		 * [try description]
		 * @var [type]
		 */
		try {
			$response = $client->request(
				'GET',
				$this->site->site_url . '/wp-json/wpdriftio/v1/getdownloads/ids_updated',
				[
					'headers' => [ 'Authorization' => 'Bearer ' . $this->access_token ],
					'query'   => $query,
				]
			);

			$download_updated = json_decode( $response->getBody()->getContents() );
			$this->add_site_meta( $this->site->site_id, 'download_updated', $download_updated );
			return $download_updated;
		} catch ( RequestException | ClientException $e ) {
			if ( $e->hasResponse() ) {

			}
		}
	}

	/**
	 * [remove_deleted description]
	 * @param  [type] $site [description]
	 * @return [type]       [description]
	 */
	public function remove_deleted() {
		$download        = $this->get_site_meta( $this->site->site_id, 'download' );
		$download_synced = $this->get_site_meta( $this->site->site_id, 'download_synced' );

		if ( empty( $download ) || empty( $download_synced ) ) {
			return [];
		}

		if ( count( $download ) > count( $download_synced ) ) {
			return [];
		}

		$removed = [];
		foreach ( $download_synced as $download_id ) {
			if ( ! in_array( $download_id, $download ) ) {
				$removed[] = $download_id;
				$this->remove_download( $download_id );
			}
		}

		return [
			'removed'  => $removed,
			'download' => count( $download ),
		];
	}

	/**
	 * [remove_download description]
	 * @param  [type] $download [description]
	 * @return [type]           [description]
	 */
	public function remove_download( $download ) {
		Download::where( 'store_id', $this->site->site_id )
		->where( 'post_id', (int) $download )
		->delete();

		DownloadMeta::where( 'store_id', $this->site->site_id )
		->where( 'post_id', (int) $download )
		->delete();

		$download_synced = $this->get_site_meta( $this->site->site_id, 'download_synced' );
		if ( ! empty( $download_synced ) && in_array( $download, $download_synced ) ) {
			$synced = [];
			foreach ( $download_synced as $download_id ) {
				if ( $download_id != $download ) {
					$synced[] = $download_id;
				}
			}

			$this->add_site_meta( $this->site->site_id, 'download_synced', $synced );
		}
	}

	/**
	 * [prepare_queue description]
	 * @param  [type] $site [description]
	 * @return [type]       [description]
	 */
	public function prepare_queue() {
		$download_queue   = [];
		$download         = $this->get_site_meta( $this->site->site_id, 'download' );
		$download_synced  = $this->get_site_meta( $this->site->site_id, 'download_synced' );
		$download_queued  = $this->get_site_meta( $this->site->site_id, 'download_queued' );
		$download_updated = $this->get_site_meta( $this->site->site_id, 'download_updated' );

		if ( empty( $download_synced ) ) {
			if ( ! empty( $download ) ) {
				$download_queue = array_merge( $download_queue, $download );
			}
		}

		if ( ! empty( $download_updated ) ) {
			$download_queue = array_merge( $download_queue, $download_updated );
		}

		if ( empty( $download_queued ) && ! empty( $download_synced ) && ( count( $download ) > count( $download_synced ) ) ) {
			foreach ( $download as $queue ) {
				if ( ! in_array( $queue, $download_synced ) ) {
					$download_queue[] = $queue;
				}
			}
		}

		$download_queue = array_unique( $download_queue );
		$this->add_site_meta( $this->site->site_id, 'download_queue', $download_queue );
		return  $download_queue;
	}

	/**
	 * [get_synced description]
	 * @return [type] [description]
	 */
	public function get_synced() {
		$download        = $this->get_site_meta( $this->site->site_id, 'download' );
		$download_synced = $this->get_site_meta( $this->site->site_id, 'download_synced' );
		return [
			'count_downloads' => $this->count_downloads(),
			'download'        => count( $download ),
			'download_synced' => count( $download_synced ),
			'count'           => Download::where( 'store_id', $this->site->site_id )->count(),
			'access_token'    => $this->access_token,
		];
	}

	/**
	 * [add_site_meta description]
	 * @param [type] $site_id    [description]
	 * @param [type] $meta_key   [description]
	 * @param [type] $meta_value [description]
	 */
	public function add_site_meta( $site_id, $meta_key, $meta_value ) {
		if ( ! $meta_key || ! is_numeric( $site_id ) ) {
			return false;
		}

		return SiteMeta::updateOrCreate(
			[
				'site_id'  => $site_id,
				'meta_key' => $meta_key,
			],
			[
				'meta_value' => $meta_value,
			]
		);
	}

	/**
	 * [get_site_meta description]
	 * @param  [type] $site_id  [description]
	 * @param  [type] $meta_key [description]
	 * @return [type]           [description]
	 */
	public function get_site_meta( $site_id, $meta_key ) {
		$meta = SiteMeta::where( 'site_id', $site_id )
		->where( 'meta_key', $meta_key )
		->first();

		if ( ! $meta ) {
			return false;
		}

		return $meta->meta_value;
	}

	/**
	 * [add_queue_jobs description]
	 * @param [type] $site [description]
	 */
	public function add_queue_jobs() {
		$payload = [
			'fetch_type'  => 'download',
			'site_id'     => $this->site->site_id,
			'download_id' => 0,
		];

		$downloads = $this->get_site_meta( $this->site->site_id, 'download_queue' );
		if ( empty( $downloads ) ) {
			return [];
		}

		$jobs            = [];
		$download_queued = [];
		$queued          = [];
		$meta_queued     = $this->get_site_meta( $this->site->site_id, 'download_queued' );
		if ( $meta_queued ) {
			$download_queued = $meta_queued;
		}

		// foreach ( $downloads as $download ) {
		// 	if ( ! in_array( $download, $download_queued ) ) {
		// 		$payload['download_id'] = $download;
		// 		dispatch( new ProcessFetch( $payload ) );
		// 		$queued[] = $download;
		//
		// 		$jobs[] = $payload;
		// 	}
		// }

		$this->add_site_meta( $this->site->site_id, 'download_queued', array_merge( $download_queued, $queued ) );
		return $jobs;
	}
}
