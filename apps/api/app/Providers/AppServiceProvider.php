<?php

namespace App\Providers;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
// Get the EDD Site Jobs Track
//use App\EddSiteJobsTrack;
// Get total jobs for site.
//use App\EddSiteTotalJobs;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    // public function boot()
    // {
    //     Queue::before(function (JobProcessing $event) {
    //         // $event->connectionName
    //         // $event->job
    //         // $event->job->payload()
    //     });
    //     // for code execution after job completed
    //     Queue::after(function (JobProcessed $event) {
    //         $jobid = $event->job->getJobId();
    //         $get_job_details = EddSiteJobsTrack::where('job_id', $jobid)->first();
    //         /**
    //          * send status to the spark site.
    //          */
    //         // 1. Get total remaining jobs
    //         $tlt_obj = EddSiteTotalJobs::where('site_id', $get_job_details->site_id)->first();
    //         $get_job_totals = $tlt_obj->total_jobs;
    //         // Send the percentage of job
    //         // delete the job from db
    //         EddSiteJobsTrack::where('job_id', $jobid)->delete();
            
    //     });
    // }
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
