<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Separator } from '@/Components/ui/separator'
import {
  ArrowLeft,
  BookOpen,
  Calendar,
  Clock,
  ClipboardList,
  Save,
  CheckCircle2,
  AlertCircle
} from 'lucide-vue-next'

const props = defineProps({
  jadwal: {
    type: Object,
    required: true
  },
  kontraks: {
    type: Array,
    required: true
  }
})

// Find active academic year from existing contracts
const tahunAkademik = props.kontraks[0]?.tahun || ''

// Safely pre-fill from props.kontraks by finding matching 'pertemuan'
const initialMateri = {}
const initialPustaka = {}
for (let i = 1; i <= 14; i++) {
  const match = props.kontraks.find(k => parseInt(k.pertemuan) === i)
  initialMateri[i] = match ? match.materi || '' : ''
  initialPustaka[i] = match ? match.pustaka || '' : ''
}

const form = useForm({
  tahun: tahunAkademik,
  matkuls_id: props.jadwal.matkuls_id || props.jadwal.matkul?.id,
  kelas_id: props.jadwal.kelas_id || props.jadwal.kelas?.id,
  materiKontrak: initialMateri,
  pustakaKontrak: initialPustaka
})

const activeTab = ref('1-7')

const submit = () => {
  form.put(route('v2.dosen.kontrak.update', props.jadwal.id), {
    onSuccess: () => {
      // redirects to index
    }
  })
}
</script>

