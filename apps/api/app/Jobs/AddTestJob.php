<?php

namespace App\Jobs;

use App\SiteProcessor;

class AddTestJob extends Job {

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
	public function handle() {
		$processor = new SiteProcessor();
        $processor->test_add( $this->payload );
	}
}
