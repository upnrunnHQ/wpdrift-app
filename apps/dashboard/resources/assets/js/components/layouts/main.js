import { mapGetters, mapActions } from 'vuex';

Vue.component('io-main', {
    props: ['user', 'teams', 'currentTeam'],
    computed: {
        ...mapGetters([
            'offcanvas',
        ]),
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
