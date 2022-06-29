<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\EddLog;
use App\EddLogMeta;
use App\Customer;
use App\Payment;
use App\PaymentMeta;

class EddLogsController extends Controller {

	/**
	 * Retrieve the user for the given ID.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show() {
		return EddLog::all();
	}

	/**
	 * [get_sales_logs description]
	 * @return [type] [description]
	 */
	public function get_sales_logs( Request $request, $store_id ) {
		$logs_data = [];
		$logs      = EddLog::where( 'store_id', $store_id );
		$download  = $this->get_filtered_download( $request );
		if ( $download ) {
			$logs->where( 'post_parent', $download );
		}

		if ( $request->has( 'sortField' ) ) {
			$short_order = ( $request->input( 'sortOrder' ) == 'ascend' ) ? 'asc' : 'desc';
			$short_field = $request->input( 'sortField' );
			if ( 'date' == $short_field ) {
				$short_field = 'post_date';
			}

			$logs->orderBy( $short_field, $short_order );
		}

		$paginated_logs = $logs->paginate( 10 );
		$logs           = $paginated_logs->items();
		// return $logs;

		if ( $logs ) {
			foreach ( $logs as $log ) {
				$payment_id = EddLogMeta::where( 'meta_key', '_edd_log_payment_id' )
				->where( 'post_id', (string) $log->ID )
				->pluck( 'meta_value' )
				->first();

				$payment = Payment::where( 'ID', (int) $payment_id )->first();

				if ( ! empty( $payment->ID ) ) {
					$customer_id = PaymentMeta::where( 'post_id', (string) $payment_id )
					->where( 'meta_key', '_edd_payment_customer_id' )
					->pluck( 'meta_value' )
					->first();

					$customer         = Customer::where( 'id', $customer_id )->first();
					$customer->avatar = $this->get_gravatar( $customer->email );

					$payment_meta = PaymentMeta::where( 'post_id', (string) $payment_id )
					->where( 'meta_key', '_edd_payment_meta' )
					->pluck( 'meta_value' )
					->first();
					$payment_meta = unserialize( $payment_meta );
					$cart_items   = $payment_meta['cart_details'];
					$amount       = 0;

					if ( is_array( $cart_items ) ) {
						foreach ( $cart_items as $item ) {

							// If the item has variable pricing, make sure it's the right variation
							if ( $item['id'] == $log->post_parent ) {
								if ( isset( $item['item_number']['options']['price_id'] ) ) {
									$log_price_id = EddLogMeta::where( 'meta_key', '_edd_log_price_id' )
									->where( 'post_id', (string) $log->ID )
									->pluck( 'meta_value' )
									->first();

									if ( (int) $item['item_number']['options']['price_id'] !== (int) $log_price_id ) {
										continue;
									}
								}

								$amount = isset( $item['price'] ) ? $item['price'] : $item['item_price'];
								break;
							}
						}

						$logs_data[] = array(
							'ID'         => $log->ID,
							'payment_id' => $payment->ID,
							'customer'   => $customer,
							'download'   => $log->post_parent,
							'price_id'   => isset( $log_price_id ) ? $log_price_id : null,
							'item_price' => isset( $item['item_price'] ) ? $item['item_price'] : $item['price'],
							'amount'     => $amount,
							'date'       => $payment->post_date,
							'quantity'   => $item['quantity'],
							// Keep track of the currency. Vital to produce the correct report
							'currency'   => $payment_meta['currency'],
							'$payment'   => $customer_id,
						);
					}
				}
			}
		}

		return [
			'total'        => $paginated_logs->total(),
			'currentPage'  => $paginated_logs->currentPage(),
			'hasMorePages' => $paginated_logs->hasMorePages(),
			'results'      => $logs_data,
		];
	}

	public function get_filtered_download( $request ) {
		return $request->has( 'download' ) ? abs( intval( $request->input( 'download' ) ) ) : false;
	}

	/**
	 * Get either a Gravatar URL or complete image tag for a specified email address.
	 * @param  [type]  $email [description]
	 * @param  integer $s     [description]
	 * @param  string  $d     [description]
	 * @param  string  $r     [description]
	 * @param  boolean $img   [description]
	 * @param  array   $atts  [description]
	 * @return [type]         [description]
	 */
	function get_gravatar( $email, $s = 80, $d = 'mp', $r = 'g', $img = false, $atts = array() ) {
		$url  = 'https://www.gravatar.com/avatar/';
		$url .= md5( strtolower( trim( $email ) ) );
		$url .= "?s=$s&d=$d&r=$r";
		if ( $img ) {
			$url = '<img src="' . $url . '"';
			foreach ( $atts as $key => $val ) {
				$url .= ' ' . $key . '="' . $val . '"';
			}
			$url .= ' />';
		}
		return $url;
	}
}
