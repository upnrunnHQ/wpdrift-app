<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

Route::get('/', 'SiteController@show');
Route::get('/process', 'SiteController@process');
Route::get('/process_sites', 'SiteController@process_sites');
Route::get('/process_site', 'SiteController@process_site');
Route::get('/fetch', 'SiteController@fetch');
Route::get('/clear', 'SiteController@clear');
Route::get('/debug', 'SiteController@debug');
Route::get('/process_posts', 'SiteController@process_posts');
Route::get('/process_customers', 'SiteController@process_customers');
Route::get('/test_payments', 'PaymentsController@subscriptions_report');

// $router->get('/', function () use ($router) {
//     return $router->app->version();
// });
/**
 * Direct route to get data from WP EDD Site
 */
// Route::post( 'setup_edd', 'Setup_EDD@setup_edd_store_data' );
// Route::post( 'update_store', 'Setup_EDD@update_edd_store' );
// Route::post( 'handle_store_delete', 'Setup_EDD@store_delete' );

Route::post('/add_site', 'SiteController@add_site');
Route::post('/delete_site', 'SiteController@delete_site');

/**
 * Add rest end point for app.wpdrift.io
 */
Route::group(
    [
        'prefix'     => 'api',
        'middleware' => 'api',
    ],
    function () {
        // Customers
        Route::get('total_customers/{store_id}', 'CustomersController@get_total_customers');
        Route::get('customers/{store_id}', 'CustomersController@get_items');
        Route::get('customers_segment/{store_id}', 'CustomersController@get_customers_segment');
        Route::get('customer/{store_id}/{customer_id}', 'CustomersController@show');
        Route::get('recent_customers/{store_id}', 'CustomersController@get_events_customers');

        // Payments
        Route::get('recent_orders/{store_id}', 'PaymentsController@get_events_orders');
        Route::get('net_revenue/{store_id}', 'PaymentsController@get_net_revenue');
        Route::get('gross_sales/{store_id}', 'PaymentsController@get_gross_sales');
        Route::get('gross_refunds/{store_id}', 'PaymentsController@get_gross_refunds');
        Route::get('gross_taxes/{store_id}', 'PaymentsController@get_gross_taxes');
        Route::get('total_items_sold/{store_id}', 'PaymentsController@get_total_items_sold');
        Route::get('total_number_refunds/{store_id}', 'PaymentsController@get_total_number_refunds');
        Route::get('refunded_amounts/{store_id}', 'PaymentsController@get_refunded_amounts');
        Route::get('orders/{store_id}', 'PaymentsController@get_orders');
        Route::get('total_number_orders/{store_id}', 'PaymentsController@get_total_number_orders');
        Route::get('refunds/{store_id}', 'PaymentsController@get_refunds');
        Route::get('orders_segment/{store_id}', 'PaymentsController@get_orders_segment');
        Route::get('order/{store_id}/{order_id}', 'PaymentsController@show');

        // Products
        Route::get('total_products/{store_id}', 'ProductsController@get_total_products');
        Route::get('total_number_products/{store_id}', 'ProductsController@get_total_number_products');
        Route::get('products/{store_id}', 'ProductsController@get_products');
        Route::get('product/{store_id}/{product_id}', 'ProductsController@show');
        Route::get('product_payments/{store_id}/{product_id}', 'ProductsController@get_products_orders');
        Route::get('top-purchased-products/{store_id}', 'ProductsController@get_top_purchased_products');
        Route::get('payments/{store_id}', 'ProductsController@get_payments');

        // Discounts
        Route::get('total_discounts/{store_id}', 'DiscountsController@get_total_discounts');
        Route::get('total_number_discounts/{store_id}', 'DiscountsController@get_total_number_discounts');
        Route::get('discounts/{store_id}', 'DiscountsController@get_discounts');

        // LOGS
        Route::get('logs/{store_id}', 'EddLogsController@get_sales_logs');

        // Subscriptions
        Route::get('subscriptions_earnings/{store_id}', 'PaymentsController@subscriptions_earnings');
        Route::get('subscriptions_refunded/{store_id}', 'PaymentsController@subscriptions_refunded');
        Route::get('subscriptions_count/{store_id}', 'PaymentsController@subscriptions_count');
        Route::get('subscriptions_refunded_count/{store_id}', 'PaymentsController@subscriptions_refunded_count');
    }
);
// Handle webhooks from WP site
// CUSTOMER
// Route::post( 'handle_customer_create', 'SyncCustomerController@customer_create' );
// Route::post( 'handle_customer_update', 'SyncCustomerController@customer_update' );
// Route::post( 'handle_customer_delete', 'SyncCustomerController@customer_delete' );
// // USER
// Route::post( 'handle_user_create', 'SyncUserController@user_create' );
// Route::post( 'handle_user_update', 'SyncUserController@user_update' );
// Route::post( 'handle_user_delete', 'SyncUserController@user_delete' );
// // TERM ASSIGNED
// Route::post( 'handle_term_assign', 'SyncTermAssignedController@term_assign' );
// // TERMS
// Route::post( 'handle_term_create', 'SyncTermController@term_create' );
// Route::post( 'handle_term_update', 'SyncTermController@term_update' );
// Route::post( 'handle_term_delete', 'SyncTermController@term_delete' );
// // DISCOUNTS
// Route::post( 'handle_discount_create', 'SyncDiscountController@discount_create' );
// Route::post( 'handle_discount_update', 'SyncDiscountController@discount_update' );
// Route::post( 'handle_discount_delete', 'SyncDiscountController@discount_delete' );
// // // DOWNLOADS
// Route::post( 'handle_download_create', 'SyncDownloadController@download_create' );
// Route::post( 'handle_download_update', 'SyncDownloadController@download_update' );
// Route::post( 'handle_download_delete', 'SyncDownloadController@download_delete' );
// // // EDD LOG
// Route::post( 'handle_eddlog_create', 'SyncEddLogController@eddlog_create' );
// Route::post( 'handle_eddlog_update', 'SyncEddLogController@eddlog_update' );
// Route::post( 'handle_eddlog_delete', 'SyncEddLogController@eddlog_delete' );
// // // PAYMENT
// Route::post( 'handle_payment_create', 'SyncPaymentController@payment_create' );
// Route::post( 'handle_payment_update', 'SyncPaymentController@payment_update' );
// Route::post( 'handle_payment_delete', 'SyncPaymentController@payment_delete' );
