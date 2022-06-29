<template lang="html">
    <line-chart :chart-data="datacollection" :options="options" :height="200"></line-chart>
</template>

<script>
import LineChart from './Line';

const backgroundColors = [
    "#55efc4",
    "#81ecec",
];

export default {
    components: {
        LineChart,
    },
    computed: {
        datacollection() {
            return {
                labels: ['Label 1', 'Label 2'],
                datasets: [
                    {
                        data: [10, 15],
                        backgroundColor: backgroundColors,
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
