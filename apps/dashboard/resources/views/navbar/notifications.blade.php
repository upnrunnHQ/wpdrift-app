<!-- Notifications Modal -->
<spark-notifications
    :notifications="notifications"
    :has-unread-announcements="hasUnreadAnnouncements"
    :loading-notifications="loadingNotifications" inline-template>

    <div id="modal-notifications">
        <div class="notifications__head">
            <div class="row">
                <div class="col-6">
                    <button class="btn btn-block btn-default" :class="{'active': showingNotifications}" @click="showNotifications">
                        {{__('Notifications')}}
                    </button>
                </div>
                <div class="col-6">
                    <button class="btn btn-block btn-default" :class="{'active': showingAnnouncements}" @click="showAnnouncements">
                        {{__('Announcements')}} <i class="fa fa-circle text-danger p-l-xs" v-if="hasUnreadAnnouncements"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Informational Messages -->
        <div class="notification-container" v-if="loadingNotifications">
            <div class="notification">
                <i class="fa fa-btn fa-spinner fa-spin"></i> {{__('Loading Notifications')}}
            </div>
        </div>

        <div class="notification-container" v-if=" ! loadingNotifications && activeNotifications.length == 0">
            <div class="notification">
                <div class="alert alert-warning mb-0">
                    {{__('We don\'t have anything to show you right now! But when we do, we\'ll be sure to let you know. Talk to you soon!')}}
                </div>
            </div>
        </div>

        <!-- List Of Notifications -->
        <div class="notification-container" v-if="showingNotifications && hasNotifications">
            <div class="notification" v-for="notification in notifications.notifications">

                <!-- Notification Icon -->
                <figure>
                    <img v-if="notification.creator" :src="notification.creator.photo_url" class="spark-profile-photo">

                    <span v-else class="fa-stack fa-2x">
                        <i class="fa fa-circle fa-stack-2x"></i>
                        <i :class="['fa', 'fa-stack-1x', 'fa-inverse', notification.icon]"></i>
                    </span>
                </figure>

                <!-- Notification -->
                <div class="notification-content">
                    <div class="meta">
                        <p class="title">
                            <span v-if="notification.creator">
                                @{{ notification.creator.name }}
                            </span>

                            <span v-else>
                                {{ Spark::product() }}
                            </span>
                        </p>

                        <div class="date">
                            @{{ notification.created_at | relative }}
                        </div>
                    </div>

                    <div class="notification-body" v-html="notification.parsed_body"></div>

                    <!-- Notification Action -->
                    <a :href="notification.action_url" class="btn btn-default" v-if="notification.action_text">
                        @{{ notification.action_text }}
                    </a>

                </div>
            </div>
        </div>

        <!-- List Of Announcements -->
        <div class="notification-container" v-if="showingAnnouncements && hasAnnouncements">
            <div class="notification" v-for="announcement in notifications.announcements">

                <!-- Notification Icon -->
                <figure>
                    <img :src="announcement.creator.photo_url" class="spark-profile-photo">
                </figure>

                <!-- Announcement -->
                <div class="notification-content">
                    <div class="meta">
                        <p class="title">@{{ announcement.creator.name }}</p>

                        <div class="date">
                            @{{ announcement.created_at | relative }}
                        </div>
                    </div>

                    <div class="notification-body" v-html="announcement.parsed_body"></div>

                    <!-- Announcement Action -->
                    <a :href="announcement.action_url" class="btn btn-default" v-if="announcement.action_text">
                        @{{ announcement.action_text }}
                    </a>

                </div>
            </div>
        </div>
    </div>
</spark-notifications>
