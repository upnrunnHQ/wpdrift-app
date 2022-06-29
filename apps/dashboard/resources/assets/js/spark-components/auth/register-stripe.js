var base = require('auth/register-stripe');

Vue.component('spark-register-stripe', {
    mixins: [base],
    created() {
        if ( ( typeof Spark.autofill.email !== 'undefined' ) && Spark.autofill.email ) {
          this.registerForm.email = Spark.autofill.email;
        }
    }
});
