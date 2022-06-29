import { mapGetters, mapActions } from 'vuex';

Vue.component('io-sidebar', {
    props: ['user', 'teams', 'currentTeam'],
    computed: {
        ...mapGetters([
            'offcanvas',
            'eddStatus',
        ]),
        eddEnabled() {
            if ('enabled' == this.eddStatus) {
                return true;
            }

            return false;
        }
    },
    methods: {
        ...mapActions([
            'setState',
        ]),
        toggleOffcanvas() {
            this.setState({
                parent: 'settings',
                child: 'offcanvas',
                value: !this.offcanvas,
            });
        },
    },
});
