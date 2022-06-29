<template lang="html">
    <div class="card card-users">
        <div class="card__top">
            <h4 class="card__title">Users</h4>
        </div>
        <div class="card-body">
            <div class="loader" v-if="busy"></div>
            <div class="loader-overlay" v-if="busy"></div>
            <div v-if="ready" class="card__tools d-flex justify-content-between">
                <div>
                    <div class="flatPickr-wrap">
                        <flat-pickr v-model="date" :config="config" @on-change="listenToOnChangeEvent"></flat-pickr>
                    </div>
                    <button type="button" class="btn btn-outline-warning" v-on:click="resetDates()">
                        <span class="icomoon-Reset"></span>
                        <span>Reset</span>
                    </button>
                </div>
                <div class="">
                    <div class="btn-group">
                        <button
                            v-for="(filter, mode) in users.data.filters"
                            v-on:click="updateMode({ mode })"
                            v-bind:disabled="!filter.active"
                            type="button"
                            class="btn btn-default">
                            {{ filter.label }}
                        </button>
                    </div>
                </div>
            </div>
            <chart-line
                v-if="ready"
                :chart-data="datacollection"
                :options="options"
                :height="300"></chart-line>
        </div>
    </div>
</template>

<script>
import Vue from 'vue';
import flatPickr from './../flatpickr';
import ChartLine from './../charts/ChartLine';
import {
    mapGetters,
    mapActions
} from 'vuex';
import VueRangedatePicker from 'vue-rangedate-picker';
Vue.use(VueRangedatePicker);

export default {
    data() {
        return {
            date: [],
            config: {
                mode: 'range',
                altInput: true,
                altFormat: "M j, Y"
            }
        };
    },
    components: {
        ChartLine,
        VueRangedatePicker,
        flatPickr,
        apexchart: VueApexCharts
    },
    mounted() {
        this.date = [
            new Date(this.selectedDate.start),
            new Date(this.selectedDate.end)
        ];

        let unReady = this.users.unReady;
        if (unReady) {
            this.fetchData();
        }
    },
    computed: {
        ...mapGetters([
            'users',
            'selectedDate'
        ]),
        busy() {
            return this.users.busy;
        },
        ready() {
            return !this.users.unReady;
        },
        unReady() {
            return this.users.unReady;
        },
        series() {
            let data = [];
            if (this.users.data) {
                data = this.users.data.data;
            }

            return [{
                data
            }];
        },
        chartOptions() {
            let labels = [];
            if(this.users.data) {
                labels = this.users.data.labels;
            }

            let data = [];
            if (this.users.data) {
                data = this.users.data.data;
            }

            return {
                chart: {
                    zoom: {
                        enabled: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 3,
                    curve: 'smooth'
                },
                series: [{
                    data
                }],
                labels,
                xaxis: {
                    type: 'numeric',
                },
                yaxis: {
                    opposite: true
                }
            }
        },
        datacollection() {
            let data = this.users.data.data;
            let labels = this.users.data.labels;

            return {
                labels,
                datasets: [{
                    data,
                    label: 'Registered users',
                    pointBorderColor: '#2a81fb',
                    borderColor: '#2a81fb',
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#fff'
                }]
            };
        },
        options() {
            return {
                maintainAspectRatio: false,
                legend: {
                    display: false,
                    labels: {
                        fontColor: 'rgba(30, 48, 86, 0.5)',
                        fontFamily: 'Avenir Next LT Pro',
                        fontSize: 50,
                    }
                },
                tooltips: {
                    backgroundColor: '#fff',
                    borderColor: '#e1eaee',
                    borderWidth: 1,
                    titleFontColor: '#000',
                    yPadding: 16,
                    xPadding: 16,
                    bodyFontColor: 'rgba(30, 48, 86, 0.5)',
                    displayColors: false
                }
            }
        }
    },
    methods: {
        ...mapActions([
            'getUsers',
            'updateDateRange',
            'setUsersMode',
        ]),
        fetchData: function() {
            let mode = this.users.mode;
            let start = this.date[0];
            let end = this.date[1];
            this.getUsers({
                mode,
                start,
                end
            });
        },
        updateMode: function(payload) {
            this.setUsersMode(payload);
            this.fetchData();
        },
        listenToOnChangeEvent(selectedDates, dateStr, instance) {
            if (selectedDates.length == 2) {
                this.fetchData();
            }
        },
        resetDates() {
            this.date = [
                new Date(this.selectedDate.start),
                new Date(this.selectedDate.end)
            ];
            this.fetchData();
        }
    },
    watch: {
        selectedDate: function(selectedDate, oldDate) {
            this.resetDates();
        }
    },
}
</script>

<style lang="css">
</style>
