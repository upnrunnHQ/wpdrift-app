<template lang="html">
    <div class="card card-tabs">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="card__title">Links</h4>
                <ul class="nav nav-pills">
                    <li class="nav-item" data-toggle="tooltip" title="Pages">
                        <a class="nav-link active" data-toggle="tab" href="#pages">
                            <span class="sr-only">Pages</span>
                            <i class="fas fa-file-alt"></i>
                        </a>
                    </li>
                    <li class="nav-item" data-toggle="tooltip" title="Referrals">
                        <a class="nav-link" data-toggle="tab" href="#referrals">
                            <span class="sr-only">Referrals</span>
                            <i class="fas fa-link"></i>
                        </a>
                    </li>
                    <li class="nav-item" data-toggle="tooltip" title="Views">
                        <a class="nav-link" data-toggle="tab" href="#views">
                            <span class="sr-only">Views</span>
                            <i class="fas fa-chart-area"></i>
                        </a>
                    </li>
                    <li class="nav-item" data-toggle="tooltip" title="Clicks">
                        <a class="nav-link" data-toggle="tab" href="#clicks">
                            <span class="sr-only">Clicks</span>
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class=" card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="pages">
                    <ul class="list-group list-group-flush" v-if="posts && posts.length">
                        <li v-for="item in posts" class="list-group-item list-group-item-action flex-column align-items-start">
                            <div class="d-flex w-100 justify-content-between">
                                <a class="d-block" v-bind:href="item.link" target="_blank">
                                    <span>{{item.link}}</span>
                                </a>
                                <small>{{ item.counts }}</small>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="tab-pane fade" id="referrals">
                    <ul class="list-group list-group-flush" v-if="referrals && referrals.length">
                        <li v-for="item in referrals" class="list-group-item list-group-item-action flex-column align-items-start">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">{{ item.domain }}</h5>
                                <small>{{ item.counts }}</small>
                            </div>
                            <a class="d-block" v-bind:href="item.link" target="_blank">
                                <i class="fas fa-link"></i>
                                <span>{{item.link}}</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="tab-pane fade" id="views">
                    <div class="list-group list-group-flush" v-if="views && views.length">
                        <div class="list-group-item list-group-item-action flex-column align-items-start">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">{{ views.today.counts }}</h5>
                                <small>{{ views.today.day }}</small>
                            </div>
                            <small>Views today</small>
                        </div>

                        <div class="list-group-item list-group-item-action flex-column align-items-start">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">{{ views.bestDay.counts }}</h5>
                                <small>{{ views.bestDay.day }}</small>
                            </div>
                            <small>Best overall day</small>
                        </div>

                        <div class="list-group-item list-group-item-action flex-column align-items-start">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">{{ views.all }}</h5>
                            </div>
                            <small>All-time views</small>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="clicks">
                    <ul class="list-group list-group-flush" v-if="clicks && clicks.length">
                        <li v-for="item in clicks" class="list-group-item list-group-item-action flex-column align-items-start">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">{{ item.host }}</h5>
                                <small>{{ item.counts }}</small>
                            </div>
                            <a class="d-block" v-bind:href="item.uri">
                                <i class="fas fa-link"></i>
                                <span>{{item.uri}}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    mounted() {
        $('[data-toggle="tooltip"]').tooltip();
    },
    computed: {
        ...mapGetters([
            'clicks',
            'views',
            'posts',
            'referrals'
        ]),
    },
    updated: function () {
        this.$nextTick(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    },
}
</script>

<style lang="css">
</style>
