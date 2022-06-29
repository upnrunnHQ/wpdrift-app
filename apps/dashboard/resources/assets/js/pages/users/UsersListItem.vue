<template lang="html">
    <tr>
        <td>
            <div class="customer-details">
                <router-link :to="{ path: `/users/${item.id}` }">
                  <img v-if="item.has_avatar" class="avatar" :src="item.avatar_urls['48']" alt="">
                  <div class="text-avatar" v-bind:style="{ backgroundColor: getRandomColor }" v-if="!item.has_avatar">
                      <span>{{ item.first_name.charAt(0) }}</span>
                      <span>{{ item.last_name.charAt(0) }}</span>
                  </div>
                </router-link>
                <div class="details">
                    <span class="full-name"><router-link :to="{ path: `/users/${item.id}` }">{{ item.first_name }} {{ item.last_name }}</router-link></span>
                    <span class="id">#{{ item.id }}</span>
                    <span class="joined">Joined {{ item.joined_date }}</span>
                </div>
            </div>
        </td>
        <td>
            <div class="customer-details">
                <div class="details">
                    <span class="email"><a :href="`mailto:${item.email}`">{{ item.email }}</a></span>
                </div>
            </div>
        </td>
        <td>
            <div v-if="item.ip_data" class="customer-details location">
              <span v-bind:class="['mr-2', 'mt-2', 'flag-icon', 'flag-icon-' + item.ip_data.countryCode.toLowerCase()]"></span>
              <div class="details">
                <span class="city">{{ item.ip_data.city }}</span>
                <span class="country">{{ item.ip_data.country }}</span>
              </div>
            </div>

            <div v-else>
              Unknown
            </div>
        </td>
        <td>
            <div class="customer-details">
                <div v-if="item.last_login.time_diff" class="details">
                    <span>{{ item.last_login.time_diff }}</span>
                </div>
                <div v-else>
                  Never active
                </div>
            </div>
        </td>
    </tr>
</template>

<script>
export default {
  props: ['item'],
  computed: {
    getRandomColor() {
      var letters = '0123456789ABCDEF';
      var color = '#';
      for (var i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
      }
      return color;
    },
  },
};
</script>

<style lang="css">
</style>
