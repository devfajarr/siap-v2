<script setup>
import { Head, Link } from '@inertiajs/vue3'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card, CardContent } from '@/Components/ui/card'
import { 
  ClipboardCheck, 
  ArrowRight, 
  CheckCircle2, 
  Clock,
  HelpCircle,
  Sparkles
} from 'lucide-vue-next'

defineProps({
  questionnaires: Array,
})

const getCategoryLabel = (type) => {
  const map = {
    pelayanan: 'Kuis Pelayanan',
    kinerja_pengajar: 'Kinerja Pengajar',
    ami: 'Kuisioner AMI',
  }
  return map[type] || type
}

const getCategoryClass = (type) => {
  const map = {
    pelayanan: 'bg-indigo-50 text-indigo-700 border-indigo-100',
    kinerja_pengajar: 'bg-emerald-50 text-emerald-700 border-emerald-100',
    ami: 'bg-amber-50 text-amber-700 border-amber-100',
  }
  return map[type] || 'bg-slate-50 text-slate-700 border-slate-100'
}
</script>

<template>
  <AdminLayout>
    <Head title="Daftar Kuisioner Aktif" />

    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Welcome Header -->
      <div class="flex items-center justify-between p-6 bg-gradient-to-r from-[#4B49AC] to-[#4B49AC]/80 text-white rounded-2xl shadow-md relative overflow-hidden">
        <div class="space-y-2 relative z-10">
          <h1 class="text-2xl sm:text-3xl font-extrabold flex items-center gap-2">
            <Sparkles class="w-7 h-7 text-amber-300 animate-pulse shrink-0" />
            Kuisioner SIAP
          </h1>
          <p class="text-slate-100 text-xs sm:text-sm leading-relaxed max-w-xl">
            Partisipasi Anda sangat penting untuk membantu meningkatkan mutu pelayanan akademik dan evaluasi pembelajaran di kampus. Silakan isi kuisioner yang aktif di bawah ini.
          </p>
        </div>
        <div class="absolute right-0 bottom-0 opacity-10 translate-x-1/4 translate-y-1/4">
          <ClipboardCheck class="w-48 h-48" />
        </div>
      </div>

      <!-- Main Section -->
      <div class="space-y-4">
        <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
          <ClipboardCheck class="w-5 h-5 text-[#4B49AC]" />
          Daftar Kuisioner Tersedia
        </h2>

        <!-- Cards List -->
        <div v-if="questionnaires && questionnaires.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="q in questionnaires" :key="q.id">
            <Card class="border border-slate-100 shadow-sm hover:shadow-md hover:border-[#4B49AC]/20 transition-all duration-300 flex flex-col justify-between h-full rounded-xl overflow-hidden bg-white">
              <CardContent class="p-5 flex flex-col justify-between h-full space-y-5">
                
                <div class="space-y-3">
                  <!-- Category Badge & Status Badge -->
                  <div class="flex items-center justify-between gap-2">
                    <span 
                      class="px-2.5 py-0.5 text-xs font-bold rounded-full border"
                      :class="getCategoryClass(q.type)"
                    >
                      {{ getCategoryLabel(q.type) }}
                    </span>
                    
                    <!-- Completion Status -->
                    <span 
                      v-if="q.type === 'kinerja_pengajar'" 
                      class="flex items-center gap-1 text-[10px] sm:text-xs font-semibold text-[#4B49AC] bg-indigo-50/50 px-2 py-0.5 rounded-full border border-[#4B49AC]/10"
                    >
                      <Clock class="w-3.5 h-3.5 text-[#4B49AC]" />
                      Progress: {{ q.completed_teachers_count }}/{{ q.total_teachers_count }} Dosen
                    </span>
                    <span 
                      v-else-if="q.responses_count > 0" 
                      class="flex items-center gap-1 text-[10px] sm:text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100"
                    >
                      <CheckCircle2 class="w-3.5 h-3.5" />
                      Sudah Diisi
                    </span>
                    <span 
                      v-else 
                      class="flex items-center gap-1 text-[10px] sm:text-xs font-semibold text-[#4B49AC] bg-indigo-50/50 px-2 py-0.5 rounded-full border border-[#4B49AC]/10"
                    >
                      <Clock class="w-3.5 h-3.5 text-[#4B49AC]" />
                      Belum Diisi
                    </span>
                  </div>

                  <!-- Questionnaire Title -->
                  <h3 class="text-base font-extrabold text-slate-800 leading-tight">
                    {{ q.title }}
                  </h3>

                  <!-- Questionnaire Description -->
                  <p class="text-xs text-[#6B7280] leading-relaxed line-clamp-3">
                    {{ q.description || 'Tidak ada deskripsi instruksi.' }}
                  </p>
                </div>

                <!-- Footer Action / List Dosen -->
                <div v-if="q.type === 'kinerja_pengajar'" class="w-full space-y-3 pt-2">
                  <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Evaluasi per Dosen Pengajar:</div>
                  <div v-if="q.teachers_to_evaluate && q.teachers_to_evaluate.length > 0" class="space-y-2 max-h-[220px] overflow-y-auto pr-1">
                    <div 
                      v-for="t in q.teachers_to_evaluate" 
                      :key="t.jadwal_id"
                      class="flex items-center justify-between p-3 rounded-lg border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition-all gap-2"
                    >
                      <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-slate-800 truncate">{{ t.nama_dosen }}</p>
                        <p class="text-[10px] text-slate-500 truncate">{{ t.nama_matkul }}</p>
                      </div>
                      
                      <div class="shrink-0">
                        <div 
                          v-if="t.is_submitted"
                          class="flex items-center gap-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100"
                        >
                          <CheckCircle2 class="w-3 h-3 text-emerald-500" />
                          Selesai
                        </div>
                        <Link 
                          v-else
                          :href="`/v2/isi-kuisioner/${q.id}?dosen_id=${t.dosen_id}&jadwal_id=${t.jadwal_id}`"
                        >
                          <Button 
                            class="h-7 bg-[#4B49AC] hover:bg-[#3f3d91] text-white font-bold rounded-md text-[10px] px-2.5 shadow-sm flex items-center gap-1"
                          >
                            Nilai
                            <ArrowRight class="w-3 h-3" />
                          </Button>
                        </Link>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-center py-4 bg-slate-50 rounded-lg text-xs text-slate-400">
                    Tidak ada dosen pengajar terdaftar di kelas Anda.
                  </div>
                </div>

                <div v-else class="pt-2">
                  <Link 
                    v-if="q.responses_count === 0" 
                    :href="`/v2/isi-kuisioner/${q.id}`"
                  >
                    <Button 
                      class="w-full h-10 bg-[#4B49AC] hover:bg-[#3f3d91] text-white font-bold rounded-lg transition-all shadow-sm"
                    >
                      Isi Kuisioner
                      <ArrowRight class="w-4 h-4 ml-2" />
                    </Button>
                  </Link>
                  <div 
                    v-else 
                    class="w-full py-2.5 px-4 bg-emerald-50/70 border border-emerald-100 text-emerald-800 rounded-lg flex items-center justify-center gap-2 text-xs sm:text-sm font-bold shadow-xs"
                  >
                    <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>Terima Kasih, Sudah Diisi</span>
                  </div>
                </div>

              </CardContent>
            </Card>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex flex-col items-center justify-center p-12 bg-white border border-slate-100 rounded-2xl shadow-xs text-center space-y-4">
          <div class="p-4 bg-indigo-50 text-[#4B49AC] rounded-full">
            <HelpCircle class="w-10 h-10" />
          </div>
          <div class="max-w-xs space-y-1">
            <h3 class="font-extrabold text-slate-800 text-lg">Belum Ada Kuisioner</h3>
            <p class="text-xs text-slate-400 leading-relaxed">
              Saat ini tidak ada kuisioner aktif yang ditargetkan untuk Anda. Terima kasih!
            </p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
