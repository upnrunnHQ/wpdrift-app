<?php

namespace App\Jobs;

use App\SiteProcessor;

class AddSite extends Job {

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
	 * @param  SiteProcessor $processor [description]
	 * @return [type]                   [description]
	 */
	public function handle( SiteProcessor $processor ) {
		$processor->add_site( $this->payload );
	}
}
