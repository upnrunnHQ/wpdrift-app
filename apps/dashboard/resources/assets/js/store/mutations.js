export default {
    setStatistics (state, payload) {
        state['referrals'] = payload['results'];
        state['browsers'] = payload['browsers'];
        state['systems'] = payload['oss'];
        state['clicks'] = payload['clicks'];
        state['posts'] = payload['posts'];
        state['views']['today'] = payload['days_hits'];
        state['views']['bestDay'] = payload['best_hits_day'];
        state['views']['all'] = payload['all_hits'];
    },
    setDashboard (state, payload) {
        state['dashboard'] = payload;
    },
    setUsers (state, payload) {
        state['userschart']['data'] = payload;
        state['userschart']['unReady'] = false;
    },
    storePosts (state, payload) {
        state['stats']['posts']['response']= payload;
        state['stats']['posts']['ready']= true;
    },
    storeStats (state, payload) {
        state['stats'][payload.type]['response']= payload.response;
        state['stats'][payload.type]['ready']= true;
    },
    updateDateRange (state, payload) {
        state['settings']['selectedDate'] = payload;
    },
    setEvents (state, payload) {
        state['events']['ready'] = true;
        state['events']['data'] = payload;
        state['events']['limit'] = 5;
    },
    setEventsVisibility (state, payload) {
        state['events']['visibility'] = payload;
    },
    setUsersMode (state, payload) {
        state['userschart']['mode'] = payload['mode'];
    },
    setEventsBusy (state, payload) {
        state['events']['busy'] = payload;
    },
    setEventsReady (state, payload) {
        state['events']['unReady'] = false;
    },
    incrementEvnets (state, payload) {
        state['events']['limit'] += state['events']['limit'];
    },
    setUsersBusy (state, payload) {
        state['userschart']['busy'] = payload;
    },
    setUsersReady (state, payload) {
        state['userschart']['unReady'] = false;
    },

    setState (state, payload) {
        state[payload.parent][payload.child] = payload.value;
    },

    updateStore (state, payload) {
        let params = payload.params;

        for (const prop in params) {
            state[payload.key][prop] = params[prop];
        }
    },

    updateEddData (state, payload) {
        state['eddData']['isLoading'] = false;
        state['eddData']['data'] = payload;
    },

    setCustomers (state, payload) {
        state['customers']['ready'] = true;
        state['customers']['busy'] = false;
        state['customers']['data'] = payload.data;
        state['customers']['total'] = payload.total;
        state['customers']['totalPages'] = payload.totalPages;
    },
};
