<template lang="html">
    <div class="wrap">
        <div class="items__head">
            <a href="#" class="items__back" v-on:click="goBack">Go back</a>
        </div>
        <div class="row">
            <div class="col-md-5">
                <profile-card
                    :item="customer"
                    :location="location"
                    >
                </profile-card>
            </div>
            <div class="col-md-7">
                <div class="widget-orders card">
                    <div class="card-header">
                        Recent Payments
                    </div>
                    <a-table :columns="columns"
                        :rowKey="record => record.ID"
                        :dataSource="payments"
                        :loading="loading"
                        :pagination="false"
                    >
                        <template slot="id" slot-scope="id">
                            <a href="#" v-on:click="viewItem(id, $event)">#{{id}}</a>
                        </template>
                        <template slot="status" slot-scope="status">
                            <item-status :status="status"></item-status>
                        </template>
                    </a-table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Vue from 'vue';
import {
    Table
} from 'ant-design-vue';
import {
    mapGetters
} from 'vuex';
import ProfileCard from './ProfileCard';
import ItemStatus from './../../payments/ItemStatus';
Vue.use(Table);

const columns = [{
        title: 'ID',
        dataIndex: 'ID',
        scopedSlots: {
            customRender: 'id'
        },
    },
    {
        title: 'Total',
        dataIndex: 'total',
    },
    {
        title: 'Date',
        dataIndex: 'date',
    },
    {
        title: 'Status',
        dataIndex: 'status',
        scopedSlots: {
            customRender: 'status'
        },
    },
];

export default {
    data() {
        return {
            item: {},
            loading: false,
            columns
        };
    },

    components: {
        ProfileCard,
        ItemStatus
    },

    created: function() {
        const customer_id = this.$route.params.id;
        this.getCustomer(customer_id);
    },
    computed: {
        payments() {
            if (this.item.payments) {
                return this.item.payments;
            }
            return [];
        },
        customer() {
            if (this.item.customer) {
                return this.item.customer;
            }
            return null;
        },
        location() {
            if (this.item.customer_location) {
                return this.item.customer_location;
            }
            return null;
        }
    },
    methods: {
        goBack() {
            window.history.length > 1 ?
                this.$router.go(-1) :
                this.$router.push('/')
        },
        getCustomer(id) {
            this.loading = true;
            axios.get('/edd/customer/' + id).then(response => {
                this.item = response.data;
                this.loading = false;
            });
        },
        viewItem(id, event) {
            if (event) event.preventDefault();
            this.$router.push({
                path: `/orders/${id}`
            });
        }
    },
}
</script>

<style lang="css">
</style>
