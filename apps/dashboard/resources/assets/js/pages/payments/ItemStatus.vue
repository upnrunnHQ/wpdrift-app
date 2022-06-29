<template lang="html">
    <a-tooltip placement="right">
        <template slot="title">
            <span>{{statusLabel}}</span>
        </template>
        <div class="">
            <div class="item__status" v-bind:class="[statusClass]"></div>
        </div>
    </a-tooltip>
</template>

<script>
export default {
    props: ['status'],
    computed: {
        statusClass() {
            return 'item__status--' + this.status;
        },
        statusLabel() {
            let status = this.status;

            switch(status) {
                case 'pending':
                    return 'Pending';
                    break;
                case 'publish':
                    return 'Complete';
                case 'refunded':
                    return 'Refunded';
                    break;
                case 'failed':
                    return 'Failed';
                    break;
                case 'abandoned':
                    return 'Abandoned';
                    break;
                case 'revoked':
                    return 'Revoked';
                    break;
                default:
                    return 'Processing';
            }
        }
    }
}
</script>

<style lang="css">
</style>
