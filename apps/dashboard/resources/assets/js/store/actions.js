import {notification} from "ant-design-vue";

function getStatistics({after, before, type}, cb) {
    axios
        .get("/statistics", {
            params: {
                after,
                before,
                type
            }
        })
        .then(response => {
            cb(response);
        })
        .catch(function(error) {
            console.log(error);
        });
}

function getUsers({after, before, mode}, cb) {
    axios
        .get("/users", {
            params: {
                after,
                before,
                mode
            }
        })
        .then(response => {
            cb(response);
        })
        .catch(function(error) {
            console.log(error);
        });
}

function getEvents({type}, cb) {
    axios
        .get("/events", {
            params: {type}
        })
        .then(response => {
            cb(response);
        })
        .catch(function(error) {
            console.log(error);
        });
}

function prepareDates(dates) {
    return {
        start: moment(dates.start).format("YYYY-MM-DD HH:mm:ss"),
        end: moment(dates.end).format("YYYY-MM-DD HH:mm:ss")
    };
}

export default {
    initApi({commit, state}) {
        let start = "";
        let end = "";

        const startDate = new Date(state.settings.selectedDate.end);
        start = startDate.toISOString();

        const endDate = new Date(state.settings.selectedDate.start);
        end = endDate.toISOString();

        var apiUrl = "/api/dashboard?" + "before=" + end + "&after=" + start;
        axios.get(apiUrl).then(response => {
            let dashboard = response.data.dashboard_info;
            commit("setDashboard", dashboard);
        });

        axios.get("/site").then(response => {
            commit("setState", {
                parent: "settings",
                child: "defaultSite",
                value: response.data
            });
        });
    },
    updateDateRange({commit}, payload) {
        commit("updateDateRange", payload);
    },
    getDashboardStats({commit, state}) {
        const dates = prepareDates(state.settings.selectedDate);
        axios
            .get("/edd/dashboard-stats", {
                params: {
                    startdate: dates.start,
                    enddate: dates.end
                }
            })
            .then(response => {
                console.log(response);
                if (response.data.success) {
                    commit("updateEddData", response.data.data);
                }
            });
    },
    getStatistics({commit, state}, payload) {
        const endDate = new Date(state.settings.selectedDate.start);
        const after = endDate.toISOString();

        const startDate = new Date(state.settings.selectedDate.end);
        const before = startDate.toISOString();

        getStatistics(
            {
                after,
                before,
                type: payload["type"]
            },
            response => {
                commit("storeStats", {
                    type: payload["type"],
                    response: response["data"]
                });
            }
        );
    },
    getUsers({commit}, payload) {
        const startDate = new Date(payload["start"]);
        const after = startDate.toISOString();

        const endDate = new Date(payload["end"]);
        const before = endDate.toISOString();

        commit("setUsersBusy", true);

        getUsers(
            {
                after,
                before,
                mode: payload["mode"]
            },
            response => {
                let users = response.data.results;
                commit("setUsers", users);
                commit("setUsersBusy", false);
                commit("setUsersReady");
            }
        );
    },
    getEvents({commit, state}, payload) {
        commit("setEventsBusy", true);
        let type = payload.type;
        getEvents({type}, response => {
            let events = response.data;
            commit("setEvents", events);
            commit("setEventsBusy", false);
            commit("setEventsReady");
        });
    },
    setEventsVisibility({commit}, payload) {
        commit("setEventsVisibility", payload);
    },
    incrementEvnets({commit}, payload) {
        commit("incrementEvnets", payload);
    },
    setUsersMode({commit}, payload) {
        commit("setUsersMode", payload);
    },

    setState({commit}, payload) {
        commit("setState", payload);
    },

    fetchItems({commit, state}, payload) {
        const dates = prepareDates(state.settings.selectedDate);
        const items = state[payload.itemsStoreId];

        commit("setState", {
            parent: payload.itemsStoreId,
            child: "status",
            value: "loading"
        });

        let params = {};
        if ("users" == payload.itemsStoreId) {
            params = {
                after: dates.start,
                before: dates.end,
                page: items.page,
                per_page: items.perPage,
                orderby: items.orderby,
                order: items.order,
                search: items.search,
                context: "edit"
            };
        } else {
            let startdate = dates.start;
            let enddate = dates.end;

            if (
                "products" == payload.itemsStoreId &&
                false == state.products.dateChanged
            ) {
                startdate = "";
                enddate = "";
            }

            params = {
                startdate,
                enddate,
                search: items.search,
                page: items.page,
                per_page: items.perPage,
                orderby: items.orderby,
                order: items.order
            };
        }

        axios
            .get(payload.itemsRoute, {params})
            .then(response => {
                let data = [];
                let total = 0;
                let totalPages = 0;
                let oldData = false;
                if ("users" == payload.itemsStoreId) {
                    data = response.data;
                    total = response.headers["x-wp-total"];
                    totalPages = response.headers["x-wp-totalpages"];
                } else {
                    data = response.data.result.data;
                    total = response.data.result.total;
                    totalPages = response.data.result.last_page;
                    oldData = response.data.old_data;
                }

                if (data && data.length) {
                    commit("updateStore", {
                        key: payload.itemsStoreId,
                        params: {
                            data,
                            total,
                            totalPages,
                            busy: false,
                            ready: true,
                            error: false,
                            error_message: "",
                            status: "loaded"
                        }
                    });

                    if (oldData && items.notificationDisplayed == false) {
                        notification["info"]({
                            message: "Displaying old data!",
                            description:
                                "There are no items in the date range, you are viewing old items.",
                            duration: 0
                        });

                        commit("setState", {
                            parent: payload.itemsStoreId,
                            child: "notificationDisplayed",
                            value: true
                        });
                    } else {
                        commit("setState", {
                            parent: payload.itemsStoreId,
                            child: "notificationDisplayed",
                            value: false
                        });
                    }
                } else {
                    commit("updateStore", {
                        key: payload.itemsStoreId,
                        params: {
                            data: [],
                            total: 0,
                            totalPages: 0,
                            busy: false,
                            ready: true,
                            error: true,
                            error_code: "no_match",
                            error_message: "No matching items were found.",
                            status: "loaded"
                        }
                    });
                }
            })
            .catch(function(error) {
                console.log(error);
            });
    },
    fetchEddData({commit, state}, payload) {
        const dates = prepareDates(state.settings.selectedDate);

        let params = {
            startdate: dates.start,
            enddate: dates.end
        };

        axios
            .get(payload.itemRoute, {params})
            .then(response => {
                commit("updateEddData", {
                    key: payload.itemKey,
                    data: response.data
                });
            })
            .catch(function(error) {
                console.log(error);
            });
    }
};
