<template id="">
<tr>
    <td>
        <div class="customer-details">
            <router-link :to="{ path: `/customers/${item.id}` }">
                <img class="rounded-circle mr-3" v-bind:src="item.customer_avatar" alt="Customer Avatar">
            </router-link>
            <div class="details">
                <span class="full-name">
                    <router-link :to="{ path: `/customers/${item.id}` }">{{item.name}}</router-link>
                </span>
                <span class="id">#{{item.id}}</span>
                <span class="email">{{item.email}}</span>
            </div>
        </div>
    </td>
    <td>
        <div>
            <span v-bind:class="['flag-icon', 'flag-icon-' + item.customer_location.code]"></span>
            <span>{{item.customer_location.address}}</span>
        </div>
    </td>
    <td>
        <span>{{item.purchase_count}}</span>
    </td>
    <td>
        <span>{{item.purchase_value}}</span>
    </td>
    <td>
        <span>{{date}}</span>
    </td>
</tr>
</template>

<script type="text/javascript">
import 'moment-timezone';
export default {
    props: ['item'],
    computed: {
        date() {
            return moment.tz(this.item.date_created, Spark.timezone).fromNow();
        },
    },
    methods: {
        viewCustomer(id, event) {
            if (event) event.preventDefault();
            this.$router.push({
                path: `/customers/${id}`
            });
        },
    },
}
</script>
