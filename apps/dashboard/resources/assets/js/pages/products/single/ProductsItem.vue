<template id="">
<div class="wrap">
    <div class="items__head">
        <a href="#" class="items__back" v-on:click="goBack">Go back</a>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="widget product-summary card">
                <div class="card-body" v-if="item">
                    <div class="widget product-summary__media media">
                        <img class="product-summary__thumb rounded" :src="item.thumbnail_url" alt="">
                        <div class="product-summary__body media-body">
                            <h3 class="card-title">{{item.title}}</h3>
                            <span class="badge badge-light">{{item.edd_price}}</span>
                        </div>
                    </div>

                    <div class="product-summary__actions">
                        <a target="_blank" v-bind:href="item.edit_link" class="btn btn-sm btn-outline-primary">
                            <i class="far fa-edit"></i>
                            <span>Edit</span>
                        </a>
                        <a target="_blank" v-bind:href="item.view_link" class="btn btn-sm btn-outline-primary">
                            <i class="far fa-eye"></i>
                            <span>View</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <span>Download Stats</span>
                </div>
                <div class="card-body" v-if="item">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="statistic">
                                <div class="statistic__title">
                                    Sales
                                </div>
                                <div class="statistic__content">
                                    <div class="statistic__content-prefix">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <div class="statistic__content-value">
                                        {{item.net_sold}}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="statistic">
                                <div class="statistic__title">
                                    Earnings
                                </div>
                                <div class="statistic__content">
                                    <div class="statistic__content-prefix">
                                        <i class="fas fa-chart-area"></i>
                                    </div>
                                    <div class="statistic__content-value">
                                        {{item.net_revenue}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <payments></payments>
        </div>
    </div>
</div>
</template>

<script type="text/javascript">
import {
    mapGetters
} from 'vuex';
import OrdersItem from './OrdersItem';
import Payments from './Payments';
import ChartSales from './../../../components/charts/ChartSales';
export default {
    data() {
        return {
            item: null,
            sales: null,
        };
    },

    components: {
        OrdersItem,
        ChartSales,
        Payments
    },

    mounted: function() {
        const productId = this.$route.params.id;
        this.getProduct(productId);
        this.getSales(productId);
    },

    methods: {
        getProduct(productId) {
            axios.get('/edd/product/' + productId).then(response => {
                this.item = response.data;
            });
        },
        getSales(productId) {
            axios.get('/edd/logs', {
                params: {
                    download: productId
                }
            }).then(response => {
                this.sales = response.data;
            });
        },
        goBack() {
          window.history.length > 1
            ? this.$router.go(-1)
            : this.$router.push('/')
        }
    },
}
</script>
