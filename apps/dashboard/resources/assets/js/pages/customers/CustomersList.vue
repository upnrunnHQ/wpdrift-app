<template lang="html">
    <div class="wrap">
        <items-list-table
            items-title="Customers"
            items-store-id="customers"
            items-route="/edd/customers"
            :thead="thead"
            :orderby="orderby">
            <customers-list-item
                v-for="item in itemsList"
                v-bind:item="item"
                v-bind:key="item.id">
            </customers-list-item>
        </items-list-table>
    </div>
</template>

<script>
import {
    mapGetters
} from 'vuex';
import ItemsListTable from './../../components/ItemsListTable';
import CustomersListItem from './CustomersListItem';
export default {
    data() {
        return {
            thead: ['Name', 'Location', 'Purchases', 'Total Spent', 'Date Created'],
            orderby: [{
                    value: 'id',
                    label: 'ID',
                },
                {
                    value: 'email',
                    label: 'Email',
                },
                {
                    value: 'name',
                    label: 'Name',
                },
                {
                    value: 'date_created',
                    label: 'Date',
                },
            ],
        };
    },
    components: {
        ItemsListTable,
        CustomersListItem,
    },
    computed: {
        ...mapGetters([
            'getItems',
        ]),
        items() {
            return this.getItems('customers');
        },
        itemsList() {
            return this.items.data;
        },
    },
}
</script>

<style lang="css">
</style>
