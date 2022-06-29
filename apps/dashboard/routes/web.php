<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get( '/', 'WelcomeController@show' );

Route::get( '/home', 'HomeController@show' );

if ( ! App::environment( 'local' ) ) {
	Route::get(
		'/register',
		function () {
			echo 'Registration is not allowed at this moment! Please join to <a href="https://wpdrift.io/#subscribe-form">email lists</a>.';
		}
	);
}

// Oauth Controller for WP REST Setup
Route::get( '/oauth/drift', 'OAuthController@driftconnect' )->name( 'oauth-drift' );
// Company and Store Routes
// Adding /settings as prefix
Route::prefix( 'settings' )->group(
	function () {
		Route::resource( 'companies', 'CompaniesController' );
		Route::resource( 'sites', 'StoresController' );
	}
);

Route::get( '/oauth-site/{store_id}', 'StoresController@oauthstore' );
// ReOauthorize store
Route::get( '/re-oauth-site/{store_id}', 'OAuthController@reoauthstore' );
Route::get( '/set-default-site/{store_id}', 'StoresController@setdefaultstore' );
/* Commenting out unwanted routes */

// GDPR
Route::get( '/gdpr_download', 'GDPRController@show_gdpr_form' );
// TOS
Route::get( '/tos', 'TermsController@show' );

Route::get( '/users', 'UserController@users' );
// EDD Lumen Setup
Route::get( 'enable_edd', 'SiteController@enable_edd' );
Route::get( 'sync_edd', 'Setup_EDD_Lumen@sync_edd' );
Route::get( 'disable_edd', 'Setup_EDD_Lumen@disable_edd' );
Route::get( 'check_edd_status', 'Setup_EDD_Lumen@check_edd_status' );
// hook for setting up the store success from edd
Route::get( 'edd_setup', 'Setup_EDD_Lumen@edd_setup' );

/**
 * [Route description]
 * @var [type]
 */
Route::get( '/customers', 'RestController@customers' );
Route::get( '/store_details', 'RestController@store_details' );
Route::get( '/statistics', 'RestController@statistics' );
Route::get( '/events', 'RestController@events' );
Route::get( '/get_token', 'RestController@get_token' );
Route::get( '/site', 'StoresController@get_details' );
Route::get( '/settings', 'DashboardController@settings' )->name( 'settings' );
