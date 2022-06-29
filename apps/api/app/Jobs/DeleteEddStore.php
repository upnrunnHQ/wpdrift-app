<?php

namespace App\Jobs;

use App\EddStore;
use App\Customer;
use App\CustomerMeta;
use App\Discount;
use App\DiscountMeta;
use App\Download;
use App\DownloadMeta;
use App\DownloadLog;
use App\EddUser;
use App\EddUserMeta;
use App\EddLog;
use App\EddLogMeta;
use App\Payment;
use App\PaymentMeta;
use App\TermAssigned;
use App\TermTaxonomy;
use App\EddSiteJobsTrack;
use App\EddSiteTotalJobs;

class DeleteEddStore extends Job
{
    public $store_url, $store_id, $access_token;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store_url, $store_id, $access_token)
    {
        $this->store_url = $store_url;
        $this->store_id = $store_id; 
        $this->access_token = $access_token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * handling the edd setup job that will setup edd data on lumen db
         */
        try {
            // delete all tables of site/store id
            EddStore::where('store_id', $this->store_id)->delete();
            Customer::where('store_id', $this->store_id)->delete();
            CustomerMeta::where('store_id', $this->store_id)->delete();
            Discount::where('store_id', $this->store_id)->delete();
            DiscountMeta::where('store_id', $this->store_id)->delete();
            Download::where('store_id', $this->store_id)->delete();
            DownloadMeta::where('store_id', $this->store_id)->delete();
            DownloadLog::where('store_id', $this->store_id)->delete();
            EddUser::where('store_id', $this->store_id)->delete();
            EddUserMeta::where('store_id', $this->store_id)->delete();
            EddLog::where('store_id', $this->store_id)->delete();
            EddLogMeta::where('store_id', $this->store_id)->delete();
            Payment::where('store_id', $this->store_id)->delete();
            PaymentMeta::where('store_id', $this->store_id)->delete();
            TermAssigned::where('store_id', $this->store_id)->delete();
            TermTaxonomy::where('store_id', $this->store_id)->delete();
            EddSiteJobsTrack::where('site_id', $this->store_id)->delete();
            EddSiteTotalJobs::where('site_id', $this->store_id)->delete();
            \Log::info("Store data deleted:" . $this->store_id);
        } catch(\Exception $e) {
            \Log::error($e->getMessage());
            $error = $e->getMessage();
            return $error;
        }
    }
}
