<template>
  <div>
    <div class="text-center">
      <div class="title">
        {{ title }}
      </div>
      <div class="mb-4">
        {{ $t('tagline') }}
      </div>

    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {

  metaInfo () {
    return { title: this.$t('home') }
  },

  computed: mapGetters({
    authenticated: 'auth/check'
  }),

  data: () => ({
    title: window.config.appName
  })
}
</script>

<style scoped>
.top-right {
  position: absolute;
  right: 10px;
  top: 18px;
}

.title {
  font-size: 7rem;
}
</style>
