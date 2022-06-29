var base = require('navbar/navbar');

Vue.component('spark-navbar', {
    mixins: [base],
    props: {
        notifications: Object,
        hasUnreadAnnouncements: Boolean,
        loadingNotifications: Boolean
    }
});
