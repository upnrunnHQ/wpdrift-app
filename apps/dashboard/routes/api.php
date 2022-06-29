<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register the API routes for your application as
| the routes are automatically authenticated using the API guard and
| loaded automatically by this application's RouteServiceProvider.
|
*/

Route::group([ 'middleware' => 'auth:api' ], function () {
    // Listing out the dashboard related routes
    Route::get('/recent_events', 'DashboardController@recent_events');
    Route::get('/dashboard', 'DashboardController@server_dashboard_api');
    Route::get( '/referrals', 'DashboardController@referrals' );
    // Listing out the customer users.
    Route::get('/customers', 'CustomersController@index');
    Route::get('/customers/{id}', 'CustomersController@show');
});
