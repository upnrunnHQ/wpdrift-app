<?php
/**
 * Payment Controller
 * This will have all the payment controller related methods that will serv
 * REST End Points
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Payment;
use App\PaymentMeta;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use GeoIp2\Database\Reader;

/**
 *  PaymentController Class
 *
 */
class PaymentsController extends Controller
{

    /**
     * For getting single customer rest call
     * @param $request - that will have request submitted to this method route.
     * @param $store_id - for specifying store
     * @param $order_id - for specifying order id
     */
    public function show(Request $request, $store_id, $order_id)
    {
        $payment_set = Payment::where('ID', (int) $order_id)
        ->where('store_id', $store_id)
        ->first();

        $payment_metas = PaymentMeta::where('post_id', (int) $payment_set->ID)
        ->where('store_id', $store_id)
        ->get([ 'meta_key', 'meta_value' ]);

        $payment['order_id']   = $payment_set->ID;
        $payment['customer']   = $payment_set->post_title;
        $payment['order_date'] = $payment_set->post_date;
        $payment['status']     = $payment_set->post_status;

        $base_payment_meta = PaymentMeta::where('store_id', $store_id)
        ->where('post_id', (string) $payment_set->ID);

        $payment_meta_set = $base_payment_meta->where('meta_key', '_edd_payment_total')->get();
        foreach ($payment_meta_set as $payment_meta_value) {
            $payment['total'] = $payment_meta_value->meta_value;
        }

        // customer meta
        $customer_payment_meta = PaymentMeta::where('store_id', $store_id)->where('post_id', (string) $payment_set->ID)
        ->where('meta_key', '_edd_payment_meta')
        ->get();
        foreach ($customer_payment_meta as $customer_payment_val) {
            if ('' != $customer_payment_val->meta_value) {
                $customer_payment_meta = unserialize($customer_payment_val->meta_value);
            } else {
                $customer_payment_meta = '';
            }
            $payment['customer_payment_meta'] = $customer_payment_meta;
        }

        $customer_ip     = PaymentMeta::where('store_id', $store_id)
        ->where('post_id', (string) $payment_set->ID)->where('meta_key', '_edd_payment_user_ip')
        ->get();
        $customer_ip_add = '';
        foreach ($customer_ip as $cust_ip_val) {
            $payment['customer_ip'] = $cust_ip_val->meta_value;
        }

        if (! empty($payment['customer_ip'])) {
            $reader                       = new Reader(storage_path('app/GeoLite2-City.mmdb'));
            $record                       = $reader->city($payment['customer_ip']);
            $payment['customer_location'] = [
                'city'    => $record->city->name,
                'country' => $record->country->name,
                'lat'     => $record->location->latitude,
                'lng'     => $record->location->longitude,
            ];
        }

        $customer_id = PaymentMeta::where('store_id', $store_id)
        ->where('post_id', (string) $payment_set->ID)->where('meta_key', '_edd_payment_customer_id')
        ->get();
        foreach ($customer_id as $cust_id_val) {
            $payment['customer_id'] = $cust_id_val->meta_value;
        }

        $customer_email = PaymentMeta::where('store_id', $store_id)
        ->where('post_id', (string) $payment_set->ID)->where('meta_key', '_edd_payment_user_email')
        ->get();
        foreach ($customer_email as $cust_eml_val) {
            $payment['customer_avatar'] = app(GeneralController::class)->get_user_avatar_by_email($cust_eml_val->meta_value);
        }

        return $payment;
    }
    /**
     * For getting total number of payments rest call
     * @param $request - that will have request submitted to this method route.
     * @param $store_id - for specifying store
     */
    public function get_total_payments(Request $request, $store_id)
    {
        $payments_set = Payment::orderBy('name', 'desc')
                ->where('store_id', $store_id);
        if ($request->has([ 'startdate', 'enddate' ])) {
            $payments_set->where('date_created', '>=', $request->startdate)
                        ->where('date_created', '<=', $request->enddate);
        }
        $get_total_payments = $payments_set->count();
        return $get_total_payments;
    }

    /**
     * For getting payments listing rest call
     * @param $request - that will have request submitted to this method route.
     * @param $store_id - for specifying store
     */
    public function get_payments(Request $request, $store_id)
    {
        $per_page     = $request->has('per_page') ? (int) $request->per_page : 10;
        $page         = $request->has('page') ? (int) $request->page : 1;
        $payments_set = Payment::where('store_id', $store_id);
        if ($request->has([ 'startdate', 'enddate' ])) {
            $payments_set->where('date_created', '>=', $request->startdate)
                        ->where('date_created', '<=', $request->enddate);
        }
        if ($request->has('search')) {
            $payments_set->orWhere(
                function ($query) use ($request) {
                    $query->where('ID', 'like', '%' . $request->search . '%')
                        ->where('email', 'like', '%' . $request->search . '%')
                        ->where('name', 'like', '%' . $request->search . '%')
                        ->where('notes', 'like', '%' . $request->search . '%');
                }
            );
        }
        $payments_set->orderBy('name', 'desc');

        return $payments_set->paginate($per_page);
    }

