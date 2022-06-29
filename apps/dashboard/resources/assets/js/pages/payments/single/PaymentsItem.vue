<template id="">
<div class="wrap">
    <div class="items__head">
        <a href="#" class="items__back" v-on:click="goBack">Go back</a>
    </div>
    <div class="products-item">
        <div class="row" v-if="item">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <span class="order__title">Payment #{{item.order_id}} details</span>
                        <span class="order__date">{{date}}</span>
                    </div>

                    <div class="list-group__head">
                        <div class="row">
                            <div class="col-6">
                                <span>Item</span>
                            </div>
                            <div class="col-2">
                                <span>Price & Qty</span>
                            </div>
                            <div class="col-2">
                                <span>Discount</span>
                            </div>
                            <div class="col-2 d-flex justify-content-end">
                                <span>Total</span>
                            </div>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li v-for="product in products" class="order-item list-group-item">
                            <div class="row">
                                <div class="order-item__title col-6 media">
                                    <div class="media-body">
                                        <span>
                                            <router-link :to="{ path: `/products/${product.id}` }">
                                                {{product.name}}
                                            </router-link>
                                        </span>
                                    </div>
                                </div>

                                <div class="order-item__volume col-2">
                                    <span class="order-item__value">${{product.item_price}}</span>
                                    <span class="order-item__times">x</span>
                                    <span class="order-item__quantity">{{product.quantity}}</span>
                                </div>

                                <div class="order-item__discount col-2">
                                    <span class="order-item__value">${{product.discount}}</span>
                                </div>

                                <div class="order-item__total col-2 d-flex justify-content-end">
                                    <span>${{product.subtotal}}</span>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-10">
                                <span class="order-total__lable">Total</span>
                            </div>
                            <div class="col-2 d-flex justify-content-end">
                                <span class="order-total__value">${{item.total}}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span>Payment Status</span>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <span>Order ID:</span>
                            <span>{{item.order_id}}</span>
                        </li>

                        <li class="list-group-item">
                            <span>Completed:</span>
                            <span>{{date}}</span>
                        </li>

                        <li class="list-group-item">
                            <span>Customer IP:</span>
                            <span>{{item.customer_ip}}</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-5" v-if="customer">
                <profile-card :item="customer" :location="item.customer_location"></profile-card>
            </div>
        </div>
    </div>
</div>
</template>

<script type="text/javascript">
import 'moment-timezone';
import {
    mapGetters
} from 'vuex';
import ProfileCard from './../../customers/single/ProfileCard';
export default {
    data() {
        return {
            item: null,
            products: null,
            customerId: null,
            customer: null,
            orderId: null
        };
    },

    components: {
        ProfileCard,
    },

    created: function() {
        this.orderId = this.$route.params.id;
        this.getOrder(this.orderId);
    },

    computed: {
        date() {
            return moment.tz(this.item.order_date, Spark.timezone).fromNow();
        }
    },

    methods: {
        getOrder(id) {
            axios.get('/edd/order/' + id).then(response => {
                this.item = response.data;
                this.products = response.data.customer_payment_meta.cart_details;
                this.getCustomerData(this.item.customer_id);
            });
        },
        getCustomerData(id) {
            axios.get('/edd/customer/' + id).then(response => {
                this.customer = response.data.customer;
            });
        },
        goBack() {
            window.history.length > 1 ?
                this.$router.go(-1) :
                this.$router.push('/')
        }
    }
}
</script>
