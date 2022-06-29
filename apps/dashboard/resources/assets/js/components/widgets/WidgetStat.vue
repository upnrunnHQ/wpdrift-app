<template lang="html">
    <div class="card stat-box">
        <div class="card-body d-flex d-flex flex-column justify-content-between">
            <div class="sta-box__top">
                <h4 class="stat-box__title">{{ label }}</h4>
                <p class="stat-box__lead">This month post increasing always from repetition inject humor or characteristic.</p>
            </div>
            <div class="stat-box__statistics" v-if="ready">
                <span class="stat-box__diff" v-bind:class="[classDiff]">
                    <span>{{ stat.response.progress.data.diff }}%</span>
                </span>
                <span v-if="ready" class="stat-box__counts">{{ stat.response.data.counts }}</span>
            </div>
            <div v-if="ready" class="stat-box__chart">
                <apexchart ref="realtimeChart" type=line height=35 width="200" :options="chartOptionsSparkLine" :series="dataArray" />
            </div>
        </div>
    </div>
</template>

<script>
import {
    mapGetters,
    mapActions
} from 'vuex';
import ChartLine from './../charts/ChartLine';
import Sparkline from './../charts/Sparkline';

export default {
    props: ['options'],
    components: {
        ChartLine,
        Sparkline,
        apexchart: VueApexCharts
    },
    mounted() {
        let type = this.options.stat;
        this.getStatistics({
            type,
        });
    },
    computed: {
        ...mapGetters([
            'getStat',
        ]),
        ready() {
            if (!this.stat.response) {
                return false;
            }

            return true;
        },
        stat() {
            let type = this.options.stat;
            return this.getStat(type);
        },
        label() {
            return this.options.label;
        },
        dataArray() {
            let data = [8,9];
            if(this.stat.response) {
                data = this.stat.response.data.data;
            }

            return [{data}];
        },
        datacollection() {
            let labels = this.stat.response.data.data;
            let data = this.stat.response.data.data;
            return {
                labels,
                datasets: [{
                    data,
                    borderColor: '#0984e3',
                    backgroundColor: 'rgba(116, 185, 255,0.05)',
                    borderWidth: 2,
                    fill: false,
                }],
            }
        },
        classDiff() {
            return (this.stat.response.progress.data.diff > 0) ? 'stat-box__up' : 'stat-box__down';
        },
        iconClass() {
            return this.options.icon;
        },
        chartOptions() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false,
                },
                elements: {
                    point: {
                        radius: 0,
                    },
                    // line: {
                    //     tension: 0,
                    // },
                },
                scales: {
                    xAxes: [{
                        gridLines: false,
                        ticks: {
                            display: false,
                        },
                    }],
                    yAxes: [{
                        gridLines: false,
                        ticks: {
                            display: false,
                        },
                    }],
                },
            };
        },
        chartOptionsSparkLine() {
            return {
                chart: {
                    height: 35,
                    sparkline: {
                        enabled: true
                    }
                },
                stroke: {
                    width: 3,
                    curve: 'smooth'
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        gradientToColors: ['#FDD835'],
                        shadeIntensity: 1,
                        type: 'horizontal',
                        opacityFrom: 1,
                        opacityTo: 1,
                        stops: [0, 100, 100, 100]
                    },
                },
                plotOptions: {
                    bar: {
                        columnWidth: '80%'
                    }
                },
                xaxis: {
                    crosshairs: {
                        width: 1
                    },
                },
                tooltip: {
                    fixed: {
                        enabled: false
                    },
                    x: {
                        show: false
                    },
                    y: {
                        title: {
                            formatter: function(seriesName) {
                                return ''
                            }
                        }
                    },
                    marker: {
                        show: false
                    }
                }
            };
        }
    },
    methods: {
        ...mapActions([
            'getStatistics',
        ]),
    },
};
</script>

<style lang="scss" scoped="">
.stats-small__value {
    line-height: 1;
    font-size: 2rem;
}
</style>
