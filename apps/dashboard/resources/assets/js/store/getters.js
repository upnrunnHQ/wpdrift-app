export default {
    dashboard: state => state.dashboard,
    customers: state => state.customers,
    getProducts: state => state.products,
    browsers: state => state.browsers,
    systems: state => state.systems,
    clicks: state => state.clicks,
    views: state => state.views,
    referrals: state => state.referrals,
    posts: state => state.posts,
    users: state => state["userschart"],
    events: state => state["events"],
    statUsers: state => state["stats"]["users"],
    statPosts: state => state["stats"]["posts"],
    statPages: state => state["stats"]["pages"],
    statComments: state => state["stats"]["comments"],
    offcanvas: state => state["settings"]["offcanvas"],
    selectedDate: state => state["settings"]["selectedDate"],
    defaultStore: state => state["settings"]["defaultStore"],
    eddStatus: state => state["settings"]["eddStatus"],
    defaultSite: state => state["settings"]["defaultSite"],
    getStat: state => type => {
        return state["stats"][type];
    },
    getItems: state => storeId => {
        return state[storeId];
    },
    getEddData: state => key => {
        if (typeof state["eddData"]['data'][key] === "undefined") {
            return {
                isLoading: state["eddData"].isLoading,
                item_value: {
                    data: {
                        categories: [],
                        series: []
                    },
                    total: 0
                },
                item_prev_value: 0,
                item_percentage: 0
            };
        }

        return {
            isLoading: state["eddData"].isLoading,
            ...state["eddData"]['data'][key]
        };
    }
};
