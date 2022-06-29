<?php
/**
 * For separate edd modules api calls
 */
Route::group(
	[
		'middleware' => 'web',
		'prefix'     => 'edd',
		'namespace'  => 'Modules\EDD\Http\Controllers',
	],
	function () {
		// Dashboard
		Route::get( 'dashboard-stats', 'DashboardController@stats' );

		// CUSTOMERS
		Route::get( 'customers', 'CustomersController@index' );
		Route::get( 'total_customers', 'CustomersController@get_total_customers' );
		Route::get( 'customers_segment', 'CustomersController@get_customers_segment' );
		Route::get( 'customer/{customer_id}', 'CustomersController@show' );
		Route::get( 'recent_customers', 'CustomersController@get_events_customers' );
		// ORDERS
		Route::get( 'recent_orders', 'PaymentsController@get_events_orders' );
		Route::get( 'net_revenue', 'PaymentsController@get_net_revenue' );
		Route::get( 'gross_sales', 'PaymentsController@get_gross_sales' );
		Route::get( 'gross_refunds', 'PaymentsController@get_gross_refunds' );
		Route::get( 'gross_taxes', 'PaymentsController@get_gross_taxes' );
		Route::get( 'total_items_sold', 'PaymentsController@get_total_items_sold' );
		Route::get( 'total_number_refunds', 'PaymentsController@get_total_number_refunds' );
		Route::get( 'refunded_amounts', 'PaymentsController@get_refunded_amounts' );
		Route::get( 'orders', 'PaymentsController@get_orders' );
		Route::get( 'total_number_orders', 'PaymentsController@get_total_number_orders' );
		Route::get( 'refunds', 'PaymentsController@get_refunds' );
		Route::get( 'orders_segment', 'PaymentsController@get_orders_segment' );
		Route::get( 'order/{order_id}', 'PaymentsController@show' );
		// PRODUCTS
		Route::get( 'total_products', 'ProductsController@get_total_products' );
		Route::get( 'total_number_products', 'ProductsController@get_total_number_products' );
		Route::get( 'top-purchased-products', 'ProductsController@get_top_purchased_products' );
		Route::get( 'products', 'ProductsController@get_products' );
		Route::get( 'product/{product_id}', 'ProductsController@show' );
		Route::get( 'product_payments/{product_id}', 'ProductsController@get_products_orders' );

		// DISCOUNTS
		Route::get( 'total_discounts', 'DiscountsController@get_total_discounts' );
		Route::get( 'total_number_discounts', 'DiscountsController@get_total_number_discounts' );
		Route::get( 'discounts', 'DiscountsController@get_discounts' );

		// LOGS
		Route::get( 'logs', 'EddLogsController@get_logs' );
		Route::get( 'payments', 'ProductsController@get_payments' );

		// Subscriptions
		Route::get( 'subscriptions_earnings', 'PaymentsController@subscriptions_earnings' );
		Route::get( 'subscriptions_refunded', 'PaymentsController@subscriptions_refunded' );
		Route::get( 'subscriptions_count', 'PaymentsController@subscriptions_count' );
		Route::get( 'subscriptions_refunded_count', 'PaymentsController@subscriptions_refunded_count' );
	}
);
