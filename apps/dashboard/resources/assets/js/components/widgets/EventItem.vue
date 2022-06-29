<template>
<li class="list-group-item" v-bind:class="itemClass">
    <router-link :to="to">
        <div class="media">
            <img v-if="hasAvatar" class="avatar" width="35" :src="item.user_avatar" alt="">
            <div v-else class="avatar" v-bind:style="{ backgroundColor: getRandomColor }">
                {{ item.user_display_name.charAt(0) }}
                <span v-if='item.user_id === "0"'>
                    {{ item.user_display_name.charAt(0) }}
                </span>
            </div>
            <div class="media-body">
                <div class="">
                    <span class="display_name" v-bind:style="{ color: getRandomColor }">{{ item.user_display_name }}</span>
                    <span class="event_text" v-if="item.event_type === 'customer'">joined as a customer</span>
                    <span class="event_text" v-else>purchased an item</span>
                </div>
                <span class="event-date">{{date}}</span>
            </div>
        </div>
    </router-link>
</li>
</template>

<script>
import 'moment-timezone';
import router from './../../router'
export default {
    props: ['item'],
    mounted() {},
    methods: {},
    computed: {
        itemClass: function() {
            return {
                'event-primary': this.item.event_type == 'signup',
                'event-success': this.item.event_type == 'comment'
            }
        },
        getRandomColor() {
            var letters = '0123456789ABCDEF';
            var color = '#';
            for (var i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        },
        hasAvatar() {
            if (typeof this.item.user_avatar == 'undefined') {
                return false;
            }

            if (this.item.user_avatar === '') {
                return false;
            }

            return true;
        },
        to() {
            let path = '/users/' + this.item.user_id;
            if (this.item.event_type === 'order') {
                path = '/orders/' + this.item.event_id;
            }

            if (this.item.event_type === 'customer') {
                path = '/customers/' + this.item.event_id;
            }

            return {
                path
            };
        },
        date() {
            return moment.tz(this.item.event_date, Spark.timezone).fromNow();
        },
    },
}
</script>
