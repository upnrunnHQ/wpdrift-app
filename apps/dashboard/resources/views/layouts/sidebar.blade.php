<io-sidebar
    :user="user"
    :teams="teams"
    :current-team="currentTeam"
    inline-template>

    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            <a-tooltip placement="right">
                <template slot="title">
                    <span>{{__('Home')}}</span>
                </template>
                <li class="nav-item">
                    <router-link to="/" exact>
                        <span class="icomoon-Dashboard"></span>
                        <span class="sr-only">{{__('Home')}}</span>
                    </router-link>
                </li>
            </a-tooltip>

            <a-tooltip placement="right">
                <template slot="title">
                    <span>{{__('Orders')}}</span>
                </template>
                <li class="nav-item" v-tooltip.right="'Orders'">
                    <router-link to="/orders">
                        <span class="icomoon-Order"></span>
                        <span class="sr-only">{{__('Orders')}}</span>
                    </router-link>
                </li>
            </a-tooltip>

            <a-tooltip placement="right">
                <template slot="title">
                    <span>{{__('Customers')}}</span>
                </template>
                <li class="nav-item">
                    <router-link to="/customers">
                        <span class="icomoon-Customer"></span>
                        <span class="sr-only">{{__('Customers')}}</span>
                    </router-link>
                </li>
            </a-tooltip>

            <a-tooltip placement="right">
                <template slot="title">
                    <span>{{__('Products')}}</span>
                </template>
                <li class="nav-item">
                    <router-link to="/products">
                        <span class="icomoon-Product"></span>
                        <span class="sr-only">{{__('Products')}}</span>
                    </router-link>
                </li>
            </a-tooltip>

            <a-tooltip placement="right">
                <template slot="title">
                    <span>{{__('Settings')}}</span>
                </template>
                <li class="nav-item">
                    <a class="nav-link" id="setting-store-side-lnk" href="/settings/sites/{{ $user_default_store }}">
                        <span class="icomoon-Setting"></span>
                        <span class="sr-only">{{__('Settings')}}</span>
                    </a>
                </li>
            </a-tooltip>
        </ul>

        <a-tooltip placement="right">
            <template slot="title">
                <span>{{__('Stores')}}</span>
            </template>
            <a class="offcanvas__toggler offcanvas__toggler--primary" href="#" v-on:click="toggleOffcanvas">
                <span class="icomoon-Switch"></span>
                <span class="sr-only">{{__('Stores')}}</span>
            </a>
        </a-tooltip>
    </div>
</io-sidebar>
