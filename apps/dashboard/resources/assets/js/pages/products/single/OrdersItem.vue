<template id="">
    <tr class="order">
        <td>
            <span class="order__id">#{{item.ID}}</span>
            <span class="order__date">{{item.date}}</span>
        </td>
        <td>
            <div class="customer-bio media">
                <img class="customer-bio__avatar" :src="item.customer_avatar" alt="">
                <div class="media-body">
                    <span class="customer-bio__name">{{item.customer.name}}</span>
                    <span class="customer-bio__location">{{item.customer.email}}</span>
                </div>
            </div>
        </td>
        <td>
            <span class="order__payment-id">{{item.payment_id}}</span>
        </td>
        <td>
            <span class="order__total">{{item.amount}}</span>
        </td>
    </tr>
</template>

<script>
export default {
    props: ['item'],
}
</script>

<style lang="css" scoped>
</style>
