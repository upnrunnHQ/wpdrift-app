<template lang="html">
    <doughnut-chart :chart-data="datacollection" :options="options"></doughnut-chart>
</template>

<script>
import DoughnutChart from './Doughnut';
import { mapGetters } from 'vuex';

let backgroundColor = [
    "#55efc4",
    "#81ecec",
    "#74b9ff",
    "#dfe6e9",
    "#00b894",
    "#00b894",
    "#ffeaa7",
    "#fab1a0",
    "#fd79a8",
    "#636e72"
];

export default {
    components: {
        DoughnutChart,
    },
    computed: {
        ...mapGetters([
            'browsers',
        ]),
        datacollection() {
            return {
                labels: this.browsers.labels,
                datasets: [
                    {
                        backgroundColor,
                        data: this.browsers.counts,
                        borderWidth: 0
                    }
                ],
            }
        },
        options() {
            return {
                responsive: true,
                maintainAspectRatio: true,
                legend: {
                    position: 'bottom',
                },
            }
        },
    },
};
</script>

<style lang="css">
</style>
