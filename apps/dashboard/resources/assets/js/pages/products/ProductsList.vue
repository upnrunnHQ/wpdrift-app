<template lang="html">
    <div class="wrap">
        <items-list-table
            items-title="Products"
            items-store-id="products"
            items-route="/edd/products"
            :thead="thead"
            :orderby="orderby">
            <products-list-item
                v-for="item in itemsList"
                v-bind:item="item"
                v-bind:key="item.id">
            </products-list-item>
        </items-list-table>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import ItemsListTable from './../../components/ItemsListTable';
import ProductsListItem from './ProductsListItem';
export default {
    data() {
        return {
            thead: ['Product', 'Net Sold', 'Net Revenue', 'Date'],
            orderby: [
                {
                    value: 'post_id',
                    label: 'ID',
                },
                {
                    value: 'post_name',
                    label: 'Title',
                },
                {
                    value: 'post_date',
                    label: 'Date',
                }
            ],
        };
    },
    components: {
        ItemsListTable,
        ProductsListItem,
    },
    computed: {
        ...mapGetters([
            'getItems',
        ]),
        items() {
            return this.getItems('products');
        },
        itemsList() {
            return this.items.data;
        },
    },
}
</script>

<style lang="css">
</style>
