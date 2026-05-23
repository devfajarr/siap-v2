<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent } from '@/Components/ui/card'
import { Input } from '@/Components/ui/input'
import { Button } from '@/Components/ui/button'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import {
  ChevronLeft,
  Printer,
  Search,
  Users,
  Percent,
  Award,
  GraduationCap,
} from 'lucide-vue-next'

const props = defineProps({
  kelas: {
    type: Object,
    required: true
  },
  jadwal: {
    type: Object,
    required: true
  },
  mahasiswas: {
    type: Array,
    required: true
  },
  jumlahTugas: {
    type: Number,
    required: true
  },
  totalPertemuan: {
    type: Number,
    required: true
  },
  rataRataKehadiran: {
    type: Number,
    required: true
  },
  wadir: {
    type: Object,
    default: null
  },
  kaprodi: {
    type: Object,
    default: null
  },
  tanggal_sekarang: {
    type: String,
    required: true
  }
})

const searchQuery = ref('')

const filteredMahasiswas = computed(() => {
  if (!searchQuery.value) return props.mahasiswas
  const query = searchQuery.value.toLowerCase()
  return props.mahasiswas.filter(mhs =>
    mhs.nama.toLowerCase().includes(query) ||
    mhs.nim.toLowerCase().includes(query)
  )
})

const getGradeBadgeClass = (grade) => {
  const base = 'px-2 py-1 rounded text-xs font-semibold border '
  if (['A', 'A-'].includes(grade)) {
    return base + 'bg-emerald-50 text-emerald-700 border-emerald-200'
  }
  if (['B+', 'B', 'B-'].includes(grade)) {
    return base + 'bg-indigo-50 text-indigo-700 border-indigo-200'
  }
  if (['C+', 'C', 'C-'].includes(grade)) {
    return base + 'bg-amber-50 text-amber-700 border-amber-200'
  }
  if (grade === 'D') {
    return base + 'bg-orange-50 text-orange-700 border-orange-200'
  }
  if (grade === 'E') {
    return base + 'bg-red-50 text-red-700 border-red-200'
  }
  return base + 'bg-gray-50 text-gray-600 border-gray-200'
}

const printPage = () => {
  window.print()
}
</script>

