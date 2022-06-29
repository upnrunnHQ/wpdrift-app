<template lang="html">
    <div class="items">
        <div class="items__head">
            <a href="#" class="items__back" v-on:click="goBack">Go back</a>
            <div class="d-flex justify-content-between">
                <div class="">
                    <h4>{{itemsTitle}}</h4>
                </div>
                <date-presets></date-presets>
            </div>
        </div>

        <div class="items__body">
            <div class="items__filters">
                <div class="row justify-content-between">
                    <div class="col-md-2">
                        <div class="items__search">
                            <input class="form-control" type="text" v-model="itemsSearch" v-bind:placeholder="placeholderText">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end">
                            <div class="items__orderby">
                                <selectize v-model="itemsOrderby">
                                    <option v-for="item in orderby" v-bind:value="item.value">{{item.label}}</option>
                                </selectize>
                            </div>
                            <div class="items__order">
                                <selectize v-model="itemsOrder">
                                    <option value="asc">ASC</option>
                                    <option value="desc">DESC</option>
                                </selectize>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="items__table">
                <a-spin :spinning="itemsOverly">
                    <div class="spin-content">
                        <table class="table table-borderless table-hover">
                            <thead>
                                <tr>
                                    <th v-for="item in thead">{{item}}</th>
                                </tr>
                            </thead>
                            <tbody v-if="haveItems">
                                <slot></slot>
                            </tbody>
                            <tbody v-else>
                                <tr>
                                    <td class="items__error" colspan="4">
                                        <span class="items__error--icon fas fa-exclamation-circle"></span>
                                        <span class="items__error--title">No matching items were found.</span>
                                        <span class="items__error--message">{{itemsErrorMessage}}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </a-spin>
            </div>

            <div class="items__filters">
                <div class="d-flex justify-content-between">
                    <div class="">
                        <span class="items__total">{{itemsTotal}} {{itemsTitle}}</span>
                    </div>
                    <div class="">
                        <div class="d-flex justify-content-end">
                            <div class="items__limit">
                                <selectize v-model="itemsLimit">
                                    <option :value="1">1</option>
                                    <option :value="5">5</option>
                                    <option :value="10">10</option>
                                    <option :value="15">15</option>
                                    <option :value="20">20</option>
                                    <option :value="30">30</option>
                                    <option :value="50">50</option>
                                </selectize>
                            </div>
                            <nav class="items__nav">
                                <paginate
                                    :page-count="itemsTotalPages"
                                    :initial-page="itemsPage"
                                    :force-page="itemsPage"
                                    :click-handler="itemsNextPage">
                                </paginate>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Vue from 'vue';
import { mapGetters, mapActions } from 'vuex';
import { Spin } from 'ant-design-vue';
import Selectize from 'vue2-selectize';
import DatePresets from './DatePresets';
Vue.use(Spin);

export default {
    props: ['itemsTitle', 'itemsStoreId', 'itemsRoute', 'thead', 'orderby'],
    data() {
        return {
            itemsSearch: '',
            itemsLimit: 1,
            itemsOrderby: 'id',
            itemsOrder: 'asc',
            ready: false,
            page: 1,
        };
    },
    components: {
        Selectize,
        DatePresets
    },
    created() {
        this.itemsLimit = this.items.perPage;
        this.itemsSearch = this.items.search;
        this.itemsOrderby = this.items.orderby;
        this.itemsOrder = this.items.order;
        this.ready = true;
        if ('neutral' == this.items.status) {
            this.itemsQuery();
        }
    },
    computed: {
        ...mapGetters([
            'selectedDate',
            'getItems',
        ]),
        items() {
            return this.getItems(this.itemsStoreId);
        },
        orders() {
            return this.getItems('orders');
        },
        itemsList() {
            return this.items.data;
        },
        haveItems() {
            return this.items.data && this.items.data.length;
        },
        itemsPage() {
            return this.items.page - 1;
        },
        itemsTotal() {
            return this.items.total;
        },
        itemsTotalPages() {
            return this.items.totalPages;
        },
        itemsOverly() {
            if ('loading' == this.items.status) {
                return true;
            }

            return false;
        },
        itemsErrorMessage() {
            return 'Try changing the filters/dates above!';
        },
        placeholderText() {
            return 'Search ' + this.itemsTitle;
        },
    },
    methods: {
        ...mapActions([
            'setState',
            'updateDateRange',
            'fetchItems',
        ]),
        itemsQuery() {
            if ('loading' == this.items.status) {
                return;
            }

            if (this.ready) {
                this.fetchItems({
                    itemsRoute: this.itemsRoute,
                    itemsStoreId: this.itemsStoreId,
                });
            }
        },
        itemsNextPage(page) {
            this.setState({
                parent: this.itemsStoreId,
                child: 'page',
                value: page,
            });

            this.itemsQuery();
        },
        goBack () {
          window.history.length > 1
            ? this.$router.go(-1)
            : this.$router.push('/')
        },
    },
    watch: {
        selectedDate: function () {
            // this.setState({
            //     parent: this.itemsStoreId,
            //     child: 'page',
            //     value: 1,
            // });

            this.itemsQuery();
        },
        itemsLimit: function () {
            this.setState({
                parent: this.itemsStoreId,
                child: 'perPage',
                value: this.itemsLimit,
            });

            // this.setState({
            //     parent: this.itemsStoreId,
            //     child: 'page',
            //     value: 1,
            // });

            this.itemsQuery();
        },
        itemsSearch: function() {
            this.setState({
                parent: this.itemsStoreId,
                child: 'search',
                value: this.itemsSearch,
            });

            // this.setState({
            //     parent: this.itemsStoreId,
            //     child: 'page',
            //     value: 1,
            // });

            this.itemsQuery();
        },

        itemsOrderby: function() {
            this.setState({
                parent: this.itemsStoreId,
                child: 'orderby',
                value: this.itemsOrderby,
            });

            // this.setState({
            //     parent: this.itemsStoreId,
            //     child: 'page',
            //     value: 1,
            // });

            this.itemsQuery();
        },
        itemsOrder: function() {
            this.setState({
                parent: this.itemsStoreId,
                child: 'order',
                value: this.itemsOrder,
            });

            // this.setState({
            //     parent: this.itemsStoreId,
            //     child: 'page',
            //     value: 1,
            // });

            this.itemsQuery();
        },
    },
}
</script>

<style lang="css">
</style>
