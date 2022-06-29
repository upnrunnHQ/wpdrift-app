<?php

namespace App\Http\Controllers;
use App\Jobs\ProcessPayment;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\GeneralController;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class TestSync extends Controller
{
    public function show()
    {
        $store_id        = 83;
        $store_url       = 'https://wpdrift.com';
        $access_token    = '864612f1d8f0aad4cfabb58233ce4d32656a38a3';
        $page            = 1;
        $edd_wp_endpoint = 'getpayments';
        $per_page        = 100;
        $offset          = 0;

        // $job = new ProcessPayment($store_url, $store_id, $access_token, $edd_wp_endpoint, $page, $per_page, $offset);
        // dispatch($job->onQueue('payment'));
        //
        // Log::info('Process queue.');

        $edd_api_end_point = '/wp-json/wpdriftio/v1/'.$edd_wp_endpoint.'/';
        $url = $store_url . $edd_api_end_point;
        $url .= "?per_page={$per_page}&offset={$offset}";
        $response = $this->gclient_request_response($url, $access_token);
        $edd_payments = $response->edd_payments;

        return $edd_payments;
    }

    public function gclient_request_response($url, $access_token)
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
