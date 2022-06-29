<template lang="html">
    <div class="wrap">
        <items-list-table
            items-title="Orders"
            items-store-id="orders"
            items-route="/edd/orders"
            :thead="thead"
            :orderby="orderby">
            <payments-list-item
                v-for="item in itemsList"
                v-bind:item="item"
                v-bind:key="item.order_id">
            </payments-list-item>
        </items-list-table>
    </div>
</template>

<script>
import {
    mapGetters
} from 'vuex';
import ItemsListTable from './../../components/ItemsListTable';
import PaymentsListItem from './PaymentsListItem';
export default {
    data() {
        return {
            thead: ['ID', 'Customer Details', 'Location', 'Items', 'Total'],
            orderby: [{
                    value: 'order_id',
                    label: 'ID',
                },
                {
                    value: 'customer',
                    label: 'Customer',
                },
                {
                    value: 'customer_id',
                    label: 'Customer ID',
                },
                {
                    value: 'order_date',
                    label: 'Date',
                },
                {
                    value: 'status',
                    label: 'Status',
                },
            ],
        };
    },
    components: {
        ItemsListTable,
        PaymentsListItem,
    },
    computed: {
        ...mapGetters([
            'getItems',
        ]),
        items() {
            console.log(this.getItems('orders'));
            return this.getItems('orders');
        },
        itemsList() {
            return this.items.data;
        }
    },
}
</script>

<style lang="css">
</style>
