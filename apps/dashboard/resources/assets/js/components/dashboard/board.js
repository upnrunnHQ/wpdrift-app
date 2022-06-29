import { mapGetters, mapActions } from 'vuex';

Vue.component('io-dashboard', {
    props: ['user', 'teams', 'currentTeam'],
    mounted() {
        this.initApi();
    },
    computed: {
        ...mapGetters([
            'offcanvas',
        ]),
        displayOffcanvas() {
            return this.offcanvas;
        },
    },
    methods: {
        ...mapActions([
          'initApi',
        ]),
    },
});
