import ToggleButton from 'vue-js-toggle-button';
import { mapGetters, mapActions } from 'vuex';

Vue.use(ToggleButton);

Vue.component('io-offcanvas', {
    props: ['user', 'teams', 'currentTeam'],
    data() {
        return {
            companies: [],
            stores: [],
            redy: false,
            selectedCompany: null,
        };
    },
    mounted() {
        this.getCompanies();
        this.setState({
            parent: 'settings',
            child: 'defaultStore',
            value: Spark.default_store,
        });
    },
    computed: {
        ...mapGetters([
            'offcanvas',
            'events',
            'defaultStore',
            'selectedDate',
        ]),
        displayOffcanvas() {
            return this.offcanvas;
        },
        hasCompanies() {
            return this.companies && this.companies.length;
        },
        hasStores() {
            return this.stores && this.stores.length;
        },
    },
    methods: {
        ...mapActions([
            'initApi',
            'setState',
            'getStatistics',
            'getUsers',
            'getEvents',
        ]),
        toggleOffcanvas() {
            this.setState({
                parent: 'settings',
                child: 'offcanvas',
                value: !this.offcanvas,
            });
        },
        hideOffcanvas() {
            this.toggleOffcanvas();
        },
        getCompanies() {
            axios.get('/settings/companies?response=json').then(response => {
                this.companies = response.data.companies;
                this.ready = true;
                this.selectedCompany = this.companies[0];
                this.getStores(this.selectedCompany['id']);
            });
        },
        getStores(company_id) {
            axios.get('/settings/companies/' + company_id + '?response=json').then(response => {
                this.stores = response.data.companies_stores;
            });
        },
        updateCompany(index) {
            this.selectedCompany = this.companies[index];
            this.getStores(this.selectedCompany['id']);
        },
        setDefaultStore( store ) {
            let start = this.selectedDate.start;
            let end = this.selectedDate.end;

            var credential = store.companies_store_credentials;
            if(credential !== null && credential !== '') {
                axios.get('set-default-site/' + store.id + '?response=json').then(response => {
                    if (response.data.success) {
                        this.$toasted.show('The store has been switched successfully!', {
                        	 duration : 1000,
                        });

                        this.setState({
                            parent: 'settings',
                            child: 'defaultStore',
                            value: store.id,
                        });

                        axios.get('/site').then(response => {
                            this.setState({
                                parent: 'settings',
                                child: 'defaultSite',
                                value: response.data,
                            });
                        });

                        // set the store logo on store switch
                        // var x = document.getElementsByClassName("navbar-brand");
                        // if(response.data.store_logo != null) {
                        //     x[0].innerHTML = '<img src="' + response.data.store_logo +'" alt="" width="32" />';
                        // } else {
                        //     x[0].innerHTML = '<img src="/img/color-logo.png" alt="" width="32" />';
                        // }
                        // set the store setting link in side bar
                        var side_lnk = document.getElementById("setting-store-side-lnk");
                        side_lnk.href = "/settings/sites/" + response.data.store_id;
                        // set the store setting link in top menu
                        var top_lnk = document.getElementById("setting-store-top-lnk");
                        top_lnk.href = "/settings/sites/" + response.data.store_id;
                        this.initApi();
                        this.getStatistics({ type: 'posts' });
                        this.getStatistics({ type: 'pages' });
                        this.getStatistics({ type: 'comments' });
                        this.getStatistics({ type: 'users' });
                        this.getUsers({mode: 'day', start, end});
                        this.getEvents({ type: this.events.visibility.value });
                    } else {
                        this.$toasted.show('There was an issue when switching the default store!', {
                        	 duration : 1000,
                        });
                    }
                });
            }
        },
    },
});
