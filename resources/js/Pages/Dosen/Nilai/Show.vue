<script setup>
import { ref, watch, computed } from 'vue'
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Separator } from '@/Components/ui/separator'
import {
  Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle,
} from '@/Components/ui/dialog'
import {
  BarChart3, BookOpen, Users, Calendar, ChevronLeft, CheckCircle2, AlertCircle,
  Clock, Send, Pencil, PlusCircle, Trash2, ClipboardList, FileText, Sparkles,
  GraduationCap, Award,
} from 'lucide-vue-next'

// ── Props ──────────────────────────────────────────────────────────────────
const props = defineProps({
  jadwal:          { type: Object, required: true },
  mahasiswas:      { type: Array,  default: () => [] },
  mahasiswaAktif:  { type: Array,  default: () => [] },
  tugasGrouped:    { type: Object, default: () => ({}) },
  jumlahTugas:     { type: Number, default: 0 },
  utss:            { type: Object, default: () => ({}) },
  uass:            { type: Object, default: () => ({}) },
  etikas:          { type: Object, default: () => ({}) },
  aktifs:          { type: Object, default: () => ({}) },
  dataAbsensi:     { type: Object, default: () => ({}) },
  totalPertemuan:  { type: Number, default: 0 },
  nilaiAkhir:      { type: Object, default: () => ({}) },
  completionStatus:{ type: Object, default: () => ({}) },
  pengajuan:       { type: Object, default: null },
  kaprodi:         { type: Object, default: null },
  wadir:           { type: Object, default: null },
})

const page = usePage()

// ── Toast ──────────────────────────────────────────────────────────────────
const showToast = ref(false)
const toastMsg  = ref('')
const toastType = ref('success')

watch(() => page.props.flash, (flash) => {
  if (flash?.success) { toastMsg.value = flash.success; toastType.value = 'success'; showToast.value = true }
  if (flash?.error)   { toastMsg.value = flash.error;   toastType.value = 'error';   showToast.value = true }
  if (showToast.value) setTimeout(() => { showToast.value = false }, 3500)
}, { deep: true, immediate: true })

watch(() => page.props.errors, (errors) => {
  if (errors && Object.keys(errors).length > 0) {
    const firstKey = Object.keys(errors)[0]
    toastMsg.value = errors[firstKey] || 'Gagal menyimpan data. Silakan periksa kembali.'
    toastType.value = 'error'
    showToast.value = true
    setTimeout(() => { showToast.value = false }, 4000)
  }
}, { deep: true })

// ── Tab State ──────────────────────────────────────────────────────────────
const tabs = [
  { key: 'tugas',  label: 'Tugas',     icon: ClipboardList },
  { key: 'uts',    label: 'UTS',       icon: BookOpen },
  { key: 'uas',    label: 'UAS',       icon: BookOpen },
  { key: 'etika',  label: 'Etika',     icon: Award },
  { key: 'aktif',  label: 'Keaktifan', icon: Users },
  { key: 'rekap',  label: 'Rekap Nilai', icon: BarChart3 },
]
const activeTab = ref('tugas')

// ── Initial State Tracking for Dirty Check ─────────────────────────────────
const initialTugasNilai = ref([])
const initialUtsNilai   = ref([])
const initialUasNilai   = ref([])
const initialEtikaNilai = ref([])
const initialAktifNilai = ref([])

const isTugasDirty = computed(() => JSON.stringify(tugasForm.nilai) !== JSON.stringify(initialTugasNilai.value))
const isUtsDirty   = computed(() => JSON.stringify(utsForm.nilai) !== JSON.stringify(initialUtsNilai.value))
const isUasDirty   = computed(() => JSON.stringify(uasForm.nilai) !== JSON.stringify(initialUasNilai.value))
const isEtikaDirty = computed(() => JSON.stringify(etikaForm.nilai) !== JSON.stringify(initialEtikaNilai.value))
const isAktifDirty = computed(() => JSON.stringify(aktifForm.nilai) !== JSON.stringify(initialAktifNilai.value))

const handleTugasOpenChange = (open) => {
  if (!open) {
    if (isTugasDirty.value && !confirm('Perubahan nilai belum disimpan. Tutup form dan buang perubahan?')) {
      return
    }
    showTugasDialog.value = false
  } else {
    showTugasDialog.value = true
  }
}

const handleUtsOpenChange = (open) => {
  if (!open) {
    if (isUtsDirty.value && !confirm('Perubahan nilai belum disimpan. Tutup form dan buang perubahan?')) {
      return
    }
    showUtsDialog.value = false
  } else {
    showUtsDialog.value = true
  }
}

const handleUasOpenChange = (open) => {
  if (!open) {
    if (isUasDirty.value && !confirm('Perubahan nilai belum disimpan. Tutup form dan buang perubahan?')) {
      return
    }
    showUasDialog.value = false
  } else {
    showUasDialog.value = true
  }
}

const handleEtikaOpenChange = (open) => {
  if (!open) {
    if (isEtikaDirty.value && !confirm('Perubahan nilai belum disimpan. Tutup form dan buang perubahan?')) {
      return
    }
    showEtikaDialog.value = false
  } else {
    showEtikaDialog.value = true
  }
}

const handleAktifOpenChange = (open) => {
  if (!open) {
    if (isAktifDirty.value && !confirm('Perubahan nilai belum disimpan. Tutup form dan buang perubahan?')) {
      return
    }
    showAktifDialog.value = false
  } else {
    showAktifDialog.value = true
  }
}

// ── Helpers ────────────────────────────────────────────────────────────────
const getNilai = (map, mhsId) => map?.[mhsId]?.nilai ?? '-'

const getAbsensi = (mhsId) => {
  const a = props.dataAbsensi?.[mhsId]
  return a ? `${a.total_kehadiran}/${props.totalPertemuan} (${a.persentase_kehadiran}%)` : '-'
}

const getTugasAvg = (mhsId) => {
  const list = props.tugasGrouped?.[mhsId]
  if (!list || list.length === 0) return '-'
  const avg = list.reduce((s, t) => s + Number(t.nilai), 0) / list.length
  return avg.toFixed(1)
}

const nilaiHurufClass = (huruf) => {
  if (['A', 'A-'].includes(huruf)) return 'bg-emerald-100 text-emerald-800'
  if (['B+', 'B', 'B-'].includes(huruf)) return 'bg-blue-100 text-blue-800'
  if (['C+', 'C', 'C-'].includes(huruf)) return 'bg-amber-100 text-amber-800'
  return 'bg-red-100 text-red-800'
}

