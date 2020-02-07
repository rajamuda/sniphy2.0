<template>
	<div class="container">
		<form :action="targetUrl" method="POST">
		</form>
	  <div class="row" v-if="resumable.support">
        <div class="col-lg-offset-2 col-lg-8">
	        	<span v-if="uploadStatus === null">
	            <button v-if="resumable.files.length === 0" type="button" class="btn btn-success" id="add-file-btn" ref="addFile">
	              <fa icon='plus-circle' fixed-width/> Add file
	            </button>
	            <button v-else type="button" class="btn btn-secondary" id="remove-file-btn" ref="removeFile" @click="remove">
	              <fa icon='sync-alt' fixed-width/> Change file
	            </button>
	          </span>

            <span v-if="uploadStatus === null || uploadStatus === 'paused'">
	            <button v-if="progressBar <= 0" type="button" class="btn btn-primary" id="start-upload-btn" @click="startUpload">
	              <fa icon='upload' fixed-width/>
	              	Start upload
	            </button>
	            <button v-else type="button" class="btn btn-primary" id="start-upload-btn" @click="startUpload">
	              <fa icon='upload' fixed-width/>
	              	Resume upload
	            </button>
	          </span>
	          <span v-else-if="uploadStatus === 'progress'">
	            <button type="button" class="btn btn-warning" id="pause-upload-btn" @click="pauseUpload">
	              <fa icon='pause-circle' fixed-width/> Pause upload
	            </button>
	            <button type="button" class="btn btn-danger" id="cancel-upload-btn" @click="cancelUpload">
	              <fa icon='times-circle' fixed-width/> Cancel upload
	            </button>
	          </span>
	          <span v-else>
	          	<div class="alert" :class="{ 'alert-success': (uploadStatus) === 'success', 'alert-danger': (uploadStatus === 'failed') }">
	          		<span v-if="uploadStatus === 'success'"> <fa icon='check-circle' fixed-width/> Success uploading files! </span>
	          		<span v-if="uploadStatus === 'failed'"> <fa icon='times-circle' fixed-width/> Failed uploading files! Please, try again in another minutes. It might be there is server issue or your current network connection is unstable. </span>
	          	</div>
	          </span>
        </div>
 
 
        <div class="col-lg-offset-2 col-lg-8" :class="{ 'd-none': (progressBar === null) }" >
          <p>
            <div class="progress" id="upload-progress">
              <div class="progress-bar progress-bar-striped" role="progressbar" 
          			:class="{ 'bg-success': (uploadStatus === 'success'), 'bg-danger': (uploadStatus === 'failed'), 'bg-primary': (uploadStatus === null), 'bg-warning': (uploadStatus === 'paused') }"
          			:style="{ width: progressBar + '%' }">
                <span class="sr-only"></span>
              </div>
            </div>
            <table v-if="resumable.files.length > 0" class="table table-stripped">
            	<tr>
            		<td>File name</td>
            		<td>{{ resumable.files[0].fileName }}</td>
            	</tr>
            	<tr>
            		<td>File size</td>
            		<td>{{ fileSize }}
            		</td>
            	</tr>
            </table>
          </p>
        </div>
    </div>
    <div class="row" v-else>
    	<h4>Your browser not supported! Please use latest version of browser, e.g. Google Chrome or Firefox</h4>
    </div>
	</div>
</template>

<script>
import Resumable from '~/components/resumable.min.js'

export default {
	name: 'Uploader',

	data: () => ({
		resumable: {},
		targetUrl: null,
		progressBar: null,
		uploadStatus: null, // 'success', 'failed', 'paused', 'progress', null (if not processed or canceled)
	}),

	computed: {
		fileSize() {
			if (this.resumable.files[0].size < 1024) {
				return this.resumable.files[0].size + ' Bytes'

			} else if (this.resumable.files[0].size < (1024 * 1024)) {
				return (this.resumable.files[0].size/(1024)).toFixed(3) + ' KB'

			} else if (this.resumable.files[0].size < (1024 * 1024 * 1024)) {
				return (this.resumable.files[0].size/(1024 * 1024)).toFixed(3) + ' MB'

			} else {
				return (this.resumable.files[0].size/(1024 * 1024 * 1024)).toFixed(3) + ' GB'

			}
		}
	},

  methods: {
  	startUpload () {
  		console.log('uploading')
  		this.uploadStatus = 'progress'
  		this.resumable.upload()
  	},

  	pauseUpload () {
  		console.log('paused')
  		this.uploadStatus = 'paused'
  		if (this.resumable.files.length > 0 && this.resumable.isUploading()) {
      	this.resumable.pause()
      }
  	},

  	remove () {
  		this.resumable.removeFile(this.resumable.files[0])
  		this.progressBar = null
  		this.uplaodStatus = null
  	},

  	fileAdded (file) {
  		if (this.isFileValid(file.fileName)) {
  			this.progressBar = 0
  		} else {
  			this.remove()
  			alert("file tidak valid!")
  		}
  	},

  	fileSuccess (file) {
  		this.progressBar = 100
  		this.uploadStatus = 'success'
  	},

  	fileError (file) {
  		this.uploadStatus = 'failed'
  	},

  	onProgress (progress) {
  		this.progressBar = progress
  	},

  	cancelUpload () {
  		if(this.resumable.files.length > 0 && this.resumable.isUploading()){
	  		this.resumable.cancel()
	  		this.uploadStatus = null
	  		this.progressBar = null
	  		this.remove()
	  	}
  	},

  	isFileValid (filename) {
  		return true
  		// return filename.includes("fa") || filename.includes("fasta") || filename.includes("fastq")
  	}
  },

  mounted () {
  	this.resumable = new Resumable({
				target: '/api/upload/test',
				chunkSize: 50*1024*1024, // 50 MB
				maxFiles: 1
			})

  	if(this.resumable.support){
	  	this.$nextTick(function () {
		  	this.resumable.assignBrowse(this.$refs.addFile)
		  })

	  	var self = this
		  this.resumable.on('fileAdded', function(file, event) {
		  	self.fileAdded(file)
		  	console.log('added')
		  })

		  this.resumable.on('fileSuccess', function(file, message) {
		  	self.fileSuccess(file)
		  	console.log('success')
		  })

		  this.resumable.on('fileError', function(file, message) {
		  	self.fileError(file)
		  	console.log('error')
		  })

		  this.resumable.on('progress', function() {
		  	self.onProgress(self.resumable.progress()*100)
		  	console.log('progress')
		  })
  	}
  }

}
</script>