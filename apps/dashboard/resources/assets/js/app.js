/*
 |--------------------------------------------------------------------------
 | Laravel Spark Bootstrap
 |--------------------------------------------------------------------------
 |
 | First, we will load all of the "core" dependencies for Spark which are
 | libraries such as Vue and jQuery. This also loads the Spark helpers
 | for things such as HTTP calls, forms, and form validation errors.
 |
 | Next, we'll create the root Vue application for Spark. This will start
 | the entire application and attach it to the DOM. Of course, you may
 | customize this script as you desire and load your own components.
 |
 */

import Vue from "vue";
import Toasted from "vue-toasted";
import {Tooltip} from "ant-design-vue";
import VueApexCharts from "vue-apexcharts";
import router from "./router";
import store from "./store";
require("spark-bootstrap");
require("./components/bootstrap");

// import Table from 'ant-design-vue/lib/table';
// Vue.component(Table.name, Table);
// Vue.component(Table.Column.name, Table.Column);
// Vue.component(Table.ColumnGroup.name, Table.ColumnGroup);

import "ant-design-vue/dist/antd.css";
Vue.use(Toasted);
Vue.use(Tooltip);
Vue.use(VueApexCharts);
Vue.component("apexchart", VueApexCharts);

new Vue({
    mixins: [require("spark")],
    router,
    store
});

$(document).ready(function() {
    $(".notification-pill + .dropdown-menu").click(function(e) {
        e.stopPropagation();
    });
});
