<template lang="html">
    <div class="card">
        <div class="card-header">
            Payment History
        </div>
        <a-table :columns="columns"
            :rowKey="record => record.ID"
            :dataSource="data"
            :pagination="pagination"
            :loading="loading"
            @change="handleTableChange"
        >
            <template slot="id" slot-scope="id">
                <a href="#" v-on:click="viewItem(id, $event)">#{{id}}</a>
            </template>
            <template slot="customer" slot-scope="customer">
                <div class="customer-bio media" v-on:click="viewCustomer(customer.id, $event)">
                    <img class="customer-bio__avatar" :src="customer.avatar" alt="">
                    <div class="media-body">
                        <span class="customer-bio__name">{{customer.title}}</span>
                        <span class="customer-bio__location">{{customer.email}}</span>
                    </div>
                </div>
            </template>
        </a-table>
    </div>
</template>

<script>
import Vue from 'vue';
import { Table } from 'ant-design-vue';
Vue.use(Table);

const columns = [{
        title: 'ID',
        dataIndex: 'ID',
        scopedSlots: { customRender: 'id' },
        sorter: true,
    },
    {
        title: 'Customer',
        dataIndex: 'customer',
        scopedSlots: { customRender: 'customer' },
    },
    {
        title: 'Total',
        dataIndex: 'total',
    },
    {
        title: 'Date',
        dataIndex: 'date',
        sorter: true,
    }
];

export default {
    mounted() {
        this.fetch();
    },
    data() {
        return {
            data: [],
            pagination: {
                showSizeChanger: true,
                showQuickJumper: true,
            },
            loading: {
                spinning: false,
            },
            columns,
        }
    },
    methods: {
        handleTableChange(pagination, filters, sorter) {
            const pager = {
                ...this.pagination
            };
            pager.current = pagination.current;
            this.pagination = pager;
            this.fetch({
                results: pagination.pageSize,
                page: pagination.current,
                sortField: sorter.field,
                sortOrder: sorter.order,
                ...filters,
            });
        },
        fetch(params = {}) {
            this.loading.spinning = true

            axios.get('/edd/payments', {
                params: {
                    results: 10,
                    download: this.$route.params.id,
                    ...params,
                }
            }).then(response => {
                const pagination = {
                    ...this.pagination
                };
                // Read total count from server
                // pagination.total = data.totalCount;
                pagination.total = response.data.total;
                this.loading.spinning = false;
                this.data = response.data.results;
                this.pagination = pagination;
            });
        },
        viewItem(id, event) {
            if (event) event.preventDefault();
            this.$router.push({ path: `/orders/${id}` });
        },
        viewCustomer(id, event) {
            if (event) event.preventDefault();
            this.$router.push({ path: `/customers/${id}` });
        },
    },
}
</script>

<style lang="css" scoped>
</style>
