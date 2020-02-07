<template>
  <card :title="'Progress of \'' + job.title +'\''">
    <table v-if="job">
      <tr>
        <td>{{ $t('status') }}</td>
        <td class="colon">
          :
        </td>
        <td>{{ job.status }}</td>
      </tr>
      <tr>
        <td>{{ $t('seq_mapper') }}</td>
        <td class="colon">
          :
        </td>
        <td>{{ job.mapper }}</td>
      </tr>
      <tr>
        <td>{{ $t('seq_snp_caller') }}</td>
        <td class="colon">
          :
        </td>
        <td>{{ job.caller }}</td>
      </tr>
      <tr>
        <td>{{ $t('start_date') }}</td>
        <td class="colon">
          :
        </td>
        <td>{{ job.submitted_at }}</td>
      </tr>
      <tr>
        <td>{{ $t('finish_date') }}</td>
        <td class="colon">
          :
        </td>
        <td>{{ job.finished_at }}</td>
      </tr>
      <tr v-if="progress < 100">
        <td>Current progress</td>
        <td class="colon">
          :
        </td>
        <td v-if="job_process.length > 0">
          {{ $t(job_process[job_process.length-1].process) }}
        </td>
      </tr>
    </table>
    <v-button type="default" native-type="button" class="btn-sm col-md-12 collapsed" style="margin-bottom: 10px;" data-toggle="collapse" :data-target="'#jobConfig'">
      {{ $t('job_config') }} <fa icon="caret-square-down" fixed-width />
    </v-button>
    <div :id="'jobConfig'" class="collapse">
      <div class="output-message" v-html="config" />
    </div><br>
    <div class="progress">
      <div class="progress-bar progress-bar-striped" :class="{'bg-success': (job.status == 'FINISHED'), 'bg-warning': (job.status == 'CANCELED'), 'bg-danger': (job.status == 'ERROR')}" role="progressbar" :style="{width: progress + '%'}" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
        {{ progress }}%
      </div>
    </div>
    <div v-if="job.status != 'ERROR'" id="button" style="margin-top: 5px;text-align:right">
      <span v-if="job.status == 'FINISHED'">
        <router-link :to="{ name: 'explore', query: {job: job_id}}"><button type="button" class="btn btn-primary">{{ $t('explore_snp') }}</button></router-link>
        <a :href="'/file/show/stats/' + job.id" target="_blank"><v-button type="success" native-type="button">{{ $t('snp_stat') }}</v-button></a>
      </span>
      <button v-else-if="job.status == 'CANCELED'" class="btn btn-info" type="button" @click="resumeJob()">
        {{ $t('resume') }}
      </button>
      <button v-else class="btn btn-danger" type="button" @click="cancelJob()">
        {{ $t('cancel') }}
      </button>
    </div>
    <div v-else style="margin-top: 5px;text-align:center" class="alert alert-danger">
      Unfortunately, an error has been occured. We are very sorry for this to be happened.<br> We'll try our best to fix this problem ASAP.
    </div>
    <!-- {{ job }} -->
    <div v-for="(jp, index) in job_process" :key="index">
      <div class="card border-primary mb-1 mt-3">
        <div class="card-header text-white bg-primary">
          {{ $t(jp.process) }}
        </div>
        <div class="card-body text-dark">
          <div class="process-status">
            <table>
              <tr>
                <td>{{ $t('status' ) }}</td>
                <td class="colon">
                  :
                </td>
                <td>{{ jp.status }}</td>
              </tr>
              <tr>
                <td>{{ $t('start_date') }}</td>
                <td class="colon">
                  :
                </td>
                <td>{{ jp.submitted_at }}</td>
              </tr>
              <tr>
                <td>{{ $t('finish_date') }}</td>
                <td class="colon">
                  :
                </td>
                <td>{{ jp.finished_at }}</td>
              </tr>
              <tr>
                <td>{{ $t('output_file') }}</td>
                <td class="colon">
                  :
                </td>
                <td><a :href="'/file/dl/process_output/' + jp.id" target="_blank">{{ baseName(jp.output) }}</a> ({{ jp.file_size }})</td>
              </tr>
            </table>
          </div>
          <v-button type="secondary" native-type="button" class="btn-sm col-md-12 collapsed" data-toggle="collapse" :data-target="'#outputMessage' + index">
            {{ $t('output_message') }} <fa icon="caret-square-down" fixed-width />
          </v-button>
          <div :id="'outputMessage' + index" class="collapse">
            <div class="output-message">
              <pre><code>{{ jp.program_message }}</code></pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </card>
</template>

<script>
import Vue from 'vue'
import axios from 'axios'
import swal from 'sweetalert2'

export default {
  scrollToTop: false,

  data () {
    return {
      socket_progress: '',
      job_process_progress: '',
      progress: 0,
      job: {},
      job_process: [],
      job_id: null,
      config: ''
    }
  },

  created () {
    // this.$socket.emit('join', 1)
    this.job_id = this.$route.params.id
    this.checkProgress()
    this.job_process_progress = setInterval(this.checkProgress, 5000)
  },

  beforeDestroy () {
    clearInterval(this.job_process_progress)
  },

  methods: {
    async checkProgress (refreshed = true) {
      try {
        const { data } = await axios.get('/api/jobs/' + this.job_id + '/process')
        const maxProcess = window.config.processType.length
        if (refreshed == true && data.process.length > 0) {
          if (data.process.length == maxProcess - 1 || data.job.status == 'FINISHED') {
            this.progress = 100
          } else {
            this.progress = parseInt((data.process.length / maxProcess) * 100)
          }
        }

        this.job = data.job
        this.job_process = data.process
        this.config = data.config
      } catch (e) {
        // console.error(e)
        if (e.response.status === 404 || e.response.status === 500) {
          this.$router.go(-1)
        }
      }
    },

    async cancelJob () {
      let result = await swal({
        title: 'Are you sure?',
        text: 'Current running job will be stopped',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, cancel it!',
        cancelButtonText: 'No, keep job running'
      })

      if (result.value) {
        try {
          await axios.patch('/api/jobs/' + this.job_id + '/cancel')
          this.checkProgress(false)
          swal(
            'Canceled!',
            'Your jobs has been canceled',
            'success'
          )
        } catch (e) {
          console.error(e)
        }
      }
    },

    async resumeJob () {
      let result = await swal({
        title: 'Are you sure?',
        text: 'Current job will be resume',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, resume it!',
        cancelButtonText: 'No, keep job stopped'
      })

      if (result.value) {
        try {
          await axios.patch('/api/jobs/' + this.job_id + '/resume')
          this.checkProgress(false)
          swal(
            'Resumed!',
            'Your jobs has been resumed',
            'success'
          )
        } catch (e) {
          console.error(e)
        }
      }
    },

    baseName (str) {
      if (str == null) return
      var base = new String(str).substring(str.lastIndexOf('/') + 1)
      return base
    }

  },

  metaInfo () {
    return { title: this.$t('jobs') }
  }
}
</script>

<style scoped>
	td {
		vertical-align: bottom;
	}

	.colon{
		padding-left: 0.5em;
		padding-right: 1em;
	}

	.output-message{
		margin-left: 10px;
		margin-right: 10px;
		max-height: 150px;
		overflow: auto;
	}

	.process-status{
		margin-bottom: 20px;
	}

	pre {
		margin-top: 10px;
	}

	code {
		font-size: 12px;
	}
</style>
