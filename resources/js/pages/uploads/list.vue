<template>
  <div class="alert alert-success">
  	Hello, from lists!
  </div>
</template>

<script>
export default {
  scrollToTop: false,

  metaInfo () {
    return { title: this.$t('upload_list') }
  }
}
</script>