// Daftar tugas_ke yang sudah ada
const tugasKes = computed(() => {
  const all = new Set()
  Object.values(props.tugasGrouped).forEach(list => list.forEach(t => all.add(t.tugas_ke)))
  return [...all].sort((a, b) => a - b)
})

// ── TUGAS — Form State ─────────────────────────────────────────────────────
const showTugasDialog = ref(false)
const editTugasKe = ref(null)   // null = create, number = edit
const showDeleteTugasDialog = ref(false)
const deleteTugasKe = ref(null)

const tugasForm = useForm({
  tugas_ke:      1,
  mahasiswas_id: [],
  nilai:         [],
})

const openTugasCreate = () => {
  editTugasKe.value = null
  tugasForm.tugas_ke = (tugasKes.value.length > 0 ? Math.max(...tugasKes.value) + 1 : 1)
  tugasForm.mahasiswas_id = props.mahasiswaAktif.map(m => m.id)
  tugasForm.nilai = props.mahasiswaAktif.map(() => '')
  initialTugasNilai.value = JSON.parse(JSON.stringify(tugasForm.nilai))
  showTugasDialog.value = true
}

const openTugasEdit = (tugasKe) => {
  editTugasKe.value = tugasKe
  tugasForm.tugas_ke = tugasKe
  tugasForm.mahasiswas_id = props.mahasiswaAktif.map(m => m.id)
  tugasForm.nilai = props.mahasiswaAktif.map(m => {
    const tList = props.tugasGrouped?.[m.id] ?? []
    const found = tList.find(t => t.tugas_ke === tugasKe)
    return found ? found.nilai : ''
  })
  initialTugasNilai.value = JSON.parse(JSON.stringify(tugasForm.nilai))
  showTugasDialog.value = true
}

const submitTugas = () => {
  if (editTugasKe.value !== null) {
    tugasForm.put(route('v2.dosen.nilai.tugas.update', [props.jadwal.id, editTugasKe.value]), {
      onSuccess: () => {
        initialTugasNilai.value = JSON.parse(JSON.stringify(tugasForm.nilai))
        showTugasDialog.value = false
      },
    })
  } else {
    tugasForm.post(route('v2.dosen.nilai.tugas.store', props.jadwal.id), {
      onSuccess: () => {
        initialTugasNilai.value = JSON.parse(JSON.stringify(tugasForm.nilai))
        showTugasDialog.value = false
      },
    })
  }
}

const deleteTugas = (tugasKe) => {
  deleteTugasKe.value = tugasKe
  showDeleteTugasDialog.value = true
}

const submitDeleteTugas = () => {
  if (deleteTugasKe.value === null) return
  router.delete(route('v2.dosen.nilai.tugas.destroy', [props.jadwal.id, deleteTugasKe.value]), {
    onSuccess: () => {
      showDeleteTugasDialog.value = false
      deleteTugasKe.value = null
    }
  })
}

// ── UTS — Form State ───────────────────────────────────────────────────────
const showUtsDialog = ref(false)

const utsForm = useForm({
  mahasiswas_id: [],
  nilai: [],
})

const openUts = () => {
  const hasData = Object.keys(props.utss).length > 0
  utsForm.mahasiswas_id = props.mahasiswaAktif.map(m => m.id)
  utsForm.nilai = props.mahasiswaAktif.map(m => props.utss?.[m.id]?.nilai ?? '')
  initialUtsNilai.value = JSON.parse(JSON.stringify(utsForm.nilai))
  showUtsDialog.value = true
}

const submitUts = () => {
  const hasData = Object.keys(props.utss).length > 0
  if (hasData) {
    utsForm.put(route('v2.dosen.nilai.uts.update', props.jadwal.id), {
      onSuccess: () => {
        initialUtsNilai.value = JSON.parse(JSON.stringify(utsForm.nilai))
        showUtsDialog.value = false
      },
    })
  } else {
    utsForm.post(route('v2.dosen.nilai.uts.store', props.jadwal.id), {
      onSuccess: () => {
        initialUtsNilai.value = JSON.parse(JSON.stringify(utsForm.nilai))
        showUtsDialog.value = false
      },
    })
  }
}

// ── UAS — Form State ───────────────────────────────────────────────────────
const showUasDialog = ref(false)

const uasForm = useForm({
  mahasiswas_id: [],
  nilai: [],
})

const openUas = () => {
  uasForm.mahasiswas_id = props.mahasiswaAktif.map(m => m.id)
  uasForm.nilai = props.mahasiswaAktif.map(m => props.uass?.[m.id]?.nilai ?? '')
  initialUasNilai.value = JSON.parse(JSON.stringify(uasForm.nilai))
  showUasDialog.value = true
}

const submitUas = () => {
  const hasData = Object.keys(props.uass).length > 0
  if (hasData) {
    uasForm.put(route('v2.dosen.nilai.uas.update', props.jadwal.id), {
      onSuccess: () => {
        initialUasNilai.value = JSON.parse(JSON.stringify(uasForm.nilai))
        showUasDialog.value = false
      },
    })
  } else {
    uasForm.post(route('v2.dosen.nilai.uas.store', props.jadwal.id), {
      onSuccess: () => {
        initialUasNilai.value = JSON.parse(JSON.stringify(uasForm.nilai))
        showUasDialog.value = false
      },
    })
  }
}

// ── ETIKA — Form State ─────────────────────────────────────────────────────
const showEtikaDialog = ref(false)

const etikaForm = useForm({
  mahasiswas_id: [],
  nilai: [],
})

const openEtika = () => {
  etikaForm.mahasiswas_id = props.mahasiswaAktif.map(m => m.id)
  etikaForm.nilai = props.mahasiswaAktif.map(m => props.etikas?.[m.id]?.nilai ?? '')
  initialEtikaNilai.value = JSON.parse(JSON.stringify(etikaForm.nilai))
  showEtikaDialog.value = true
}

const submitEtika = () => {
  const hasData = Object.keys(props.etikas).length > 0
  if (hasData) {
    etikaForm.put(route('v2.dosen.nilai.etika.update', props.jadwal.id), {
      onSuccess: () => {
        initialEtikaNilai.value = JSON.parse(JSON.stringify(etikaForm.nilai))
        showEtikaDialog.value = false
      },
    })
  } else {
    etikaForm.post(route('v2.dosen.nilai.etika.store', props.jadwal.id), {
      onSuccess: () => {
        initialEtikaNilai.value = JSON.parse(JSON.stringify(etikaForm.nilai))
        showEtikaDialog.value = false
      },
    })
  }
}

