import { mapGetters } from 'vuex';

Vue.component('navbar-brand', {
    props: ['user', 'teams', 'currentTeam'],
    mounted() {
    },
    computed: {
        ...mapGetters([
            'defaultSite',
        ]),
        logo() {
            return this.defaultSite.logo;
        },
    },
});
