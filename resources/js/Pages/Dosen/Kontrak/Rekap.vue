<script setup>
import { onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { Printer, ArrowLeft } from 'lucide-vue-next'
import { Button } from '@/Components/ui/button'

const props = defineProps({
  jadwal: {
    type: Object,
    required: true
  },
  kontraks: {
    type: Array,
    required: true
  },
  kaprodi: {
    type: Object,
    default: null
  },
  wadir: {
    type: Object,
    default: null
  }
})

// Auto trigger print on page load, identical to legacy V1 behavior
onMounted(() => {
  setTimeout(() => {
    window.print()
  }, 500)
})

const printPage = () => {
  window.print()
}

// Calculate SKS
const totalSks = (props.jadwal.matkul?.praktek || 0) + (props.jadwal.matkul?.teori || 0)

// Helper to get contract data for a specific meeting row
const getMeetingData = (index) => {
  // meetings in V1 map like:
  // index 1-7: map to contracts at adjustedIndex = index - 1
  // index 8: UTS (no contract row)
  // index 9-15: map to contracts at adjustedIndex = index - 2
  // index 16: UAS (no contract row)
  if (index === 8) {
    return { materi: 'UTS', pustaka: '-' }
  }
  if (index === 16) {
    return { materi: 'UAS', pustaka: '-' }
  }

  const adjustedIndex = index > 8 ? index - 2 : index - 1
  const contract = props.kontraks[adjustedIndex]
  return {
    materi: contract ? contract.materi || '' : '',
    pustaka: contract ? contract.pustaka || '' : ''
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-100 py-6 px-4 font-serif">
    <Head title="Cetak Rekap Kontrak Perkuliahan" />

    <!-- Top Navigation Bar (Hidden during print) -->
    <div class="no-print max-w-[760px] mx-auto mb-6 flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-slate-200">
      <Link :href="route('v2.dosen.kontrak.index')">
        <Button variant="outline" class="flex items-center gap-1.5 text-slate-700 font-sans font-bold">
          <ArrowLeft class="w-4 h-4" />
          Kembali ke Dashboard
        </Button>
      </Link>
      <Button @click="printPage" class="bg-[#4B49AC] hover:bg-[#3f3e91] text-white flex items-center gap-1.5 font-sans font-bold shadow-sm">
        <Printer class="w-4 h-4" />
        Cetak Dokumen
      </Button>
    </div>

    <!-- Paper Sheet Document Container -->
    <div class="print-area max-w-[760px] mx-auto bg-white p-8 md:p-12 shadow-md border border-slate-200 print:shadow-none print:border-none print:p-0">
      <!-- Kop Surat -->
      <div class="flex items-start gap-4 border-b-[2px] border-black pb-2">
        <img src="/images/logomini2.png" alt="Logo Polsa" class="w-16 h-16 object-contain" />
        <div class="flex-1 font-sans">
          <h3 class="text-sm font-black tracking-wide text-white stroke-black text-shadow-kop" style="text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;">
            POLITEKNIK
          </h3>
          <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 leading-none mt-0.5">
            SAWUNGGALIH AJI
          </h2>
          <p class="text-[9px] font-bold text-slate-700 mt-1 leading-normal">
            Jl. Wismoaji No. 8 Kutoarjo, Purworejo, Jawa Tengah, Indonesia Kode Pos 54212
          </p>
          <p class="text-[8px] text-slate-600 leading-normal">
            Telp. (0275)642466 (HUNTING) (0275) 3410444 Fax (0275) 642467 | Http://www.polsa.ac.id E-mail : info@polsa.ac.id
          </p>
        </div>
      </div>

      <!-- Title -->
      <h3 class="text-center font-bold text-base mt-4 mb-4 tracking-wide text-black uppercase">
        KONTRAK PERKULIAHAN
      </h3>

      <!-- Metadata -->
      <div class="space-y-1 text-[11px] text-black leading-relaxed">
        <div class="flex">
          <span class="w-[180px] shrink-0">Mata Kuliah/Bobot SKS</span>
          <span class="mr-2">:</span>
          <span class="font-bold">{{ jadwal.matkul?.nama_matkul }} / {{ totalSks }} SKS</span>
        </div>
        <div class="flex">
          <span class="w-[180px] shrink-0">Prodi/Semester</span>
          <span class="mr-2">:</span>
          <span>{{ jadwal.kelas?.prodi?.nama_prodi }} / Semester {{ jadwal.kelas?.semester?.semester }}</span>
        </div>
        <div class="flex mt-2">
          <span class="font-semibold underline">Materi Perkuliahan Satu Semester</span>
        </div>
      </div>

      <!-- Syllabus Table -->
      <table class="w-full border-collapse mt-3 border border-black text-[11px]">
        <thead>
          <tr class="bg-slate-800 text-white print:bg-slate-800 print:text-white">
            <th class="border border-black px-2 py-1.5 text-center font-bold w-[80px]">Pertemuan</th>
            <th class="border border-black px-3 py-1.5 text-left font-bold w-[380px]">Materi Perkuliahan</th>
            <th class="border border-black px-3 py-1.5 text-left font-bold">Daftar Pustaka</th>
          </tr>
        </thead>
        <tbody>
          <tr 
            v-for="i in 16" 
            :key="i"
            :class="i % 2 === 0 ? 'bg-slate-50 print:bg-white' : 'bg-white'"
          >
            <!-- Pertemuan Number -->
            <td class="border border-black px-2 py-1 text-center font-mono font-bold">{{ i }}</td>

            <!-- UTS and UAS special rows -->
            <template v-if="i === 8">
              <td colspan="2" class="border border-black px-3 py-1 text-center font-bold tracking-widest bg-slate-100">
                UJIAN TENGAH SEMESTER (UTS)
              </td>
            </template>
            <template v-else-if="i === 16">
              <td colspan="2" class="border border-black px-3 py-1 text-center font-bold tracking-widest bg-slate-100">
                UJIAN AKHIR SEMESTER (UAS)
              </td>
            </template>
            <template v-else>
              <!-- Regular meeting data -->
              <td class="border border-black px-3 py-1 text-left leading-tight">
                {{ getMeetingData(i).materi }}
              </td>
              <td class="border border-black px-3 py-1 text-left leading-tight">
                {{ getMeetingData(i).pustaka }}
              </td>
            </template>
          </tr>
        </tbody>
      </table>

      <!-- Grade Presentation Section -->
      <div class="mt-4 text-[11px] leading-relaxed text-black">
        <p class="font-bold underline mb-1">Presentasi Perkuliahan</p>
        <table class="w-auto border-none text-[11px]">
          <tbody>
            <tr>
              <td class="pr-2 py-0.5 text-left w-[160px]">1. Presensi/Kehadiran</td>
              <td class="px-2 py-0.5 text-center">:</td>
              <td class="px-2 py-0.5 text-left font-bold">15%</td>
            </tr>
            <tr>
              <td class="pr-2 py-0.5 text-left">2. Tugas</td>
              <td class="px-2 py-0.5 text-center">:</td>
              <td class="px-2 py-0.5 text-left font-bold">20%</td>
            </tr>
            <tr>
              <td class="pr-2 py-0.5 text-left">3. Sikap dan Keaktifan</td>
              <td class="px-2 py-0.5 text-center">:</td>
              <td class="px-2 py-0.5 text-left font-bold">15%</td>
            </tr>
            <tr>
              <td class="pr-2 py-0.5 text-left">4. UTS</td>
              <td class="px-2 py-0.5 text-center">:</td>
              <td class="px-2 py-0.5 text-left font-bold">25%</td>
            </tr>
            <tr>
              <td class="pr-2 py-0.5 text-left border-b border-black">5. UAS</td>
              <td class="px-2 py-0.5 text-center border-b border-black">:</td>
              <td class="px-2 py-0.5 text-left font-bold border-b border-black">25%</td>
            </tr>
            <tr class="font-bold">
              <td class="pr-2 py-1 text-left">Total</td>
              <td class="px-2 py-1 text-center">:</td>
              <td class="px-2 py-1 text-left">100%</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Signatures Grid -->
      <div class="mt-6 text-[11px] leading-normal text-black page-break-avoid">
        <div class="grid grid-cols-2 gap-8 text-center">
          <div>
            <p>Dosen Pengampu</p>
            <div class="h-20"></div>
            <p class="font-bold underline">{{ jadwal.dosen?.nama }}</p>
          </div>
          <div>
            <p>Komting Mahasiswa</p>
            <div class="h-20"></div>
            <p>.....................................................</p>
          </div>
        </div>

        <div class="text-center font-bold my-4 uppercase tracking-wider text-[10px]">
          Mengetahui,
        </div>

        <div class="grid grid-cols-2 gap-8 text-center">
          <div>
            <p>Ketua Program Studi</p>
            <div class="h-20"></div>
            <p class="font-bold underline">{{ kaprodi ? kaprodi.nama : '.....................................................' }}</p>
          </div>
          <div>
            <p>Wakil Direktur I Bidang Akademik</p>
            <div class="h-20"></div>
            <p class="font-bold underline">{{ wadir ? wadir.nama : '.....................................................' }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
/* Print-specific overrides */
@media print {
  body {
    background-color: white !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  
  .min-h-screen {
    min-height: auto !important;
    background-color: white !important;
    padding: 0 !important;
  }

  .no-print {
    display: none !important;
  }

  .print-area {
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    margin: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
  }

  /* Keep colors when printing */
  th {
    background-color: #1e293b !important;
    color: white !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  .bg-slate-100 {
    background-color: #f1f5f9 !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  .page-break-avoid {
    page-break-inside: avoid !important;
  }
}

.text-shadow-kop {
  -webkit-text-stroke: 0.5px black;
}
</style>