<template>
  <AdminLayout>
    <Head title="Edit Kontrak Perkuliahan" />

    <div class="space-y-6 max-w-5xl mx-auto">
      <!-- Header -->
      <div class="flex items-center gap-3">
        <Link :href="route('v2.dosen.kontrak.index')">
          <Button variant="outline" size="icon" class="rounded-full shadow-sm hover:bg-slate-100">
            <ArrowLeft class="w-4 h-4 text-slate-700" />
          </Button>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
            Edit Kontrak Perkuliahan
          </h1>
          <p class="text-slate-500 text-sm mt-0.5">
            Perbarui materi dan pustaka perkuliahan yang telah diisi sebelumnya.
          </p>
        </div>
      </div>

      <!-- Course Info Header -->
      <Card class="border-none shadow-sm bg-gradient-to-br from-indigo-50/50 to-indigo-100/30">
        <CardContent class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-3">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-[#4B49AC] text-white rounded-lg shadow-sm">
                  <BookOpen class="w-5 h-5" />
                </div>
                <div>
                  <h3 class="text-base font-bold text-slate-800">{{ jadwal.matkul?.nama_matkul }}</h3>
                  <p class="text-xs font-mono text-[#4B49AC] font-bold">KODE: {{ jadwal.matkul?.kode || '-' }}</p>
                </div>
              </div>

              <div class="text-sm text-slate-600 space-y-1.5 pl-1">
                <p>Dosen Pengampu: <strong class="text-slate-800">{{ jadwal.dosen?.nama }}</strong></p>
                <p>Program Studi: <strong class="text-slate-800">{{ jadwal.kelas?.prodi?.nama_prodi }}</strong></p>
              </div>
            </div>

            <div class="flex flex-col justify-between md:items-end text-sm text-slate-600 space-y-2 md:space-y-0">
              <div class="md:text-right">
                <p>Kelas: <strong class="text-slate-800">{{ jadwal.kelas?.nama_kelas }}</strong></p>
                <p>Tahun Akademik: <strong class="text-[#4B49AC]">{{ tahunAkademik }}</strong></p>
              </div>

              <div class="flex gap-2">
                <Badge variant="secondary" class="bg-indigo-50 text-[#4B49AC] border border-indigo-100 font-semibold">
                  Hari: {{ jadwal.hari }}
                </Badge>
                <Badge variant="secondary" class="bg-slate-50 text-slate-700 border border-slate-200 font-semibold">
                  SKS: {{ (jadwal.matkul?.praktek || 0) + (jadwal.matkul?.teori || 0) }}
                </Badge>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Main Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Tab Switches -->
        <div class="flex border-b border-slate-200">
          <button
            type="button"
            class="px-5 py-3 text-sm font-bold border-b-2 transition-all"
            :class="activeTab === '1-7' ? 'border-[#4B49AC] text-[#4B49AC]' : 'border-transparent text-slate-400 hover:text-slate-600'"
            @click="activeTab = '1-7'"
          >
            Pertemuan 1 - 7 (Paruh Pertama)
          </button>
          <button
            type="button"
            class="px-5 py-3 text-sm font-bold border-b-2 transition-all"
            :class="activeTab === '8-14' ? 'border-[#4B49AC] text-[#4B49AC]' : 'border-transparent text-slate-400 hover:text-slate-600'"
            @click="activeTab = '8-14'"
          >
            Pertemuan 8 - 14 (Paruh Kedua)
          </button>
        </div>

        <!-- Tab 1: Meetings 1 - 7 -->
        <div v-show="activeTab === '1-7'" class="space-y-4">
          <Card v-for="i in 7" :key="i" class="border-none shadow-sm hover:shadow-md transition-shadow">
            <CardHeader class="p-4 pb-2 bg-slate-50/50 rounded-t-lg">
              <CardTitle class="text-sm font-bold text-slate-700 flex items-center gap-1.5">
                <span class="w-5 h-5 rounded-full bg-[#4B49AC]/10 text-[#4B49AC] flex items-center justify-center text-xs font-mono font-bold">
                  {{ i }}
                </span>
                Pertemuan ke-{{ i }}
              </CardTitle>
            </CardHeader>
            <CardContent class="p-4 pt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <Label :for="`materi_${i}`" class="text-xs font-semibold text-slate-500">Materi Perkuliahan</Label>
                <Input
                  :id="`materi_${i}`"
                  type="text"
                  v-model="form.materiKontrak[i]"
                  placeholder="Masukkan materi perkuliahan"
                  required
                  class="w-full text-slate-700 border-slate-200 focus-visible:ring-[#4B49AC]"
                />
              </div>
              <div class="space-y-1.5">
                <Label :for="`pustaka_${i}`" class="text-xs font-semibold text-slate-500">Daftar Pustaka</Label>
                <Input
                  :id="`pustaka_${i}`"
                  type="text"
                  v-model="form.pustakaKontrak[i]"
                  placeholder="Masukkan daftar pustaka / buku referensi"
                  required
                  class="w-full text-slate-700 border-slate-200 focus-visible:ring-[#4B49AC]"
                />
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Tab 2: Meetings 8 - 14 -->
        <div v-show="activeTab === '8-14'" class="space-y-4">
          <Card v-for="i in [8,9,10,11,12,13,14]" :key="i" class="border-none shadow-sm hover:shadow-md transition-shadow">
            <CardHeader class="p-4 pb-2 bg-slate-50/50 rounded-t-lg">
              <CardTitle class="text-sm font-bold text-slate-700 flex items-center gap-1.5">
                <span class="w-5 h-5 rounded-full bg-[#4B49AC]/10 text-[#4B49AC] flex items-center justify-center text-xs font-mono font-bold">
                  {{ i }}
                </span>
                Pertemuan ke-{{ i }}
              </CardTitle>
            </CardHeader>
            <CardContent class="p-4 pt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <Label :for="`materi_${i}`" class="text-xs font-semibold text-slate-500">Materi Perkuliahan</Label>
                <Input
                  :id="`materi_${i}`"
                  type="text"
                  v-model="form.materiKontrak[i]"
                  placeholder="Masukkan materi perkuliahan"
                  required
                  class="w-full text-slate-700 border-slate-200 focus-visible:ring-[#4B49AC]"
                />
              </div>
              <div class="space-y-1.5">
                <Label :for="`pustaka_${i}`" class="text-xs font-semibold text-slate-500">Daftar Pustaka</Label>
                <Input
                  :id="`pustaka_${i}`"
                  type="text"
                  v-model="form.pustakaKontrak[i]"
                  placeholder="Masukkan daftar pustaka / buku referensi"
                  required
                  class="w-full text-slate-700 border-slate-200 focus-visible:ring-[#4B49AC]"
                />
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between pt-4 border-t border-slate-200">
          <Link :href="route('v2.dosen.kontrak.index')">
            <Button type="button" variant="outline" class="border-slate-200 text-slate-700">
              Batal / Kembali
            </Button>
          </Link>
          <div class="flex gap-3">
            <Button
              v-if="activeTab === '1-7'"
              type="button"
              class="bg-[#4B49AC] hover:bg-[#3f3e91] text-white font-semibold"
              @click="activeTab = '8-14'"
            >
              Pertemuan Selanjutnya (8-14)
            </Button>
            <Button
              v-else
              type="submit"
              class="bg-emerald-600 hover:bg-emerald-700 text-white flex items-center gap-1.5 shadow-sm font-semibold"
              :disabled="form.processing"
            >
              <Save class="w-4 h-4" />
              {{ form.processing ? 'Sedang Memperbarui...' : 'Perbarui Kontrak' }}
            </Button>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
