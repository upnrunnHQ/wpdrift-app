<template lang="html">
    <div class="card widget">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card__title">Registered users</h3>
            <span v-if="ready" class="d-block widget__stat">{{users.counts.total_users}}</span>
        </div>
        <div class="card-body">
            <chart-pie
                v-if="ready"
                :chart-data="datacollection"
                :options="chartOptions"></chart-pie>
        </div>
    </div>
</template>

<script>
import ChartPie from './../charts/ChartPie';
import { mapGetters } from 'vuex';

let backgroundColor = [
    "#55efc4",
    "#81ecec",
    "#74b9ff",
    "#dfe6e9",
    "#00b894",
];

export default {
    components: {
        ChartPie,
    },
    computed: {
        ...mapGetters([
            'getStat',
        ]),
        ready() {
            if ( ! this.users ) {
                return false;
            }

            return true;
        },
        users() {
            let type = 'users';
            let users = this.getStat(type);
            return users.response;
        },
        datacollection() {
            return {
                labels: ['Administrator', 'Customer', 'Shop Manager', 'Contributor', 'Subscriber'],
                datasets: [
                    {
                        backgroundColor,
                        data: [
                            this.users.counts.avail_roles.administrator,
                            this.users.counts.avail_roles.customer,
                            this.users.counts.avail_roles.shop_manager,
                            this.users.counts.avail_roles.contributor,
                            this.users.counts.avail_roles.subscriber
                        ],
                        borderWidth: [0, 0, 0, 0, 0]
                    }
                ],
            }
        },
        chartOptions() {
            return {
                responsive: true,
                maintainAspectRatio: true,
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        fontFamily: 'Avenir Next LT Pro',
                        fontColor: 'rgba(30, 48, 86, 0.75)'
                    }
                },
            }
        },
    },
}
</script>

<style lang="css">
</style>