// ── AKTIF — Form State ─────────────────────────────────────────────────────
const showAktifDialog = ref(false)

const aktifForm = useForm({
  mahasiswas_id: [],
  nilai: [],
})

const openAktif = () => {
  aktifForm.mahasiswas_id = props.mahasiswaAktif.map(m => m.id)
  aktifForm.nilai = props.mahasiswaAktif.map(m => props.aktifs?.[m.id]?.nilai ?? '')
  initialAktifNilai.value = JSON.parse(JSON.stringify(aktifForm.nilai))
  showAktifDialog.value = true
}

const submitAktif = () => {
  const hasData = Object.keys(props.aktifs).length > 0
  if (hasData) {
    aktifForm.put(route('v2.dosen.nilai.aktif.update', props.jadwal.id), {
      onSuccess: () => {
        initialAktifNilai.value = JSON.parse(JSON.stringify(aktifForm.nilai))
        showAktifDialog.value = false
      },
    })
  } else {
    aktifForm.post(route('v2.dosen.nilai.aktif.store', props.jadwal.id), {
      onSuccess: () => {
        initialAktifNilai.value = JSON.parse(JSON.stringify(aktifForm.nilai))
        showAktifDialog.value = false
      },
    })
  }
}

// ── REKAP / PENGAJUAN ─────────────────────────────────────────────────────
const rekapForm = useForm({})

const ajukanRekap = () => {
  if (!confirm('Ajukan rekap nilai untuk diverifikasi? Pastikan semua komponen sudah lengkap.')) return
  rekapForm.post(route('v2.dosen.nilai.rekap.store', props.jadwal.id))
}

const canAjukan = computed(() =>
  props.completionStatus.semua_lengkap && !props.pengajuan
)
</script>

