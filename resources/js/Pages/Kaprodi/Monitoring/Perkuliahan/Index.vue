<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import { School, ArrowRight } from 'lucide-vue-next'

defineProps({
  kelas: {
    type: Array,
    required: true
  }
})
</script>

<template>
  <AdminLayout>
    <Head title="Monitoring Perkuliahan" />

    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-[#1F2937]">Monitoring Perkuliahan</h1>
        <p class="text-[#6B7280]">Pilih kelas untuk melihat detail jadwal dan kehadiran perkuliahan.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link 
          v-for="item in kelas" 
          :key="item.id"
          :href="route('v2.kaprodi.monitoring.perkuliahan.detail', item.id)"
          class="group"
        >
          <Card class="border-none shadow-sm group-hover:shadow-md transition-all duration-300 overflow-hidden">
            <CardHeader class="pb-3 border-b bg-gray-50/50">
              <div class="flex items-center justify-between">
                <div class="p-2 bg-white rounded-lg shadow-sm">
                  <School class="w-5 h-5 text-primary" />
                </div>
                <Badge variant="outline" class="bg-white">
                  {{ item.semester?.semester ? `Semester ${item.semester.semester}` : '-' }}
                </Badge>
              </div>
              <CardTitle class="text-lg font-bold mt-2 group-hover:text-primary transition-colors">
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

        <div v-if="kelas.length === 0" class="col-span-full">
          <Card class="border-dashed border-2 bg-transparent">
            <CardContent class="p-12 text-center text-gray-500">
              Belum ada data kelas untuk program studi Anda.
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
