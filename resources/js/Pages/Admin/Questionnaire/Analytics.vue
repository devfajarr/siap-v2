<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent } from '@/Components/ui/card'
import { 
  ArrowLeft, 
  Download, 
  Users, 
  FileSpreadsheet, 
  FileText,
  HelpCircle,
  CheckCircle2,
  ClipboardCheck,
  List,
  BarChart3
} from 'lucide-vue-next'

const props = defineProps({
  questionnaire: Object,
  totalRespondents: Number,
  targetStats: {
    type: Object,
    default: () => ({
      total: 0,
      filled: 0,
      pending: 0,
      filled_percentage: 0,
      pending_percentage: 0
    })
  },
  analytics: Array,
  category: String,
  categoryName: String,
})

const isExporting = ref(false)
const viewModes = ref({})

const getViewMode = (questionId) => {
  return viewModes.value[questionId] || 'list'
}

const setViewMode = (questionId, mode) => {
  viewModes.value[questionId] = mode
}

const handleExport = (format) => {
  window.location.href = `/v2/admin/kuisioner/${props.category}/${props.questionnaire.id}/export/${format}`
}

const getRespondentBadge = (target) => {
  const map = {
    all: 'Semua Responden',
    mahasiswa: 'Mahasiswa',
    dosen: 'Dosen',
    pegawai: 'Pegawai / Staf',
    dosen_pegawai: 'Dosen dan Pegawai',
  }
  return map[target] || target
}
</script>

