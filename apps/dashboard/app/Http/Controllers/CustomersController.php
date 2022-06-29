<?php
// App/Http/Controllers/CustomersController.php
/** For setting up the customers so that customer
* Rest Calls
*/
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// For adding OAuth2 Adoy

use App\UserDefaultStore;
use App\Store;
use Symfony\Component\HttpFoundation\Session\Session;
use Laravel\Spark\Configuration\ProvidesScriptVariables;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class CustomersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show customers listing, and response as json for API call #/customers
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $gclient = new Client();
        $items = $total_pages = 0;
        $get_default_store = UserDefaultStore::where('user_id', Auth::user()->id)
                      ->first();
        if ($get_default_store) {
            $default_store = $get_default_store->store_id;
        } else {
            $default_store = "";
            return response()->json(
                        [
                          'code'=> 'default_store_not_set',
                          'message' => "You have to chose current store.",
                          'data' => ['status' => 401]
                        ],
                200
            );
        }
        // setup the store information based on default store
        if ($default_store != "") {
            $store_info = Store::where('id', $default_store)
                        ->first();
            $companies_store_credentials = unserialize($store_info->companies_store_credentials);
            $store_url = $store_info->auth_server_url;
            $api_end_point = '/wp-json/wpdriftio/v1/users-list/';
            $spark_provide_vars = ProvidesScriptVariables::scriptVariables();
            if(empty($spark_provide_vars['customersPageSess'])) {
              $spark_provide_vars['customersPageSess']['per_page'] = 10;
              $spark_provide_vars['customersPageSess']['page'] = 1;
              $spark_provide_vars['customersPageSess']['orderby'] = 'name';
              $spark_provide_vars['customersPageSess']['search'] = '';
            }
            $context = isset($_GET['context']) ? trim(($_GET['context'])) : 'edit';
            $per_page = isset($_GET['per_page']) ? trim(($_GET['per_page'])) : $spark_provide_vars['customersPageSess']['per_page'];
            $page = isset($_GET['page']) ? trim(($_GET['page'])) : $spark_provide_vars['customersPageSess']['page'];
            $orderby = isset($_GET['orderby']) ? trim(($_GET['orderby'])) : $spark_provide_vars['customersPageSess']['orderby'];
            $order = isset($_GET['order']) ? trim(($_GET['order'])) : 'asc';
            $search = isset($_GET['search']) ? trim(($_GET['search'])) : $spark_provide_vars['customersPageSess']['search'];
            $before = isset($_GET['before']) ? trim(($_GET['before'])) : '';
            $after = isset($_GET['after']) ? trim(($_GET['after'])) : '';
            // Setup the session variales for state maintain gitlab #2
            $session = new Session();
            // save variables in session
            $orderby_sess = $session->set('orderby_sess', $orderby);
            $search_sess = $session->set('search_sess', $search);
            $per_page_sess = $session->set('per_page_sess', $per_page);
            $page_sess = $session->set('page_sess', $page);
            if (
              (isset($_GET['orderby']) && trim($_GET['orderby']) != "") || (isset($_GET['per_page']) && trim($_GET['per_page']) != "") ||
              (isset($_GET['search']) && trim($_GET['search']) != "") || (isset($_GET['before']) && trim($_GET['before']) != "") || (isset($_GET['after']) && trim($_GET['after']) != "")
            ) {
                $page_sess = $session->set('page_sess', 1);
            }

            // Prepare URL for rest
            $url = $store_url . $api_end_point;
            $url .= '?context=' . $context;
            $url .= '&per_page=' . $per_page;
            $url .= '&page=' . $page;
            $url .= '&order=' . $order;
            $url .= '&orderby=' . $orderby;
            $url .= '&search=' . urlencode($search);

            if ($before != "") {
                $url .= '&before=' . $before;
            }

            if ($after != "") {
                $url .= '&after=' . $after;
            }
            try {
              // Guzzle usage
              $request_var = $gclient->request(
                  'GET',
                  $url,
                  [
                      'headers' =>
                          [
                              'Authorization' => 'Bearer ' . $companies_store_credentials['access_token_info']['result']['access_token']
                          ]
                  ]
              );
              $gresponse = $request_var->getBody()->getContents();
              $customers = trim($gresponse);
              $gheaders = $request_var->getHeaders();
              $get_status_code = $request_var->getStatusCode();
              // get total page and total items from header response
              $items = trim($gheaders['X-WP-Total'][0]);
              $total_pages = trim($gheaders['X-WP-TotalPages'][0]);
              // check first header info contains 200 OK
              if ($get_status_code == "200") {
                  return response()->json(
                      [
                      'customers' => json_decode($customers),
                      'total_pages' => $total_pages,
                      'items' => $items
                      ],
                      200
                  );
              } else {
                  $error_msg = $this->get_error_message_from_code($get_status_code);
                  return response()->json(
                      [
                      'code'=> 'server_error',
                      'message' => $error_msg,
                      'data' => [
                          'status' => 500
                          ]
                      ],
                      200
                  );
              }
            } catch (\Exception $e) {
              $error_msg = $e->getMessage();
              return response()->json(
                  [
                  'code'=> 'server_error',
                  'message' => $error_msg,
                  'data' => [
                      'status' => 500
                      ]
                  ],
                  200
              );
            }
        }
    }
    // show user single details based on passed id
    public function show($id)
    {
        $gclient = new Client();
        $get_default_store = UserDefaultStore::where('user_id', Auth::user()->id)
                    ->first();
        if ($get_default_store) {
            $default_store = $get_default_store->store_id;
        } else {
            $default_store = "";
            return response()->json(
                [
                    'code'=> 'default_store_not_set',
                    'message' => "You have to chose current store.",
                    'data' =>
                    [
                    'status' => 401
                    ]
                ],
                200
            );
        }

        // setup the store information
        if ($default_store != "") {
            $store_info = Store::where('id', $default_store)
                      ->first();
            $companies_store_credentials = unserialize($store_info->companies_store_credentials);
            $store_url = $store_info->auth_server_url;
            $context = isset($_GET['context']) ? trim(($_GET['context'])) : 'edit';
            $api_end_point = '/wp-json/wpdriftio/v1/users-list/'.$id;
            // Prepare URL for rest
            $url = $store_url  . $api_end_point . '?context=' . $context;
            try {
              // Guzzle usage
              $request_var = $gclient->request(
                  'GET',
                  $url,
                  [
                      'headers' =>
                          [
                              'Authorization' => 'Bearer ' . $companies_store_credentials['access_token_info']['result']['access_token']
                          ]
                  ]
              );

              $gresponse = $request_var->getBody()->getContents();
              $customer = trim($gresponse);
              $get_status_code = $request_var->getStatusCode();
              // check first header info contains 200 OK
              if ($get_status_code == "200") {
                  return response()->json(
                      [
                      'customer' => json_decode($customer)
                      ],
                      200
                  );
              } else {
                  $error_msg = $this->get_error_message_from_code(get_status_code);
                  return response()->json(
                      [
                      'code'=> 'server_error',
                      'message' => $error_msg,
                      'data' =>
                      [
                          'status' => $get_status_code
                          ]
                      ],
                      200
                  );
              }
            } catch (\Exception $e) {
              $error_msg = $e->getMessage();
              return response()->json(
                  [
                  'code'=> 'server_error',
                  'message' => $error_msg,
                  'data' => [
                      'status' => 500
                      ]
                  ],
                  200
              );
            }

        }
    }

    private function get_error_message_from_code($code)
    {
        $error_message = "";
        switch ($code) {
            case '301':
              $error_message = 'Error: 301 (Moved Permanently)';
              break;
            case '302':
              $error_message = 'Error: 302 (Found)';
              break;
            case '302':
              $error_message = 'Error: 302 (Found)';
              break;
            case '303':
              $error_message = 'Error: 303 (See Other)';
              break;
            case '303':
              $error_message = 'Error: 303 (See Other)';
              break;
            case '307':
              $error_message = 'Error: 307 (Temporary Redirect)';
              break;
            case '400':
              $error_message = 'Error: 400 (Bad Request)';
              break;
            case '401':
              $error_message = 'Error: 401 (Unauthorized)';
              break;
            case '403':
              $error_message = 'Error: 403 (Forbidden)';
              break;
            case '404':
              $error_message = 'Error: 404 (Not Found)';
              break;
            case '405':
              $error_message = 'Error: 405 (Method Not Allowed)';
              break;
            case '406':
              $error_message = 'Error: 406 (Not Acceptable)';
              break;
            case '412':
              $error_message = 'Error: 412 (Precondition Failed)';
              break;
            case '500':
              $error_message = 'Error: 500 (Internal Server Error)';
              break;
            case '501':
              $error_message = 'Error: 501 (Not Implemented)';
              break;
      }
        return $error_message;
    }
}
