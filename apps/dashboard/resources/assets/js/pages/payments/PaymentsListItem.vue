<template lang="html">
    <tr class="order">
        <td>
            <div class="media" v-on:click="viewItem(item.order_id, $event)">
                <item-status :status="item.status"></item-status>
                <div class="media-body">
                    <span class="order__id">#{{item.order_id}}</span>
                    <span class="order__date">{{date}}</span>
                </div>
            </div>
        </td>
        <td>
            <div class="customer-bio media" v-on:click="viewCustomer(item.customer.id, $event)">
                <img class="customer-bio__avatar" v-bind:src="item.customer.avatar" alt="">
                <div class="media-body">
                    <span class="customer-bio__name">{{item.customer.title}}</span>
                    <span class="customer-bio__email">{{item.customer.email}}</span>
                </div>
            </div>
        </td>
        <td>
            <span class="order__location" v-if="displayLocation">
                <span v-bind:class="['flag-icon', 'flag-icon-' + item.customer.location.code]"></span>
                <span class="customer-bio__location">{{item.customer.location.address}}</span>
            </span>
        </td>
        <td>
            <span class="order__items">{{item.items}}</span>
        </td>
        <td>
            <span class="order__total">{{item.total}}</span>
        </td>
    </tr>
</template>

<script>
import 'moment-timezone';
import ItemStatus from './ItemStatus';
import OrdersItem from './single/PaymentsItem';
export default {
    props: ['item'],
    components: {
        ItemStatus,
        OrdersItem
    },
    computed: {
        date() {
            return moment.tz(this.item.order_date, Spark.timezone).fromNow();
        },
        displayLocation() {
            if (typeof this.item.customer.location === 'undefined') {
                return false;
            }

            return true;
        }
    },
    methods: {
        viewItem(id, event) {
            if (event) event.preventDefault();
            this.$router.push({
                path: `/orders/${id}`
            });
        },
        viewCustomer(id, event) {
            if (event) event.preventDefault();
            this.$router.push({
                path: `/customers/${id}`
            });
        }
    },
}
</script>

<style lang="css">
</style>
