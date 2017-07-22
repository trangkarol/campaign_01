<template lang="html">
    <div>
        <gmap-map
            :center="center"
            :zoom="7"
            style="width: 500px; height: 300px"
            >
            <gmap-marker
              :key="index"
              v-for="(m, index) in markers"
              :position="m.position"
              :clickable="true"
              :draggable="true"
              @click="center=m.position"
              @dragend="locationUpdated($event)"
            ></gmap-marker>
        </gmap-map>

    </div>
</template>

<script>
import Vue from 'vue'
import * as VueGoogleMaps from 'vue2-google-maps'
import LocationPicker from 'vue-location-picker'

export default {
    data () {

      return {
        place:null,
        center: {lat: 10.0, lng: 10.0},
        markers: [{
          position: {lat: 10.0, lng: 10.0}
        }, {
          position: {lat: 11.0, lng: 11.0}
        }]
      }
    },
    created: function () {
        // this.campaignDetail(this.$route.params.id)
    },
    methods: {
        // ...mapActions('campaign', ['campaignDetail'])
        locationUpdated(event) {
            this.latLng = {
                    lat: event.latLng.lat(),
                    lng: event.latLng.lng(),
                };
                // this.newEvent.latitude = event.latLng.lat();
                // this.newEvent.longitude = event.latLng.lng();
                console.log(event.latLng.lat(), event.latLng.lng())
        }
    },
    components: {
       VueGoogleMaps,
       LocationPicker
    }
}
</script>

<style lang="scss">
</style>
