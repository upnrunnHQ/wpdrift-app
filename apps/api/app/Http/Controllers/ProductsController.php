<?php
/**
 * Products Controller
 * This will have all the products controller related methods that will serv
 * REST End Points
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleHttp;
use App\Download;
use App\DownloadMeta;
use App\DownloadLog;
use App\Site;
use App\TermAssigned;
use App\TermTaxonomy;
use App\Payment;
use App\PaymentMeta;
use App\EddLog;
use App\EddLogMeta;
use App\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProductsController extends Controller {

	/**
	 * For getting single product rest call
	 * @param $request - that will have request submitted to this method route.
	 * @param $store_id - for specifying store
	 * @param $product_id - for specifying product id
	 */
	public function show( Request $request, $store_id, $product_id ) {
		$product_set = Download::where( 'post_id', (int) $product_id )
		->where( 'store_id', $store_id )
		->first();

		// get store details
		$store_info = Site::where( 'site_id', $store_id )->first();

		// prepare individual items as per the page need
		$product['title']         = $product_set->post_title;
		$product['edit_link']     = $store_info->site_url . '/wp-admin/post.php?post=' . $product_id . '&action=edit';
		$product['view_link']     = $store_info->site_url . '/downloads/' . $product_set->post_name . '/';
		$product['post_author']   = $product_set->post_author;
		$product['name']          = $product_set->post_name;
		$product['content']       = $product_set->post_content;
		$product['excerpt']       = substr( $product_set->post_content, 0, 100 );
		$product['status']        = $product_set->post_status;
		$product['create_date']   = $product_set->post_date;
		$product['modified_date'] = $product_set->post_modified;
		// get category for products
		$term_assigned = TermAssigned::where( 'store_id', $store_id )
		->where( 'object_id', $product_id )
		->get();

		$terms = 0;
		foreach ( $term_assigned as $term_detail ) {
			// get term name
			$term_name                  = TermTaxonomy::where( 'term_taxonomy_id', $term_detail->term_taxonomy_id )
									->where( 'store_id', $store_id )
									->first( [ 'term_id', 'name', 'slug', 'taxonomy', 'description' ] );
			$product['terms'][ $terms ] = $term_name;
			$terms++;
		}

		// net sold
		$net_sold = DownloadMeta::where( 'post_id', $product_id )
		->where( 'store_id', $store_id )
		->where( 'meta_key', '_edd_download_sales' )
		->first();
		if ( $net_sold ) {
			$product['net_sold'] = $net_sold->meta_value;
		}

		// net revenue
		$net_revenue = DownloadMeta::where( 'post_id', $product_id )
		->where( 'store_id', $store_id )
		->where( 'meta_key', '_edd_download_earnings' )
		->first();
		if ( $net_revenue ) {
			$product['net_revenue'] = $net_revenue->meta_value;
		}

		// thumbnail
		$products_thumbnail = DownloadMeta::where( 'store_id', $store_id )
		->where( 'post_id', $product_id )
		->where( 'meta_key', '_thumbnail_url' )
		->first();

		$product['thumbnail_url'] = $products_thumbnail ? $products_thumbnail->meta_value : $this->get_logo( $store_info->site_url );

		// all download logs
		$product['logs'] = DownloadLog::where( 'download_id', $product_id )
		->where( 'store_id', $store_id )
		->get();

		$product_metas  = DownloadMeta::where( 'post_id', $product_id )
		->where( 'store_id', $store_id )
		->get();
		$exclude_metas  = [ '_edd_download_sales', '_edd_download_earnings', '_thumbnail_url' ];
		$searilize_meta = [ 'edd_download_files', 'edd_download_files', 'edd_variable_prices', '_edd_bundled_products', '_edd_bundled_products_conditions', 'bonus_item' ];
		foreach ( $product_metas as $product_meta ) {
			if ( in_array( $product_meta->meta_key, $searilize_meta ) ) {
				$product[ $product_meta->meta_key ] = unserialize( $product_meta->meta_value );
			} else {
				if ( ! in_array( $product_meta->meta_key, $exclude_metas ) ) {
					$product[ $product_meta->meta_key ] = $product_meta->meta_value;
				}
			}
		}

		/**
		 * get orders for particular download
		 * Issue #136: Allow getting orders for a specific product
		 */
		// payment ids first
		$payment_ids = PaymentMeta::where( 'meta_key', '_edd_payment_meta' )
		->where( 'meta_value', 'like', '%i:' . $product_id . ';s:8:"quantity";%' )
		->get();

		$k = 0;
		foreach ( $payment_ids as $payment_id ) {
			$product['payment_info'][ $k ] = unserialize( $payment_id->meta_value );
			$k++;
		}

		return $product;
	}
	/**
	 * For getting total number of products rest call
	 * @param $request - that will have request submitted to this method route.
	 * @param $store_id - for specifying store
	 */
	public function get_total_products( Request $request, $store_id ) {
		$products_set = Download::orderBy( 'name', 'desc' )->where( 'store_id', $store_id );
		if ( $request->has( [ 'startdate', 'enddate' ] ) ) {
			$products_set->where( 'post_date', '>=', $request->startdate )
						->where( 'post_date', '<=', $request->enddate );
		}
		$get_total_products = $products_set->count();
		return $get_total_products;
	}

	/**
	 * For getting all products rest call
	 * @param $request - that will have request submitted to this method route.
	 * @param $store_id - for specifying store
	 */
	public function get_products( Request $request, $store_id ) {
		$per_page = $request->has( 'per_page' ) ? (int) $request->per_page : 10;
		$page     = $request->has( 'page' ) ? (int) $request->page : 1;
		$search   = $request->has( 'search' ) ? $request->search : '';
		$results  = Download::where( 'store_id', strval( $store_id ) )
		->whereNotIn( 'post_status', [ 'draft' ] );
		$products = [];
		$old_data = false;

		$store_details = Site::where( 'site_id', $store_id )->first();
		$store_logo    = $this->get_logo( $store_details->site_url );

		if ( $request->has( [ 'startdate', 'enddate' ] ) ) {
			if ( ( '' != $request->startdate ) && ( '' != $request->enddate ) ) {
				$after  = $this->prepare_after( $request->startdate );
				$before = $this->prepare_before( $request->enddate );

				// to check if items exists in this period if not then return all products
				$total_items = Download::where( 'store_id', $store_id )->where( 'post_date', '>=', $after )
				->where( 'post_date', '<=', $before )
				->count();

				if ( ( 0 == $total_items ) && ( '' == $search ) ) {
					$results->where( 'post_date', '!=', '' );
					$old_data = true;
				} else {
					$results->where( 'post_date', '>=', $after )
					->where( 'post_date', '<=', $before );
				}
			}
		}

		if ( '' != $search ) {
			$results->where(
				function( $query ) use ( $search ) {
					$query->where( 'post_title', 'like', '%' . $search . '%' )
					->orWhere( 'post_name', 'like', '%' . $search . '%' )
					->orWhere( 'post_content', 'like', '%' . $search . '%' )
					->orWhere( 'post_id', '=', (int) $search );
				}
			);
		}

		if ( $request->has( [ 'orderby', 'order' ] ) ) {
			$results->orderBy( $request->orderby, $request->order );
		} else {
			$results->orderBy( 'post_id', 'desc' );
		}

		$total_products    = $results->count();
		$paginated_results = $results->paginate( $per_page );

		// get products meta
		$i = 0;
		foreach ( $paginated_results as $product ) {
			$products[ $i ]['id']            = $product->post_id;
			$products[ $i ]['title']         = $product->post_title;
			$products[ $i ]['post_author']   = $product->post_author;
			$products[ $i ]['name']          = $product->post_name;
			$products[ $i ]['content']       = $product->post_content;
			$products[ $i ]['excerpt']       = substr( $product->post_content, 0, 100 );
			$products[ $i ]['status']        = $product->post_status;
			$products[ $i ]['create_date']   = $product->post_date;
			$products[ $i ]['modified_date'] = $product->post_modified;
			// thumbnail
			$products_thumbnail = DownloadMeta::where( 'store_id', $store_id )->where( 'post_id', (string) $product->post_id )->where( 'meta_key', '_thumbnail_url' )
			->first( [ 'meta_key', 'meta_value' ] );

			$products[ $i ]['thumbnail_url'] = $products_thumbnail ? $products_thumbnail->meta_value : $store_logo;

			// get product link
			$products[ $i ]['link'] = $store_details->site_url . '/downloads/' . $product->post_name;
			// net sold
			$net_sold = DownloadMeta::where( 'post_id', (string) $product->post_id )
			->where( 'store_id', $store_id )
			->where( 'meta_key', '_edd_download_sales' )
			->first();
			if ( $net_sold ) {
				$products[ $i ]['net_sold'] = $net_sold->meta_value;
			} else {
				$products[ $i ]['net_sold'] = 0;
			}

			// net revenue
			$net_revenue = DownloadMeta::where( 'post_id', $product->post_id )
			->where( 'store_id', $store_id )
			->where( 'meta_key', '_edd_download_earnings' )
			->first();

			if ( $net_revenue ) {
				$products[ $i ]['net_revenue'] = $net_sold->meta_value;
			} else {
				$products[ $i ]['net_revenue'] = 0;
			}

			$i++;
		}

		$val = new LengthAwarePaginator(
			$products, // Only grab the items we need
			$total_products, // Total items
			$per_page, // Items per page
			$page
		);

		return [
			'old_data' => $old_data,
			'result'   => $val,
		];
	}

	/**
	 * For getting all products orders rest call
	 * @param $request - that will have request submitted to this method route.
	 * @param $store_id - for specifying store
	 */
	public function get_products_orders( Request $request, $store_id, $product_id ) {
		$poduct_payments_ids = PaymentMeta::where( 'meta_key', '=', '_edd_payment_meta' )
		->where( 'store_id', '=', "{$store_id}" )
		->where( 'meta_value', 'like', '%i:' . $product_id . ';%' )
		->get();
		$payment_ids         = [];
		foreach ( $poduct_payments_ids as $poduct_payments_id ) {
			$payment_ids[] = $poduct_payments_id->post_id;
		}
		$p_ids = implode( ',', $payment_ids );
		// get payments information
		$payments = Payment::whereIn( 'ID', $payment_ids )->get();

		return $payments;
	}

	/**
	 * Get total no. of products
	 */
	/**
	 * For getting products listing rest call
	 * @param $request - that will have request submitted to this method route.
	 * @param $store_id - for specifying store
	 */
	public function get_total_number_products( Request $request, $store_id ) {
		$products_set = Download::where( 'store_id', $store_id );
		if ( $request->has( [ 'startdate', 'enddate' ] ) ) {
			if ( $request->startdate != '' && $request->enddate != '' ) {
				// to check if items exists in this period if not then return all products
				$products_set->where( 'post_date', '>=', $request->startdate )
							->where( 'post_date', '<=', $request->enddate );
			}
		}
		return $total_products = $products_set->count();
	}

	/**
	 * Get top purchased products
	 */
	/**
	 * For getting top purchased products listing rest call
	 * @param $request - that will have request submitted to this method route.
	 * @param $store_id - for specifying store
	 */
	public function get_top_purchased_products( Request $request, $store_id ) {
		$per_page = $request->has( 'per_page' ) ? (int) $request->per_page : 10;
		// get products meta
		$products_meta = DownloadMeta::where( 'store_id', $store_id )
							->where( 'meta_key', '_edd_download_sales' )
							->where( 'meta_value', '<>', '0' )
							->orderBy( 'meta_value', 'DESC' )
							->take( $per_page )
							->get();
		$products_top  = [];
		$i             = 0;
		foreach ( $products_meta as $product_meta ) {
			// set up the array for final
			$product_details = Download::where( 'store_id', $store_id )
							->where( 'post_id', (int) $product_meta->post_id )
							->select( 'post_id', 'post_title' )
							->first();
			if ( $product_details ) {
				$products_top[ $i ]['id']    = $product_details->post_id;
				$products_top[ $i ]['name']  = $product_details->post_title;
				$products_top[ $i ]['sales'] = $product_meta->meta_value;
				$i++;
			}
		}
		return $products_top;
	}

	/**
	 * [get_payments description]
	 * @param  Request $request  [description]
	 * @param  [type]  $store_id [description]
	 * @return [type]            [description]
	 */
	public function get_payments( Request $request, $store_id ) {
		$data     = [];
		$logs     = EddLog::where( 'store_id', $store_id );
		$download = $this->get_filtered_download( $request );
		if ( $download ) {
			$logs->where( 'post_parent', $download );
		}

		$log_ids = $logs->pluck( 'ID' );
		if ( $log_ids ) {
			$payment_ids = [];
			foreach ( $log_ids as $log_id ) {
				$payment_id = EddLogMeta::where( 'meta_key', '_edd_log_payment_id' )
				->where( 'post_id', (string) $log_id )
				->pluck( 'meta_value' )
				->first();

				if ( $payment_id ) {
					$payment_ids[] = (int) $payment_id;
				}
			}
		}

		$base_payments = Payment::whereIn( 'ID', $payment_ids );
		if ( $request->has( 'sortField' ) ) {
			$short_order = ( $request->input( 'sortOrder' ) == 'ascend' ) ? 'asc' : 'desc';
			$short_field = $request->input( 'sortField' );
			if ( 'date' == $short_field ) {
				$short_field = 'post_date';
			}
		} else {
			$short_order = 'desc';
			$short_field = 'post_date';
		}

		$base_payments->orderBy( $short_field, $short_order );

		$paginated_payments = $base_payments->paginate( 10 );
		$payments           = $paginated_payments->items();

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

				$email = PaymentMeta::where( 'store_id', $store_id )
				->where( 'post_id', (string) $payment->ID )
				->where( 'meta_key', '_edd_payment_user_email' )
				->pluck( 'meta_value' )
				->first();

				$customer_id = PaymentMeta::where( 'store_id', $store_id )
				->where( 'post_id', (string) $payment->ID )
				->where( 'meta_key', '_edd_payment_customer_id' )
				->pluck( 'meta_value' )
				->first();

				$data[] = [
					'ID'       => $payment->ID,
					'customer' => [
						'id'     => $customer_id,
						'title'  => $payment->post_title,
						'avatar' => $this->get_gravatar( $email ),
						'email'  => $email,
					],
					'total'    => $total,
					'subtotal' => $subtotal,
					'date'     => $payment->post_date,
				];
			}
		}

		return [
			'total'        => $paginated_payments->total(),
			'currentPage'  => $paginated_payments->currentPage(),
			'hasMorePages' => $paginated_payments->hasMorePages(),
			'results'      => $data,
		];
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
	 * [get_filtered_download description]
	 * @param  [type] $request [description]
	 * @return [type]          [description]
	 */
	public function get_filtered_download( $request ) {
		return $request->has( 'download' ) ? abs( intval( $request->input( 'download' ) ) ) : false;
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
		// $client     = new GuzzleHttp( [ 'base_uri' => $logo_url ] );
		// $response   = $client->request( 'GET' );
		// if ( '400' == $response->getStatusCode() ) {
		// 	return false;
		// }

		/**
		 * [return description]
		 * @var [type]
		 */
		return $logo_url;
	}
}
