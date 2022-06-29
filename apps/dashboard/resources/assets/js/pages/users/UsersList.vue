<template lang="html">
    <items-list-table
        items-title="Users"
        items-store-id="users"
        items-route="/customers"
        :thead="thead"
        :orderby="orderby">
        <users-list-item
            v-for="item in itemsList"
            v-bind:item="item"
            v-bind:key="item.id">
        </users-list-item>
    </items-list-table>
</template>

<script>
import { mapGetters } from 'vuex';
import ItemsListTable from './../../components/ItemsListTable';
import UsersListItem from './UsersListItem';
export default {
    data() {
        return {
            thead: ['User', 'Contact Details', 'Location', 'Activity'],
            orderby: [
                {
                    value: 'id',
                    label: 'ID',
                },
                {
                    value: 'name',
                    label: 'Name',
                },
                {
                    value: 'email',
                    label: 'Email',
                },
                {
                    value: 'registered_date',
                    label: 'Registered',
                }
            ],
        };
    },
    components: {
        ItemsListTable,
        UsersListItem,
    },
    computed: {
        ...mapGetters([
            'getItems',
        ]),
        items() {
            return this.getItems('users');
        },
        itemsList() {
            return this.items.data;
        },
    },
}
</script>

<style lang="css">
</style>
