import Vue from "vue";
import Vuex from "vuex";

import getters from "./getters";
import mutations from "./mutations";
import actions from "./actions";

Vue.use(Vuex);

const n = new Date();
const start = new Date(n.getFullYear(), n.getMonth(), n.getDate() - 365);
const end = new Date(n.getFullYear(), n.getMonth(), n.getDate() + 1);

const state = {
    dashboard: null,
    userschart: {
        data: null,
        mode: "day",
        unReady: true,
        busy: true,
        error: null
    },
    posts: [],
    pages: [],
    comments: [],
    events: {
        data: [],
        unReady: true,
        busy: true,
        visibility: {
            label: "Everything",
            value: "all"
        },
        limit: 5
    },
    referrals: [],
    views: {
        today: null,
        bestDay: null,
        all: 0
    },
    clicks: [],
    systems: [],
    browsers: {
        counts: [],
        labels: []
    },
    users: {
        data: [],
        page: 1,
        perPage: 5,
        search: "",
        orderby: "id",
        order: "desc",
        total: 0,
        totalPages: 0,
        ready: false,
        busy: true,
        error: false,
        error_code: "",
        error_message: "",
        status: "neutral"
    },
    products: {
        data: [],
        page: 1,
        perPage: 5,
        search: "",
        orderby: "post_id",
        order: "desc",
        total: 0,
        totalPages: 0,
        ready: false,
        busy: true,
        error: false,
        error_code: "",
        error_message: "",
        status: "neutral",
        dateChanged: false,
        notificationDisplayed: false
    },
    orders: {
        data: [],
        page: 1,
        perPage: 5,
        search: "",
        orderby: "order_id",
        order: "desc",
        total: 0,
        totalPages: 0,
        ready: false,
        busy: true,
        error: false,
        error_code: "",
        error_message: "",
        status: "neutral",
        notificationDisplayed: false
    },
    customers: {
        data: [],
        page: 1,
        perPage: 5,
        search: "",
        orderby: "id",
        order: "desc",
        total: 0,
        totalPages: 0,
        ready: false,
        busy: true,
        error: false,
        error_code: "",
        error_message: "",
        status: "neutral",
        notificationDisplayed: false
    },
    stats: {
        posts: {
            ready: false,
            response: null,
            error: null
        },
        pages: {
            ready: false,
            response: null,
            error: null
        },
        comments: {
            ready: false,
            response: null,
            error: null
        },
        users: {
            ready: false,
            response: null,
            error: null
        }
    },
    settings: {
        defaultSite: {
            logo: "/img/icon_png.png"
        },
        offcanvas: false,
        selectedDate: {
            start: start,
            end: end
        },
        defaultStore: 0,
        defaultCompany: 0,
        eddStatus: "disabled"
    },
    eddData: {
        isLoading: true,
        error: false,
        data: {}
    }
};

export default new Vuex.Store({
    state,
    getters,
    mutations,
    actions
});
