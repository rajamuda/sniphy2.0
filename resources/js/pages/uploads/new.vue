<template>
	<card :title="$t('upload_new')">
	  <div class="alert alert-info">
	  	<fa icon="info-circle" fixed-width/> You can only upload FASTA (<b>.fa or .fasta</b>) or FASTQ (<b>.fastq</b>) format 
	  </div>
	  <div>
	  	<uploader targetUrl="/api/upload/do-upload">
	  	</uploader>
	  </div>
	</card>
</template>

<script>
export default {
  scrollToTop: false,

	metaInfo () {
    return { title: this.$t('upload_new') }
  },
}
</script>

<style lang="scss" scoped>

</style>