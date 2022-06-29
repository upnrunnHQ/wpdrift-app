
/*
 |--------------------------------------------------------------------------
 | Laravel Spark Components
 |--------------------------------------------------------------------------
 |
 | Here we will load the Spark components which makes up the core client
 | application. This is also a convenient spot for you to load all of
 | your components that you write while building your applications.
 */

require('./../spark-components/bootstrap');
require('./layouts/sidebar');
require('./layouts/main');
require('./layouts/offcanvas');
require('./dashboard/board');
require('./navbar/navbar-brand');
require('./navbar/navbar-search');

require('./map');

require('./paginate');
