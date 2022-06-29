<io-main
    :user="user"
    :teams="teams"
    :current-team="currentTeam"
    inline-template>
    <div class="main">
        <router-view
            :plugin-version="'{{ $plugin_version }}'"
            :user-default-store="'{{ $user_default_store }}'"
            :store-setup="'{{ $store_setup }}'">
        </router-view>
    </div>
</io-main>