    /**
     * For getting latest 25payments listing rest call
     * @param $request - that will have request submitted to this method route.
     * @param $store_id - for specifying store
     */
    public function get_events_orders(Request $request, $store_id)
    {
        $per_page     = $request->has('per_page') ? (int) $request->per_page : 25;
        $page         = $request->has('page') ? (int) $request->page : 1;
        $payments_set = Payment::where('store_id', $store_id);
        if ($request->has([ 'startdate', 'enddate' ])) {
            $payments_set->where('date_created', '>=', $request->startdate)
                        ->where('date_created', '<=', $request->enddate);
        }
        if ($request->has('search')) {
            $payments_set->orWhere('ID', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                        ->orWhere('name', 'like', '%' . $request->search . '%')
                        ->orWhere('notes', 'like', '%' . $request->search . '%');
        }
        $payments = $payments_set->orderBy('post_date', 'desc')->paginate($per_page);

        $recent_events = [];
        $k             = 0;
        foreach ($payments as $payment) {
            $recent_events[ $k ]['event_type']        = 'order';
            $recent_events[ $k ]['event_id']          = $payment->ID;
            $recent_events[ $k ]['user_display_name'] = $payment->post_title;
            $recent_events[ $k ]['event_date']        = $payment->post_date;
            $recent_events[ $k ]['user_id']           = $payment->post_author;
            $recent_events[ $k ]['status']            = $payment->post_status;
            $payment_meta_set                         = PaymentMeta::where('store_id', $store_id)
                                ->where('post_id', $payment->ID)
                                ->where('meta_key', '_edd_payment_total')
                                ->get();
            foreach ($payment_meta_set as $payment_meta_value) {
                $recent_events[ $k ]['payment'] = $payment_meta_value->meta_value;
            }
            $k++;
        }

        return $recent_events;
    }
    /**
     * Net Revenue
     * @param $request - that will have request submitted to this method route.
     * @param $store_id - for specifying store
     */
    public function get_net_revenue(Request $request, $store_id)
    {
        $payments_set = Payment::select('ID')->where('store_id', $store_id)
        ->where('post_status', 'publish');

        if ($request->has([ 'startdate', 'enddate' ])) {
            $after  = $this->prepare_after($request->startdate);
            $before = $this->prepare_before($request->enddate);

            $payments_set->where('post_date', '>=', $after)
            ->where('post_date', '<=', $before);
        }

        $payments    = $payments_set->orderBy('name', 'desc')->get();
        $net_revenue = 0;
        $k           = 0;

        $data = [];
        foreach ($payments as $payment) {
            $payment_meta_set = PaymentMeta::where('store_id', $store_id)
            ->where('post_id', (string) $payment->ID)
            ->where('meta_key', '_edd_payment_total')
            ->get();

            foreach ($payment_meta_set as $payment_meta_value) {
                $net_revenue += $payment_meta_value->meta_value;
            }

            $payment_tax_set = PaymentMeta::where('store_id', $store_id)
            ->where('post_id', (string) $payment->ID)
            ->where('meta_key', '_edd_payment_tax')
            ->get();

            foreach ($payment_tax_set as $payment_meta_value) {
                $net_revenue -= $payment_meta_value->meta_value;
            }

            $k++;
        }

        return $net_revenue;
    }

    /**
     * Gross Sales
     * @param $request - that will have request submitted to this method route.
     * @param $store_id - for specifying store
     */
    public function get_gross_sales(Request $request, $store_id)
    {
        $payments_set = Payment::select('ID')
                        ->where('store_id', $store_id)
                        ->where('post_status', 'publish');
        if ($request->has([ 'startdate', 'enddate' ])) {
            $payments_set->where('post_date', '>=', $request->startdate)
                        ->where('post_date', '<=', $request->enddate);
        }
        if ($request->has('search')) {
            $payments_set->orWhere('ID', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                        ->orWhere('name', 'like', '%' . $request->search . '%')
                        ->orWhere('notes', 'like', '%' . $request->search . '%');
        }
        $payments    = $payments_set->orderBy('name', 'desc')->get();
        $gross_sales = 0;
        $k           = 0;
        foreach ($payments as $payment) {
            $payment_meta_set = PaymentMeta::where('store_id', $store_id)
                                ->where('post_id', $payment->ID)
                                ->where('meta_key', '_edd_payment_total')
                                ->get();
            foreach ($payment_meta_set as $payment_meta_value) {
                $gross_sales += $payment_meta_value->meta_value;
            }
            $k++;
        }
        return $gross_sales;
    }
    /**
     * Gross Refunds
     * @param $request - that will have request submitted to this method route.
     * @param $store_id - for specifying store
     */
    public function get_gross_refunds(Request $request, $store_id)
    {
        $payments_set = Payment::select('ID')
                        ->where('store_id', $store_id)
                        ->where('post_status', 'refunded');
        if ($request->has([ 'startdate', 'enddate' ])) {
            $payments_set->where('post_date', '>=', $request->startdate)
                        ->where('post_date', '<=', $request->enddate);
        }
        if ($request->has('search')) {
            $payments_set->orWhere('ID', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                        ->orWhere('name', 'like', '%' . $request->search . '%')
                        ->orWhere('notes', 'like', '%' . $request->search . '%');
        }
        $payments      = $payments_set->orderBy('name', 'desc')->get();
        $gross_refunds = 0;
        $k             = 0;
        foreach ($payments as $payment) {
            $payment_meta_set = PaymentMeta::where('store_id', $store_id)
                                ->where('post_id', $payment->ID)
                                ->where('meta_key', '_edd_payment_total')
                                ->get();
            foreach ($payment_meta_set as $payment_meta_value) {
                $gross_refunds += $payment_meta_value->meta_value;
            }
            $k++;
        }
        return $gross_refunds;
    }
    /**
     * For getting total number of items sold
     * @param $request - that will have request submitted to this method route.
     * @param $store_id - for specifying store
     */
    public function get_total_items_sold(Request $request, $store_id)
    {
        $payments_set = Payment::select('ID')->where('store_id', $store_id)
        ->where('post_status', 'publish');

        if ($request->has([ 'startdate', 'enddate' ])) {
            $after  = $this->prepare_after($request->startdate);
            $before = $this->prepare_before($request->enddate);

            $payments_set->where('post_date', '>=', $after)->where('post_date', '<=', $before);
        }

        $total_items_sold = 0;
        foreach ($payments_set->get() as $payment) {
            $payment_meta_set = PaymentMeta::where('store_id', $store_id)
            ->where('post_id', (string) $payment->ID)
            ->where('meta_key', '_edd_payment_meta')
            ->get();

            foreach ($payment_meta_set as $payment_meta_value) {
                $payment_meta_set_arry = unserialize($payment_meta_value->meta_value);
                $total_items_sold     += count($payment_meta_set_arry['downloads']);
            }
        }

        return $total_items_sold;
    }
    /**
     * Gross Taxes
     * @param $request - that will have request submitted to this method route.
     * @param $store_id - for specifying store
     */
    public function get_gross_taxes(Request $request, $store_id)
    {
        $payments_set = Payment::select('ID')
                        ->where('store_id', $store_id)
                        ->where('post_status', 'published');
        if ($request->has([ 'startdate', 'enddate' ])) {
            $payments_set->where('post_date', '>=', $request->startdate)
                        ->where('post_date', '<=', $request->enddate);
        }
        if ($request->has('search')) {
            $payments_set->orWhere('ID', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                        ->orWhere('name', 'like', '%' . $request->search . '%')
                        ->orWhere('notes', 'like', '%' . $request->search . '%');
        }
        $payments    = $payments_set->orderBy('name', 'desc')->get();
        $gross_taxes = 0;
        $k           = 0;
        foreach ($payments as $payment) {
            $payment_meta_set = PaymentMeta::where('store_id', $store_id)
                                ->where('post_id', $payment->ID)
                                ->where('meta_key', '_edd_payment_tax')
                                ->get();
            foreach ($payment_meta_set as $payment_meta_value) {
                $gross_taxes += $payment_meta_value->meta_value;
            }
            $k++;
        }
        return $gross_taxes;
    }
    /**
     * For getting total number of refunds
     * @param $request - that will have request submitted to this method route.
     * @param $store_id - for specifying store
     */
    public function get_total_number_refunds(Request $request, $store_id)
    {
        $payments_set = Payment::select('ID')->where('store_id', $store_id)
        ->where('post_status', 'refunded');

        if ($request->has([ 'startdate', 'enddate' ])) {
            $after  = $this->prepare_after($request->startdate);
            $before = $this->prepare_before($request->enddate);

            $payments_set->where('post_date', '>=', $after)
            ->where('post_date', '<=', $before);
        }

        if ($request->has('search')) {
            $payments_set->orWhere('ID', 'like', '%' . $request->search . '%')
            ->orWhere('email', 'like', '%' . $request->search . '%')
            ->orWhere('name', 'like', '%' . $request->search . '%')
            ->orWhere('notes', 'like', '%' . $request->search . '%');
        }

        $total_number_refunds = $payments_set->orderBy('name', 'desc')->count();
        return $total_number_refunds;
    }
    /**
     * For getting orders listing rest call
     * @param $request - that will have request submitted to this method route.
     * @param $store_id - for specifying store
     */
    public function get_orders(Request $request, $store_id)
    {
        $per_page     = $request->has('per_page') ? (int) $request->per_page : 10;
        $page         = $request->has('page') ? (int) $request->page : 1;
        $search       = $request->has('search') ? $request->search : '';
        $payments_set = Payment::where('store_id', $store_id);
        $orders       = [];
        $old_data     = false;

        if ($request->has([ 'startdate', 'enddate' ])) {
            if (( '' != $request->startdate ) && ( '' != $request->enddate )) {
                $after  = $this->prepare_after($request->startdate);
                $before = $this->prepare_before($request->enddate);

                // to check if items exists in this period if not then return all products
                $total_items = Payment::where('store_id', $store_id)->where('post_date', '>=', $after)
                ->where('post_date', '<=', $before)
                ->count();

                if (( 0 == $total_items ) && ( '' == $search )) {
                    $payments_set->where('post_date', '!=', '');
                    $old_data = true;
                } else {
                    $payments_set->where('post_date', '>=', $after)->where('post_date', '<=', $before);
                }
            }
        }

        if ('' != $search) {
            $payments_set->where(
                function ($query) use ($search) {
                    $query->where('post_title', 'like', '%' . $search . '%')
                    ->orWhere('post_name', 'like', '%' . $search . '%')
                    ->orWhere('ID', '=', (int) $search);
                }
            );
        }

        if ($request->has([ 'orderby', 'order' ])) {
            switch ($request->orderby) {
                case 'order_id':
                    $order_by_param = 'ID';
                    break;
                case 'customer':
                    $order_by_param = 'post_title';
                    break;
                case 'customer_id':
                    $order_by_param = 'user_id';
                    break;
                case 'order_date':
                    $order_by_param = 'post_date';
                    break;
                case 'status':
                    $order_by_param = 'post_status';
                    break;
            }
            $payments_set->orderBy($order_by_param, $request->order);
        } else {
            $payments_set->orderBy('name', 'desc');
        }
        $total_payments = $payments_set->count();
        $payments       = $payments_set->paginate($per_page);

        $k = 0;
        foreach ($payments as $payment_set) {
            $orders[ $k ]['order_id']          = $payment_set->ID;
            $orders[ $k ]['customer']          = $payment_set->post_title;
            $orders[ $k ]['order_date']        = $payment_set->post_date;
            $orders[ $k ]['status']            = $payment_set->post_status;
            $orders[ $k ]['customer_location'] = false;

            $payment_meta = unserialize(
                PaymentMeta::where('store_id', $store_id)
                ->where('post_id', (string) $payment_set->ID)
                ->where('meta_key', '_edd_payment_meta')
                ->pluck('meta_value')
                ->first()
            );

            $total = PaymentMeta::where('store_id', $store_id)
            ->where('post_id', (string) $payment_set->ID)
            ->where('meta_key', '_edd_payment_total')
            ->pluck('meta_value')
            ->first();

            if (empty($total) && ( '0.00' != $total )) {
                if (isset($payment_meta['amount'])) {
                    $total = $payment_meta['amount'];
                }
            }

            $subtotal     = 0;
            $cart_details = isset($payment_meta['cart_details']) ? $payment_meta['cart_details'] : [];

            if (is_array($cart_details)) {
                foreach ($cart_details as $item) {
                    if (isset($item['subtotal'])) {
                        $subtotal += $item['subtotal'];
                    }
                }
            } else {
                $subtotal = $total;
            }

            $orders[ $k ]['total']    = $total;
            $orders[ $k ]['subtotal'] = $subtotal;

            $customer_id = PaymentMeta::where('store_id', $store_id)
            ->where('post_id', (string) $payment_set->ID)
            ->where('meta_key', '_edd_payment_customer_id')
            ->pluck('meta_value')
            ->first();

            $customer_email = PaymentMeta::where('store_id', $store_id)
            ->where('post_id', (string) $payment_set->ID)
            ->where('meta_key', '_edd_payment_user_email')
            ->pluck('meta_value')
            ->first();

            $customer_ip = PaymentMeta::where('store_id', $store_id)
            ->where('post_id', (string) $payment_set->ID)
            ->where('meta_key', '_edd_payment_user_ip')
            ->pluck('meta_value')
            ->first();

            $customer = [
                'id'     => $customer_id,
                'title'  => $payment_set->post_title,
                'email'  => $customer_email,
                'avatar' => $this->get_gravatar($customer_email),
                'ip'     => $customer_ip,
            ];

            if ($customer_ip) {
                $reader               = new Reader(storage_path('app/GeoLite2-City.mmdb'));
                $record               = $reader->city($customer_ip);
                $customer['location'] = [
                    'city'    => $record->city->name,
                    'country' => $record->country->name,
                    'lat'     => $record->location->latitude,
                    'lng'     => $record->location->longitude,
                ];

                $address = [];
                if ($record->city->name) {
                    $address[] = $record->city->name;
                }

                if ($record->country->name) {
                    $address[]                    = $record->country->name;
                    $customer['location']['code'] = strtolower($record->country->isoCode);
                }
                $customer['location']['address'] = implode(', ', $address);
            }

            $orders[ $k ]['customer'] = $customer;
            $orders[ $k ]['items']    = count($cart_details);
            $k++;
        }

        $val = new LengthAwarePaginator(
            $orders, // Only grab the items we need
            $total_payments, // Total items
            $per_page, // Items per page
            $page
        );

        return [
            'old_data' => $old_data,
            'result'   => $val,
        ];
    }

    /**
     * For getting refunds listing rest call
     * @param $request - that will have request submitted to this method route.
     * @param $store_id - for specifying store
     */
    public function get_refunds(Request $request, $store_id)
    {
        $per_page     = $request->has('per_page') ? (int) $request->per_page : 10;
        $page         = $request->has('page') ? (int) $request->page : 1;
        $payments_set = Payment::where('store_id', $store_id)
                        ->where('post_status', 'refunded');

        if ($request->has([ 'startdate', 'enddate' ])) {
            $payments_set->where('date_created', '>=', $request->startdate)
                        ->where('date_created', '<=', $request->enddate);
        }
        if ($request->has('search')) {
            $payments_set->orWhere('ID', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                        ->orWhere('name', 'like', '%' . $request->search . '%')
                        ->orWhere('notes', 'like', '%' . $request->search . '%');
        }
        $total_payments = $payments_set->count();
        $payments_set->orderBy('name', 'desc')->paginate($per_page);
        $refunds = [];
        $k       = 0;
        foreach ($payments_set as $payment_set) {
            $refunds[ $k ]['refund_id']   = $payment_set->ID;
            $refunds[ $k ]['customer']    = $payment_set->post_title;
            $refunds[ $k ]['customer_id'] = $payment_set->user_id;
            $payment_meta_set             = PaymentMeta::where('store_id', $store_id)
                                ->where('post_id', $payment_set->ID)
                                ->where('meta_key', '_edd_payment_total')
                                ->get();
            foreach ($payment_meta_set as $payment_meta_value) {
                $refunds[ $k ]['total'] = $payment_meta_value->meta_value;
            }
            $refunds[ $k ]['items'] = 1;
            $k++;
        }
        return new LengthAwarePaginator(
            $refunds, // Only grab the items we need
            $total_payments, // Total items
            $per_page, // Items per page
            $page
        );
    }
    /**
     * For getting orders segment listing rest call
     * @param $request - that will have request submitted to this method route.
     * @param $store_id - for specifying store
     */
    public function get_orders_segment(Request $request, $store_id)
    {
         // successfull orders
        $success_orders_set = Payment::where('store_id', $store_id)
                        ->where('post_status', 'publish');
        if ($request->has([ 'startdate', 'enddate' ])) {
            $success_orders_set->where('date_created', '>=', $request->startdate)
                        ->where('date_created', '<=', $request->enddate);
        }
        if ($request->has('search')) {
            $success_orders_set->orWhere('ID', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                        ->orWhere('name', 'like', '%' . $request->search . '%')
                        ->orWhere('notes', 'like', '%' . $request->search . '%');
        }
        $success_orders_set->orderBy('name', 'desc');

        $orders_segment['successfull_orders']['orders'] = $success_orders_set->orderBy('name', 'desc')->count();
        $success_full_order_gross_sales                 = 0;
        $items = 0;

        foreach ($success_orders_set->get() as $payment_set) {
            $payment_meta_set = PaymentMeta::where('store_id', $store_id)
                                ->where('post_id', $payment_set->ID)
                                ->where('meta_key', '_edd_payment_total')
                                ->get();
            foreach ($payment_meta_set as $payment_meta_value) {
                $success_full_order_gross_sales += $payment_meta_value->meta_value;
            }
            $payment_tax_set = PaymentMeta::where('store_id', $store_id)
                                ->where('post_id', $payment_set->ID)
                                ->where('meta_key', '_edd_payment_tax')
                                ->get();
            foreach ($payment_tax_set as $payment_meta_value) {
                $success_full_order_gross_sales -= $payment_meta_value->meta_value;
            }
            $items++;
        }
        $orders_segment['successfull_orders']['gross_sales'] = $success_full_order_gross_sales;
        $orders_segment['successfull_orders']['total_items'] = $items;
        if ($success_full_order_gross_sales > 0) {
            $orders_segment['successfull_orders']['aov'] = $success_full_order_gross_sales / $orders_segment['successfull_orders']['orders'];
        } else {
            $orders_segment['successfull_orders']['aov'] = 0;
        }
        if ($items > 0) {
            $orders_segment['successfull_orders']['aoi'] = $items / $orders_segment['successfull_orders']['orders'];
        } else {
            $orders_segment['successfull_orders']['aoi'] = 0;
        }
        // customer orders
        $customer_orders_set = Payment::where('store_id', $store_id)
                        ->where('post_status', 'publish')
                        ->where('user_id', '!=', 0);
        if ($request->has([ 'startdate', 'enddate' ])) {
            $customer_orders_set->where('date_created', '>=', $request->startdate)
                        ->where('date_created', '<=', $request->enddate);
        }
        if ($request->has('search')) {
            $customer_orders_set->orWhere('id', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                        ->orWhere('name', 'like', '%' . $request->search . '%')
                        ->orWhere('notes', 'like', '%' . $request->search . '%');
        }
        $customer_orders_set->orderBy('name', 'desc');

        $orders_segment['customer_orders']['orders'] = $customer_orders_set->orderBy('name', 'desc')->count();
        $customer_order_gross_sales                  = 0;
        $items                                       = 0;
        foreach ($customer_orders_set->get() as $payment_set) {
            $payment_meta_set = PaymentMeta::where('store_id', $store_id)
                                ->where('post_id', $payment_set->ID)
                                ->where('meta_key', '_edd_payment_total')
                                ->get();
            foreach ($payment_meta_set as $payment_meta_value) {
                $customer_order_gross_sales += $payment_meta_value->meta_value;
            }
            $payment_tax_set = PaymentMeta::where('store_id', $store_id)
                                ->where('post_id', $payment_set->ID)
                                ->where('meta_key', '_edd_payment_tax')
                                ->get();
            foreach ($payment_tax_set as $payment_meta_value) {
                $customer_order_gross_sales -= $payment_meta_value->meta_value;
            }
            $items++;
        }
        $orders_segment['customer_orders']['gross_sales'] = $customer_order_gross_sales;
        $orders_segment['customer_orders']['total_items'] = $items;
        if ($customer_order_gross_sales > 0) {
            $orders_segment['customer_orders']['aov'] = $customer_order_gross_sales / $orders_segment['customer_orders']['gross_sales'];
        } else {
            $orders_segment['customer_orders']['aov'] = 0;
        }
        if ($items > 0) {
            $orders_segment['customer_orders']['aoi'] = $items / $orders_segment['customer_orders']['orders'];
        } else {
            $orders_segment['customer_orders']['aoi'] = 0;
        }
        // guest orders
        $guest_orders_set = Payment::where('store_id', $store_id)
                        ->where('post_status', 'publish')
                        ->where('user_id', '=', 0);
        if ($request->has([ 'startdate', 'enddate' ])) {
            $guest_orders_set->where('date_created', '>=', $request->startdate)
                        ->where('date_created', '<=', $request->enddate);
        }
        if ($request->has('search')) {
            $guest_orders_set->orWhere('id', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                        ->orWhere('name', 'like', '%' . $request->search . '%')
                        ->orWhere('notes', 'like', '%' . $request->search . '%');
        }
        $guest_orders_set->orderBy('name', 'desc');

        $orders_segment['guest_orders']['orders'] = $guest_orders_set->orderBy('name', 'desc')->count();
        $guest_order_gross_sales                  = 0;
        $items                                    = 0;
        foreach ($guest_orders_set->get() as $payment_set) {
            $payment_meta_set = PaymentMeta::where('store_id', $store_id)
                                ->where('post_id', $payment_set->ID)
                                ->where('meta_key', '_edd_payment_total')
                                ->get();
            foreach ($payment_meta_set as $payment_meta_value) {
                $guest_order_gross_sales += $payment_meta_value->meta_value;
            }
            $payment_tax_set = PaymentMeta::where('store_id', $store_id)
                                ->where('post_id', $payment_set->ID)
                                ->where('meta_key', '_edd_payment_tax')
                                ->get();
            foreach ($payment_tax_set as $payment_meta_value) {
                $guest_order_gross_sales -= $payment_meta_value->meta_value;
            }
            $items++;
        }
        $orders_segment['guest_orders']['gross_sales'] = $guest_order_gross_sales;
        $orders_segment['guest_orders']['total_items'] = $items;
        if ($guest_order_gross_sales > 0) {
            $orders_segment['guest_orders']['aov'] = $guest_order_gross_sales / $orders_segment['guest_orders']['gross_sales'];
        } else {
            $orders_segment['guest_orders']['aov'] = 0;
        }
        if ($guest_order_gross_sales > 0) {
            $orders_segment['guest_orders']['aoi'] = $items / $orders_segment['guest_orders']['orders'];
        } else {
            $orders_segment['guest_orders']['aoi'] = 0;
        }
        // all orders
        $all_orders_set = Payment::where('store_id', $store_id);
        if ($request->has([ 'startdate', 'enddate' ])) {
            $all_orders_set->where('date_created', '>=', $request->startdate)
                        ->where('date_created', '<=', $request->enddate);
        }
        if ($request->has('search')) {
            $all_orders_set->orWhere('id', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                        ->orWhere('name', 'like', '%' . $request->search . '%')
                        ->orWhere('notes', 'like', '%' . $request->search . '%');
        }
        $all_orders_set->orderBy('name', 'desc');

        $orders_segment['all_orders']['orders'] = $all_orders_set->orderBy('name', 'desc')->count();
        $all_order_gross_sales                  = 0;
        $items                                  = 0;
        foreach ($all_orders_set->get() as $payment_set) {
            $payment_meta_set = PaymentMeta::where('store_id', $store_id)
                                ->where('post_id', $payment_set->ID)
                                ->where('meta_key', '_edd_payment_total')
                                ->get();
            foreach ($payment_meta_set as $payment_meta_value) {
                $all_order_gross_sales += $payment_meta_value->meta_value;
            }
            $payment_tax_set = PaymentMeta::where('store_id', $store_id)
                                ->where('post_id', $payment_set->ID)
                                ->where('meta_key', '_edd_payment_tax')
                                ->get();
            foreach ($payment_tax_set as $payment_meta_value) {
                $all_order_gross_sales -= $payment_meta_value->meta_value;
            }
            $items++;
        }
        $orders_segment['all_orders']['gross_sales'] = $all_order_gross_sales;
        $orders_segment['all_orders']['total_items'] = $items;
        if ($all_order_gross_sales > 0) {
            $orders_segment['all_orders']['aov'] = $all_order_gross_sales / $orders_segment['all_orders']['gross_sales'];
        } else {
            $orders_segment['all_orders']['aov'] = 0;
        }
        if ($items > 0) {
            $orders_segment['all_orders']['aoi'] = $items / $orders_segment['all_orders']['orders'];
        } else {
            $orders_segment['all_orders']['aoi'] = 0;
        }

        return $orders_segment;
    }

    /**
     * Get total no. of orders
     */
    /**
     * For getting orders listing rest call
     * @param $request - that will have request submitted to this method route.
     * @param $store_id - for specifying store
     */
    public function get_total_number_orders(Request $request, $store_id)
    {
        $payments_set = Payment::where('store_id', $store_id);
        if ($request->has([ 'startdate', 'enddate' ])) {
            if ($request->startdate != '' && $request->enddate != '') {
                // to check if items exists in this period if not then return all products
                $payments_set->where('post_date', '>=', $request->startdate)
                            ->where('post_date', '<=', $request->enddate);
            }
        }
        return $total_payments = $payments_set->count();
    }

    /**
     * For getting refund amount rest call
     * @param $request - that will have request submitted to this method route.
     * @param $store_id - for specifying store
     */
    public function get_refunded_amounts(Request $request, $store_id)
    {
        $payments_set = Payment::where('store_id', $store_id)
                        ->where('post_status', 'refunded');

        if ($request->has([ 'startdate', 'enddate' ])) {
            $payments_set->where('post_date', '>=', $request->startdate)
                        ->where('post_date', '<=', $request->enddate);
        }
        $refunded_amounts = 0;
        foreach ($payments_set->get() as $payment_set) {
            $payment_meta_set = PaymentMeta::where('store_id', $store_id)
                                ->where('post_id', $payment_set->ID)
                                ->where('meta_key', '_edd_payment_total')
                                ->get();
            foreach ($payment_meta_set as $payment_meta_value) {
                $refunded_amounts += $payment_meta_value->meta_value;
            }
        }
        return $refunded_amounts;
    }

    /**
     * [subscriptions_earnings description]
     * @param  Request $request  [description]
     * @param  [type]  $store_id [description]
     * @return [type]            [description]
     */
    public function subscriptions_earnings(Request $request, $store_id)
    {
        $subscriptions_report = $this->subscriptions_report($request, $store_id);
        return $subscriptions_report['earnings'];
    }

    /**
     * [subscriptions_refunded description]
     * @param  Request $request  [description]
     * @param  [type]  $store_id [description]
     * @return [type]            [description]
     */
    public function subscriptions_refunded(Request $request, $store_id)
    {
        $subscriptions_report = $this->subscriptions_report($request, $store_id);
        return $subscriptions_report['refunded'];
    }

    /**
     * [subscriptions_count description]
     * @param  Request $request  [description]
     * @param  [type]  $store_id [description]
     * @return [type]            [description]
     */
    public function subscriptions_count(Request $request, $store_id)
    {
        $subscriptions_report = $this->subscriptions_report($request, $store_id);
        return $subscriptions_report['count'];
    }

    /**
     * [subscriptions_refunded_count description]
     * @param  Request $request  [description]
     * @param  [type]  $store_id [description]
     * @return [type]            [description]
     */
    public function subscriptions_refunded_count(Request $request, $store_id)
    {
        $subscriptions_report = $this->subscriptions_report($request, $store_id);
        return $subscriptions_report['refunded_count'];
    }

    /**
     * [subscriptions_report description]
     * @param  Request $request  [description]
     * @param  [type]  $store_id [description]
     * @return [type]            [description]
     */
    public function subscriptions_report(Request $request, $store_id)
    {
        $subscriptions = Payment::where('store_id', $store_id)
        ->whereIn('post_status', [ 'edd_subscription', 'refunded' ]);
        if ($request->has([ 'startdate', 'enddate' ])) {
            $after  = $this->prepare_after($request->startdate);
            $before = $this->prepare_before($request->enddate);
            $subscriptions->where('post_date', '>=', $after)->where('post_date', '<=', $before);
        }

        $return                   = [];
        $return['earnings']       = 0;
        $return['refunded']       = 0;
        $return['count']          = 0;
        $return['refunded_count'] = 0;

        $subscriptions = $subscriptions->get();
        $debug         = [];
        if ($subscriptions) {
            foreach ($subscriptions as $renewal) {
                $subscription_id = PaymentMeta::where('store_id', $store_id)
                ->where('post_id', (string) $renewal->ID)
                ->where('meta_key', 'subscription_id')
                ->pluck('meta_value')
                ->first();

                if (! $subscription_id) {
                    continue;
                }

                $debug[] = $renewal;

                $payment_meta = unserialize(
                    PaymentMeta::where('store_id', $store_id)
                    ->where('post_id', (string) $renewal->ID)
                    ->where('meta_key', '_edd_payment_meta')
                    ->pluck('meta_value')
                    ->first()
                );

                $amount = PaymentMeta::where('store_id', $store_id)
                ->where('post_id', (string) $renewal->ID)
                ->where('meta_key', '_edd_payment_total')
                ->pluck('meta_value')
                ->first();

                if (empty($amount) && ( '0.00' != $amount )) {
                    if (isset($payment_meta['amount'])) {
                        $amount = $payment_meta['amount'];
                    }
                }

                switch ($renewal->post_status) {
                    case 'edd_subscription':
                        $tax = PaymentMeta::where('store_id', $store_id)
                        ->where('post_id', (string) $renewal->ID)
                        ->where('meta_key', '_edd_payment_tax')
                        ->pluck('meta_value')
                        ->first();

                        // We don't have tax as it's own meta and no meta was passed
                        if ('' === $tax) {
                            $tax = isset($payment_meta['tax']) ? $payment_meta['tax'] : 0;
                        }

                        $return['count']    += 1;
                        $return['earnings'] += ( $amount - $tax );

                        break;
                    case 'refunded':
                        $return['refunded_count'] += 1;
                        $return['refunded']       += $amount;

                        break;
                }
            }
        }

        return $return;
    }

    /**
     * [prepare_after description]
     * @param  [type] $date_string [description]
     * @return [type]              [description]
     */
    public function prepare_after($date_string)
    {
        $after = new Carbon($date_string);
        return $after->startOfDay()->toDateTimeString();
    }

    /**
     * [prepare_before description]
     * @param  [type] $date_string [description]
     * @return [type]              [description]
     */
    public function prepare_before($date_string)
    {
        $before = new Carbon($date_string);
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
    public function get_gravatar($email, $s = 80, $d = 'mp', $r = 'g', $img = false, $atts = array())
    {
        $url  = 'https://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$s&d=$d&r=$r";
        if ($img) {
            $url = '<img src="' . $url . '"';
            foreach ($atts as $key => $val) {
                $url .= ' ' . $key . '="' . $val . '"';
            }
            $url .= ' />';
        }
        return $url;
    }
}
