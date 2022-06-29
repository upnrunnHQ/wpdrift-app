import Vue from "vue";
import Router from "vue-router";
import VueBreadcrumbs from "vue-breadcrumbs";
import titleMixin from "./../mixin/titleMixin";

Vue.use(Router);
Vue.use(VueBreadcrumbs);
Vue.mixin(titleMixin);

import BlogDashboard from "./../components/dashboard/Dashboard";

import ProductsList from "./../pages/products/ProductsList";
import ProductsItem from "./../pages/products/single/ProductsItem";

import PaymentsList from "./../pages/payments/PaymentsList";
import PaymentsItem from "./../pages/payments/single/PaymentsItem";

import UsersList from "./../pages/users/UsersList";
import UsersItem from "./../pages/users/single/UsersItem";

import CustomersList from "./../pages/customers/CustomersList";
import CustomersItem from "./../pages/customers/single/CustomersItem";

const routes = [
    {
        path: "/",
        component: BlogDashboard,
        meta: {
            title: "Dashboard | WPdrift"
        }
    },
    {
        path: "/users",
        component: UsersList,
        meta: {
            title: "Users | WPdrift"
        }
    },
    {
        path: "/users/:id",
        component: UsersItem,
        meta: {
            title: "User | WPdrift"
        }
    },
    {
        path: "/products",
        component: ProductsList,
        meta: {
            title: "Products | WPdrift"
        }
    },
    {
        path: "/products/:id",
        component: ProductsItem,
        meta: {
            title: "Product | WPdrift"
        }
    },
    {
        path: "/orders",
        component: PaymentsList,
        meta: {
            title: "Orders | WPdrift"
        }
    },
    {
        path: "/orders/:id",
        component: PaymentsItem,
        meta: {
            title: "Order | WPdrift"
        }
    },
    {
        path: "/customers",
        component: CustomersList,
        meta: {
            title: "EDD Customers | WPdrift"
        }
    },
    {
        path: "/customers/:id",
        component: CustomersItem,
        meta: {
            title: "Customer | WPdrift"
        }
    }
];

const router = new Router({routes});

// This callback runs before every route change, including on page load.
router.beforeEach((to, from, next) => {
    // This goes through the matched routes from last to first, finding the closest route with a title.
    // eg. if we have /some/deep/nested/route and /some, /deep, and /nested have titles, nested's will be chosen.
    const nearestWithTitle = to.matched
        .slice()
        .reverse()
        .find(r => r.meta && r.meta.title);

    // Find the nearest route element with meta tags.
    const nearestWithMeta = to.matched
        .slice()
        .reverse()
        .find(r => r.meta && r.meta.metaTags);
    const previousNearestWithMeta = from.matched
        .slice()
        .reverse()
        .find(r => r.meta && r.meta.metaTags);

    // If a route with a title was found, set the document (page) title to that value.
    if (nearestWithTitle) document.title = nearestWithTitle.meta.title;

    // Remove any stale meta tags from the document using the key attribute we set below.
    Array.from(document.querySelectorAll("[data-vue-router-controlled]")).map(
        el => el.parentNode.removeChild(el)
    );

    // Skip rendering meta tags if there are none.
    if (!nearestWithMeta) return next();

    // Turn the meta tag definitions into actual elements in the head.
    nearestWithMeta.meta.metaTags
        .map(tagDef => {
            const tag = document.createElement("meta");

            Object.keys(tagDef).forEach(key => {
                tag.setAttribute(key, tagDef[key]);
            });

            // We use this to track which meta tags we create, so we don't interfere with other ones.
            tag.setAttribute("data-vue-router-controlled", "");

            return tag;
        })
        // Add the meta tags to the document head.
        .forEach(tag => document.head.appendChild(tag));

    next();
});

export default router;
