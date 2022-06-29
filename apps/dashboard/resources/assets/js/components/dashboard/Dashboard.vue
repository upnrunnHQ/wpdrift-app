<template>
<div>
    <div v-if="storeSetup == 'Y'">
        <div class="" v-if="pluginVersion !== '' && userDefaultStore !== ''">
            <div class="page-head">
                <div class="d-flex justify-content-between">
                    <ul class="nav align-items-center">
                        <li class="nav-item">
                            <span>Dashboard</span>
                        </li>
                    </ul>
                    <date-presets></date-presets>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6">
                            <widget-edd-stat label="Net Revenue" item-key="net_revenue">
                            </widget-edd-stat>
                        </div>
                        <div class="col-md-6">
                            <widget-edd-stat label="Total Items Sold" item-key="total_items_sold">
                            </widget-edd-stat>
                        </div>
                        <div class="col-md-6">
                            <widget-edd-stat label="Total Refunds" item-key="gross_refunds">
                            </widget-edd-stat>
                        </div>
                        <div class="col-md-6">
                            <widget-edd-stat label="Total Number of Refunds" item-key="total_number_refunds">
                            </widget-edd-stat>
                        </div>
                        <div class="col-md-6">
                            <widget-edd-stat label="Subscriptions Gross Earnings" item-key="subscriptions_earnings">
                            </widget-edd-stat>
                        </div>
                        <div class="col-md-6">
                            <widget-edd-stat label="Subscriptions Refunded Earnings" item-key="subscriptions_refunded">
                            </widget-edd-stat>
                        </div>
                        <div class="col-md-6">
                            <widget-edd-stat label="Total Subscriptions Renewals" item-key="subscriptions_renewals">
                            </widget-edd-stat>
                        </div>

                        <div class="col-md-6">
                            <widget-edd-stat label="Total Subscriptions Renewals Refunded" item-key="subscriptions_renewals_refunded">
                            </widget-edd-stat>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <widget-events></widget-events>
                </div>
            </div>
        </div>
        <div class="plugin-not-installed-section" v-if="pluginVersion == '' && userDefaultStore !== ''">
            <div class="text-black text-5xl text-15xl font-black">Error :(</div>
            <div class="w-16 h-1 bg-purple-light my-3 my-6"></div>
            <p class="
            text-grey-darker text-2xl text-3xl
            font-light
            mb-8
            leading-normal
          ">
                Plugin is not installed at your WP site.
            </p>
            <a :href="defaultStoreURL">
                <button class="
              bg-transparent
              text-grey-darkest
              font-bold
              uppercase
              tracking-wide
              py-3
              px-6
              border-2 border-grey-light
              hover:border-grey
              rounded-lg
            ">
                    Go to Site Setting Page
                </button>
            </a>
        </div>
        <div v-if="userDefaultStore == ''" class="set-default-site-err-section">
            <div class="text-black text-5xl text-15xl font-black">Error :(</div>
            <div class="w-16 h-1 bg-purple-light my-3 my-6"></div>
            <p class="
            text-grey-darker text-2xl text-3xl
            font-light
            mb-8
            leading-normal
          ">
                Please set default site.
            </p>
            <a href="/settings/sites">
                <button class="
              bg-transparent
              text-grey-darkest
              font-bold
              uppercase
              tracking-wide
              py-3
              px-6
              border-2 border-grey-light
              hover:border-grey
              rounded-lg
            ">
                    Go to Sites Listing Page
                </button>
            </a>
        </div>
    </div>
    <div v-if="storeSetup == 'N'" class="site-setup-err-section">
        <div class="text-black text-5xl text-15xl font-black">Error :(</div>
        <div class="w-16 h-1 bg-purple-light my-3 my-6"></div>
        <p class="
          text-grey-darker text-2xl text-3xl
          font-light
          mb-8
          leading-normal
        ">
            Site is not setup.
        </p>
        <a :href="defaultStoreURL">
            <button class="
            bg-transparent
            text-grey-darkest
            font-bold
            uppercase
            tracking-wide
            py-3
            px-6
            border-2 border-grey-light
            hover:border-grey
            rounded-lg
          ">
                Go to Site Setting Page
            </button>
        </a>
    </div>
</div>
</template>

<script>
import {
    mapGetters,
    mapActions
} from "vuex";
import WidgetEvents from "./../widgets/WidgetEvents";
import WidgetEddStat from "./../widgets/WidgetEddStat";
import DatePresets from "./../DatePresets";

export default {
    props: ["pluginVersion", "userDefaultStore", "storeSetup"],
    data: function() {
        return {
            pageTitle: "Dashboard | WPdrift",
            defaultStoreURL: "/settings/sites/" + Spark.default_store,
        };
    },
    mounted() {
        $('[data-toggle="tooltip"]').tooltip();
    },
    created: function() {
        document.title = this.pageTitle;
    },
    components: {
        DatePresets,
        WidgetEddStat,
        WidgetEvents,
    },
    computed: {
        ...mapGetters(["selectedDate"]),
    },
    methods: {
        ...mapActions(["updateDateRange", "getDashboardStats"]),
    },
    watch: {
        selectedDate: function() {
            const {
                start,
                end
            } = this.selectedDate;
            this.getDashboardStats({
                startdate: start,
                enddate: end
            });
        },
    },
    updated: function() {
        this.$nextTick(function() {
            $('[data-toggle="tooltip"]').tooltip();
            $("select").selectize();
        });
    },
};
</script>
