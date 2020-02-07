<template>
  <div class="row">
    <div class="col-md-3">
      <card :title="$t('upload')" class="upload-card">
        <ul class="nav flex-column nav-pills">
          <li v-for="tab in tabs" class="nav-item">
            <router-link :to="{ name: tab.route }" class="nav-link" active-class="active">
              <fa :icon="tab.icon" fixed-width/>
              {{ tab.name }}
            </router-link>
          </li>
        </ul>
      </card>
    </div>

    <div class="col-md-9">
      <transition name="fade" mode="out-in">
        <router-view/>
      </transition>
    </div>
  </div>
</template>

<script>
export default {
  middleware: 'auth',

  computed: {
    tabs () {
      return [
        {
          icon: 'upload',
          name: this.$t('upload_new'),
          route: 'upload.new'
        },
        {
          icon: 'list',
          name: this.$t('upload_list'),
          route: 'upload.list'
        }
      ]
    }
  }
}
</script>

<style>
.settings-card .card-body {
  padding: 0;
}
</style>
