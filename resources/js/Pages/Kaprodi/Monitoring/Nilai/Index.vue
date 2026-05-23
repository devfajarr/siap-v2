<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import { Input } from '@/Components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select'
import { School, ArrowRight, Search, BookOpen, Grid, AlertCircle, Award } from 'lucide-vue-next'

const props = defineProps({
  kelas: {
    type: Array,
    required: true
  }
})

const searchQuery = ref('')
const selectedSemester = ref('all')

const uniqueSemesters = computed(() => {
  const sems = props.kelas
    .map(k => k.semester?.semester)
    .filter((s) => s !== undefined && s !== null)
  return [...new Set(sems)].sort((a, b) => a - b)
})

const filteredKelas = computed(() => {
  return props.kelas.filter(k => {
    const matchesSearch = k.nama_kelas.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesSemester = selectedSemester.value === 'all' || String(k.semester?.semester) === selectedSemester.value
    return matchesSearch && matchesSemester
  })
})
</script>

<template>
  <AdminLayout>
    <Head title="Monitoring Nilai" />

    <div class="space-y-8">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-extrabold text-[#1F2937] tracking-tight">Monitoring Nilai</h1>
          <p class="text-[#6B7280] text-sm mt-1">Pilih kelas untuk memantau nilai akademik, rekapitulasi, dan kelulusan mahasiswa.</p>
        </div>
      </div>

      <!-- Stats Overview Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <Card class="border-none shadow-sm bg-gradient-to-br from-white to-slate-50/50 hover:shadow-md transition-shadow">
          <CardContent class="p-5 flex items-center gap-4">
            <div class="p-3 bg-[#4B49AC]/10 text-[#4B49AC] rounded-2xl">
              <School class="w-6 h-6" />
            </div>
            <div>
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Kelas</p>
              <h3 class="text-2xl font-bold text-gray-800">{{ kelas.length }}</h3>
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm bg-gradient-to-br from-white to-slate-50/50 hover:shadow-md transition-shadow">
          <CardContent class="p-5 flex items-center gap-4">
            <div class="p-3 bg-indigo-500/10 text-indigo-600 rounded-2xl">
              <BookOpen class="w-6 h-6" />
            </div>
            <div>
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Semester</p>
              <h3 class="text-2xl font-bold text-gray-800">{{ uniqueSemesters.length }}</h3>
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm bg-gradient-to-br from-white to-slate-50/50 hover:shadow-md transition-shadow">
          <CardContent class="p-5 flex items-center gap-4">
            <div class="p-3 bg-emerald-500/10 text-emerald-600 rounded-2xl">
              <Grid class="w-6 h-6" />
            </div>
            <div>
              <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Kelas Terfilter</p>
              <h3 class="text-2xl font-bold text-gray-800">{{ filteredKelas.length }}</h3>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Filters Section -->
      <Card class="border-none shadow-sm bg-white p-4">
        <div class="flex flex-col sm:flex-row gap-4 items-center">
          <div class="relative w-full sm:flex-1">
            <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <Input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Cari kelas berdasarkan nama..." 
              class="pl-10 h-11 border-gray-200 focus-visible:ring-[#4B49AC] rounded-xl text-sm"
            />
          </div>
          <div class="w-full sm:w-[220px]">
            <Select v-model="selectedSemester">
              <SelectTrigger class="h-11 border-gray-200 focus:ring-[#4B49AC] rounded-xl text-sm w-full">
                <SelectValue placeholder="Pilih Semester" />
              </SelectTrigger>
              <SelectContent class="rounded-xl">
                <SelectItem value="all" class="text-sm rounded-lg">Semua Semester</SelectItem>
                <SelectItem 
                  v-for="sem in uniqueSemesters" 
                  :key="sem" 
                  :value="String(sem)"
                  class="text-sm rounded-lg"
                >
                  Semester {{ sem }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </Card>

      <!-- Classes Grid -->
      <div v-if="filteredKelas.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link 
          v-for="item in filteredKelas" 
          :key="item.id"
          :href="route('v2.kaprodi.monitoring.nilai.detail', item.id)"
          class="group"
        >
          <Card class="border border-slate-100 hover:border-[#4B49AC]/20 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 rounded-2xl overflow-hidden bg-white">
            <CardHeader class="pb-3 border-b border-slate-50 bg-[#4B49AC]/[0.02] p-6">
              <div class="flex items-center justify-between">
                <div class="p-2.5 bg-white border border-amber-200 rounded-xl group-hover:scale-110 transition-transform duration-300 shadow-sm">
                  <Award class="w-5 h-5 text-amber-500" />
                </div>
                <Badge variant="secondary" class="bg-indigo-50 text-[#4B49AC] font-bold border border-indigo-100/50 px-2.5 py-1 text-[11px] rounded-lg">
                  {{ item.semester?.semester ? `Semester ${item.semester.semester}` : '-' }}
                </Badge>
              </div>
              <CardTitle class="text-lg font-bold mt-4 text-gray-800 group-hover:text-[#4B49AC] transition-colors leading-tight">
                Kelas {{ item.nama_kelas }}
              </CardTitle>
            </CardHeader>
            <CardContent class="pt-4 pb-5 px-6">
              <div class="flex items-center justify-between text-xs font-bold text-[#4B49AC] transition-colors">
                <span class="tracking-wider text-slate-400 group-hover:text-[#4B49AC] transition-colors">LIHAT DETAIL NILAI</span>
                <div class="w-6 h-6 rounded-full bg-slate-50 group-hover:bg-[#4B49AC]/10 flex items-center justify-center transition-colors">
                  <ArrowRight class="w-3.5 h-3.5 text-[#6B7280] group-hover:text-[#4B49AC] group-hover:translate-x-0.5 transition-all" />
                </div>
              </div>
            </CardContent>
          </Card>
        </Link>
      </div>

      <!-- Empty State -->
      <div v-else class="py-16 text-center">
        <Card class="border-dashed border-2 border-slate-200 bg-slate-50/50 max-w-md mx-auto rounded-2xl shadow-sm">
          <CardContent class="p-8 flex flex-col items-center">
            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mb-4">
              <AlertCircle class="w-6 h-6 text-slate-400" />
            </div>
            <h4 class="text-sm font-bold text-gray-800">Tidak ada kelas ditemukan</h4>
            <p class="text-xs text-gray-500 max-w-[280px] mt-1 leading-relaxed">
              Tidak ada kelas yang cocok dengan kata kunci pencarian atau filter semester yang dipilih.
            </p>
          </CardContent>
        </Card>
      </div>
    </div>
  </AdminLayout>
</template>
