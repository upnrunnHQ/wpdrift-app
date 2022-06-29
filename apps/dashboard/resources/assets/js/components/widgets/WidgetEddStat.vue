<template lang="html">
    <div class="stat-box rounded mb-4 p-4 bg-white shadow-sm" v-bind:class="boxClasses">
        <h4 class="stat-box__title">{{ label }}</h4>
        <div v-if="stat.isLoading" class="spinner-grow spinner-grow-sm" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <div v-else class="stat-box__inner">
            <div class="d-flex justify-content-between align-items-center">
                <h2 v-html="itemTotal"></h2>
                <span v-bind:class="trendClass" v-if="displayTrend" class="stat-box__icon ml-auto">
                    <svg v-if="isUpTrend" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L7.5 2.707V14.5a.5.5 0 0 0 .5.5z"/>
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-down" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 1a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L7.5 13.293V1.5A.5.5 0 0 1 8 1z"/>
                    </svg>
                </span>
                <span v-bind:class="trendClass" v-if="displayTrend" class="stat-box__item-percentage ml-2">{{stat.item_percentage}}%</span>
            </div>
            <apexchart width="100%" height="200" type="line" :options="options" :series="series"></apexchart>
        </div>
    </div>
</template>

<script>
import {
    mapGetters,
    mapActions
} from "vuex";

const lookup = [{
        value: 1,
        symbol: ""
    },
    {
        value: 1e3,
        symbol: "k"
    },
    {
        value: 1e6,
        symbol: "M"
    },
    {
        value: 1e9,
        symbol: "G"
    },
    {
        value: 1e12,
        symbol: "T"
    },
    {
        value: 1e15,
        symbol: "P"
    },
    {
        value: 1e18,
        symbol: "E"
    }
];

export default {
    props: ["label", "itemRoute", "itemKey"],
    computed: {
        ...mapGetters(["getEddData"]),
        stat() {
            return this.getEddData(this.itemKey);
        },
        displayTrend() {
            return 0 === this.stat.item_percentage ? false : true;
        },
        isUpTrend() {
            return this.stat.item_percentage >= 1 ? true : false;
        },
        trendClass() {
            return this.stat.item_percentage >= 0 ? "up" : "down";
        },
        boxClasses() {
            return {
                "stat-box--refund": this.itemKey == "totalRefunds",
            };
        },
        itemTotal() {
            let total = this.stat.item_value.total;
            let prevTotal = this.stat.item_prev_value;
            if ("net_revenue" === this.itemKey) {
                total = `${Spark.settings.edd_currency_symbol}${this.stat.item_value.total}`;
                prevTotal = `${Spark.settings.edd_currency_symbol}${this.stat.item_prev_value}`
            }

            return `${total} <small>from ${prevTotal}</small>`;
        },
        options() {
            const itemKey = this.itemKey;

            return {
                chart: {
                    id: 'vuechart-example',
                    toolbar: {
                        show: false
                    },
                },
                yaxis: {
                    labels: {
                        formatter: function(value) {
                            const rx = /\.0+$|(\.[0-9]*[1-9])0+$/;
                            const item = lookup.slice().reverse().find(function(item) {
                                return value >= item.value;
                            });

                            return item ? (value / item.value).toFixed(1).replace(rx, "$1") + item.symbol : "0";
                        }
                    },
                },
                xaxis: {
                    type: 'datetime',
                    categories: this.stat.item_value.data.categories
                },
                stroke: {
                    width: 3,
                },
                grid: {
                    show: false
                }
            }
        },
        series() {
            return this.stat.item_value.data.series
        },
    },
    methods: {
        getRandomInt() {
            return Math.floor(Math.random() * (50 - 5 + 1)) + 5;
        }
    }
};
</script>

<style lang="scss" scoped="">
.stats-small__value {
    line-height: 1;
    font-size: 2rem;
}
</style>
