<script setup>
import { computed, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { Printer, ArrowLeft } from 'lucide-vue-next'

const props = defineProps({
  mahasiswa: {
    type: Object,
    required: true
  },
  tahunAkademik: {
    type: String,
    required: true
  },
  kaprodi: {
    type: String,
    required: true
  },
  items: {
    type: Array,
    default: () => []
  },
  rekap: {
    type: Object,
    required: true
  }
})

const currentDateFormatted = computed(() => {
  const date = new Date()
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
})

const triggerPrint = () => {
  window.print()
}

onMounted(() => {
  setTimeout(() => {
    window.print()
  }, 500)
})
</script>

<template>
  <Head :title="`KHS - ${mahasiswa.nim} - ${mahasiswa.nama_lengkap}`" />

  <div class="min-h-screen bg-gray-100 font-sans text-gray-900 pb-12">
    <!-- Top Action Bar (Hanya tampil di peramban, tersembunyi saat cetak) -->
    <div class="print:hidden bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <Link 
          href="/v2/mahasiswa/nilai" 
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-sm font-semibold transition-colors"
        >
          <ArrowLeft class="w-4 h-4" /> Kembali ke Rekap Nilai
        </Link>
        <div class="hidden md:block">
          <p class="text-xs text-gray-400 font-medium">Pratinjau Cetak Kartu Hasil Studi (KHS)</p>
          <h2 class="text-sm font-bold text-gray-800">{{ mahasiswa.nama_lengkap }} ({{ mahasiswa.nim }})</h2>
        </div>
      </div>

      <button 
        @click="triggerPrint" 
        class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-xl shadow-md font-bold text-sm transition-all"
      >
        <Printer class="w-4 h-4" /> Cetak Dokumen KHS
      </button>
    </div>

    <!-- Print Sheet Container -->
    <div class="max-w-[210mm] mx-auto my-8 print:my-0 bg-white shadow-lg print:shadow-none p-[15mm] md:p-[20mm] print:p-0 text-black leading-snug">
      <!-- Kop Surat PSA -->
      <div class="flex items-center justify-between border-b-[3px] border-black pb-4 mb-6">
        <div class="flex items-center gap-6">
          <img src="/images/file.png" alt="Logo POLSA" class="w-24 h-24 shrink-0 object-contain" />
          <div class="text-left space-y-0.5">
            <h1 class="text-xl font-extrabold uppercase tracking-wide text-gray-900">Yayasan Sawunggalih Aji Purworejo</h1>
            <p class="text-xs italic font-serif text-gray-700">Sawunggalih Aji Foundation Purworejo</p>
            <h2 class="text-2xl font-black uppercase text-black tracking-wider">Politeknik Sawunggalih Aji</h2>
            <p class="text-xs italic font-serif text-gray-700">Sawunggalih Aji Polytechnic</p>
          </div>
        </div>
        <div class="text-right text-xs font-medium space-y-0.5">
          <p>Jl. Wismoaji No. 8 Kutoarjo, Purworejo</p>
          <p>Telp. (0275) 642466, 3140444</p>
          <p>Fax. (0275) 642467</p>
        </div>
      </div>

      <!-- Judul Dokumen -->
      <div class="text-center mb-8">
        <h3 class="text-lg font-black underline uppercase tracking-widest text-black">Kartu Hasil Studi</h3>
        <p class="text-xs italic font-serif text-gray-600">Study Result File</p>
      </div>

      <!-- Identitas Mahasiswa -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-xs md:text-sm mb-8">
        <!-- Kiri -->
        <div class="space-y-2">
          <div class="grid grid-cols-3">
            <div class="font-bold">
              <div>NIM</div>
              <div class="text-[10px] italic font-serif font-normal text-gray-600">Student Number</div>
            </div>
            <div class="col-span-2 font-mono font-bold">: {{ mahasiswa.nim }}</div>
          </div>
          <div class="grid grid-cols-3">
            <div class="font-bold">
              <div>NAMA</div>
              <div class="text-[10px] italic font-serif font-normal text-gray-600">Name of Student</div>
            </div>
            <div class="col-span-2 font-bold uppercase">: {{ mahasiswa.nama_lengkap }}</div>
          </div>
          <div class="grid grid-cols-3">
            <div class="font-bold">
              <div>SEMESTER / T.A</div>
              <div class="text-[10px] italic font-serif font-normal text-gray-600">Semester / Academic Year</div>
            </div>
            <div class="col-span-2 font-semibold">: {{ mahasiswa.semester_romawi }} / {{ tahunAkademik }}</div>
          </div>
        </div>

        <!-- Kanan -->
        <div class="space-y-2">
          <div class="grid grid-cols-3">
            <div class="font-bold">
              <div>PROGRAM STUDI</div>
              <div class="text-[10px] italic font-serif font-normal text-gray-600">Study Program</div>
            </div>
            <div class="col-span-2 font-semibold">: {{ mahasiswa.prodi_nama }}</div>
          </div>
          <div class="grid grid-cols-3">
            <div class="font-bold">
              <div>ALIAS</div>
              <div class="text-[10px] italic font-serif font-normal text-gray-600">Alias Program</div>
            </div>
            <div class="col-span-2 font-semibold">: {{ mahasiswa.prodi_alias }}</div>
          </div>
          <div class="grid grid-cols-3">
            <div class="font-bold">
              <div>JENJANG</div>
              <div class="text-[10px] italic font-serif font-normal text-gray-600">Degree</div>
            </div>
            <div class="col-span-2 font-semibold">: {{ mahasiswa.jenjang }} ({{ mahasiswa.alias_jenjang }})</div>
          </div>
        </div>
      </div>

      <!-- Tabel KHS -->
      <table class="w-full border-collapse border border-black text-xs md:text-sm mb-6">
        <thead>
          <tr class="bg-gray-100 border-b border-black text-center font-bold">
            <th class="border border-black py-2.5 px-3 w-12">
              <div>No</div>
              <div class="text-[10px] font-normal italic font-serif">No</div>
            </th>
            <th class="border border-black py-2.5 px-3 w-28">
              <div>Kode</div>
              <div class="text-[10px] font-normal italic font-serif">Code</div>
            </th>
            <th class="border border-black py-2.5 px-4 text-left">
              <div>Mata Kuliah</div>
              <div class="text-[10px] font-normal italic font-serif">Courses</div>
            </th>
            <th class="border border-black py-2.5 px-3 text-center w-16">
              <div>SKS</div>
              <div class="text-[10px] font-normal italic font-serif">Credit</div>
            </th>
            <th class="border border-black py-2.5 px-3 text-center w-20">
              <div>Nilai</div>
              <div class="text-[10px] font-normal italic font-serif">Grade</div>
            </th>
            <th class="border border-black py-2.5 px-3 text-center w-20">
              <div>Kredit</div>
              <div class="text-[10px] font-normal italic font-serif">Point</div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, idx) in items" :key="idx" class="border-b border-black">
            <td class="border border-black py-2 px-3 text-center font-medium">{{ idx + 1 }}</td>
            <td class="border border-black py-2 px-3 text-center font-mono font-bold">{{ item.kode }}</td>
            <td class="border border-black py-2 px-4 text-left">
              <div class="font-bold text-gray-900">{{ item.nama_matkul }}</div>
              <div class="text-[11px] text-gray-700 italic">({{ item.alias }})</div>
            </td>
            <td class="border border-black py-2 px-3 text-center font-bold">{{ item.sks }}</td>
            <td class="border border-black py-2 px-3 text-center font-bold text-base">{{ item.nilai_huruf }}</td>
            <td class="border border-black py-2 px-3 text-center font-bold">{{ item.kredit }}</td>
          </tr>

          <!-- Summary Rows -->
          <tr class="bg-gray-50 border-t-2 border-black font-bold">
            <td colspan="3" class="border border-black py-3 px-4 text-right">
              <div>JUMLAH (Total)</div>
            </td>
            <td class="border border-black py-3 px-3 text-center text-base font-black">{{ rekap.sks_ips }}</td>
            <td class="border border-black py-3 px-3 text-center">-</td>
            <td class="border border-black py-3 px-3 text-center text-base font-black">{{ rekap.kredit_ips }}</td>
          </tr>
          <tr class="bg-gray-50 font-bold">
            <td colspan="3" class="border border-black py-3 px-4 text-right">
              <div>KUMULATIF (Cumulative)</div>
            </td>
            <td class="border border-black py-3 px-3 text-center text-base font-black">{{ rekap.sks_ipk }}</td>
            <td class="border border-black py-3 px-3 text-center">-</td>
            <td class="border border-black py-3 px-3 text-center text-base font-black">{{ rekap.kredit_ipk }}</td>
          </tr>
          <!-- IPK / IPS Final Row -->
          <tr class="bg-indigo-50/50 font-bold text-sm">
            <td colspan="3" class="border border-black py-3 px-4 text-center">
              <div>Indeks Prestasi Semester (IPS)</div>
              <div class="text-lg font-black text-[#4B49AC]">IPS : {{ rekap.ips }}</div>
              <div class="text-[10px] font-normal italic font-serif">Grade Point Average</div>
            </td>
            <td colspan="3" class="border border-black py-3 px-4 text-center">
              <div>Indeks Prestasi Kumulatif (IPK)</div>
              <div class="text-lg font-black text-[#4B49AC]">IPK : {{ rekap.ipk }}</div>
              <div class="text-[10px] font-normal italic font-serif">Cumulative GPA</div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Tanggal & Tanda Tangan -->
      <div class="mt-8 pt-4">
        <div class="text-right text-xs md:text-sm mb-8 pr-12">
          Purworejo, {{ currentDateFormatted }}
        </div>

        <div class="grid grid-cols-2 gap-8 text-center text-xs md:text-sm">
          <!-- Ketua Prodi -->
          <div class="space-y-20">
            <div>
              <p class="font-bold">Ketua Program Studi,</p>
              <p class="text-xs italic font-serif text-gray-600">Head of Study Program</p>
            </div>
            <p class="font-bold underline uppercase">{{ kaprodi }}</p>
          </div>

          <!-- Pembimbing Akademik -->
          <div class="space-y-20">
            <div>
              <p class="font-bold">Pembimbing Akademik,</p>
              <p class="text-xs italic font-serif text-gray-600">Academic Supervisor</p>
            </div>
            <p class="font-bold underline uppercase">{{ mahasiswa.pembimbing_akademik }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@media print {
  body {
    background-color: white !important;
    color: black !important;
  }
  @page {
    size: A4 portrait;
    margin: 15mm;
  }
}
</style>
