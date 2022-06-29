<io-sidebar
    :user="user"
    :teams="teams"
    :current-team="currentTeam"
    inline-template>

    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Home">
                <a href="/#/">
                    <i class="fas fa-home"></i>
                    <span class="sr-only">{{__('Home')}}</span>
                </a>
            </li>

            <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Orders">
                <a href="/#/orders">
                    <i class="fas fa-list-alt"></i>
                    <span class="sr-only">{{__('Orders')}}</span>
                </a>
            </li>

            <li class="nav-item dropdown" data-toggle="tooltip" data-placement="right" title="Users">
                <a class="nav-link collapsed" data-toggle="collapse" href="#collapse-navs" role="button" aria-expanded="false" aria-controls="collapse-navs">
                    <i class="fas fa-user-circle"></i>
                    <span class="sr-only">{{__('Users')}}</span>
                </a>
                <div class="sub-navs collapse" id="collapse-navs">
                    <a href="/#/users"><span>{{__('Users')}}</span></a>
                    <a href="/#/customers"><span>{{__('Customers')}}</span></a>
                </div>
            </li>

            <li class="nav-item" >
                <a href="/#/products">
                    <i class="fas fa-cube"></i>
                    <span class="sr-only">{{__('Products')}}</span>
                </a>
            </li>

            <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Settings">
                <a class="nav-link" href="/settings/sites">
                    <i class="fas fa-cog"></i>
                    <span class="sr-only">{{__('Settings')}}</span>
                </a>
            </li>
        </ul>

        <a class="offcanvas__toggler offcanvas__toggler--primary" href="#" v-on:click="toggleOffcanvas" data-toggle="tooltip" data-placement="right" title="Stores">
            <i class="fas fa-th"></i>
            <span class="sr-only">{{__('Stores')}}</span>
        </a>
    </div>
</io-sidebar>
