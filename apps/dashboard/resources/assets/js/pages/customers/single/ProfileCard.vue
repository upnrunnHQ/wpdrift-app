<template lang="html">
    <div class="card card--customer-info">
        <div class="card-body">
            <google-map
                :lat="location.lat"
                :lng="location.lng"
                class="rounded mb-4"
                v-if="location"
            ></google-map>
            <div class="media mb-4" v-on:click="viewCustomer(item.id, $event)">
                <img class="rounded-circle mr-3" v-bind:src="item.gravatar" alt="Customer Avatar">
                <div class="media-body">
                    <h5 class="mb-1">
                        <a href="#" v-on:click="viewCustomer(item.id, $event)">{{item.name}}</a>
                    </h5>
                    <span class="card-subtitle" v-if="location">{{location.city}}, {{location.country}}</span>
                </div>
            </div>

            <div class="about">
                <ul class="user-bio">
                    <li class="date">{{date}}</li>
                    <li class="email">{{item.email}}</li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
import 'moment-timezone';
export default {
    props: ['item', 'location'],
    computed: {
        date() {
            return moment.tz(this.item.date_created, Spark.timezone).fromNow();
        }
    },
    methods: {
        viewCustomer(id, event) {
            if (event) event.preventDefault();
            this.$router.push({ path: `/customers/${id}` });
        }
    }
}
</script>

<style lang="css">
</style>
