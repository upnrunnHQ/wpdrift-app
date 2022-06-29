<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\SiteProcessor;

class AddSite implements ShouldQueue {

	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
	public function handle() {
		$processor = new SiteProcessor( $this->payload );
		$processor->add_site();
	}
}
