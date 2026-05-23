<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import { Input } from '@/Components/ui/input'
import { School, ArrowRight, Search } from 'lucide-vue-next'

const props = defineProps({
  kelas: {
    type: Array,
    required: true
  }
})

const searchQuery = ref('')

const filteredKelas = computed(() => {
  if (!searchQuery.value) return props.kelas
  const query = searchQuery.value.toLowerCase()
  return props.kelas.filter(item => 
    item.nama_kelas.toLowerCase().includes(query) ||
    (item.prodi?.nama_prodi && item.prodi.nama_prodi.toLowerCase().includes(query))
  )
})
</script>

<template>
  <AdminLayout>
    <Head title="Monitoring Perkuliahan" />

    <div class="space-y-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-[#1F2937] font-nunito">Monitoring Perkuliahan</h1>
          <p class="text-[#6B7280]">Pilih kelas untuk melihat detail jadwal dan kehadiran perkuliahan secara global.</p>
        </div>
        <div class="relative w-full md:w-80">
          <Search class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
          <Input 
            v-model="searchQuery" 
            placeholder="Cari kelas atau prodi..." 
            class="pl-9 rounded-lg border-gray-200"
          />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link 
          v-for="item in filteredKelas" 
          :key="item.id"
          :href="route('v2.direktur.monitoring.perkuliahan.detail', item.id)"
          class="group"
        >
          <Card class="border-none shadow-sm group-hover:shadow-md transition-all duration-300 overflow-hidden rounded-lg">
            <CardHeader class="pb-3 border-b bg-gray-50/50">
              <div class="flex items-center justify-between gap-2">
                <div class="p-2 bg-white rounded-lg shadow-sm">
                  <School class="w-5 h-5 text-[#4B49AC]" />
                </div>
                <div class="flex gap-1.5 flex-wrap justify-end">
                  <Badge variant="outline" class="bg-white text-xs">
                    {{ item.semester?.semester ? `Semester ${item.semester.semester}` : '-' }}
                  </Badge>
                  <Badge class="bg-[#4B49AC]/10 text-[#4B49AC] hover:bg-[#4B49AC]/20 border-none text-[10px] uppercase font-bold">
                    {{ item.prodi?.nama_prodi ? item.prodi.nama_prodi : '-' }}
                  </Badge>
                </div>
              </div>
              <CardTitle class="text-lg font-bold mt-2 group-hover:text-primary transition-colors font-nunito text-[#1F2937]">
                {{ item.nama_kelas }}
              </CardTitle>
            </CardHeader>
            <CardContent class="pt-4">
              <div class="flex items-center justify-between text-sm">
                <span class="text-[#6B7280]">Lihat Detail Jadwal</span>
                <ArrowRight class="w-4 h-4 text-primary group-hover:translate-x-1 transition-transform" />
              </div>
            </CardContent>
          </Card>
        </Link>

        <div v-if="filteredKelas.length === 0" class="col-span-full">
          <Card class="border-dashed border-2 bg-transparent">
            <CardContent class="p-12 text-center text-gray-500">
              Tidak ada data kelas yang cocok dengan pencarian Anda.
            </CardContent>
          </Card>
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
