<template>
    <div class="card card-events">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card__title">Recent Events</h4>
            <dropdown-item
                :options="visibilityFilters"
                :selected="visibility"
                v-on:updateOption="updateVisibility"
                :placeholder="'Select an Item'">
            </dropdown-item>
        </div>
        <div class="card-body">
            <a-spin :spinning="busy">
                <div class="spin-content">
                    <ul class="list-group" v-if="ready">
                        <event-item
                            v-for="(item, index) in events.data"
                            v-if="index < limit"
                            v-bind:item="item"
                            v-bind:key="item.id">
                        </event-item>
                    </ul>
                </div>
            </a-spin>
        </div>
        <div class="card-footer">
            <button v-bind:disabled="counts <= limit" v-on:click="loadMore" type="button" name="button" class="btn btn-block btn-link">
                <span v-if="counts <= limit">No more recent events...</span>
                <span v-else>Load more</span>
            </button>
        </div>
    </div>
</template>
<script>
import Vue from 'vue';
import { Spin } from 'ant-design-vue';
import { mapGetters, mapActions } from 'vuex';
import DropdownItem from './../Dropdown';
import EventItem from './EventItem';
Vue.use(Spin);

const visibilityFilters = [
    {
        label: 'Everything',
        value: 'all',
    },
    {
        label: 'Customers',
        value: 'customer',
    },
    {
        label: 'Orders',
        value: 'order',
    }
];

export default {
    data: function() {
        return {
            visibilityFilters,
        };
    },
    components: {
        DropdownItem,
        EventItem,
    },
    created() {
        let unReady = this.events.unReady;
        if (unReady) {
            this.fetchData();
        }
    },
    computed: {
        ...mapGetters([
            'events',
        ]),
        visibility() {
            return this.events.visibility;
        },
        counts() {
            return this.events.data.length;
        },
        limit() {
            return this.events.limit
        },
        busy() {
            return this.events.busy;
        },
        ready() {
            return !this.events.unReady;
        },
        unReady() {
            return this.events.unReady;
        },
    },
    methods: {
        ...mapActions([
            'getEvents',
            'setEventsVisibility',
            'incrementEvnets',
        ]),
        fetchData: function() {
            let type = this.events.visibility.value;
            this.getEvents({ type });
        },
        loadMore: function() {
            this.incrementEvnets(10);
        },
        updateVisibility(payload) {
            this.setEventsVisibility(payload);
            this.fetchData();
        },
    },
};
</script>
