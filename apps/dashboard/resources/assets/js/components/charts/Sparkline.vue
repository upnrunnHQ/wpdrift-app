<template lang="html">
    <div class="">
        {{dataArray}}
        <apexchart ref="realtimeChart" type=line height=35 width="200" :options="chartOptionsSparkLine" :series="dataArray" />
    </div>
</template>

<script>
export default {
    props: {
        dataArray: {
            type: Array,
            default: () => [{
                data: [9,6]
            }]
        }
    },
    data() {
        return {
            chartOptionsSparkLine: {
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
            }
        }
    },
    components: {
        apexchart: VueApexCharts
    },
    watch: {
        dataArray: function() {
            this.$refs.realtimeChart.updateSeries(this.dataArray);
            alert('gg');
        }
    }
}
</script>

<style lang="css">
</style>
