<?php
/**
 * Customers Controller
 * This will have all the customers controller related methods that will serv
 * REST End Points
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Customer;
use App\CustomerMeta;
use App\EddUser;
use App\EddUserMeta;
use App\Payment;
use App\PaymentMeta;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use GeoIp2\Database\Reader;

class CustomersController extends Controller {

	/**
	 * For getting single customer rest call
	 * @param $request - that will have request submitted to this method route.
	 * @param $store_id - for specifying store
	 * @param $customer_id - for specifying customer id
	 */
	public function show( Request $request, $store_id, $customer_id ) {
		$customer = [
			'customer_location' => false,
		];

		// customer details retrieve
		$customer_details = Customer::where( 'store_id', $store_id )
		->where( 'id', $customer_id )
		->first();

		$customer_email = '';
		if ( isset( $customer_details->email ) ) {
			$customer_email = $customer_details->email;
		}

		$customer['customer'] = [
			'id'           => $customer_details->id,
			'store_id'     => $customer_details->store_id,
			'name'         => $customer_details->name,
			'email'        => $customer_email,
			'date_created' => $customer_details->date_created,
			'gravatar'     => $this->get_gravatar( $customer_email ),
		];

		// orders
		$recent_payments = [];
		$payment_ids     = array_map( 'intval', explode( ',', $customer_details->payment_ids ) );
		$payments        = Payment::whereIn( 'ID', $payment_ids )
		->orderBy( 'post_date', 'desc' )
		->take( 10 )
		->get();
		if ( $payments ) {
			foreach ( $payments as $payment ) {
				$payment_meta = PaymentMeta::where( 'store_id', $store_id )
				->where( 'post_id', (string) $payment->ID )
				->where( 'meta_key', '_edd_payment_meta' )
				->pluck( 'meta_value' )
				->first();

				$payment_meta = unserialize( $payment_meta );

				$total = PaymentMeta::where( 'store_id', $store_id )
				->where( 'post_id', (string) $payment->ID )
				->where( 'meta_key', '_edd_payment_total' )
				->pluck( 'meta_value' )
				->first();

				if ( empty( $total ) && '0.00' != $total ) {
					if ( isset( $payment_meta['amount'] ) ) {
						$total = $payment_meta['amount'];
					}
				}

				$subtotal     = 0;
				$cart_details = isset( $payment_meta['cart_details'] ) ? $payment_meta['cart_details'] : [];

				if ( is_array( $cart_details ) ) {
					foreach ( $cart_details as $item ) {
						if ( isset( $item['subtotal'] ) ) {
							$subtotal += $item['subtotal'];
						}
					}
				} else {
					$subtotal = $total;
				}

				$customer_ip = PaymentMeta::where( 'store_id', $store_id )
				->where( 'post_id', (string) $payment->ID )
				->where( 'meta_key', '_edd_payment_user_ip' )
				->pluck( 'meta_value' )
				->first();

				if ( $customer_ip && ( false === $customer['customer_location'] ) ) {
					$reader                        = new Reader( storage_path( 'app/GeoLite2-City.mmdb' ) );
					$record                        = $reader->city( $customer_ip );
					$customer['customer_location'] = [
						'city'    => $record->city->name,
						'country' => $record->country->name,
						'lat'     => $record->location->latitude,
						'lng'     => $record->location->longitude,
					];
				}

				$recent_payments[] = [
					'ID'       => $payment->ID,
					'total'    => $total,
					'subtotal' => $subtotal,
					'status'   => $payment->post_status,
					'date'     => ( new Carbon( $payment->post_date ) )->toDayDateTimeString(),
				];
			}
		}

		$customer['payments'] = $recent_payments;
		return $customer;
	}
	/**
	 * For getting total number of customers rest call
	 * @param $request - that will have request submitted to this method route.
	 * @param $store_id - for specifying store
	 */
	public function get_total_customers( Request $request, $store_id ) {
		$customers_set = Customer::orderBy( 'name', 'desc' )
				->where( 'store_id', $store_id );
		if ( $request->has( [ 'startdate', 'enddate' ] ) ) {
			$customers_set->where( 'date_created', '>=', $request->startdate )
						->where( 'date_created', '<=', $request->enddate );
		}
		$get_total_customers = $customers_set->count();
		return $get_total_customers;
	}

	/**
	 * For getting all customers rest call
	 * @param  Request $request  [description]
	 * @param  [type]  $store_id [description]
	 * @return [type]            [description]
	 */
	public function get_items( Request $request, $store_id ) {
		$defaults = [
			'page'     => 1,
			'per_page' => 10,
		];

		$customers = [];
		$old_data  = false;

		$results = Customer::where( 'store_id', $store_id );

		$per_page = $request->has( 'per_page' ) ? (int) $request->per_page : 10;
		$page     = $request->has( 'page' ) ? (int) $request->page : 1;
		$search   = $request->has( 'search' ) ? $request->search : '';

		if ( $request->has( [ 'startdate', 'enddate' ] ) ) {
			if ( ( '' != $request->startdate ) && ( '' != $request->enddate ) ) {
				$after  = $this->prepare_after( $request->startdate );
				$before = $this->prepare_before( $request->enddate );

				// to check if items exists in this period if not then return all products
				$total_items = Customer::where( 'store_id', $store_id )->where( 'date_created', '>=', $after )
				->where( 'date_created', '<=', $before )
				->count();

				if ( ( 0 == $total_items ) && ( '' == $search ) ) {
					$results->where( 'date_created', '!=', '' );
					$old_data = true;
				} else {
					$results->where( 'date_created', '>=', $after )->where( 'date_created', '<=', $before );
				}
			}
		}

		if ( '' != $search ) {
			$results->where(
				function( $query ) use ( $search ) {
					$query->where( 'id', 'like', '%' . $search . '%' )
					->orWhere( 'email', 'like', '%' . $search . '%' )
					->orWhere( 'name', 'like', '%' . $search . '%' )
					->orWhere( 'notes', 'like', '%' . $search . '%' );
				}
			);
		}

		if ( $request->has( [ 'orderby', 'order' ] ) ) {
			$results->orderBy( $request->orderby, $request->order );
		} else {
			$results->orderBy( 'date_created', 'desc' );
		}

		$total_customers   = $results->count();
		$paginated_results = $results->paginate( $per_page );
		// custom array build for avatar

		$k = 0;
		foreach ( $paginated_results as $customer ) {
			$customers[ $k ]['id']                = $customer->id;
			$customers[ $k ]['user_id']           = $customer->user_id;
			$customers[ $k ]['email']             = $customer->email;
			$customers[ $k ]['name']              = $customer->name;
			$customers[ $k ]['purchase_value']    = $customer->purchase_value;
			$customers[ $k ]['purchase_count']    = $customer->purchase_count;
			$customers[ $k ]['payment_ids']       = $customer->payment_ids;
			$customers[ $k ]['notes']             = $customer->notes;
			$customers[ $k ]['customer_avatar']   = app( GeneralController::class )->get_user_avatar_by_email( $customer->email );
			$customers[ $k ]['date_created']      = $customer->date_created;
			$customers[ $k ]['customer_location'] = false;

			$payment_ids = array_map( 'intval', explode( ',', $customer->payment_ids ) );
			if ( ! empty( $payment_ids ) ) {
				foreach ( $payment_ids as $payment_id ) {
					if ( false === $customers[ $k ]['customer_location'] ) {
						$customer_ip = PaymentMeta::where( 'store_id', $store_id )
						->where( 'post_id', (string) $payment_id )
						->where( 'meta_key', '_edd_payment_user_ip' )
						->pluck( 'meta_value' )
						->first();

						if ( $customer_ip ) {
							$reader                               = new Reader( storage_path( 'app/GeoLite2-City.mmdb' ) );
							$record                               = $reader->city( $customer_ip );
							$customers[ $k ]['customer_location'] = [
								'city'    => $record->city->name,
								'country' => $record->country->name,
								'lat'     => $record->location->latitude,
								'lng'     => $record->location->longitude,
								'code'    => false,
							];

							$address = [];
							if ( $record->city->name ) {
								$address[] = $record->city->name;
							}

							if ( $record->country->name ) {
								$address[]                                    = $record->country->name;
								$customers[ $k ]['customer_location']['code'] = strtolower( $record->country->isoCode );
							}
							$customers[ $k ]['customer_location']['address'] = implode( ', ', $address );
						}
					}
				}
			}

			$k++;
		}

		$val = new LengthAwarePaginator(
			$customers, // Only grab the items we need
			$total_customers, // Total items
			$per_page, // Items per page
			$page
		);
		return [
			'old_data' => $old_data,
			'result'   => $val,
		];
	}

	/**
	 * For getting customers segment rest call
	 * @param $request - that will have request submitted to this method route.
	 * @param $store_id - for specifying store
	 */
	public function get_customers_segment( Request $request, $store_id ) {
		// VIP - Get customers whose LTV is more than 98
		$vip_customers                         = $this->customers_set_basic( $request, $store_id );
		$vip_cust_set                          = $vip_customers->where( 'purchase_value', '>=', '98' );
		$customers_segment['vip']['customers'] = $vip_cust_set->count();
		$vip_count_orders                      = 0;
		$vip_revenue                           = 0;
		foreach ( $vip_cust_set->get() as $customer_set ) {
			$expld_payment_ids = explode( ',', $customer_set->payment_ids );
			$vip_count_orders += count( $expld_payment_ids );
			$vip_revenue      += $customer_set->purchase_value;
		}
		$customers_segment['vip']['orders']  = $vip_count_orders;
		$customers_segment['vip']['revenue'] = $vip_revenue;
		if ( $vip_count_orders > 0 ) {
			$customers_segment['vip']['aco'] = $vip_count_orders / $customers_segment['vip']['customers'];
		} else {
			$customers_segment['vip']['aco'] = 0.00;
		}
		if ( $vip_revenue > 0 ) {
			$customers_segment['vip']['ltv'] = $vip_revenue / $customers_segment['vip']['customers'];
		} else {
			$customers_segment['vip']['ltv'] = 0.00;
		}

		// Single Orders Customers
		$single_order_cust_set                    = $this->customers_set_basic( $request, $store_id )
								->where( 'purchase_count', 'like', '1' );
		$customers_segment['single']['customers'] = $single_order_cust_set->count();
		$single_count_orders                      = 0;
		$single_revenue                           = 0;
		foreach ( $single_order_cust_set->get() as $customer_set ) {
			$expld_payment_ids    = explode( ',', $customer_set->payment_ids );
			$single_count_orders += count( $expld_payment_ids );
			$single_revenue      += $customer_set->purchase_value;
		}
		$customers_segment['single']['orders']  = $single_count_orders;
		$customers_segment['single']['revenue'] = $single_revenue;
		if ( $single_count_orders > 0 ) {
			$customers_segment['single']['aco'] = $single_count_orders / $customers_segment['single']['customers'];
		} else {
			$customers_segment['single']['aco'] = 0.00;
		}
		if ( $single_revenue > 0 ) {
			$customers_segment['single']['ltv'] = $single_revenue / $customers_segment['single']['customers'];
		} else {
			$customers_segment['single']['ltv'] = 0.00;
		}

		// Repeat Orders Customers
		$repeat_order_cust_set                    = $this->customers_set_basic( $request, $store_id )
								->where( 'purchase_count', '>=', '2' );
		$customers_segment['repeat']['customers'] = $repeat_order_cust_set->count();
		$repeat_count_orders                      = 0;
		$repeat_revenue                           = 0;
		foreach ( $repeat_order_cust_set->get() as $customer_set ) {
			$expld_payment_ids    = explode( ',', $customer_set->payment_ids );
			$repeat_count_orders += count( $expld_payment_ids );
			$repeat_revenue      += $customer_set->purchase_value;
		}
		$customers_segment['repeat']['orders']  = $repeat_count_orders;
		$customers_segment['repeat']['revenue'] = $repeat_revenue;
		if ( $repeat_count_orders > 0 ) {
			$customers_segment['repeat']['aco'] = $repeat_count_orders / $customers_segment['repeat']['customers'];
		} else {
			$customers_segment['repeat']['aco'] = 0.00;
		}
		if ( $repeat_revenue > 0 ) {
			$customers_segment['repeat']['ltv'] = $repeat_revenue / $customers_segment['repeat']['customers'];
		} else {
			$customers_segment['repeat']['ltv'] = 0.00;
		}

		// At Risk Orders Customers - customers which are only orders before 36 days
		$today                     = \Carbon\Carbon::today();
		$at_risk_order_payment_set = $this->payments_set_basic( $request, $store_id )
								->where(
									'post_date',
									'>',
									$today->subDays( 36 )
									->toDateTimeString()
								)
								->where( 'post_status', '=', 'publish' );
		$at_rsk_cust               = 0;
		foreach ( $at_risk_order_payment_set->get() as $order_set ) {
			$at_rsk_cust += $this->customers_set_basic( $request, $store_id )
							->where( 'payment_ids', 'like', '%' . $order_set->ID . '%' )
							->count();
		}
		$customers_segment['at_risk']['customers'] = $at_rsk_cust;
		$customers_segment['at_risk']['orders']    = $at_risk_order_payment_set->count();
		$at_risk_revenue                           = 0;
		foreach ( $at_risk_order_payment_set->get() as $order_set ) {
			$pay_at_rsk = $this->payments_metas_set_basic( $request, $store_id )
							->where( 'post_id', '=', $order_set->ID )
							->where( 'meta_key', 'like', '_edd_payment_total' );
			foreach ( $pay_at_rsk->get() as $payment_at_risk ) {
				$at_risk_revenue += $payment_at_risk->meta_value;
			}
		}

		$customers_segment['at_risk']['revenue'] = $at_risk_revenue;
		if ( $customers_segment['at_risk']['orders'] > 0 ) {
			$customers_segment['at_risk']['aco'] = $customers_segment['at_risk']['orders'] / $at_rsk_cust;
		} else {
			$customers_segment['at_risk']['aco'] = 0.00;
		}
		if ( $at_risk_revenue > 0 ) {
			$customers_segment['at_risk']['ltv'] = $at_risk_revenue / $at_rsk_cust;
		} else {
			$customers_segment['at_risk']['ltv'] = 0.00;
		}

		// Lost customers which are customers which orders not before 36 days
		$lost_order_payment_set = $this->payments_set_basic( $request, $store_id )
								->where(
									'post_date',
									'>',
									$today->subDays( 36 )
									->toDateTimeString()
								)
								->where( 'post_status', '!=', 'publish' );
		$lost_cust              = 0;
		foreach ( $lost_order_payment_set->get() as $order_set ) {
			$lost_cust += $this->customers_set_basic( $request, $store_id )
							->where( 'payment_ids', 'like', '%' . $order_set->ID . '%' )
							->count();
		}
		$customers_segment['lost']['customers'] = $lost_cust;
		$customers_segment['lost']['orders']    = $lost_order_payment_set->count();
		$lost_revenue                           = 0;
		foreach ( $lost_order_payment_set->get() as $order_set ) {
			$pay_lost = $this->payments_metas_set_basic( $request, $store_id )
							->where( 'post_id', '=', $order_set->ID )
							->where( 'meta_key', 'like', '_edd_payment_total' );
			foreach ( $pay_lost->get() as $payment_lst ) {
				$lost_revenue += $payment_lst->meta_value;
			}
		}

		$customers_segment['lost']['revenue'] = $lost_revenue;
		if ( $customers_segment['lost']['orders'] > 0 ) {
			$customers_segment['lost']['aco'] = $customers_segment['lost']['orders'] / $lost_cust;
		} else {
			$customers_segment['lost']['aco'] = 0.00;
		}
		if ( $lost_revenue > 0 ) {
			$customers_segment['lost']['ltv'] = $lost_revenue / $lost_cust;
		} else {
			$customers_segment['lost']['ltv'] = 0.00;
		}

		// Joined last month - actual last month
		$firstDayofPreviousMonth                      = \Carbon\Carbon::now()->startOfMonth()->subMonth()->toDateString();
		$lastDayofPreviousMonth                       = \Carbon\Carbon::now()->subMonth()->endOfMonth()->toDateString();
		$last_month_order_cust_set                    = $this->customers_set_basic( $request, $store_id )
								->where( 'date_created', '>=', $firstDayofPreviousMonth )
								->where( 'date_created', '<=', $lastDayofPreviousMonth );
		$customers_segment['last_month']['customers'] = $last_month_order_cust_set->count();
		$last_month_count_orders                      = 0;
		$last_month_revenue                           = 0;
		foreach ( $last_month_order_cust_set->get() as $customer_set ) {
			$expld_payment_ids        = explode( ',', $customer_set->payment_ids );
			$last_month_count_orders += count( $expld_payment_ids );
			$last_month_revenue      += $customer_set->purchase_value;
		}
		$customers_segment['last_month']['orders']  = $last_month_count_orders;
		$customers_segment['last_month']['revenue'] = $last_month_revenue;
		if ( $last_month_count_orders > 0 ) {
			$customers_segment['last_month']['aco'] = $last_month_count_orders / $customers_segment['last_month']['customers'];
		} else {
			$customers_segment['last_month']['aco'] = 0.00;
		}
		if ( $last_month_revenue > 0 ) {
			$customers_segment['last_month']['ltv'] = $last_month_revenue / $customers_segment['last_month']['customers'];
		} else {
			$customers_segment['last_month']['ltv'] = 0.00;
		}

		// Checked out as guest customer
		$guest_order_cust_set                    = $this->customers_set_basic( $request, $store_id )
								->where( 'user_id', '=', 0 );
		$customers_segment['guest']['customers'] = $guest_order_cust_set->count();
		$guest_count_orders                      = 0;
		$guest_revenue                           = 0;
		foreach ( $guest_order_cust_set->get() as $customer_set ) {
			$expld_payment_ids   = explode( ',', $customer_set->payment_ids );
			$guest_count_orders += count( $expld_payment_ids );
			$guest_revenue      += $customer_set->purchase_value;
		}
		$customers_segment['guest']['orders']  = $guest_count_orders;
		$customers_segment['guest']['revenue'] = $guest_revenue;
		if ( $guest_count_orders > 0 ) {
			$customers_segment['guest']['aco'] = $guest_count_orders / $customers_segment['guest']['customers'];
		} else {
			$customers_segment['guest']['aco'] = 0.00;
		}
		if ( $guest_revenue > 0 ) {
			$customers_segment['guest']['ltv'] = $guest_revenue / $customers_segment['guest']['customers'];
		} else {
			$customers_segment['guest']['ltv'] = 0.00;
		}

		// Checked out as registered customer
		$registered_order_cust_set                    = $this->customers_set_basic( $request, $store_id )
								->where( 'user_id', '!=', 0 );
		$customers_segment['registered']['customers'] = $registered_order_cust_set->count();
		$registered_count_orders                      = 0;
		$registered_revenue                           = 0;
		foreach ( $registered_order_cust_set->get() as $customer_set ) {
			$expld_payment_ids        = explode( ',', $customer_set->payment_ids );
			$registered_count_orders += count( $expld_payment_ids );
			$registered_revenue      += $customer_set->purchase_value;
		}
		$customers_segment['registered']['orders']  = $registered_count_orders;
		$customers_segment['registered']['revenue'] = $registered_revenue;
		if ( $registered_count_orders > 0 ) {
			$customers_segment['registered']['aco'] = $registered_count_orders / $customers_segment['registered']['customers'];
		} else {
			$customers_segment['registered']['aco'] = 0.00;
		}
		if ( $registered_revenue > 0 ) {
			$customers_segment['registered']['ltv'] = $registered_revenue / $customers_segment['registered']['customers'];
		} else {
			$customers_segment['registered']['ltv'] = 0.00;
		}

		// All customers
		$all_order_cust_set                    = $this->customers_set_basic( $request, $store_id );
		$customers_segment['all']['customers'] = $all_order_cust_set->count();
		$all_count_orders                      = 0;
		$all_revenue                           = 0;
		foreach ( $all_order_cust_set->get() as $customer_set ) {
			$expld_payment_ids = explode( ',', $customer_set->payment_ids );
			$all_count_orders += count( $expld_payment_ids );
			$all_revenue      += $customer_set->purchase_value;
		}
		$customers_segment['all']['orders']  = $all_count_orders;
		$customers_segment['all']['revenue'] = $all_revenue;
		if ( $all_count_orders > 0 ) {
			$customers_segment['all']['aco'] = $all_count_orders / $customers_segment['all']['customers'];
		} else {
			$customers_segment['all']['aco'] = 0.00;
		}
		if ( $all_revenue > 0 ) {
			$customers_segment['all']['ltv'] = $all_revenue / $customers_segment['all']['customers'];
		} else {
			$customers_segment['all']['ltv'] = 0.00;
		}

		return $customers_segment;
	}

	/**
	 * For getting latest 25 customers listing rest call
	 * @param $request - that will have request submitted to this method route.
	 * @param $store_id - for specifying store
	 */
	public function get_events_customers( Request $request, $store_id ) {
		$per_page      = $request->has( 'per_page' ) ? (int) $request->per_page : 25;
		$page          = $request->has( 'page' ) ? (int) $request->page : 1;
		$search        = $request->has( 'search' ) ? $request->search : '';
		$customers_set = Customer::where( 'store_id', $store_id );
		if ( $request->has( [ 'startdate', 'enddate' ] ) ) {
			$customers_set->where( 'date_created', '>=', $request->startdate )
						->where( 'date_created', '<=', $request->enddate );
		}
		if ( $search != '' ) {
			$customers_set->where(
				function( $query ) use ( $search ) {
							$query->where( 'id', 'like', '%' . $search . '%' )
							->orWhere( 'email', 'like', '%' . $search . '%' )
							->orWhere( 'name', 'like', '%' . $search . '%' )
							->orWhere( 'notes', 'like', '%' . $search . '%' );
				}
			);
		}
		$customers = $customers_set->orderBy( 'date_created', 'desc' )->paginate( $per_page );

		$recent_events = [];
		$k             = 0;
		foreach ( $customers as $customer ) {
			$recent_events[ $k ]['event_type']        = 'customer';
			$recent_events[ $k ]['event_id']          = $customer->id;
			$recent_events[ $k ]['user_display_name'] = $customer->name;
			$recent_events[ $k ]['event_date']        = $customer->date_created;
			$recent_events[ $k ]['user_avatar']       = app( GeneralController::class )->get_user_avatar_by_email( $customer->email );
			$recent_events[ $k ]['user_id']           = $customer->user_id;
			$recent_events[ $k ]['purchase_count']    = $customer->purchase_count;
			// $customer_meta_set = CustomerMeta::where('store_id', $store_id)
			//                     ->where('customer_id', $customer->id)
			//                     ->where('meta_key', '_edd_customer_total')
			//                     ->get();
			// foreach ($customer_meta_set as $customer_meta_value) {
			//     $recent_events[$k]['customer'] = $customer_meta_value->meta_value;
			// }
			$k++;
		}

		return $recent_events;
	}

	protected function customers_set_basic( $request, $store_id ) {
		 $customers_set = Customer::where( 'store_id', $store_id );
		if ( $request->has( [ 'startdate', 'enddate' ] ) ) {
			$customers_set->where( 'date_created', '>=', $request->startdate )
						->where( 'date_created', '<=', $request->enddate );
		}
		if ( $request->has( 'search' ) ) {
			$customers_set->orWhere( 'id', 'like', '%' . $request->search . '%' )
						->orWhere( 'email', 'like', '%' . $request->search . '%' )
						->orWhere( 'name', 'like', '%' . $request->search . '%' )
						->orWhere( 'notes', 'like', '%' . $request->search . '%' );
		}
		return $customers_set;
	}

	protected function payments_set_basic( $request, $store_id ) {
		$payments_set = Payment::where( 'store_id', $store_id );
		if ( $request->has( [ 'startdate', 'enddate' ] ) ) {
			$payments_set->where( 'date_created', '>=', $request->startdate )
						->where( 'date_created', '<=', $request->enddate );
		}
		if ( $request->has( 'search' ) ) {
			$payments_set->orWhere( 'id', 'like', '%' . $request->search . '%' )
						->orWhere( 'email', 'like', '%' . $request->search . '%' )
						->orWhere( 'name', 'like', '%' . $request->search . '%' )
						->orWhere( 'notes', 'like', '%' . $request->search . '%' );
		}
		return $payments_set;
	}

	protected function payments_metas_set_basic( $request, $store_id ) {
		$payments_metas_set = PaymentMeta::where( 'store_id', $store_id );
		if ( $request->has( [ 'startdate', 'enddate' ] ) ) {
			$payments_metas_set->where( 'date_created', '>=', $request->startdate )
						->where( 'date_created', '<=', $request->enddate );
		}
		if ( $request->has( 'search' ) ) {
			$payments_metas_set->orWhere( 'id', 'like', '%' . $request->search . '%' )
						->orWhere( 'email', 'like', '%' . $request->search . '%' )
						->orWhere( 'name', 'like', '%' . $request->search . '%' )
						->orWhere( 'notes', 'like', '%' . $request->search . '%' );
		}
		return $payments_metas_set;
	}

	protected function ip_info( $ip = null, $purpose = 'location', $deep_detect = true ) {
		$output = null;
		if ( filter_var( $ip, FILTER_VALIDATE_IP ) === false ) {
			$ip = $_SERVER['REMOTE_ADDR'];
			if ( $deep_detect ) {
				if ( filter_var( @$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP ) ) {
					$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				}
				if ( filter_var( @$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP ) ) {
					$ip = $_SERVER['HTTP_CLIENT_IP'];
				}
			}
		}
		$purpose    = str_replace( array( 'name', "\n", "\t", ' ', '-', '_' ), null, strtolower( trim( $purpose ) ) );
		$support    = array( 'country', 'countrycode', 'state', 'region', 'city', 'location', 'address' );
		$continents = array(
			'AF' => 'Africa',
			'AN' => 'Antarctica',
			'AS' => 'Asia',
			'EU' => 'Europe',
			'OC' => 'Australia (Oceania)',
			'NA' => 'North America',
			'SA' => 'South America',
		);
		if ( filter_var( $ip, FILTER_VALIDATE_IP ) && in_array( $purpose, $support ) ) {
			$ipdat = @json_decode( file_get_contents( 'http://www.geoplugin.net/json.gp?ip=' . $ip ) );
			if ( @strlen( trim( $ipdat->geoplugin_countryCode ) ) == 2 ) {
				switch ( $purpose ) {
					case 'location':
						$output = array(
							'city'           => @$ipdat->geoplugin_city,
							'state'          => @$ipdat->geoplugin_regionName,
							'country'        => @$ipdat->geoplugin_countryName,
							'country_code'   => @$ipdat->geoplugin_countryCode,
							'continent'      => @$continents[ strtoupper( $ipdat->geoplugin_continentCode ) ],
							'continent_code' => @$ipdat->geoplugin_continentCode,
						);
						break;
					case 'address':
						$address = array( $ipdat->geoplugin_countryName );
						if ( @strlen( $ipdat->geoplugin_regionName ) >= 1 ) {
							$address[] = $ipdat->geoplugin_regionName;
						}
						if ( @strlen( $ipdat->geoplugin_city ) >= 1 ) {
							$address[] = $ipdat->geoplugin_city;
						}
						$output = implode( ', ', array_reverse( $address ) );
						break;
					case 'city':
						$output = @$ipdat->geoplugin_city;
						break;
					case 'state':
						$output = @$ipdat->geoplugin_regionName;
						break;
					case 'region':
						$output = @$ipdat->geoplugin_regionName;
						break;
					case 'country':
						$output = @$ipdat->geoplugin_countryName;
						break;
					case 'countrycode':
						$output = @$ipdat->geoplugin_countryCode;
						break;
				}
			}
		}
		return $output;
	}

	/**
	 * [prepare_after description]
	 * @param  [type] $date_string [description]
	 * @return [type]              [description]
	 */
	public function prepare_after( $date_string ) {
		$after = new Carbon( $date_string );
		return $after->startOfDay()->toDateTimeString();
	}

	/**
	 * [prepare_before description]
	 * @param  [type] $date_string [description]
	 * @return [type]              [description]
	 */
	public function prepare_before( $date_string ) {
		$before = new Carbon( $date_string );
		return $before->endOfDay()->toDateTimeString();
	}

	/**
	 * [get_gravatar description]
	 * @param  [type]  $email [description]
	 * @param  integer $s     [description]
	 * @param  string  $d     [description]
	 * @param  string  $r     [description]
	 * @param  boolean $img   [description]
	 * @param  array   $atts  [description]
	 * @return [type]         [description]
	 */
	public function get_gravatar( $email, $s = 80, $d = 'mp', $r = 'g', $img = false, $atts = array() ) {
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