<template>
  <AdminLayout>
    <Head :title="`Rekap Nilai - ${jadwal.matkul?.nama_matkul}`" />

    <div class="space-y-6 max-w-7xl mx-auto pb-12 font-sans">
      <!-- Top Action Bar (Screen Only) -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 print:hidden">
        <div class="flex items-center gap-2">
          <Link
            :href="route('v2.direktur.monitoring.nilai.detail', kelas.id)"
            class="p-1.5 hover:bg-gray-100 rounded-full transition-colors"
          >
            <ChevronLeft class="w-6 h-6 text-gray-500" />
          </Link>
          <div>
            <h1 class="text-2xl font-bold text-[#1F2937] tracking-tight font-nunito">Cek Rekap Nilai</h1>
            <p class="text-[#6B7280] text-sm font-light">
              Monitoring rekapitulasi nilai akhir mata kuliah mahasiswa.
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2 self-end sm:self-auto">
          <Button
            @click="printPage"
            class="bg-[#4B49AC] hover:bg-[#3f3da3] text-white flex items-center gap-1.5 rounded-lg shadow-sm font-semibold"
          >
            <Printer class="w-4 h-4" />
            Cetak Rekap
          </Button>
        </div>
      </div>

      <!-- Stats Grid (Screen Only) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 print:hidden">
        <Card class="border-none shadow-sm bg-white rounded-lg">
          <CardContent class="p-4 flex items-center gap-4">
            <div class="p-3 bg-indigo-50 rounded-lg text-[#4B49AC]">
              <Users class="w-6 h-6" />
            </div>
            <div>
              <p class="text-xs text-gray-400 uppercase font-bold tracking-wider font-nunito">Total Mahasiswa</p>
              <h3 class="text-xl font-bold text-gray-800">{{ mahasiswas.length }} orang</h3>
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm bg-white rounded-lg">
          <CardContent class="p-4 flex items-center gap-4">
            <div class="p-3 bg-emerald-50 rounded-lg text-emerald-600">
              <Award class="w-6 h-6" />
            </div>
            <div>
              <p class="text-xs text-gray-400 uppercase font-bold tracking-wider font-nunito">Total Tugas</p>
              <h3 class="text-xl font-bold text-gray-800">{{ jumlahTugas }} Tugas</h3>
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm bg-white rounded-lg">
          <CardContent class="p-4 flex items-center gap-4">
            <div class="p-3 bg-amber-50 rounded-lg text-amber-600">
              <GraduationCap class="w-6 h-6" />
            </div>
            <div>
              <p class="text-xs text-gray-400 uppercase font-bold tracking-wider font-nunito">Total Pertemuan</p>
              <h3 class="text-xl font-bold text-gray-800">{{ totalPertemuan }} Pertemuan</h3>
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm bg-white rounded-lg">
          <CardContent class="p-4 flex items-center gap-4">
            <div class="p-3 bg-rose-50 rounded-lg text-rose-600">
              <Percent class="w-6 h-6" />
            </div>
            <div>
              <p class="text-xs text-gray-400 uppercase font-bold tracking-wider font-nunito">Avg Kehadiran</p>
              <h3 class="text-xl font-bold text-gray-800">{{ rataRataKehadiran }} / Mhs</h3>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Printable Document Container -->
      <div id="print-area" class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 md:p-8">
        <!-- Print Header (A4 Landscape Letterhead) -->
        <div class="text-center border-b pb-4 mb-6">
          <h2 class="text-lg font-bold uppercase tracking-wide text-gray-900 leading-tight">DAFTAR NILAI MAHASISWA</h2>
          <p class="text-xs font-semibold text-gray-600 mt-1">
            Semester: {{ kelas.semester?.semester % 2 === 0 ? 'Genap' : 'Ganjil' }}
            TA {{ jadwal.tahun ?? '-' }}
          </p>
          <p class="text-sm font-bold text-gray-900 mt-0.5">POLITEKNIK SAWUNGGALIH AJI</p>
        </div>

        <!-- Academic Info Section -->
        <div class="grid grid-cols-2 gap-4 text-sm mb-6 pb-4 border-b border-dashed">
          <div class="space-y-1">
            <div class="flex">
              <span class="w-32 font-medium text-gray-500">Mata Kuliah</span>
              <span class="font-bold text-gray-900">: {{ jadwal.matkul?.nama_matkul }}</span>
            </div>
            <div class="flex">
              <span class="w-32 font-medium text-gray-500">Dosen Pengampu</span>
              <span class="font-semibold text-gray-800">: {{ jadwal.dosen?.nama }}</span>
            </div>
            <div class="flex">
              <span class="w-32 font-medium text-gray-500">NIDN</span>
              <span class="text-gray-700 font-semibold">: {{ jadwal.dosen?.nidn ?? '-' }}</span>
            </div>
            <div class="flex">
              <span class="w-32 font-medium text-gray-500">Program Studi</span>
              <span class="text-gray-700 font-semibold">: {{ kelas.prodi?.jenjang }} {{ kelas.prodi?.nama_prodi }}</span>
            </div>
          </div>
          <div class="space-y-1">
            <div class="flex">
              <span class="w-32 font-medium text-gray-500">Kelas</span>
              <span class="text-gray-700 font-semibold">
                : {{ kelas.jenis_kelas === 'Reguler' ? 'A' : (kelas.jenis_kelas === 'Karyawan' ? 'B' : '-') }}
                ({{ kelas.nama_kelas }})
              </span>
            </div>
            <div class="flex">
              <span class="w-32 font-medium text-gray-500">Kode Kelas</span>
              <span class="text-gray-700 font-semibold">: {{ kelas.kode_kelas }}</span>
            </div>
            <div class="flex">
              <span class="w-32 font-medium text-gray-500">Jumlah TM</span>
              <span class="text-gray-700 font-semibold">: {{ totalPertemuan }} kali tatap muka</span>
            </div>
            <div class="flex">
              <span class="w-32 font-medium text-gray-500">Avg Kehadiran</span>
              <span class="text-gray-700 font-semibold">: {{ rataRataKehadiran }} kehadiran per mahasiswa</span>
            </div>
          </div>
        </div>

        <!-- Toolbar (Search) (Screen Only) -->
        <div class="flex items-center justify-between mb-4 print:hidden">
          <div class="relative w-full max-w-sm">
            <Search class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
            <Input
              v-model="searchQuery"
              placeholder="Cari mahasiswa NIM / Nama..."
              class="pl-9 h-9 rounded-lg border-gray-200"
            />
          </div>
          <div class="text-xs text-gray-400 italic">
            Menampilkan {{ filteredMahasiswas.length }} dari {{ mahasiswas.length }} mahasiswa
          </div>
        </div>

        <!-- Grade Grid Table -->
        <div class="rounded-lg border border-gray-200 overflow-x-auto shadow-sm">
          <Table class="min-w-full">
            <TableHeader class="bg-gray-50">
              <TableRow>
                <TableHead class="text-center font-bold border-r w-12" rowspan="2">No</TableHead>
                <TableHead class="text-center font-bold border-r w-24" rowspan="2">NIM</TableHead>
                <TableHead class="font-bold border-r min-w-[150px]" rowspan="2">Nama Mahasiswa</TableHead>
                <TableHead class="text-center font-bold border-r" :colspan="jumlahTugas + 1">Tugas (25%)</TableHead>
                <TableHead class="text-center font-bold border-r" colspan="2">Aktif (5%)</TableHead>
                <TableHead class="text-center font-bold border-r" colspan="2">Etika (5%)</TableHead>
                <TableHead class="text-center font-bold border-r" colspan="2">Presensi (15%)</TableHead>
                <TableHead class="text-center font-bold border-r" colspan="2">UTS (25%)</TableHead>
                <TableHead class="text-center font-bold border-r" colspan="2">UAS (25%)</TableHead>
                <TableHead class="text-center font-bold border-r bg-indigo-50/50 w-20" rowspan="2">Jumlah</TableHead>
                <TableHead class="text-center font-bold w-16" rowspan="2">NA</TableHead>
              </TableRow>
              <TableRow class="bg-gray-50/50">
                <!-- Tugas Columns -->
                <TableHead v-for="t in jumlahTugas" :key="`t-${t}`" class="text-center font-semibold text-xs border-r w-10">T{{ t }}</TableHead>
                <TableHead class="text-center font-semibold text-xs border-r w-16">%</TableHead>
                <!-- Aktif -->
                <TableHead class="text-center font-semibold text-xs border-r w-10">K</TableHead>
                <TableHead class="text-center font-semibold text-xs border-r w-16">%</TableHead>
                <!-- Etika -->
                <TableHead class="text-center font-semibold text-xs border-r w-10">E</TableHead>
                <TableHead class="text-center font-semibold text-xs border-r w-16">%</TableHead>
                <!-- Presensi -->
                <TableHead class="text-center font-semibold text-xs border-r w-10">Hadir</TableHead>
                <TableHead class="text-center font-semibold text-xs border-r w-16">%</TableHead>
                <!-- UTS -->
                <TableHead class="text-center font-semibold text-xs border-r w-10">UTS</TableHead>
                <TableHead class="text-center font-semibold text-xs border-r w-16">%</TableHead>
                <!-- UAS -->
                <TableHead class="text-center font-semibold text-xs border-r w-10">UAS</TableHead>
                <TableHead class="text-center font-semibold text-xs border-r w-16">%</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="(mhs, idx) in filteredMahasiswas"
                :key="mhs.id"
                class="hover:bg-gray-50/40 transition-colors"
              >
                <TableCell class="text-center border-r font-medium text-xs">{{ idx + 1 }}</TableCell>
                <TableCell class="text-center border-r text-xs font-mono text-gray-600">{{ mhs.nim }}</TableCell>
                <TableCell class="border-r text-xs font-semibold text-gray-800 leading-tight">{{ mhs.nama }}</TableCell>
                
                <!-- Tugas Values -->
                <TableCell v-for="(tVal, tIdx) in mhs.tugas_list" :key="`tval-${tIdx}`" class="text-center border-r text-xs text-gray-600">{{ tVal }}</TableCell>
                <TableCell class="text-center border-r text-xs text-gray-500 font-mono">{{ mhs.persentase_tugas }}%</TableCell>
                
                <!-- Aktif Values -->
                <TableCell class="text-center border-r text-xs text-gray-600">{{ mhs.nilai_keaktifan }}</TableCell>
                <TableCell class="text-center border-r text-xs text-gray-500 font-mono">{{ mhs.persentase_keaktifan }}%</TableCell>
                
                <!-- Etika Values -->
                <TableCell class="text-center border-r text-xs text-gray-600">{{ mhs.nilai_etika }}</TableCell>
                <TableCell class="text-center border-r text-xs text-gray-500 font-mono">{{ mhs.persentase_etika }}%</TableCell>
                
                <!-- Presensi Values -->
                <TableCell class="text-center border-r text-xs text-gray-600 font-medium">{{ mhs.total_kehadiran }}</TableCell>
                <TableCell class="text-center border-r text-xs text-gray-500 font-mono">{{ mhs.persentase_kehadiran }}%</TableCell>
                
                <!-- UTS Values -->
                <TableCell class="text-center border-r text-xs text-gray-600">{{ mhs.nilai_uts }}</TableCell>
                <TableCell class="text-center border-r text-xs text-gray-500 font-mono">{{ mhs.persentase_uts }}%</TableCell>
                
                <!-- UAS Values -->
                <TableCell class="text-center border-r text-xs text-gray-600">{{ mhs.nilai_uas }}</TableCell>
                <TableCell class="text-center border-r text-xs text-gray-500 font-mono">{{ mhs.persentase_uas }}%</TableCell>
                
                <!-- Total & NA Badge -->
                <TableCell class="text-center border-r text-xs font-bold text-[#4B49AC] bg-indigo-50/20 font-mono">{{ mhs.jumlah_total }}%</TableCell>
                <TableCell class="text-center">
                  <span :class="getGradeBadgeClass(mhs.nilai_huruf)">
                    {{ mhs.nilai_huruf }}
                  </span>
                </TableCell>
              </TableRow>
              <TableRow v-if="filteredMahasiswas.length === 0">
                <TableCell :colspan="16 + jumlahTugas" class="h-32 text-center text-gray-400 text-sm">
                  Tidak ada data mahasiswa ditemukan.
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <!-- Academic Signatures Section -->
        <div class="mt-12 hidden print:grid grid-cols-3 gap-6 text-sm text-gray-800 print-sig-container">
          <div class="space-y-1 print-sig-col">
            <p class="font-semibold">Mengesahkan,</p>
            <p class="font-bold text-gray-600">Kaprodi</p>
            <div class="h-20"></div>
            <p class="font-bold text-gray-900 border-b w-max pr-6 print-sig-name">{{ kaprodi?.nama ?? '( Belum Diatur )' }}</p>
            <p class="text-xs text-gray-400">Politeknik Sawunggalih Aji</p>
          </div>

          <div class="space-y-1 print-sig-col text-center print-sig-center">
            <p class="font-semibold">Mengetahui,</p>
            <p class="font-bold text-gray-600">Wakil Direktur I</p>
            <div class="h-20"></div>
            <p class="font-bold text-gray-900 border-b w-max mx-auto px-6 print-sig-name">{{ wadir?.nama ?? '( Belum Diatur )' }}</p>
            <p class="text-xs text-gray-400">Politeknik Sawunggalih Aji</p>
          </div>

          <div class="space-y-1 print-sig-col text-right flex flex-col items-end">
            <p class="font-semibold text-gray-500">Purworejo, {{ tanggal_sekarang }}</p>
            <p class="font-bold text-gray-600">Dosen Pengampu,</p>
            <div class="h-20"></div>
            <p class="font-bold text-gray-900 border-b w-max print-sig-name">{{ jadwal.dosen?.nama }}</p>
            <p class="text-xs text-gray-400 text-right">NIDN. {{ jadwal.dosen?.nidn ?? '-' }}</p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.font-nunito {
  font-family: 'Nunito', sans-serif;
}
</style>

