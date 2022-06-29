<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\EddStore;
use App\Jobs\ProcessSinglePayment;
use App\Payment;

class GetLastDayPayments extends Command
{
    /*
    * The name and signature of the console command.
    *
    * @var string
    */
    protected $signature = 'GetLastDayPayments:getpayments';

    /**
    * The console command description.
    *
    * @var string
    */
    protected $description = 'Get Last Day Payments';

    /**
    * Create a new command instance.
    *
    * @return void
    */
    public function __construct()
    {
        parent::__construct();
    }

    /**
    * Execute the console command.
    *
    * @return mixed
    */
    public function handle()
    {
        $all_stores = EddStore::where('store_id', '!=', '')->get();
        foreach ($all_stores as $single_store) {
            // get the payment from store.
            $url = $single_store->store_url;
            $id = $single_store->store_id;
            $access_token = $single_store->store_access_token;
            $end_point = 'wp-json/wpdriftio/v1/getdaypayments';
            try {
              $payments_id = $this->gclient_request_response($url . '/' . $end_point, $access_token);
              if($payments_id) {
                foreach ($payments_id->edd_payments as $payment_id) {
                    // first check that is payment exits with ID
                    $payment_exists = Payment::where('store_id', $id)
                    ->where('post_id', (int) $payment_id)->exists();
                    if( ! $payment_exists ) {
                        $job = new ProcessSinglePayment($url, $id, $access_token, 'getpayments', $payment_id);
                        dispatch($job->onQueue('payment'));
                    }
                }
              }
            } catch (\Exception $e) {
                $error = $e->getMessage();
                \Log::error($error);
            }
        }
    }
    /**
     * General cURL function
     */
    protected function gclient_request_response($url, $access_token)
    {
        $gclient = new Client();
        $request_var = $gclient->request(
            'GET',
            $url,
            [
                'headers' =>
                    [
                        'Authorization' => 'Bearer ' . $access_token
                    ]
            ]
        );
        $gresponse = $request_var->getBody()->getContents();
        $edd_api_response = trim($gresponse);
        $req_jsn_decode = json_decode($edd_api_response);
        return $req_jsn_decode;
    }
}
?>
