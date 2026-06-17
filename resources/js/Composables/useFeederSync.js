import { ref, reactive } from 'vue'
import axios from 'axios'

// Module-level global state (singleton)
const isSyncing = ref(false)
const progress = ref(0)
const processed = ref(0)
const total = ref(0)
const message = ref('')
const status = ref('idle') // 'idle', 'running', 'completed', 'failed'
const type = ref('') // 'pull-prodis', 'pull-dosens', 'pull-matkuls', 'pull-mahasiswas', 'push-mahasiswa', 'push-mahasiswa-kelas'
const showWidget = ref(false)
const stats = reactive({
  matched: 0,
  created: 0,
  failed: 0
})

let isEchoListening = false

export function useFeederSync() {
  
  function initEchoListener() {
    if (isEchoListening) {
      return
    }

    if (!window.Echo) {
      console.warn('Laravel Echo is not defined on window object.')
      return
    }

    window.Echo.channel('feeder-sync')
      .listen('.FeederSyncProgress', (e) => {
        status.value = e.status
        type.value = e.type
        progress.value = e.progress
        processed.value = e.processed
        total.value = e.total
        message.value = e.message
        
        if (e.stats) {
          stats.matched = e.stats.matched || 0
          stats.created = e.stats.created || 0
          stats.failed = e.stats.failed || 0
        }

        if (e.status === 'running') {
          isSyncing.value = true
          showWidget.value = true
        } else if (e.status === 'completed') {
          isSyncing.value = false
          // Auto close after 5 seconds of showing completion status
          setTimeout(() => {
            if (status.value === 'completed') {
              showWidget.value = false
              status.value = 'idle'
            }
          }, 5000)
        } else if (e.status === 'failed') {
          isSyncing.value = false
        }
      })

    isEchoListening = true
  }

  async function triggerSync(syncType, params = {}) {
    let url = ''
    switch (syncType) {
      case 'pull-prodis':
        url = route('v2.admin.feeder.pull-prodis')
        break
      case 'pull-dosens':
        url = route('v2.admin.feeder.pull-dosens')
        break
      case 'pull-matkuls':
        url = route('v2.admin.feeder.pull-matkuls')
        break
      case 'pull-mahasiswas':
        url = route('v2.admin.feeder.pull-mahasiswas')
        break
      case 'push-mahasiswa':
        if (!params.id) {
          throw new Error('ID mahasiswa diperlukan')
        }
        url = route('v2.admin.feeder.push-mahasiswa', { id: params.id })
        break
      case 'push-mahasiswa-kelas':
        if (!params.kelas_id) {
          throw new Error('ID kelas rombel diperlukan')
        }
        url = route('v2.admin.feeder.push-mahasiswa-kelas', { kelas_id: params.kelas_id })
        break
      default:
        throw new Error(`Sync type "${syncType}" is not supported.`)
    }

    // Reset local reactive states before calling endpoint
    status.value = 'running'
    type.value = syncType
    progress.value = 0
    processed.value = 0
    total.value = 0
    message.value = 'Menghubungkan ke server Feeder...'
    stats.matched = 0
    stats.created = 0
    stats.failed = 0
    isSyncing.value = true
    showWidget.value = true

    // Ensure Echo listener is attached
    initEchoListener()

    try {
      const response = await axios.post(url)
      return response.data
    } catch (error) {
      isSyncing.value = false
      status.value = 'failed'
      message.value = error.response?.data?.message || error.message || 'Gagal memulai proses sinkronisasi'
      throw error
    }
  }

  function dismissWidget() {
    showWidget.value = false
    if (status.value === 'completed' || status.value === 'failed') {
      status.value = 'idle'
    }
  }

  return {
    isSyncing,
    progress,
    processed,
    total,
    message,
    status,
    type,
    stats,
    showWidget,
    initEchoListener,
    triggerSync,
    dismissWidget
  }
}