<template>
  <AdminLayout>
    <Head :title="`Nilai — ${jadwal.matkul}`" />

    <div class="space-y-6">
      <!-- Breadcrumb & Header -->
      <div class="flex flex-col gap-3">
        <Link :href="route('v2.dosen.nilai.index')" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-[#4B49AC] transition-colors w-fit">
          <ChevronLeft class="w-4 h-4" />
          Kembali ke Daftar Nilai
        </Link>

        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">{{ jadwal.matkul }}</h1>
            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1.5 text-sm text-slate-500">
              <span class="flex items-center gap-1"><Users class="w-3.5 h-3.5" /> {{ jadwal.kelas }}</span>
              <span class="flex items-center gap-1"><BookOpen class="w-3.5 h-3.5" /> {{ jadwal.prodi }}</span>
              <span class="flex items-center gap-1"><Calendar class="w-3.5 h-3.5" /> {{ jadwal.hari }}</span>
              <span class="flex items-center gap-1"><GraduationCap class="w-3.5 h-3.5" /> SKS: {{ jadwal.sks }}</span>
            </div>
          </div>

          <!-- Status pengajuan -->
          <div class="flex-shrink-0">
            <Badge v-if="pengajuan?.status === 1" class="bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold flex items-center gap-1 px-3 py-1 rounded-full">
              <CheckCircle2 class="w-3.5 h-3.5" /> Nilai Disetujui
            </Badge>
            <Badge v-else-if="pengajuan?.status === 0" class="bg-amber-50 text-amber-700 border border-amber-200 font-semibold flex items-center gap-1 px-3 py-1 rounded-full">
              <Clock class="w-3.5 h-3.5" /> Menunggu Verifikasi
            </Badge>
          </div>
        </div>
      </div>

      <!-- Completion Chips -->
      <Card class="border-none shadow-sm">
        <CardContent class="p-4">
          <div class="flex flex-wrap items-center gap-3">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
              <Sparkles class="w-3.5 h-3.5 text-amber-500" />
              Kelengkapan:
            </span>
            <template v-for="(tab) in tabs.slice(0, 5)" :key="tab.key">
              <button
                @click="activeTab = tab.key"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold border transition-all"
                :class="completionStatus[tab.key]
                  ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
                  : 'bg-slate-50 text-slate-400 border-slate-200 hover:bg-slate-100'"
              >
                <CheckCircle2 v-if="completionStatus[tab.key]" class="w-3 h-3" />
                <AlertCircle v-else class="w-3 h-3" />
                {{ tab.label }}
              </button>
            </template>
            <span class="ml-auto text-xs text-slate-400">
              {{ Object.values(completionStatus).filter(v => v === true).length - (completionStatus.semua_lengkap ? 1 : 0) }}/5 komponen terisi
            </span>
          </div>
        </CardContent>
      </Card>

      <!-- Tab Navigation -->
      <div class="border-b border-slate-200">
        <nav class="flex gap-1 overflow-x-auto pb-px">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            class="flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold border-b-2 transition-all whitespace-nowrap"
            :class="activeTab === tab.key
              ? 'border-[#4B49AC] text-[#4B49AC]'
              : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'"
          >
            <component :is="tab.icon" class="w-4 h-4" />
            {{ tab.label }}
            <span
              v-if="tab.key !== 'rekap' && completionStatus[tab.key]"
              class="w-1.5 h-1.5 rounded-full bg-emerald-500 ml-0.5"
            />
          </button>
        </nav>
      </div>

      <!-- ════════ TAB: TUGAS ════════ -->
      <div v-if="activeTab === 'tugas'" class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-bold text-slate-800">Nilai Tugas</h2>
          <Button
            class="bg-[#4B49AC] hover:bg-[#3f3e91] text-white flex items-center gap-1.5"
            @click="openTugasCreate"
          >
            <PlusCircle class="w-4 h-4" />
            Tambah Tugas
          </Button>
        </div>

        <!-- Tugas empty state -->
        <Card v-if="tugasKes.length === 0" class="border-dashed border-2 bg-transparent">
          <CardContent class="p-10 text-center">
            <ClipboardList class="w-10 h-10 text-slate-300 mx-auto mb-2" />
            <p class="text-slate-500 text-sm">Belum ada data nilai tugas. Klik "Tambah Tugas" untuk mulai mengisi.</p>
          </CardContent>
        </Card>

        <!-- Tampilan Spreadsheet Matrix untuk Nilai Tugas -->
        <Card v-else class="border-none shadow-sm overflow-hidden bg-white">
          <CardContent class="p-0">
            <div class="overflow-x-auto">
              <table class="w-full text-sm border-collapse min-w-[700px]">
                <thead class="bg-slate-50 border-b border-slate-100">
                  <tr>
                    <!-- Kolom Identitas Sticky -->
                    <th class="sticky left-0 bg-[#F9FAFB] z-20 text-left px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider w-[60px] border-r border-slate-100/50">No</th>
                    <th class="sticky left-[60px] bg-[#F9FAFB] z-20 text-left px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider w-[120px] border-r border-slate-100/50">NIM</th>
                    <th class="sticky left-[180px] bg-[#F9FAFB] z-20 text-left px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider min-w-[200px] border-r border-slate-100">Nama Mahasiswa</th>
                    
                    <!-- Kolom Dinamis per Tugas_Ke -->
                    <th v-for="ke in tugasKes" :key="ke" class="text-center px-4 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-slate-100 min-w-[120px] group/col relative">
                      <div class="flex flex-col items-center justify-between gap-1.5 h-full min-h-[55px]">
                        <span class="text-slate-700 font-extrabold text-xs">Tugas {{ ke }}</span>
                        <!-- Aksi header: Edit & Hapus -->
                        <div class="flex gap-1 opacity-80 group-hover/col:opacity-100 transition-opacity">
                          <button 
                            @click="openTugasEdit(ke)"
                            class="p-1 text-slate-500 hover:text-[#4B49AC] hover:bg-slate-200/50 rounded transition-colors"
                            title="Edit Nilai Tugas"
                          >
                            <Pencil class="w-3 h-3" />
                          </button>
                          <button 
                            @click="deleteTugas(ke)"
                            class="p-1 text-slate-400 hover:text-[#FF4747] hover:bg-red-50 rounded transition-colors"
                            title="Hapus Tugas"
                          >
                            <Trash2 class="w-3 h-3" />
                          </button>
                        </div>
                      </div>
                    </th>
                    
                    <!-- Kolom Rerata -->
                    <th class="text-center px-5 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider min-w-[100px] bg-indigo-50/20">Rerata</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="(mhs, idx) in mahasiswas" :key="mhs.id" class="hover:bg-slate-50/50 transition-colors group">
                    <td class="sticky left-0 bg-white group-hover:bg-[#F9FAFB] transition-colors z-10 px-5 py-3.5 text-slate-400 text-xs border-r border-slate-100/50">{{ idx + 1 }}</td>
                    <td class="sticky left-[60px] bg-white group-hover:bg-[#F9FAFB] transition-colors z-10 px-5 py-3.5 font-mono text-xs text-slate-600 border-r border-slate-100/50">{{ mhs.nim }}</td>
                    <td class="sticky left-[180px] bg-white group-hover:bg-[#F9FAFB] transition-colors z-10 px-5 py-3.5 text-slate-800 font-medium border-r border-slate-100">{{ mhs.nama_lengkap }}</td>
                    
                    <!-- Kolom Dinamis Nilai Tugas -->
                    <td v-for="ke in tugasKes" :key="ke" class="px-4 py-3.5 text-center border-r border-slate-100">
                      <span class="inline-block px-2.5 py-0.5 rounded-lg text-sm font-bold"
                        :class="tugasGrouped?.[mhs.id]?.find(t => t.tugas_ke === ke)
                          ? 'bg-indigo-50 text-indigo-700'
                          : 'bg-slate-100 text-slate-400'"
                      >
                        {{ tugasGrouped?.[mhs.id]?.find(t => t.tugas_ke === ke)?.nilai ?? '-' }}
                      </span>
                    </td>
                    
                    <!-- Rata-rata Tugas -->
                    <td class="px-5 py-3.5 text-center font-extrabold text-[#4B49AC] bg-indigo-50/5 group-hover:bg-indigo-50/10 transition-colors">
                      {{ getTugasAvg(mhs.id) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- ════════ TAB: UTS ════════ -->
      <div v-if="activeTab === 'uts'" class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-bold text-slate-800">Nilai UTS</h2>
          <Button class="bg-[#4B49AC] hover:bg-[#3f3e91] text-white flex items-center gap-1.5" @click="openUts">
            <component :is="Object.keys(utss).length > 0 ? Pencil : PlusCircle" class="w-4 h-4" />
            {{ Object.keys(utss).length > 0 ? 'Edit Nilai UTS' : 'Input Nilai UTS' }}
          </Button>
        </div>

        <Card class="border-none shadow-sm">
          <CardContent class="p-0" v-if="mahasiswas.length > 0">
            <table class="w-full text-sm">
              <thead class="bg-slate-50">
                <tr>
                  <th class="text-left px-5 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">No</th>
                  <th class="text-left px-5 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">NIM</th>
                  <th class="text-left px-5 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama</th>
                  <th class="text-center px-5 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nilai UTS</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="(mhs, idx) in mahasiswas" :key="mhs.id" class="hover:bg-slate-50/50 transition-colors">
                  <td class="px-5 py-3 text-slate-400 text-xs">{{ idx + 1 }}</td>
                  <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ mhs.nim }}</td>
                  <td class="px-5 py-3 text-slate-800 font-medium">{{ mhs.nama_lengkap }}</td>
                  <td class="px-5 py-3 text-center">
                    <span class="inline-block px-2.5 py-0.5 rounded-lg text-sm font-bold"
                      :class="utss[mhs.id] ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-400'">
                      {{ utss[mhs.id]?.nilai ?? '-' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </CardContent>
          <CardContent v-else class="p-10 text-center">
            <p class="text-slate-400 text-sm">Belum ada data mahasiswa.</p>
          </CardContent>
        </Card>
      </div>

      <!-- ════════ TAB: UAS ════════ -->
      <div v-if="activeTab === 'uas'" class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-bold text-slate-800">Nilai UAS</h2>
          <Button class="bg-[#4B49AC] hover:bg-[#3f3e91] text-white flex items-center gap-1.5" @click="openUas">
            <component :is="Object.keys(uass).length > 0 ? Pencil : PlusCircle" class="w-4 h-4" />
            {{ Object.keys(uass).length > 0 ? 'Edit Nilai UAS' : 'Input Nilai UAS' }}
          </Button>
        </div>

        <Card class="border-none shadow-sm">
          <CardContent class="p-0">
            <table class="w-full text-sm">
              <thead class="bg-slate-50">
                <tr>
                  <th class="text-left px-5 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">No</th>
                  <th class="text-left px-5 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">NIM</th>
                  <th class="text-left px-5 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama</th>
                  <th class="text-center px-5 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nilai UAS</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="(mhs, idx) in mahasiswas" :key="mhs.id" class="hover:bg-slate-50/50 transition-colors">
                  <td class="px-5 py-3 text-slate-400 text-xs">{{ idx + 1 }}</td>
                  <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ mhs.nim }}</td>
                  <td class="px-5 py-3 text-slate-800 font-medium">{{ mhs.nama_lengkap }}</td>
                  <td class="px-5 py-3 text-center">
                    <span class="inline-block px-2.5 py-0.5 rounded-lg text-sm font-bold"
                      :class="uass[mhs.id] ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-400'">
                      {{ uass[mhs.id]?.nilai ?? '-' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </CardContent>
        </Card>
      </div>

      <!-- ════════ TAB: ETIKA ════════ -->
      <div v-if="activeTab === 'etika'" class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-bold text-slate-800">Nilai Etika</h2>
          <Button class="bg-[#4B49AC] hover:bg-[#3f3e91] text-white flex items-center gap-1.5" @click="openEtika">
            <component :is="Object.keys(etikas).length > 0 ? Pencil : PlusCircle" class="w-4 h-4" />
            {{ Object.keys(etikas).length > 0 ? 'Edit Nilai Etika' : 'Input Nilai Etika' }}
          </Button>
        </div>

        <Card class="border-none shadow-sm">
          <CardContent class="p-0">
            <table class="w-full text-sm">
              <thead class="bg-slate-50">
                <tr>
                  <th class="text-left px-5 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">No</th>
                  <th class="text-left px-5 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">NIM</th>
                  <th class="text-left px-5 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama</th>
                  <th class="text-center px-5 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nilai Etika</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="(mhs, idx) in mahasiswas" :key="mhs.id" class="hover:bg-slate-50/50 transition-colors">
                  <td class="px-5 py-3 text-slate-400 text-xs">{{ idx + 1 }}</td>
                  <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ mhs.nim }}</td>
                  <td class="px-5 py-3 text-slate-800 font-medium">{{ mhs.nama_lengkap }}</td>
                  <td class="px-5 py-3 text-center">
                    <span class="inline-block px-2.5 py-0.5 rounded-lg text-sm font-bold"
                      :class="etikas[mhs.id] ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-400'">
                      {{ etikas[mhs.id]?.nilai ?? '-' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </CardContent>
        </Card>
      </div>

      <!-- ════════ TAB: KEAKTIFAN ════════ -->
      <div v-if="activeTab === 'aktif'" class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-bold text-slate-800">Nilai Keaktifan</h2>
          <Button class="bg-[#4B49AC] hover:bg-[#3f3e91] text-white flex items-center gap-1.5" @click="openAktif">
            <component :is="Object.keys(aktifs).length > 0 ? Pencil : PlusCircle" class="w-4 h-4" />
            {{ Object.keys(aktifs).length > 0 ? 'Edit Keaktifan' : 'Input Keaktifan' }}
          </Button>
        </div>

        <Card class="border-none shadow-sm">
          <CardContent class="p-0">
            <table class="w-full text-sm">
              <thead class="bg-slate-50">
                <tr>
                  <th class="text-left px-5 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">No</th>
                  <th class="text-left px-5 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">NIM</th>
                  <th class="text-left px-5 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama</th>
                  <th class="text-center px-5 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nilai Keaktifan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="(mhs, idx) in mahasiswas" :key="mhs.id" class="hover:bg-slate-50/50 transition-colors">
                  <td class="px-5 py-3 text-slate-400 text-xs">{{ idx + 1 }}</td>
                  <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ mhs.nim }}</td>
                  <td class="px-5 py-3 text-slate-800 font-medium">{{ mhs.nama_lengkap }}</td>
                  <td class="px-5 py-3 text-center">
                    <span class="inline-block px-2.5 py-0.5 rounded-lg text-sm font-bold"
                      :class="aktifs[mhs.id] ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-400'">
                      {{ aktifs[mhs.id]?.nilai ?? '-' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </CardContent>
        </Card>
      </div>

      <!-- ════════ TAB: REKAP NILAI ════════ -->
      <div v-if="activeTab === 'rekap'" class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <h2 class="text-lg font-bold text-slate-800">Rekap Nilai Akhir</h2>
          <Button
            v-if="canAjukan"
            class="bg-[#4B49AC] hover:bg-[#3f3e91] text-white flex items-center gap-1.5"
            @click="ajukanRekap"
            :disabled="rekapForm.processing"
          >
            <Send class="w-4 h-4" />
            {{ rekapForm.processing ? 'Mengirim...' : 'Ajukan Verifikasi' }}
          </Button>
          <div v-else-if="pengajuan?.status === 0" class="flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
            <Clock class="w-4 h-4" /> Menunggu persetujuan admin
          </div>
          <div v-else-if="pengajuan?.status === 1" class="flex items-center gap-2 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
            <CheckCircle2 class="w-4 h-4" /> Nilai telah disetujui
          </div>
          <div v-else-if="!completionStatus.semua_lengkap" class="flex items-center gap-2 text-sm text-slate-500 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
            <AlertCircle class="w-4 h-4" /> Lengkapi semua komponen terlebih dahulu
          </div>
        </div>

        <!-- Bobot info -->
        <div class="flex flex-wrap gap-2 text-xs text-slate-500">
          <span class="bg-slate-100 rounded-full px-2.5 py-1 font-semibold">Tugas 25%</span>
          <span class="bg-slate-100 rounded-full px-2.5 py-1 font-semibold">UTS 25%</span>
          <span class="bg-slate-100 rounded-full px-2.5 py-1 font-semibold">UAS 25%</span>
          <span class="bg-slate-100 rounded-full px-2.5 py-1 font-semibold">Kehadiran 15%</span>
          <span class="bg-slate-100 rounded-full px-2.5 py-1 font-semibold">Etika 5%</span>
          <span class="bg-slate-100 rounded-full px-2.5 py-1 font-semibold">Keaktifan 5%</span>
        </div>

        <Card class="border-none shadow-sm overflow-hidden">
          <CardContent class="p-0">
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-[#4B49AC] text-white">
                  <tr>
                    <th class="text-left px-4 py-3 text-xs font-bold uppercase tracking-wider">No</th>
                    <th class="text-left px-4 py-3 text-xs font-bold uppercase tracking-wider">NIM</th>
                    <th class="text-left px-4 py-3 text-xs font-bold uppercase tracking-wider">Nama</th>
                    <th class="text-center px-4 py-3 text-xs font-bold uppercase tracking-wider">Tugas</th>
                    <th class="text-center px-4 py-3 text-xs font-bold uppercase tracking-wider">UTS</th>
                    <th class="text-center px-4 py-3 text-xs font-bold uppercase tracking-wider">UAS</th>
                    <th class="text-center px-4 py-3 text-xs font-bold uppercase tracking-wider">Etika</th>
                    <th class="text-center px-4 py-3 text-xs font-bold uppercase tracking-wider">Aktif</th>
                    <th class="text-center px-4 py-3 text-xs font-bold uppercase tracking-wider">Kehadiran</th>
                    <th class="text-center px-4 py-3 text-xs font-bold uppercase tracking-wider">Total</th>
                    <th class="text-center px-4 py-3 text-xs font-bold uppercase tracking-wider">Huruf</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                  <tr v-for="(mhs, idx) in mahasiswas" :key="mhs.id" class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-4 py-3 text-slate-400 text-xs">{{ idx + 1 }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ mhs.nim }}</td>
                    <td class="px-4 py-3 text-slate-800 font-medium">{{ mhs.nama_lengkap }}</td>
                    <td class="px-4 py-3 text-center text-slate-700">{{ getTugasAvg(mhs.id) }}</td>
                    <td class="px-4 py-3 text-center text-slate-700">{{ utss[mhs.id]?.nilai ?? '-' }}</td>
                    <td class="px-4 py-3 text-center text-slate-700">{{ uass[mhs.id]?.nilai ?? '-' }}</td>
                    <td class="px-4 py-3 text-center text-slate-700">{{ etikas[mhs.id]?.nilai ?? '-' }}</td>
                    <td class="px-4 py-3 text-center text-slate-700">{{ aktifs[mhs.id]?.nilai ?? '-' }}</td>
                    <td class="px-4 py-3 text-center text-slate-700 text-xs">{{ getAbsensi(mhs.id) }}</td>
                    <td class="px-4 py-3 text-center font-bold text-slate-800">{{ nilaiAkhir[mhs.id]?.total ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                      <span
                        v-if="nilaiAkhir[mhs.id]?.huruf"
                        class="inline-block px-2.5 py-0.5 rounded-lg text-xs font-extrabold"
                        :class="nilaiHurufClass(nilaiAkhir[mhs.id].huruf)"
                      >
                        {{ nilaiAkhir[mhs.id].huruf }}
                      </span>
                      <span v-else class="text-slate-400">-</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- ════ DIALOG: TUGAS ════ -->
    <Dialog :open="showTugasDialog" @update:open="handleTugasOpenChange">
      <DialogContent class="sm:max-w-[500px] overflow-hidden p-0 max-h-[85vh] flex flex-col" @pointer-down-outside.prevent @escape-key-down.prevent>
        <div class="bg-[#4B49AC] text-white p-5">
          <DialogTitle class="text-white text-base font-bold">
            {{ editTugasKe !== null ? `Edit Nilai Tugas ke-${editTugasKe}` : `Tambah Tugas ke-${tugasForm.tugas_ke}` }}
          </DialogTitle>
          <DialogDescription class="text-indigo-200 text-xs mt-0.5">
            {{ jadwal.matkul }} — {{ jadwal.kelas }}
          </DialogDescription>
        </div>
        <div class="flex-1 overflow-y-auto p-5 space-y-4">
          <div v-if="mahasiswaAktif.length === 0" class="text-center py-8">
            <Users class="w-10 h-10 text-slate-300 mx-auto mb-3" />
            <p class="text-slate-500 text-sm font-medium">Belum ada mahasiswa di kelas ini yang mengambil KRS.</p>
            <p class="text-slate-400 text-xs mt-1">Data mahasiswa akan muncul setelah mahasiswa melakukan pengisian KRS.</p>
          </div>
          <table v-else class="w-full text-sm">
            <thead>
              <tr class="border-b border-slate-100">
                <th class="text-left pb-2 text-xs font-bold text-slate-500">Nama Mahasiswa</th>
                <th class="text-center pb-2 text-xs font-bold text-slate-500 w-24">Nilai (0-100)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="(mhs, idx) in mahasiswaAktif" :key="mhs.id">
                <td class="py-2.5">
                  <p class="text-slate-800 font-medium text-xs">{{ mhs.nama_lengkap }}</p>
                  <p class="text-slate-400 font-mono text-[11px]">{{ mhs.nim }}</p>
                </td>
                <td class="py-2.5 text-center">
                  <Input
                    v-model="tugasForm.nilai[idx]"
                    type="number" min="0" max="100"
                    class="w-20 text-center mx-auto h-8 text-sm"
                    :class="tugasForm.errors['nilai.' + idx] ? 'border-red-500 text-red-900 focus-visible:ring-red-500' : 'border-slate-200'"
                    placeholder="0"
                  />
                  <span v-if="tugasForm.errors['nilai.' + idx]" class="text-[10px] text-red-500 block text-center mt-0.5">
                    Harus 0-100
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="border-t border-slate-100 p-4 flex justify-end gap-2">
          <Button variant="outline" @click="handleTugasOpenChange(false)" :disabled="tugasForm.processing" class="border-slate-200 text-slate-700">
            Batal
          </Button>
          <Button class="bg-[#4B49AC] hover:bg-[#3f3e91] text-white" @click="submitTugas" :disabled="tugasForm.processing || mahasiswaAktif.length === 0">
            {{ tugasForm.processing ? 'Menyimpan...' : 'Simpan' }}
          </Button>
        </div>
      </DialogContent>
    </Dialog>

    <!-- ════ DIALOG: UTS ════ -->
    <Dialog :open="showUtsDialog" @update:open="handleUtsOpenChange">
      <DialogContent class="sm:max-w-[500px] overflow-hidden p-0 max-h-[85vh] flex flex-col" @pointer-down-outside.prevent @escape-key-down.prevent>
        <div class="bg-[#4B49AC] text-white p-5">
          <DialogTitle class="text-white text-base font-bold">
            {{ Object.keys(utss).length > 0 ? 'Edit Nilai UTS' : 'Input Nilai UTS' }}
          </DialogTitle>
          <DialogDescription class="text-indigo-200 text-xs mt-0.5">{{ jadwal.matkul }} — {{ jadwal.kelas }}</DialogDescription>
        </div>
        <div class="flex-1 overflow-y-auto p-5">
          <div v-if="mahasiswaAktif.length === 0" class="text-center py-8">
            <Users class="w-10 h-10 text-slate-300 mx-auto mb-3" />
            <p class="text-slate-500 text-sm font-medium">Belum ada mahasiswa di kelas ini yang mengambil KRS.</p>
            <p class="text-slate-400 text-xs mt-1">Data mahasiswa akan muncul setelah mahasiswa melakukan pengisian KRS.</p>
          </div>
          <table v-else class="w-full text-sm">
            <thead><tr class="border-b border-slate-100">
              <th class="text-left pb-2 text-xs font-bold text-slate-500">Mahasiswa</th>
              <th class="text-center pb-2 text-xs font-bold text-slate-500 w-24">Nilai</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="(mhs, idx) in mahasiswaAktif" :key="mhs.id">
                <td class="py-2.5">
                  <p class="text-slate-800 font-medium text-xs">{{ mhs.nama_lengkap }}</p>
                  <p class="text-slate-400 font-mono text-[11px]">{{ mhs.nim }}</p>
                </td>
                <td class="py-2.5 text-center">
                  <Input 
                    v-model="utsForm.nilai[idx]" 
                    type="number" min="0" max="100" 
                    class="w-20 text-center mx-auto h-8 text-sm" 
                    :class="utsForm.errors['nilai.' + idx] ? 'border-red-500 text-red-900 focus-visible:ring-red-500' : 'border-slate-200'"
                    placeholder="0" 
                  />
                  <span v-if="utsForm.errors['nilai.' + idx]" class="text-[10px] text-red-500 block text-center mt-0.5">
                    Harus 0-100
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="border-t border-slate-100 p-4 flex justify-end gap-2">
          <Button variant="outline" @click="handleUtsOpenChange(false)" :disabled="utsForm.processing" class="border-slate-200">Batal</Button>
          <Button class="bg-[#4B49AC] hover:bg-[#3f3e91] text-white" @click="submitUts" :disabled="utsForm.processing || mahasiswaAktif.length === 0">
            {{ utsForm.processing ? 'Menyimpan...' : 'Simpan' }}
          </Button>
        </div>
      </DialogContent>
    </Dialog>

    <!-- ════ DIALOG: UAS ════ -->
    <Dialog :open="showUasDialog" @update:open="handleUasOpenChange">
      <DialogContent class="sm:max-w-[500px] overflow-hidden p-0 max-h-[85vh] flex flex-col" @pointer-down-outside.prevent @escape-key-down.prevent>
        <div class="bg-[#4B49AC] text-white p-5">
          <DialogTitle class="text-white text-base font-bold">
            {{ Object.keys(uass).length > 0 ? 'Edit Nilai UAS' : 'Input Nilai UAS' }}
          </DialogTitle>
          <DialogDescription class="text-indigo-200 text-xs mt-0.5">{{ jadwal.matkul }} — {{ jadwal.kelas }}</DialogDescription>
        </div>
        <div class="flex-1 overflow-y-auto p-5">
          <div v-if="mahasiswaAktif.length === 0" class="text-center py-8">
            <Users class="w-10 h-10 text-slate-300 mx-auto mb-3" />
            <p class="text-slate-500 text-sm font-medium">Belum ada mahasiswa di kelas ini yang mengambil KRS.</p>
            <p class="text-slate-400 text-xs mt-1">Data mahasiswa akan muncul setelah mahasiswa melakukan pengisian KRS.</p>
          </div>
          <table v-else class="w-full text-sm">
            <thead><tr class="border-b border-slate-100">
              <th class="text-left pb-2 text-xs font-bold text-slate-500">Mahasiswa</th>
              <th class="text-center pb-2 text-xs font-bold text-slate-500 w-24">Nilai</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="(mhs, idx) in mahasiswaAktif" :key="mhs.id">
                <td class="py-2.5">
                  <p class="text-slate-800 font-medium text-xs">{{ mhs.nama_lengkap }}</p>
                  <p class="text-slate-400 font-mono text-[11px]">{{ mhs.nim }}</p>
                </td>
                <td class="py-2.5 text-center">
                  <Input 
                    v-model="uasForm.nilai[idx]" 
                    type="number" min="0" max="100" 
                    class="w-20 text-center mx-auto h-8 text-sm" 
                    :class="uasForm.errors['nilai.' + idx] ? 'border-red-500 text-red-900 focus-visible:ring-red-500' : 'border-slate-200'"
                    placeholder="0" 
                  />
                  <span v-if="uasForm.errors['nilai.' + idx]" class="text-[10px] text-red-500 block text-center mt-0.5">
                    Harus 0-100
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="border-t border-slate-100 p-4 flex justify-end gap-2">
          <Button variant="outline" @click="handleUasOpenChange(false)" :disabled="uasForm.processing" class="border-slate-200">Batal</Button>
          <Button class="bg-[#4B49AC] hover:bg-[#3f3e91] text-white" @click="submitUas" :disabled="uasForm.processing || mahasiswaAktif.length === 0">
            {{ uasForm.processing ? 'Menyimpan...' : 'Simpan' }}
          </Button>
        </div>
      </DialogContent>
    </Dialog>

    <!-- ════ DIALOG: ETIKA ════ -->
    <Dialog :open="showEtikaDialog" @update:open="handleEtikaOpenChange">
      <DialogContent class="sm:max-w-[500px] overflow-hidden p-0 max-h-[85vh] flex flex-col" @pointer-down-outside.prevent @escape-key-down.prevent>
        <div class="bg-[#4B49AC] text-white p-5">
          <DialogTitle class="text-white text-base font-bold">
            {{ Object.keys(etikas).length > 0 ? 'Edit Nilai Etika' : 'Input Nilai Etika' }}
          </DialogTitle>
          <DialogDescription class="text-indigo-200 text-xs mt-0.5">{{ jadwal.matkul }} — {{ jadwal.kelas }}</DialogDescription>
        </div>
        <div class="flex-1 overflow-y-auto p-5">
          <div v-if="mahasiswaAktif.length === 0" class="text-center py-8">
            <Users class="w-10 h-10 text-slate-300 mx-auto mb-3" />
            <p class="text-slate-500 text-sm font-medium">Belum ada mahasiswa di kelas ini yang mengambil KRS.</p>
            <p class="text-slate-400 text-xs mt-1">Data mahasiswa akan muncul setelah mahasiswa melakukan pengisian KRS.</p>
          </div>
          <table v-else class="w-full text-sm">
            <thead><tr class="border-b border-slate-100">
              <th class="text-left pb-2 text-xs font-bold text-slate-500">Mahasiswa</th>
              <th class="text-center pb-2 text-xs font-bold text-slate-500 w-24">Nilai</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="(mhs, idx) in mahasiswaAktif" :key="mhs.id">
                <td class="py-2.5">
                  <p class="text-slate-800 font-medium text-xs">{{ mhs.nama_lengkap }}</p>
                  <p class="text-slate-400 font-mono text-[11px]">{{ mhs.nim }}</p>
                </td>
                <td class="py-2.5 text-center">
                  <Input 
                    v-model="etikaForm.nilai[idx]" 
                    type="number" min="0" max="100" 
                    class="w-20 text-center mx-auto h-8 text-sm" 
                    :class="etikaForm.errors['nilai.' + idx] ? 'border-red-500 text-red-900 focus-visible:ring-red-500' : 'border-slate-200'"
                    placeholder="0" 
                  />
                  <span v-if="etikaForm.errors['nilai.' + idx]" class="text-[10px] text-red-500 block text-center mt-0.5">
                    Harus 0-100
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="border-t border-slate-100 p-4 flex justify-end gap-2">
          <Button variant="outline" @click="handleEtikaOpenChange(false)" :disabled="etikaForm.processing" class="border-slate-200">Batal</Button>
          <Button class="bg-[#4B49AC] hover:bg-[#3f3e91] text-white" @click="submitEtika" :disabled="etikaForm.processing || mahasiswaAktif.length === 0">
            {{ etikaForm.processing ? 'Menyimpan...' : 'Simpan' }}
          </Button>
        </div>
      </DialogContent>
    </Dialog>

    <!-- ════ DIALOG: KEAKTIFAN ════ -->
    <Dialog :open="showAktifDialog" @update:open="handleAktifOpenChange">
      <DialogContent class="sm:max-w-[500px] overflow-hidden p-0 max-h-[85vh] flex flex-col" @pointer-down-outside.prevent @escape-key-down.prevent>
        <div class="bg-[#4B49AC] text-white p-5">
          <DialogTitle class="text-white text-base font-bold">
            {{ Object.keys(aktifs).length > 0 ? 'Edit Nilai Keaktifan' : 'Input Nilai Keaktifan' }}
          </DialogTitle>
          <DialogDescription class="text-indigo-200 text-xs mt-0.5">{{ jadwal.matkul }} — {{ jadwal.kelas }}</DialogDescription>
        </div>
        <div class="flex-1 overflow-y-auto p-5">
          <div v-if="mahasiswaAktif.length === 0" class="text-center py-8">
            <Users class="w-10 h-10 text-slate-300 mx-auto mb-3" />
            <p class="text-slate-500 text-sm font-medium">Belum ada mahasiswa di kelas ini yang mengambil KRS.</p>
            <p class="text-slate-400 text-xs mt-1">Data mahasiswa akan muncul setelah mahasiswa melakukan pengisian KRS.</p>
          </div>
          <table v-else class="w-full text-sm">
            <thead><tr class="border-b border-slate-100">
              <th class="text-left pb-2 text-xs font-bold text-slate-500">Mahasiswa</th>
              <th class="text-center pb-2 text-xs font-bold text-slate-500 w-24">Nilai</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="(mhs, idx) in mahasiswaAktif" :key="mhs.id">
                <td class="py-2.5">
                  <p class="text-slate-800 font-medium text-xs">{{ mhs.nama_lengkap }}</p>
                  <p class="text-slate-400 font-mono text-[11px]">{{ mhs.nim }}</p>
                </td>
                <td class="py-2.5 text-center">
                  <Input 
                    v-model="aktifForm.nilai[idx]" 
                    type="number" min="0" max="100" 
                    class="w-20 text-center mx-auto h-8 text-sm" 
                    :class="aktifForm.errors['nilai.' + idx] ? 'border-red-500 text-red-900 focus-visible:ring-red-500' : 'border-slate-200'"
                    placeholder="0" 
                  />
                  <span v-if="aktifForm.errors['nilai.' + idx]" class="text-[10px] text-red-500 block text-center mt-0.5">
                    Harus 0-100
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="border-t border-slate-100 p-4 flex justify-end gap-2">
          <Button variant="outline" @click="handleAktifOpenChange(false)" :disabled="aktifForm.processing" class="border-slate-200">Batal</Button>
          <Button class="bg-[#4B49AC] hover:bg-[#3f3e91] text-white" @click="submitAktif" :disabled="aktifForm.processing || mahasiswaAktif.length === 0">
            {{ aktifForm.processing ? 'Menyimpan...' : 'Simpan' }}
          </Button>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Delete Tugas Confirm Modal -->
    <Dialog :open="showDeleteTugasDialog" @update:open="showDeleteTugasDialog = $event">
      <DialogContent class="sm:max-w-[450px] p-0 overflow-hidden border-none shadow-2xl bg-white rounded-lg">
        <div class="p-8 text-center bg-white">
          <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
            <AlertCircle class="w-10 h-10 text-danger" />
          </div>
          <DialogHeader>
            <DialogTitle class="text-2xl font-bold text-gray-800 text-center">Konfirmasi Hapus</DialogTitle>
            <DialogDescription class="text-gray-500 text-center mt-3 px-2 text-base leading-relaxed">
              Apakah Anda yakin ingin menghapus semua nilai <span class="text-danger font-extrabold">Tugas ke-{{ deleteTugasKe }}</span>?
              <p class="text-[10px] mt-4 font-bold bg-red-50 text-danger p-2 rounded-lg italic tracking-wide">
                Tindakan ini tidak dapat dibatalkan dan akan menghapus nilai tugas ini untuk seluruh mahasiswa di kelas ini.
              </p>
            </DialogDescription>
          </DialogHeader>

          <div class="flex items-center justify-center gap-4 mt-10">
            <Button 
              type="button" 
              variant="ghost" 
              @click="showDeleteTugasDialog = false" 
              class="h-12 px-8 rounded-lg text-gray-500 font-bold hover:bg-gray-100 transition-all border border-slate-200"
            >
              Batal
            </Button>
            <Button 
              @click="submitDeleteTugas" 
              class="h-12 px-10 bg-[#FF4747] hover:bg-[#d63c3c] text-white rounded-lg shadow-lg shadow-red-100 font-bold transition-all"
            >
              Ya, Hapus Data
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Toast -->
    <transition name="toast">
      <div v-if="showToast" class="fixed bottom-10 right-10 z-[100]">
        <div class="bg-[#1F2937] text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-gray-700 backdrop-blur-sm">
          <div :class="toastType === 'error' ? 'bg-red-500' : 'bg-green-500'" class="p-1.5 rounded-full">
            <CheckCircle2 v-if="toastType === 'success'" class="w-4 h-4 text-white" />
            <AlertCircle v-else class="w-4 h-4 text-white" />
          </div>
          <span class="text-sm font-medium">{{ toastMsg }}</span>
        </div>
      </div>
    </transition>
  </AdminLayout>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(20px) scale(0.95); }
</style>