<style>
@media print {
  /* Set A4 Landscape printable frame */
  @page {
    size: A4 landscape;
    margin: 1cm !important;
  }
  
  /* Hide AdminLayout elements globally when printing this page */
  nav, aside, footer, header, .print\:hidden {
    display: none !important;
  }

  main {
    margin: 0 !important;
    margin-left: 0 !important;
    padding: 0 !important;
    width: 100% !important;
  }

  /* Reset layout wrapper top padding and screen background */
  div.flex.flex-1.pt-\[70px\] {
    padding-top: 0 !important;
  }

  .min-h-screen {
    background-color: white !important;
    min-height: auto !important;
  }

  .p-6 {
    padding: 0 !important;
  }

  .space-y-6 {
    margin: 0 !important;
    padding: 0 !important;
  }

  #print-area {
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
  }

  /* Traditional table style overlays for document printing */
  table {
    border-collapse: collapse !important;
    width: 100% !important;
  }

  th, td {
    border: 1px solid #000000 !important;
    color: #000000 !important;
    padding: 4px 6px !important;
    font-size: 9px !important;
    background: transparent !important;
  }

  /* Keep badge styles flat and legible in monochrome/color prints */
  span {
    background: none !important;
    border: none !important;
    padding: 0 !important;
    font-weight: bold !important;
    color: #000000 !important;
  }
}
</style>
