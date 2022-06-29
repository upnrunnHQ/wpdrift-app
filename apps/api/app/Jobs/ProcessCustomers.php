<?php

namespace App\Jobs;

use App\CustomerProcessor;

class ProcessCustomers extends Job {

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
	 * @return [type] [description]
	 */
	public function handle() {
		$processor = new CustomerProcessor( $this->payload );
		$processor->process();
	}
}
