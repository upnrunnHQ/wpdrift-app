<template lang="html">
    <div class="flatpickr-wrap">
        <flat-pickr
            v-model="date"
            :config="config"
            @on-change="listenToOnChangeEvent"
            @on-close="listenToOnCloseEvent">
        </flat-pickr>
        <div class="dropdown">
            <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="dropdown-toggle-icon"></span>
                <span class="dropdown-toggle-icon"></span>
                <span class="dropdown-toggle-icon"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                <a v-for="item in presets" class="dropdown-item" href="#" v-on:click="updateDate(item)">
                    <span class="label">{{ item.label }}</span>
                    <span class="date">{{ formatDate(item.start) }}</span>
                    <span class="sep">-</span>
                    <span class="date">{{ formatDate(item.end) }}</span>
                </a>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';
import flatPickr from './flatpickr';
import 'flatpickr/dist/flatpickr.css';
export default {
    data() {
        const n = new Date();

        return {
            date: [],
            config: {
                mode: 'range',
                altInput: true,
                altFormat: "M j, Y"
            },
            presets: {
                last30days: {
                    label: 'Last 30 days',
                    start: new Date(n.getFullYear(), n.getMonth(), n.getDate() - 29),
                    end: new Date(n.getFullYear(), n.getMonth(), n.getDate())
                },
                thisMonth: {
                    label: 'This month',
                    start: new Date(n.getFullYear(), n.getMonth(), 1),
                    end: new Date(n.getFullYear(), n.getMonth() + 1, 0)
                },
                lastMonth: {
                    label: 'Last month',
                    start: new Date(n.getFullYear(), n.getMonth() - 1, 1),
                    end: new Date(n.getFullYear(), n.getMonth(), 0)
                },
                last3Months: {
                    label: 'Last 3 months',
                    start: new Date(n.getFullYear(), n.getMonth() - 3, 1),
                    end: new Date(n.getFullYear(), n.getMonth(), 0)
                },
                last6Months: {
                    label: 'Last 6 months',
                    start: new Date(n.getFullYear(), n.getMonth() - 6, 1),
                    end: new Date(n.getFullYear(), n.getMonth(), 0)
                },
                lastYear: {
                    label: 'Last year',
                    start: new Date(n.getFullYear() - 1, 0, 1),
                    end: new Date(n.getFullYear(), 0, 0)
                },
                thisYear: {
                    label: 'This year',
                    start: new Date(n.getFullYear(), 0, 1),
                    end: new Date(n.getFullYear(), n.getMonth(), n.getDate())
                },
                all: {
                    label: 'All time',
                    start: new Date(n.getFullYear() - 3, 1, 1),
                    end: new Date(n.getFullYear(), n.getMonth(), n.getDate())
                }
            }
        };
    },
    components: {
        flatPickr
    },
    mounted() {
        this.date = [
            new Date(this.selectedDate.start),
            new Date(this.selectedDate.end)
        ];
    },
    computed: {
        ...mapGetters([
            'selectedDate'
        ])
    },
    methods: {
        ...mapActions([
            'setState',
            'updateDateRange'
        ]),
        listenToOnChangeEvent(selectedDates, dateStr, instance) {
            if ( selectedDates.length == 2 ) {
                this.updateDateRange({
                    start: selectedDates[0],
                    end: selectedDates[1]
                });
            }
        },
        listenToOnCloseEvent(selectedDates, dateStr, instance) {
            if ( selectedDates.length < 2 ) {
                this.updateDateRange({
                    start: selectedDates[0],
                    end: new Date(this.selectedDate.end)
                });

                this.date = [
                    selectedDates[0],
                    new Date(this.selectedDate.end)
                ];
            }

            if ('/products' == this.$router.currentRoute.fullPath) {
                this.setState({
                    parent: 'products',
                    child: 'dateChanged',
                    value: true,
                });
            }
        },
        formatDate(date) {
            return moment(date).format("MMM D, YY");
        },
        updateDate(date) {
            this.updateDateRange({
                start: date.start,
                end: date.end
            });

            this.date = [
                new Date(date.start),
                new Date(date.end)
            ];
        }
    }
}
</script>

<style lang="css">
</style>
