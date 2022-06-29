<template id="">
<tr class="product">
    <td>
        <div class="product__media" v-on:click="viewProduct(item.id, $event)">
            <img v-if="item.thumbnail_url" class="product__thumb rounded" v-bind:src="item.thumbnail_url" alt="">
            <div class="product__title">
                <a href="#">{{item.title}}</a>
                <span>#{{item.id}}</span>
            </div>
        </div>
    </td>
    <td>
        <span class="product__sold">{{item.net_sold}}</span>
    </td>
    <td>
        <span class="product__revenue">{{item.net_revenue}}</span>
    </td>
    <td>
        <span class="product__stock text-success">{{date}}</span>
    </td>
</tr>
</template>

<script type="text/javascript">
import 'moment-timezone';
export default {
    props: ['item'],
    computed: {
        date() {
            return moment.tz(this.item.create_date, Spark.timezone).fromNow();
        },
    },
    methods: {
        viewProduct(id, event) {
            if (event) event.preventDefault();
            this.$router.push({
                path: `/products/${id}`
            });
        },
    },
}
</script>
