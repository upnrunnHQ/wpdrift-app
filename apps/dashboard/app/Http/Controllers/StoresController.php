<?php
// app/Http/Controllers/StoresController.php
/**
* For managing stores related stuffs.
**/
namespace App\Http\Controllers;

use App\Store;
use URL;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Company;
use App\CompanyUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
// For adding OAuth2 Adoy
use OAuth2\Client;
use App\UserDefaultStore;
use App\Rules\ValidStoreURL;
use App\Rules\ValidDuplicateStoreURL;
use Intervention\Image\ImageManagerStatic as Image;
// Used custom helpers for adding custom method
use Helpers;
use App\Events\StoreCreated;
use GuzzleHttp\Client as GuzzleHttp;
use App\SiteProcessor;
use App\Jobs\DeleteSite;
use Illuminate\Support\Facades\Log;

class StoresController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware( 'auth' );
		// Exclude some method for middleware that will check store's user
		$this->middleware(
			'storeUser',
			[
				'except' => [
					'oauthstore',
					'setdefaultstore',
				],
			]
		);
	}

	public function addstore( Request $request, Company $company ) {
		// check first is company there for this user or not.
		$companies = Company::where( 'user_id', Auth::user()->id )
						  ->get();
		// Add a store under company first
		if ( $request->input( 'site' ) != '' ) {
			// Setting up the default store
			$store                            = Store::where( 'user_id', Auth::user()->id )
							  ->where( 'id', $request->input( 'site' ) )
							  ->first();
			$companies_store_credentials      = $store->companies_store_credentials;
			$companies_store_credentials_arry = unserialize( $companies_store_credentials );
			$oauth_access_token               = $companies_store_credentials_arry['access_token_info']['result']['access_token'];
			// Set this store as default store
			if ( $request->query( 'default' ) != '' && $oauth_access_token != '' ) {
				// set the variable store id in Session
				$default_store = $request->input( 'store' );
				if ( Auth::check() ) {
					// get default is having record
					$chkdftstr = UserDefaultStore::where( 'user_id', Auth::user()->id )
										->first();
					if ( ! $chkdftstr ) {
						// add default store
						$userdefaultstore = UserDefaultStore::create(
							[
								'user_id'  => Auth::user()->id,
								'store_id' => $default_store,
							]
						);
					} else {
						// update the default store
						$updatedefaultstore = UserDefaultStore::where( 'user_id', Auth::user()->id )
										->update(
											[
												'store_id' => $default_store,
											]
										);
					}
				}
			}

			// check and retrieve default store id
			$gtdftstr = UserDefaultStore::where( 'user_id', Auth::user()->id )
								->first();
			if ( $gtdftstr ) {
				$default_store = $gtdftstr->store_id;
			} else {
				$default_store = '';
			}
			if ( $store ) {
				$single_company = Company::where( 'user_id', Auth::user()->id )
				->where( 'id', $store->company_id )
				->first();
				// show store details and update the information
				return view(
					'companies.stores.showstore',
					[
						'company'            => $single_company,
						'store'              => $store,
						'default_store'      => $default_store,
						'oauth_access_token' => $oauth_access_token,
					]
				);
			}
		}
		// check that current user can not have more than 5 stores & 5 companies
		$companies_store_count = Company::where( 'user_id', Auth::user()->id )->count();
		$stores_count          = Store::where( 'user_id', Auth::user()->id )->count();
		// Show the form for adding store
		return view(
			'companies.stores.addstore',
			[
				'companies'             => $companies,
				'companies_store_count' => $companies_store_count,
				'stores_count'          => $stores_count,
			]
		);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		// Listing out the Stores
		if ( Auth::check() ) {
			// get default Store
			$get_default_store = UserDefaultStore::where( 'user_id', Auth::user()->id )
							->first();
			if ( $get_default_store ) {
				$default_store = $get_default_store->store_id;
			} else {
				$default_store = '';
			}
			$stores = Store::where( 'user_id', Auth::user()->id )->get();
			if ( isset( $_GET['response'] ) && trim( $_GET['response'] ) == 'json' ) {
				return response()->json(
					[
						'stores'        => $stores,
						'default_store' => $default_store,
					],
					200
				);
			}
			return view(
				'settings.sites',
				[
					'sites'         => $stores,
					'default_store' => $default_store,
				]
			);
		}
		return view( 'auth.login' );
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		// check that current user can not have more than 20 stores & 20 companies
		$companies             = Company::where( 'user_id', Auth::user()->id )->get();
		$companies_store_count = Company::where( 'user_id', Auth::user()->id )->count();
		if ( $companies_store_count == 0 ) {
			// redirect user to company add page.
			return redirect()->route(
				'companies.create'
			);
		}
		$stores_count = Store::where( 'user_id', Auth::user()->id )->count();
		// Show the form for adding store
		return view(
			'settings.site.add',
			[
				'companies'             => $companies,
				'companies_store_count' => $companies_store_count,
				'stores_count'          => $stores_count,
			]
		);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store( Request $request, Company $company ) {
		$request->validate(
			[
				'name'            => 'required|unique:stores|max:255',
				'auth_server_url' => [ 'required', 'unique:stores', new ValidStoreURL, new ValidDuplicateStoreURL ],
				'store_photo'     => 'image|mimes:jpeg,png,jpg,gif|max:2048',
				'company_id'      => 'required',
				'description'     => 'max:500',
				'publish_at'      => 'nullable|date',
			],
			[
				'company_id.required' => 'Company is required.',
				'description.max'     => 'You can not add more than 500 characters.',
			]
		);

		// save company if not Added
		if ( Auth::check() ) {
			// Get company from choosed
			$company_id = $request->input( 'company_id' );
			$company    = Company::where( 'user_id', Auth::user()->id )
			->where( 'id', $company_id )
			->first();

			// Add Store
			if ( $request->input( 'name' ) ) {
				// remove trailing slash from url
				$auth_server_url = rtrim( $request->input( 'auth_server_url' ), '/' );
				// add store now
				$store = Store::create(
					[
						'name'            => $request->input( 'name' ),
						'auth_server_url' => $auth_server_url,
						'description'     => $request->input( 'description' ),
						'company_id'      => $company_id,
						'user_id'         => Auth::user()->id,
					]
				);

				/**
				 * [if description]
				 * @var [type]
				 */
				if ( $store ) {
					$this->enable_site( $store->id );
				}

				// save logo for store
				if ( $request->hasFile( 'store_photo' ) ) {
					$image     = $request->file( 'store_photo' );
					$extension = $image->getClientOriginalExtension();
					$fileName  = rand( 11111, 99999 ) . '_' . time() . '.' . $extension;
					$img       = Image::make( $_FILES['store_photo']['tmp_name'] );
					// resize image
					$img->fit( 300, 300 );
					// save image
					$img->save( public_path( 'store-logos/' . $fileName ) );
					// save logo url to db
					$photo_url = URL::to( '/' ) . '/store-logos/' . $fileName;
					Store::where( 'id', $store->id )
					->update(
						[
							'photo_url' => $photo_url,
						]
					);
				}

				// Relate the company with users and see if the company is new
				$companyUser = CompanyUser::where( 'user_id', Auth::user()->id )
				->where( 'company_id', $company_id )
				->first();

				if ( ! $companyUser ) {
					$company->users()->attach( Auth::user()->id );
				}
			}

			// Fire store created event Listeners
			// event( new StoreCreated( $store ) );

			return redirect()->route(
				'sites.show',
				[
					'store' => $store->id,
				]
			)
			->with( 'success', 'Site created successfully' );
		}

		return back()->withInput()->with( 'errors', 'Error creating new site' );
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function savestore( Request $request, Company $company ) {
		// save company if not Added
		if ( Auth::check() ) {
			if ( $request->input( 'company-name' ) != '' ) {
				$company    = Company::create(
					[
						'name'    => $request->input( 'company-name' ),
						'user_id' => Auth::user()->id,
					]
				);
				$company_id = $company->id;
			} else {
				$company_id = $request->input( 'company-id' );
				$company    = Company::where( 'user_id', Auth::user()->id )
								  ->where( 'id', $company_id )
								  ->first();
			}
			// Add Store
			if ( $request->input( 'store-name' ) ) {
				// add store now
				$store = Store::create(
					[
						'name'            => $request->input( 'store-name' ),
						'auth_server_url' => $request->input( 'store-url' ),
						'company_id'      => $company_id,
						'user_id'         => Auth::user()->id,
					]
				);

				// Relate the company with users and seeif the company is new
				$companyUser = CompanyUser::where( 'user_id', Auth::user()->id )
								  ->where( 'company_id', $company_id )
								  ->first();
				if ( ! $companyUser ) {
					$company->users()->attach( Auth::user()->id );
				}
			}
			return redirect()->route(
				'addstore',
				[
					'store' => $store->id,
				]
			)
			->with( 'success', 'Added successfully.' );
		}

		return back()->withInput()->with( 'errors', 'Error creating new store' );
		// save store as well
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  \App\Store  $store
	 * @return \Illuminate\Http\Response
	 */
	public function show( Request $request, Store $site ) {
		$default_store        = '';
		$plugin_status         = $request->has( 'plugin' ) ? $request->plugin : '';
		$company               = Company::find( $site->getoriginal()['company_id'] );
		$companies             = Company::where( 'user_id', Auth::user()->id )->get();
		$companies_store_count = Company::where( 'user_id', Auth::user()->id )->count();
		$sites                 = Store::where( 'user_id', Auth::user()->id )->where( 'company_id', $site->getoriginal()['company_id'] )->get();

		// check store url is valid or not
		$url_valid = Helpers::get_url_response( $site->auth_server_url );
		if ( ! $url_valid ) {
			return view(
				'settings.site.site-settings',
				[
					'store'                 => $site->getoriginal(),
					'company'               => $company,
					'companies'             => $companies,
					'url_valid'             => $url_valid,
					'default_store'         => $default_store,
					'plugin_status'         => $plugin_status,
					'companies_store_count' => $companies_store_count,
					'sites'                 => $sites,
				]
			);
		}
		// check store configuration information
		$store_info                  = Store::where( 'id', $site->id )
					->first();
		$access_token_lifetime       = $wp_store_details = '';
		$companies_store_credentials = unserialize( $store_info->companies_store_credentials );
		$store_url                   = $store_info->auth_server_url;

		// check the plugin is installed or not on store site and get version
		$plgn_chk_api_end_point       = '/wp-json/wpdriftio/v1/site/plugin-status/';
		$plgn_chk_url                 = $store_url . $plgn_chk_api_end_point;
		$plgn_chk_html                = Helpers::simple_get_curl_response( $plgn_chk_url );
		list($header, $plgn_chk_html) = explode( "\r\n\r\n", $plgn_chk_html, 2 );
		$plgn_chk_html                = json_decode( $plgn_chk_html );
		$plugin_version               = '';
		if ( $plgn_chk_html ) {
			if ( $plgn_chk_html != 'Invalid Host' ) {
				if ( array_key_exists( 'code', $plgn_chk_html ) ) {
					if ( $plgn_chk_html->code == 'rest_no_route' ) {
						$plugin_version = '';
					}
				} else {
					if ( $plgn_chk_html != 'Invalid Host' ) {
						try {
							if ( array_key_exists( 'version', $plgn_chk_html ) ) {
								$plugin_version = $plgn_chk_html->version;
							}
						} catch ( \Exception $e ) {
							$error = $e->getMessage();
						}
					}
				}
			}
		}

		// get WordPress version store's site
		$wp_chk_api_end_point = '/wp-json/wpdriftio/v1/site/';
		$wp_store_info_url    = $store_url . $wp_chk_api_end_point;

		$wp_store_information               = Helpers::simple_get_curl_response( $wp_store_info_url );
		list($wp_header, $wp_store_details) = explode( "\r\n\r\n", $wp_store_information, 2 );
		$wp_store_details                   = json_decode( $wp_store_details );

		$api_end_point         = '/wp-json/wpdriftio/v1/clients/token';
		$url                   = $store_url . $api_end_point . '?token=' . $companies_store_credentials['access_token_info']['result']['access_token'];
		$access_token_lifetime = '';
		if ( $companies_store_credentials['access_token_info']['result']['access_token'] != '' ) {
			$token_call                = Helpers::simple_get_curl_response( $url, $companies_store_credentials['access_token_info']['result']['access_token'] );
			list($token_header, $html) = explode( "\r\n\r\n", $token_call, 2 );
			try {
				if ( $html != 'null' && ! empty( $html ) ) {
					$html   = json_decode( $html );
					$a_html = (array) $html;
					if ( array_key_exists( 'code', $a_html ) ) {
						if ( $html->code == 'rest_no_route' ) {
							$access_token_lifetime = '';
						}
					} else {
						if ( $html != 'Invalid Host' ) {
							$access_token_lifetime = $html->expires;
						} else {
							$access_token_lifetime = '';
						}
					}
				}
			} catch ( \Exception $e ) {
				$error = $e->getMessage();
			}
		}
		$gtdftstr = UserDefaultStore::where( 'user_id', Auth::user()->id )
							->first();
		if ( $gtdftstr ) {
			$default_store = $gtdftstr->store_id;
		}

		/**
		 * get edd plugin status on wp site
		 */
		// check edd plugin is installed or not on store site and get version
		$edd_plgn_chk_api_end_point = '/wp-json/wpdriftio/v1/site/edd-plugin-status/';
		$edd_plgn_chk_url           = $store_url . $edd_plgn_chk_api_end_point;
		$edd_plugin_version         = '';
		try {
			$edd_plgn_chk_html                    = Helpers::simple_get_curl_response( $edd_plgn_chk_url );
			list($edd_header, $edd_plgn_chk_html) = explode( "\r\n\r\n", $edd_plgn_chk_html, 2 );
			$edd_plgn_chk_html                    = json_decode( $edd_plgn_chk_html );
			if ( array_key_exists( 'version', $edd_plgn_chk_html ) ) {
				$edd_plugin_version = $edd_plgn_chk_html->version;
			}
		} catch ( \Exception $e ) {
			$error = $e->getMessage();
		}
		$stores_count = Store::where( 'user_id', Auth::user()->id )->count();

		return view(
			'settings.site.site-settings',
			[
				'store'                 => $site->getoriginal(),
				'company'               => $company,
				'companies'             => $companies,
				'url_valid'             => $url_valid,
				'expire_token_time'     => $access_token_lifetime,
				'default_store'         => $default_store,
				'plugin_version'        => $plugin_version,
				'edd_plugin_version'    => $edd_plugin_version,
				'wp_store_details'      => $wp_store_details,
				'plugin_status'         => $plugin_status,
				'companies_store_count' => $companies_store_count,
				'sites'                 => $sites,
			]
		);

		// return view(
		// 	'companies.stores.show',
		// [
		//   'company' => $company,
		//   'store' => $site->getoriginal(),
		//   'url_valid' => $url_valid,
		//   'expire_token_time' => $access_token_lifetime,
		//   'default_store' => $default_store,
		//   'plugin_version' => $plugin_version,
		//   'edd_plugin_version' => $edd_plugin_version,
		//   'wp_store_details' => $wp_store_details,
		//   'plugin_status' => $plugin_status,
		// ]
		// );

	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\Store  $site
	 * @return \Illuminate\Http\Response
	 */
	public function edit( Store $site ) {
		$companies             = Company::where( 'user_id', Auth::user()->id )
						->get();
		$companies_store_count = Company::where( 'user_id', Auth::user()->id )->count();
		$stores_count          = Store::where( 'user_id', Auth::user()->id )->count();
		$store                 = Store::find( $site->id );
		$stores_company        = Company::find( $store->company_id );
		return view(
			'companies.stores.edit',
			[
				'companies'             => $companies,
				'store'                 => $store,
				'companies_store_count' => $companies_store_count,
				'stores_company'        => $stores_company,
			]
		);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Store  $site
	 * @return \Illuminate\Http\Response
	 */
	public function update( Request $request, Store $site ) {
		if ( $request->input( 'name' ) == '' ) {
			$request->validate(
				[
					'name'            => 'required',
					'auth_server_url' => [ 'required', new ValidStoreURL ],
					'store_photo'     => 'image|mimes:jpeg,png,jpg,gif|max:2048',
					'description'     => 'max:500',
				],
				[ 'description.max' => 'You can not add more than 500 characters.' ]
			);
		} else {
			if ( $site->name != $request->input( 'name' ) ) {
				$request->validate(
					[
						'name'            => 'unique:stores',
						'auth_server_url' => [ 'required', new ValidStoreURL ],
						'store_photo'     => 'image|mimes:jpeg,png,jpg,gif|max:2048',
						'description'     => 'max:500',
					],
					[ 'description.max' => 'You can not add more than 500 characters.' ]
				);
			} else {
				$request->validate(
					[
						'name'            => 'max:255',
						'auth_server_url' => [ 'required', new ValidStoreURL ],
						'store_photo'     => 'image|mimes:jpeg,png,jpg,gif|max:2048',
						'description'     => 'max:500',
					],
					[ 'description.max' => 'You can not add more than 500 characters.' ]
				);
			}
		}
		// validate url if not same
		if ( $site->auth_server_url != $request->input( 'auth_server_url' ) ) {
			$request->validate(
				[
					'auth_server_url' => [ 'required', 'unique:stores', new ValidStoreURL, new ValidDuplicateStoreURL ],
					'store_photo'     => 'image|mimes:jpeg,png,jpg,gif|max:2048',
					'description'     => 'max:500',
				],
				[ 'description.max' => 'You can not add more than 500 characters.' ]
			);
		}
		$auth_server_url = rtrim( $request->input( 'auth_server_url' ), '/' );
		$storeUpdate     = Store::where( 'id', $site->id )
						->update(
							[
								'name'            => $request->input( 'name' ),
								'auth_server_url' => $auth_server_url,
								'description'     => $request->input( 'description' ),
							]
						);
		// save logo for store
		if ( $request->hasFile( 'store_photo' ) ) {
			$image     = $request->file( 'store_photo' );
			$extension = $image->getClientOriginalExtension();
			$fileName  = rand( 11111, 99999 ) . '_' . time() . '.' . $extension;
			$img       = Image::make( $_FILES['store_photo']['tmp_name'] );
			// resize image
			$img->fit( 300, 300 );
			// save image
			$img->save( public_path( 'store-logos/' . $fileName ) );
			// save logo url to db
			$photo_url = URL::to( '/' ) . '/store-logos/' . $fileName;
			// remove old logo if exists
			$old_logo = $site->photo_url;
			if ( $old_logo != '' ) {
				$expl_str_log = explode( '/store-logos/', $old_logo );
				// check file exits if exists remove
				if ( \File::exists( public_path( 'store-logos/' . $expl_str_log[1] ) ) ) {
					\File::delete( public_path( 'store-logos/' . $expl_str_log[1] ) );
				}
			}
			Store::where( 'id', $site->id )
								->update(
									[
										'photo_url' => $photo_url,
									]
								);
		}
		// If store updated
		if ( $storeUpdate ) {
			return redirect()
						  ->route( 'sites.show', [ 'store' => $site->id ] )
						  ->with( 'success', 'Site updated successfully' );
		}
		//redirect
		return back()->withInput();
	}

	/**
	 * Remove the specified resource from storage.
	 * @param  Store  $site [description]
	 * @return [type]       [description]
	 */
	public function destroy( Store $site ) {
		$site_id                     = $site->id;
		$findstore                   = Store::find( $site_id );
		$companies_store_credentials = unserialize( $findstore->companies_store_credentials );
		$store_url                   = $findstore->auth_server_url;

		/**
		 * [$default_store description]
		 * @var [type]
		 */
		$default_store = UserDefaultStore::where( 'store_id', $site_id )->first();
		if ( $default_store ) {
			$default_store->delete();
		}

		if ( ! $findstore->delete() ) {
			return back()->withInput()->with( 'error', 'Site could not be deleted' );
		}

		$this->delete_data_edd(
			[
				'site_id'      => $site_id,
				'access_token' => $companies_store_credentials['access_token_info']['result']['access_token'],
			]
		);

		return redirect()->route( 'sites.index' )->with( 'success', 'Site deleted successfully' );
	}

	public function oauthstore( Request $request, $store_id ) {
		// get the store infomation from store table
		$store_info = Store::where( 'user_id', Auth::user()->id )
		->where( 'id', $store_id )
		->first();

		// step 1 to authorize store
		if ( $store_info->auth_server_url != '' && filter_var( $store_info->auth_server_url, FILTER_VALIDATE_URL ) ) {
			// 1. send rest api request for validate url of store
			$url  = $store_info->auth_server_url . '/wp-json/wpdriftio/v1/clients/';
			$html = $this->get_curl_response( $url, 'sid=' . $store_info->id . '&store_name=' . $store_info->name . '&return_url=' . URL::to( '/oauth/drift' ) );
			$json = json_decode( $html, true );
			// 2. Get Response and Save information to store table
			$store_id          = $json['store_id'];
			$client_id         = $json['meta_input']['client_id'];
			$client_secret_key = $json['meta_input']['client_secret'];
			$storeUpdate       = Store::where( 'id', $store_id )
								->update(
									[
										'auth_client_id' => $client_id,
										'auth_client_secret' => $client_secret_key,
									]
								);
			session( [ 'store_id' => $store_id ] );
			// 3. Update the store with client credentials
			if ( $storeUpdate ) {
				$store_single = Store::find( $store_id );
				// Define the variables
				$auth_client_id           = $store_single->auth_client_id;
				$auth_client_secret       = $store_single->auth_client_secret;
				$auth_server_url          = $store_info->auth_server_url . '/';
				$auth_client_callback_url = URL::to( '/oauth/drift' );
				// create oauth client object
				return redirect()->route( 'oauth-drift' );
				die();
			}
		} else {
			if ( ! filter_var( $store_info->auth_server_url, FILTER_VALIDATE_URL ) ) {
				echo 'Site is not having valid URL.';
				exit;
			} elseif ( $store_info->auth_server_url == '' ) {
				echo 'Site URL can not be empty.';
				exit;
			}
			echo 'Your site was already being setup.';
			exit;
		}
	}

	/**
	 * [enable_site description]
	 * @param [type] $store_id [description]
	 */
	public function enable_site( $store_id ) {
		$user_id       = Auth::user()->id;
		$default_store = UserDefaultStore::where( 'user_id', $user_id )->first();
		if ( $default_store ) {
			return;
		}

		UserDefaultStore::create(
			[
				'user_id'  => $user_id,
				'store_id' => $store_id,
			]
		);
	}

	public function setdefaultstore( Request $request, $store_id ) {
		$store_info = Store::where( 'user_id', Auth::user()->id )
		->where( 'id', $store_id )
		->first();

		if ( $store_info ) {
			// Update the store customers default session values
			$session = new Session();
			// save variables in session
			$per_page_sess = $session->set( 'per_page_sess', 10 );
			$page_sess     = $session->set( 'page_sess', 1 );
			$orderby_sess  = $session->set( 'orderby_sess', 'name' );
			$search_sess   = $session->set( 'search_sess', '' );

			$chkdftstr = UserDefaultStore::where( 'user_id', Auth::user()->id )
							  ->first();
			if ( ! $chkdftstr ) {
				// add default store
				$userdefaultstore = UserDefaultStore::create(
					[
						'user_id'  => Auth::user()->id,
						'store_id' => $store_info->id,
					]
				);
			} else {
				// update the default store
				$updatedefaultstore = UserDefaultStore::where( 'user_id', Auth::user()->id )
								->update(
									[
										'store_id' => $store_info->id,
									]
								);
			}

			// prepare the image url of store and also check if blank then set name

			if ( isset( $_GET['response'] ) && trim( $_GET['response'] ) == 'json' ) {
				return response()->json(
					[
						'success'    => 'true',
						'store_logo' => $store_info->photo_url,
						'store_name' => $store_info->name,
						'store_id'   => $store_info->id,
					],
					200
				);
			}
			return redirect()->route( 'sites.index' )
			->with( 'success', 'Set Site as Current successfully.' );
		}
		if ( isset( $_GET['response'] ) && trim( $_GET['response'] ) == 'json' ) {
			return response()->json( [ 'error' => 'Site could not be set as Current' ], 200 );
		}
		return back()->withInput()->with( 'error', 'Site could not be set as Current' );
	}

	private function get_curl_response( $url, $postvars ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_POSTREDIR, 3 );
		curl_setopt(
			$ch,
			CURLOPT_POSTFIELDS,
			$postvars
		);
		if ( curl_exec( $ch ) === false ) {
			echo 'ok';
		} else {
			echo 'error';
		}
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$html = curl_exec( $ch );
		curl_close( $ch );
		return $html;
	}

	/**
	 * [store_details description]
	 * @return [type] [description]
	 */
	public function get_details() {
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
			return response()->json(
				[
					'code'    => 'default_store_not_set',
					'message' => __( 'Plese set up a default store to get details.' ),
					'data'    => [ 'status' => 401 ],
				]
			);
		}

		/**
		 * [$store_info description]
		 * @var [type]
		 */
		$store = Store::where( 'id', $default_store->store_id )->first();
		return response()->json(
			[
				'id'          => $store->id,
				'url'         => $store->auth_server_url,
				'credentials' => $store->companies_store_credentials,
				'logo'        => $this->get_logo( $store->auth_server_url ),
			]
		);
	}

	/**
	 * [get_logo description]
	 * @param  [type] $url [description]
	 * @return [type]      [description]
	 */
	public function get_logo( $url ) {
		/**
		 * [$parsed_url description]
		 * @var [type]
		 */
		$parsed_url = parse_url( $url );
		$logo_url   = 'https://logo.clearbit.com/' . $parsed_url['host'];
		$client     = new GuzzleHttp( [ 'base_uri' => $logo_url ] );
		$response   = $client->request( 'GET' );
		if ( '400' == $response->getStatusCode() ) {
			return '';
		}

		/**
		 * [return description]
		 * @var [type]
		 */
		return $logo_url;
	}

	/**
	 * Remove edd from Lumne
	 * Get api response from wp edd site and send it to Lumen Edd
	 * @param $store_url, $companies_store_credentials
	 * @param $store_id - store id
	 * @param $access_token - Access token
	 * return response or error
	 */
	protected function delete_data_edd( $payload ) {
		DeleteSite::dispatch( $payload )->delay( now()->addSeconds( 5 ) );
	}
}
