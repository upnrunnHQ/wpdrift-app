<?php

namespace App\Jobs;

use App\SiteProcessor;

class ProcessDelete extends Job {

	protected $payload;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct( $payload ) {
		$this->payload = $payload;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle( SiteProcessor $processor ) {
		// $processor->delete_site( $this->payload );
	}
}
