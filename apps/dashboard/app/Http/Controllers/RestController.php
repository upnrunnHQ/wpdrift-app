<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\UserDefaultStore;
use App\Store;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

class RestController extends Controller {
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware( 'auth' );
	}

	/**
	 * [get_token description]
	 * @return [type] [description]
	 */
	public function get_token( Request $request ) {
		/**
		 * [$response description]
		 * @var array
		 */
		$response = [];
		$client   = new Client( [ 'base_uri' => 'https://dhakadesk.com/wp-json/wpdriftio/v1/clients' ] );

		try {
			/**
			 * [$response description]
			 * @var [type]
			 */
			$response = $client->request( 'GET' );

			/**
			 * [return description]
			 * @var [type]
			 */
			$response = [
				'headers'  => $response->getHeaders(),
				'contents' => json_decode( $response->getBody()->getContents() ),
			];

		} catch ( RequestException | ClientException $e ) {
			/**
			 * [if description]
			 * @var [type]
			 */
			if ( $e->hasResponse() ) {
				$response = [
					'contents' => json_decode( $e->getResponse()->getBody()->getContents() ),
				];
			}
		}
		return response()->json( $response );
	}

	/**
	 * [statistics description]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function statistics( Request $request ) {
		/**
		 * [$http_data description]
		 * @var array
		 */
		$http_request_data = [
			'route' => 'wpdriftio/v1/statistics',
			'query' => $request->query(),
		];

		/**
		 * [$response description]
		 * @var [type]
		 */
		$response = $this->http_request( $http_request_data );

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( isset( $response['code'] ) && 'bad_server_response' == $response['code'] ) {
			return response()->json( $response );
		}

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( isset( $response['contents'] ) ) {
			return response()->json( $response['contents'] );
		}
	}

	/**
	 * [events description]
	 * @return [type] [description]
	 */
	public function events( Request $request ) {
		/**
		 * [$events description]
		 * @var array
		 */
		$events = [];

		/**
		 * [$query description]
		 * @var [type]
		 */
		$query = $request->query();
		$type  = $request->query( 'type' );

		/**
		 * [switch description]
		 * @var [type]
		 */
		switch ( $type ) {
			case 'customer':
				$customers = $this->get_response(
					[
						'route' => 'api/recent_customers/',
						'query' => $query,
						'edd'   => true,
					]
				);

				/**
				 * [if description]
				 * @var [type]
				 */
				if ( ! empty( $customers ) ) {
					$events = $customers;
				}

				break;
			case 'order':
				$orders = $this->get_response(
					[
						'route' => 'api/recent_orders/',
						'query' => $query,
						'edd'   => true,
					]
				);
				/**
				 * [if description]
				 * @var [type]
				 */
				if ( ! empty( $orders ) ) {
					$events = $orders;
				}

				break;
			default:
				/**
				 * [$customers description]
				 * @var [type]
				 */
				$customers = $this->get_response(
					[
						'route' => 'api/recent_customers/',
						'query' => $query,
						'edd'   => true,
					]
				);

				if ( ! empty( $customers ) && ! ( isset( $customers['code'] ) && 'bad_server_response' == $customers['code'] ) ) {
					$events = array_merge( $events, $customers );
				}

				/**
				 * [$orders description]
				 * @var [type]
				 */
				$orders = $this->get_response(
					[
						'route' => 'api/recent_orders/',
						'query' => $query,
						'edd'   => true,
					]
				);

				if ( ! empty( $orders ) && ! ( isset( $orders['code'] ) && 'bad_server_response' == $orders['code'] ) ) {
					$events = array_merge( $events, $orders );
				}

				/**
				 * [usort description]
				 * @var [type]
				 */
				usort(
					$events,
					function( $a, $b ) {
						return strtotime( $b->event_date ) - strtotime( $a->event_date );
					}
				);
		}

		/**
		 * [return description]
		 * @var [type]
		 */
		return response()->json( $events );
	}

	/**
	 * [edd_request description]
	 * @param  [type] $atts [description]
	 * @return [type]       [description]
	 */
	public function get_response( $atts ) {
		/**
		 * [$data description]
		 * @var array
		 */
		$data = [];

		$response = $this->http_request( $atts );
		if ( isset( $response['code'] ) && 'bad_server_response' == $response['code'] ) {
			return $response;
		}

		if ( isset( $response['contents'] ) ) {
			return $response['contents'];
		}

		/**
		 * [return description]
		 * @var [type]
		 */
		return $data;
	}

	/**
	 * [posts description]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function customers( Request $request ) {
		/**
		 * [$http_data description]
		 * @var array
		 */
		$http_request_data = [
			'route' => 'wpdriftio/v1/users-list',
			'query' => $request->query(),
		];

		/**
		 * [$response description]
		 * @var [type]
		 */
		$response = $this->prepare_customers( $http_request_data );

		/**
		 * [return description]
		 * @var [type]
		 */
		return $response;
	}

	/**
	 * [prepare_customers description]
	 * @return [type] [description]
	 */
	public function prepare_customers( $http_request_data ) {
		/**
		 * [$data description]
		 * @var array
		 */
		$data = [];

		/**
		 * [$response description]
		 * @var [type]
		 */
		$response = $this->http_request( $http_request_data );

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( isset( $response['code'] ) && 'bad_server_response' == $response['code'] ) {
			return response()->json( $response );
		}

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( isset( $response['contents'] ) && isset( $http_request_data['query']['search'] ) && ( '' != $http_request_data['query']['search'] ) ) {
			$headers = $this->prepare_headers( $response );
			return response()->json( $response['contents'] )->withHeaders( $headers );
		}

		/**
		 * [unset description]
		 * @var [type]
		 */
		unset( $http_request_data['query']['after'], $http_request_data['query']['before'] );
		$response = $this->http_request( $http_request_data );

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( isset( $response['code'] ) && 'bad_server_response' == $response['code'] ) {
			return response()->json( $response );
		}

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( isset( $response['contents'] ) ) {
			$headers = $this->prepare_headers( $response );
			return response()->json( $response['contents'] )->withHeaders( $headers );
		}

		/**
		 * [return description]
		 * @var [type]
		 */
		return $data;
	}

	/**
	 * [prepare_headers description]
	 * @param  [type] $response [description]
	 * @return [type]           [description]
	 */
	public function prepare_headers( $response ) {
		/**
		 * [$headers description]
		 * @var array
		 */
		$headers = [];

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( ! isset( $response['headers'] ) ) {
			return $headers;
		}

		/**
		 * [$headers description]
		 * @var array
		 */
		$headers = [
			'X-WP-Total'      => $response['headers']['X-WP-Total'][0],
			'X-WP-TotalPages' => $response['headers']['X-WP-TotalPages'][0],
		];

		/**
		 * [return description]
		 * @var [type]
		 */
		return $headers;
	}

	/**
	 * [http_request description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function http_request( $http_data ) {
		/**
		 * [$store_details description]
		 * @var [type]
		 */
		$store_details = $this->store_details();

		/**
		 * [if description]
		 * @var [type]
		 */
		if ( ! isset( $store_details['idToken'] ) ) {
			return response()->json( $store_details );
		}

		/**
		 * [$base_url description]
		 * @var [type]
		 */
		$base_url = $store_details['api_uri'] . '/wp-json/';
		$route    = $http_data['route'];
		if ( isset( $http_data['edd'] ) && $http_data['edd'] ) {
			$base_url = config( 'app.edd_server_url' );
			$route    = '/' . $http_data['route'] . $store_details['store_id'];
		}

		// Guzzle usage
		$client = new Client( [ 'base_uri' => $base_url ] );

		/**
		 * [try description]
		 * @var [type]
		 */
		try {
			/**
			 * [$response description]
			 * @var [type]
			 */
			$response = $client->request(
				'GET',
				$route,
				[
					'headers' => [ 'Authorization' => 'Bearer ' . $store_details['idToken'] ],
					'query'   => $http_data['query'],
				]
			);

			/**
			 * [return description]
			 * @var [type]
			 */
			return [
				'headers'  => $response->getHeaders(),
				'contents' => json_decode( $response->getBody()->getContents() ),
			];

		} catch ( RequestException | ClientException $e ) {
			/**
			 * [if description]
			 * @var [type]
			 */
			if ( $e->hasResponse() ) {
				return [
					'contents' => json_decode( $e->getResponse()->getBody()->getContents() ),
				];
			}
		}

		/**
		 * [return description]
		 * @var [type]
		 */
		return [
			'code'    => 'bad_server_response',
			'message' => __( 'We did not get any response form your store.' ),
			'data'    => [ 'status' => 404 ],
		];
	}

	/**
	 * [store_details description]
	 * @return [type] [description]
	 */
	public function store_details() {
		/**
		 * [$default_store description]
		 * @var [type]
		 */
		$default_store = UserDefaultStore::where( 'user_id', Auth::user()->id )->first();

		/**
		 * Exit early.
		 * @var [type]
		 */
		if ( ! $default_store ) {
			return [
				'code'    => 'default_store_not_set',
				'message' => __( 'Plese set up a default store to get details.' ),
				'data'    => [ 'status' => 401 ],
			];
		}

		/**
		 * [$store_info description]
		 * @var [type]
		 */
		$store                       = Store::where( 'id', $default_store->store_id )->first();
		$companies_store_credentials = unserialize( $store->companies_store_credentials );
		return [
			'store_id'    => $store->id,
			'api_uri'     => $store->auth_server_url,
			'credentials' => $companies_store_credentials,
			'idToken'     => $companies_store_credentials['access_token_info']['result']['access_token'],
		];
	}
}