<template>
  <AdminLayout>
    <Head title="Analisis Jawaban Kuisioner" />

    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
          <Link :href="`/v2/admin/kuisioner/${category}`">
            <Button variant="outline" size="sm" class="border-gray-200 text-gray-500 rounded-lg">
              <ArrowLeft class="w-4 h-4 mr-2" />
              Kembali
            </Button>
          </Link>
          <div>
            <h1 class="text-2xl font-bold text-gray-800">Analisis Jawaban</h1>
            <p class="text-[#6B7280] text-sm">Statistik respon dan unduhan laporan lengkap.</p>
          </div>
        </div>

        <!-- Export Buttons Group -->
        <div class="flex items-center gap-2">
          <Button @click="handleExport('xlsx')" class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm">
            <FileSpreadsheet class="w-4 h-4 mr-2" />
            Ekspor Excel
          </Button>
          <Button @click="handleExport('csv')" variant="outline" class="border-gray-200 text-gray-700 hover:bg-gray-50 rounded-lg">
            <Download class="w-4 h-4 mr-2" />
            Ekspor CSV
          </Button>
        </div>
      </div>

      <!-- Overview Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card 1: Questionnaire Details (col-span-2) -->
        <Card class="border-none shadow-sm md:col-span-2 bg-white flex flex-col justify-between">
          <CardContent class="p-6 h-full flex flex-col justify-between">
            <div class="space-y-3">
              <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 text-xs font-extrabold rounded-md uppercase tracking-wider"
                  :class="{
                    'bg-[#4B49AC]/10 text-[#4B49AC]': category === 'pelayanan',
                    'bg-amber-100 text-amber-800': category === 'kinerja-pengajar',
                    'bg-emerald-100 text-emerald-800': category === 'ami',
                  }"
                >
                  {{ categoryName }}
                </span>
                <span class="px-2.5 py-1 text-xs font-extrabold rounded-md uppercase tracking-wider"
                  :class="questionnaire.status === 'aktif' || questionnaire.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                >
                  {{ questionnaire.status }}
                </span>
              </div>
              <h2 class="font-extrabold text-gray-800 text-xl leading-tight">{{ questionnaire.title }}</h2>
              <p v-if="questionnaire.description" class="text-sm text-[#6B7280] line-clamp-2 leading-relaxed">
                {{ questionnaire.description }}
              </p>
            </div>
            
            <div class="pt-4 border-t border-gray-100 mt-4 grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
              <div>
                <span class="text-xs text-gray-400 block font-semibold uppercase">Target Responden</span>
                <strong class="text-gray-700 font-bold">{{ getRespondentBadge(questionnaire.target_respondent) }}</strong>
              </div>
              <div>
                <span class="text-xs text-gray-400 block font-semibold uppercase">Telah Mengisi</span>
                <strong class="text-gray-700 font-bold">{{ targetStats.filled }} / {{ targetStats.total }} orang</strong>
              </div>
              <div class="col-span-2 sm:col-span-1">
                <span class="text-xs text-gray-400 block font-semibold uppercase">Tingkat Partisipasi</span>
                <strong class="text-[#4B49AC] font-black">{{ targetStats.filled_percentage }}%</strong>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Card 2: Rasio Pengisian (col-span-1) -->
        <Card class="border-none shadow-sm bg-white overflow-hidden">
          <CardContent class="p-6 flex flex-col items-center justify-center">
            <h3 class="text-sm font-bold text-gray-700 mb-4 self-start">Rasio Pengisian Kuisioner</h3>
            
            <div class="relative flex items-center justify-center">
              <!-- SVG Doughnut Chart -->
              <svg class="w-36 h-36 transform -rotate-90" viewBox="0 0 100 100">
                <!-- Background Circle -->
                <circle
                  cx="50"
                  cy="50"
                  r="38"
                  stroke="#F3F4F6"
                  stroke-width="8"
                  fill="transparent"
                />
                <!-- Filled Circle -->
                <circle
                  cx="50"
                  cy="50"
                  r="38"
                  stroke="#4B49AC"
                  stroke-width="8"
                  fill="transparent"
                  stroke-dasharray="238.76"
                  :stroke-dashoffset="238.76 - (targetStats.filled_percentage / 100) * 238.76"
                  stroke-linecap="round"
                  class="transition-all duration-1000 ease-out"
                />
              </svg>
              <!-- Center Text -->
              <div class="absolute flex flex-col items-center justify-center">
                <span class="text-2xl font-black text-gray-800">{{ targetStats.filled_percentage }}%</span>
                <span class="text-[10px] font-bold text-gray-400 uppercase">Selesai</span>
              </div>
            </div>

            <!-- Legends -->
            <div class="w-full grid grid-cols-2 gap-2 mt-5 pt-4 border-t border-gray-100 text-[11px] leading-tight">
              <div class="flex items-center gap-2">
                <div class="w-2.5 h-2.5 bg-[#4B49AC] rounded-full shrink-0"></div>
                <div class="truncate">
                  <span class="text-gray-500 block text-[9px] font-bold uppercase">Sudah</span>
                  <span class="font-bold text-gray-700">{{ targetStats.filled }} ({{ targetStats.filled_percentage }}%)</span>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-2.5 h-2.5 bg-gray-200 rounded-full shrink-0"></div>
                <div class="truncate">
                  <span class="text-gray-500 block text-[9px] font-bold uppercase">Belum</span>
                  <span class="font-bold text-gray-700">{{ targetStats.pending }} ({{ targetStats.pending_percentage }}%)</span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Analytics Per Section & Question Card -->
      <div class="space-y-8">
        <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
          <ClipboardCheck class="w-5 h-5 text-[#4B49AC]" />
          Rincian Jawaban Per Bagian & Pertanyaan
        </h2>

        <div v-for="(section, sIdx) in analytics" :key="section.id" class="space-y-4">
          <!-- Section Header Card -->
          <div class="bg-gradient-to-r from-[#4B49AC]/10 to-[#4B49AC]/5 p-5 rounded-xl border border-indigo-100 shadow-sm">
            <h3 class="font-extrabold text-[#4B49AC] text-lg flex items-center gap-2">
              <span class="flex items-center justify-center w-6 h-6 rounded-full bg-[#4B49AC] text-white text-xs font-bold shadow-xs">
                {{ sIdx + 1 }}
              </span>
              {{ section.title }}
            </h3>
            <p v-if="section.description" class="text-xs text-gray-500 mt-2 pl-8 leading-relaxed">{{ section.description }}</p>
          </div>

          <!-- Section Questions List -->
          <div class="space-y-4 pl-4 border-l-2 border-dashed border-indigo-100/70 ml-3">
            <div v-for="(stat, index) in section.questions" :key="stat.id">
              <Card class="border-none shadow-sm">
                <CardContent class="p-6 space-y-4">
                  <!-- Question Header -->
                  <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                      <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold shrink-0 mt-0.5">
                        {{ index + 1 }}
                      </span>
                      <div class="flex-1">
                        <h3 class="font-bold text-gray-800 text-base leading-tight">{{ stat.question_text }}</h3>
                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-1 flex items-center gap-2">
                          <span>Tipe: {{ stat.question_type }}</span>
                          <span v-if="stat.is_required" class="text-rose-500">* Wajib Diisi</span>
                        </div>
                      </div>
                    </div>

                    <!-- Toggle View Mode for Choice Question -->
                    <div v-if="['radio', 'checkbox', 'select'].includes(stat.question_type) && stat.total_answers > 0" class="flex items-center border border-gray-200 rounded-lg p-0.5 bg-gray-50 shrink-0 self-end sm:self-start">
                      <button 
                        @click="setViewMode(stat.id, 'list')"
                        class="p-1.5 rounded-md transition-all text-xs flex items-center gap-1 font-semibold"
                        :class="getViewMode(stat.id) === 'list' ? 'bg-white text-[#4B49AC] shadow-xs' : 'text-gray-500 hover:text-gray-800'"
                        title="Tampilan Persentase List"
                      >
                        <List class="w-3.5 h-3.5" />
                        <span>Persentase</span>
                      </button>
                      <button 
                        @click="setViewMode(stat.id, 'chart')"
                        class="p-1.5 rounded-md transition-all text-xs flex items-center gap-1 font-semibold"
                        :class="getViewMode(stat.id) === 'chart' ? 'bg-white text-[#4B49AC] shadow-xs' : 'text-gray-500 hover:text-gray-800'"
                        title="Tampilan Grafik Kolom"
                      >
                        <BarChart3 class="w-3.5 h-3.5" />
                        <span>Grafik</span>
                      </button>
                    </div>
                  </div>

                  <!-- Content for Choice Question Type (Radio, Checkbox, Select) -->
                  <div v-if="['radio', 'checkbox', 'select'].includes(stat.question_type)" class="pt-2">
                    <!-- 1. List View Mode -->
                    <div v-if="getViewMode(stat.id) === 'list'" class="space-y-3">
                      <div v-for="item in stat.data" :key="item.label" class="space-y-1">
                        <div class="flex items-center justify-between text-sm">
                          <span class="text-gray-700 font-medium">{{ item.label }}</span>
                          <span class="text-gray-500 font-semibold">{{ item.count }} Respon ({{ item.percentage }}%)</span>
                        </div>
                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-100 h-2.5 rounded-full overflow-hidden">
                          <div 
                            class="bg-[#4B49AC] h-full rounded-full transition-all duration-500" 
                            :style="{ width: `${item.percentage}%` }"
                          />
                        </div>
                      </div>
                    </div>

                    <!-- 2. Chart (Vertical Column) View Mode -->
                    <div v-else class="relative w-full pt-8 pb-4">
                      <!-- Grid lines behind the bars -->
                      <div class="absolute inset-x-0 top-8 bottom-12 flex flex-col justify-between pointer-events-none text-[9px] text-gray-300 font-semibold h-40 border-b border-gray-100">
                        <div class="border-t border-dashed border-gray-100 w-full pt-0.5 flex justify-between">
                          <span>100%</span>
                        </div>
                        <div class="border-t border-dashed border-gray-100 w-full pt-0.5 flex justify-between">
                          <span>75%</span>
                        </div>
                        <div class="border-t border-dashed border-gray-100 w-full pt-0.5 flex justify-between">
                          <span>50%</span>
                        </div>
                        <div class="border-t border-dashed border-gray-100 w-full pt-0.5 flex justify-between">
                          <span>25%</span>
                        </div>
                        <div class="w-full flex justify-between pt-0.5">
                          <span>0%</span>
                        </div>
                      </div>

                      <!-- The actual bars -->
                      <div class="relative z-10 flex items-end justify-around h-40 gap-4 px-8">
                        <div 
                          v-for="item in stat.data" 
                          :key="item.label" 
                          class="flex flex-col items-center flex-1 h-full justify-end group"
                        >
                          <!-- Label showing percentage/count above the bar -->
                          <span class="text-[10px] font-extrabold text-gray-600 mb-1 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            {{ item.count }} ({{ item.percentage }}%)
                          </span>
                          <!-- Bar -->
                          <div 
                            class="w-full max-w-[32px] bg-gradient-to-t from-[#4B49AC] to-indigo-400 rounded-t-md hover:from-indigo-600 hover:to-indigo-500 transition-all duration-500 ease-out cursor-pointer shadow-xs relative"
                            :style="{ height: `${item.percentage}%` }"
                          >
                            <!-- Glow on hover -->
                            <div class="absolute inset-0 bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-t-md"></div>
                          </div>
                          <!-- X-axis Label -->
                          <span 
                            class="text-xs font-semibold text-gray-500 mt-2 truncate w-full text-center max-w-[64px]"
                            :title="item.label"
                          >
                            {{ item.label }}
                          </span>
                        </div>
                      </div>
                    </div>

                    <div v-if="stat.total_answers === 0" class="text-sm text-gray-400 italic text-center py-4">
                      Belum ada jawaban untuk pertanyaan pilihan ini.
                    </div>
                  </div>

                  <!-- Content for Open Answer Type (Text, Paragraph) -->
                  <div v-else class="pt-2">
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 space-y-2">
                      <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center justify-between">
                        <span>Jawaban Teks Terkumpul (Maksimal 50)</span>
                        <span class="text-[#4B49AC]">{{ stat.total_answers }} Jawaban</span>
                      </div>

                      <div class="max-h-[250px] overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                        <div 
                          v-for="(textAnswer, textIdx) in stat.data" 
                          :key="textIdx" 
                          class="bg-white p-3 rounded-lg border border-gray-100 text-sm text-gray-700 shadow-sm"
                        >
                          {{ textAnswer }}
                        </div>

                        <div v-if="stat.total_answers === 0" class="text-sm text-gray-400 italic text-center py-6">
                          Belum ada jawaban teks bebas yang diisi.
                        </div>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
/* Custom scrollbar for answers */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #E2E8F0;
  border-radius: 10px;
}
</style>
