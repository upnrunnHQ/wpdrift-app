<?php

namespace App\Console\Commands;

use App\Site;
use App\Jobs\ProcessSite;
use Illuminate\Console\Command;

class ProcessSites extends Command {

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'process:sites';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Process sites';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 * @param  Site   $site [description]
	 * @return [type]       [description]
	 */
	public function handle( Site $site ) {
		$sites = Site::pluck( 'site_id' )->toArray();
		if ( ! $sites ) {
			return;
		}

		foreach ( $sites as $payload ) {
			dispatch( new ProcessSite( $payload ) );
		}
	}
}
