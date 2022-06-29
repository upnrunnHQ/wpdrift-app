<template>
    <div>
        <div id="customer">
            <div v-if="loading" class="loader">Loading...</div>
            <div v-if="customer">
                <div class="mb-4">
                    <button class="btn btn-sm btn-link" v-on:click="goBack()">Go Back</button>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card card-bio">
                            <google-map
                                v-if="customer.ip_data"
                                v-bind:lat="customer.ip_data.lat"
                                v-bind:lng="customer.ip_data.lon">
                            </google-map>
                            <div class="head">
                                <img v-if="customer.has_avatar" class="avatar" :src="customer.avatar_urls['96']" alt="">
                                <div class="text-avatar" v-if="!customer.has_avatar">
                                    <span>{{ customer.first_name.charAt(0) }}</span>
                                    <span>{{ customer.last_name.charAt(0) }}</span>
                                </div>
                                <div class="info">
                                    <h4 class="card-title">{{ customer.first_name }} {{ customer.last_name }}</h4>
                                    <span class="id">#{{ customer.id }}</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="about">
                                    <ul class="user-bio row no-gutters">
                                        <li class="col-12 date">Joined {{ customer.joined_date }}</li>
                                        <li class="col-12 email"><a :href="`mailto:${customer.email}`">{{ customer.email }}</a></li>
                                        <li class="col-12 fa-users"><span v-for="(role,index) in customer.roles">{{ role }} </span></li>
                                        <li class="col-12 fa-sign-in-alt" v-if="customer.last_login !== null">
                                            <span class="label">IP</span>
                                            <span class="value">{{customer.last_login.ip}}</span>
                                            <span class="label">User Agent</span>
                                            <span class="value">{{customer.last_login.ua}}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end .col-md-4 -->

                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6" v-if="customer.url !== ''">
                                <div class="card card-state">
                                    <div class="card-body">
                                        <span class="lead">URL</span>
                                        <span class="title"><a target="_blank" v-bind:href="customer.url">{{ customer.url }}</a></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-state">
                                    <div class="card-body">
                                        <span class="lead">{{ customer.posted_content_count.posts_count }}</span>
                                        <span class="title">Posted Posts</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end .row -->
                        <div class="row" v-if="customer.description !== ''">
                          <div class="col-md-12">
                            <div class="card card-state">
                                <div class="card-body">
                                    <span class="lead">Bio</span>
                                    <span class="title">{{ customer.description }}</span>
                                </div>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-state">
                                    <div class="card-body">
                                        <span class="lead">{{ customer.posted_content_count.comments_count }}</span>
                                        <span class="title">Comments</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-primary text-white card-state">
                                    <div class="card-body">
                                        <span class="lead">{{ customer.posted_content_count.pages_count }}</span>
                                        <span class="title">Posted Pages</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end .row -->
                    </div>
                    <!-- end .col-md-8 -->
                </div>
            </div>
            <div class="" v-if="error_code">
                <div class="mb-4">
                    <router-link :to="{ path: `/customers/` }">View Customers</router-link>
                </div>
                <div class="py-2 py-md-5 text-danger text-center">
                    <h4>{{ error_message }}</h4>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data: function() {
        return {
            customer: null,
            loading: true,
            custName: '#' + this.$route.params.id,
            error_code: false,
            error_message: '',
        }
    },
    created: function() {
        this.fetchData()
    },
    mounted: function () {
    },
    methods: {
        fetchData: function() {
            var id = this.$route.params.id
            var self = this
            var apiUrl = 'api/customers/' + id + '?response=json'
            this.custName = id
            axios.get(apiUrl).then(response => {
              if (response.data.code) {
                self.error_code = true
                self.error_message = response.data.message
                self.customer = null
                this.loading = false
              } else {
                if ( response.data.customer ) {
                  this.customer = response.data.customer
                  this.custName = this.customer.first_name + ' ' + this.customer.last_name
                  document.title = `Customer - ${this.custName} | WPdrift`
                  this.loading = false
                } else {
                    self.error_code = true
                    self.customer = null
                    self.error_message = 'No customers found.'
                    this.loading = false
                }
              }
            });
        },

        goBack () {
          window.history.length > 1
            ? this.$router.go(-1)
            : this.$router.push('/')
        }
    }
}
</script>

<style>
#map {
    height: 200px;  /* The height is 400 pixels */
    width: 100%;  /* The width is the width of the web page */
}
</style>
