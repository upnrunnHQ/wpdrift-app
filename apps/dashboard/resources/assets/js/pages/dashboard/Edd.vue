<template lang="html">
    <div class="">
        <div class="page-head">
            <div class="d-flex justify-content-between">
                <ul class="nav align-items-center">
                    <li class="nav-item">
                        <span>Dashboard:</span>
                    </li>
                    <li class="nav-item">
                        <router-link to="/" exact>
                            <div class="icon dripicons-home"></div>
                            <span>Blog</span>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/store">
                            <div class="icon dripicons-cart"></div>
                            <span>Store</span>
                        </router-link>
                    </li>
                </ul>
                <vue-rangedate-picker
                    ref="rangeDatePicker"
                    @selected="updateDateRange"
                    i18n="EN"
                    righttoleft="true">
                </vue-rangedate-picker>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <widget-edd-stat label="Monthly Recurring Revenue"></widget-edd-stat>
                    </div>
                    <div class="col-md-6">
                        <widget-edd-stat label="Net Revenue"></widget-edd-stat>
                    </div>
                    <div class="col-md-6">
                        <widget-edd-stat label="Net Revenue"></widget-edd-stat>
                    </div>
                    <div class="col-md-6">
                        <widget-edd-stat label="Annual Run Rate"></widget-edd-stat>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <widget-events></widget-events>
            </div>
        </div>
    </div>
</template>

<script>
import Vue from 'vue';
import { mapGetters, mapActions } from 'vuex';
import VueRangedatePicker from 'vue-rangedate-picker';
import WidgetEvents from './../../components/widgets/WidgetEvents';
import WidgetEddStat from './../../components/widgets/WidgetEddStat';
Vue.use(VueRangedatePicker);
export default {
    components: {
        VueRangedatePicker,
        WidgetEvents,
        WidgetEddStat
    },
    mounted() {
        this.$refs.rangeDatePicker.dateRange = {
            start: new Date(this.selectedDate.start),
            end: new Date(this.selectedDate.end)
        }
    },
    computed: {
        ...mapGetters([
          'selectedDate',
      ]),
  },
  methods: {
      ...mapActions([
        'updateDateRange'
      ]),
  },
  updated: function () {
      this.$nextTick(function () {
          $('[data-toggle="tooltip"]').tooltip();
      });
  },
}
</script>

<style lang="css">
</style>
