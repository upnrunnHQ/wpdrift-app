<?php

namespace App\Jobs;

class ProcessPosts extends Job {

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
		$this->payload->fetch();
	}
}
