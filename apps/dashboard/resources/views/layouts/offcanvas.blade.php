<io-offcanvas
    :user="user"
    :teams="teams"
    :current-team="currentTeam"
    inline-template>
    <div class="offcanvas" v-bind:class="{ display: displayOffcanvas }">
        <div class="offcanvas__body">
            <ul class="options list-group list-group-flush">
                <li v-if="hasCompanies" class="option option--current-site list-group-item px-0">
                    <div class="dropdown">
                        <button class="btn btn-block btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="dropdown-toggle__title">@{{selectedCompany.name}} #@{{selectedCompany.id}}</span>
                            <span class="dropdown-toggle__description">@{{selectedCompany.description}}</span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a v-for="(company, index) in companies" v-on:click="updateCompany(index)" class="dropdown-item" href="#">
                                <span class="dropdown-item__title">@{{company.name}} #@{{company.id}}</span>
                                <span class="dropdown-item__description">@{{company.description}}</span>
                            </a>
                        </div>
                    </div>
                    <div class="sites">
                        <div class="row">
                            <div class="col-md-6" v-for="store in stores">
                                <a href="#" class="site" v-bind:class="{ active: defaultStore == store.id }" v-on:click="setDefaultStore(store)">
                                    <div class="site__icon">
                                        <span v-if="null == store.photo_url" class="icomoon-Woo_Commerce"></span>
                                        <img v-else v-bind:src="store.photo_url">
                                    </div>
                                    <div class="site__title">
                                        <span>@{{store.name}}</span>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-6">
                                <a href="/settings/sites/create" class="site">
                                    <div class="site__icon">
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <div class="site__title">
                                        <span>Add store</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="option option--enable-edd list-group-item d-flex justify-content-between align-items-center px-0">
                    <span>All Sites</span>
                    <a class="btn btn-sm btn-default" href="/settings/sites/">View</a>
                </li>
            </ul>
        </div>

        <a href="#" class="offcanvas__toggler offcanvas__toggler--secondary" v-on:click="hideOffcanvas">
            <span class="icon dripicons-chevron-left"></span>
            <span>Hide controls</span>
        </a>
    </div>
</io-offcanvas>
