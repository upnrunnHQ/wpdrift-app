<?php

namespace App\Jobs;

use App\SiteProcessor;

class ProcessSite extends Job {

	protected $payload;

	/**
	 * [__construct description]
	 * @param [type] $payload [description]
	 */
	public function __construct( $payload ) {
		$this->payload = $payload;
	}

	/**
	 * [handle description]
	 * @param  DownloadProcessor $processor [description]
	 * @return [type]                       [description]
	 */
	public function handle( SiteProcessor $processor ) {
		$processor->process_site( $this->payload );
	}
}
