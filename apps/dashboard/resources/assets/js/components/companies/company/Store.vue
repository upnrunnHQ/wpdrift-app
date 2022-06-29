<template>
    <div class="col-md-3">
        <div class="card store" v-bind:class="{ 'active': isDefault( store.id ) }">
            <div class="card-body">
                <div class="mb-3">
                    <h4 class="card-title">
                      <img v-bind:src="store.photo_url" alt="" border="0" width="32" /> {{ store.name }} #{{ store.id }}
                    </h4>
                    <p class="lead">{{ store.description }}</p>
                    <small>Created at {{ store.created_at }}</small>
                </div>

                <div class="">
                    <button class="btn btn-block btn-sm btn-primary"
                        v-if="! isDefault( store.id ) && hasCredential"
                        v-on:click="setDefault( store.id )">
                        Switch to {{ store.name }}
                    </button>
                    <a class="btn btn-block btn-sm btn-secondary" v-bind:href="'settings/sites/' + store.id">View Site Details</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['store', 'defaultid'],
    methods: {
        isDefault: function ( id ) {
            if ( this.defaultid == id ) {
                return true
            }

            return false
        },

        setDefault: function ( id ) {
            axios.get('set-default-site/' + id + '?response=json').then(response => {
                if (response.data.success) {
                    Spark.customersPageSess.per_page = 10
                    Spark.customersPageSess.page = 1
                    Spark.customersPageSess.orderby = 'name'
                    Spark.customersPageSess.search = ''
                    this.$emit('set-default', id)
                    // set the store logo on store switch
                    var x = document.getElementsByClassName("navbar-brand");
                    if(response.data.store_logo != null) {
                        x[0].innerHTML = '<img src="' + response.data.store_logo +'" alt="" width="32" />';
                    } else {
                        x[0].innerHTML = response.data.store_name;
                    }
                    // set the store setting link in side bar
                    var side_lnk = document.getElementById("setting-store-side-lnk");
                    side_lnk.href = "/settings/sites/" + response.data.store_id;
                    // set the store setting link in top menu
                    var top_lnk = document.getElementById("setting-store-top-lnk");
                    top_lnk.href = "/settings/sites/" + response.data.store_id;
                }
            });
        }
    },
    computed: {
        hasCredential () {
            var credential = this.store.companies_store_credentials
            if(credential !== null && credential !== '') {
                return true
            }

            return false
        }
    }
}
</script>
