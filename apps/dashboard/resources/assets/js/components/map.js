import Vue from 'vue';
Vue.component('google-map', {
    props: ['lat', 'lng'],

    template: `
    <div id="map">
    </div>
    `,

    mounted() {
        var uluru = {
            lat: this.lat,
            lng: this.lng
        };
        var map = new google.maps.Map( this.$el, {
            zoom: 10,
            center: uluru
        });
        new google.maps.Marker({
            position: uluru,
            map: map
        });
    }
});
