<?php

namespace App\Jobs;

use App\Fetch\Fetch;

class ProcessFetch extends Job {

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
	public function handle() {
		$fetch = new Fetch( $this->payload );
		$fetch->process();
	}
}
