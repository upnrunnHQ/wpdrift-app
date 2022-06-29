<template lang="html">
    <div class="card card-bio">
        <!-- 21:9 aspect ratio -->
        <div class="embed-responsive embed-responsive-21by9">
            <iframe class="embed-responsive-item" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d233668.38703692693!2d90.27923991057244!3d23.780573258035957!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755b8b087026b81%3A0x8fa563bbdd5904c2!2sDhaka!5e0!3m2!1sen!2sbd!4v1527679401276"></iframe>
        </div>
        <div class="head">
            <img class="avatar" v-bind:src="item.avatar" alt="">
            <div class="info">
                <h4 class="card-title">{{item.name}}</h4>
                <span class="location">New York, NY</span>
            </div>
        </div>
        <div class="card-body">
            <div class="about">
                <ul class="user-bio row no-gutters">
                    <li class="date col-md-6">5 days ago</li>
                    <li class="role col-md-6">Customer</li>
                    <li class="email col-12">{{item.email}}</li>
                    <li class="phone col-12">0034678167465</li>
                    <li class="address col-12">
                        <span class="label">Billing Address</span>
                        <span class="value">
                            hendrik jasper van der velde
                            FDRadio Foundation
                            Sant Antonio Maria Claret 38-40 2/1
                            Arenys de Munt
                            B 08358
                            Spain
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['item'],
}
</script>

<style lang="css">
</style>
