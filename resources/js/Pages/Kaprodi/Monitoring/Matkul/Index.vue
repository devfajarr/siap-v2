<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { GraduationCap } from 'lucide-vue-next'

defineProps({
  semesters: {
    type: Array,
    required: true
  }
})
</script>

<template>
  <AdminLayout>
    <Head title="Monitoring Mata Kuliah" />

    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-[#1F2937]">Data Mata Kuliah</h1>
        <p class="text-[#6B7280]">Pilih semester untuk melihat daftar mata kuliah yang tersedia.</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <Link 
          v-for="semester in semesters" 
          :key="semester.id"
          :href="route('v2.kaprodi.monitoring.matkul.detail', semester.id)"
          class="group"
        >
          <Card class="border-none shadow-sm group-hover:shadow-md transition-all duration-300 group-hover:-translate-y-1 overflow-hidden">
            <CardHeader class="bg-white pb-2">
              <div class="flex items-center justify-between">
                <div class="p-2 bg-[#F5F7FF] rounded-lg group-hover:bg-primary transition-colors">
                  <GraduationCap class="w-6 h-6 text-primary group-hover:text-white" />
                </div>
                <CardTitle class="text-xl font-bold group-hover:text-primary transition-colors">
                  Semester {{ semester.semester }}
                </CardTitle>
              </div>
            </CardHeader>
            <CardContent class="bg-white">
              <p class="text-sm text-[#6B7280]">Lihat daftar mata kuliah pada semester {{ semester.semester }}.</p>
            </CardContent>
          </Card>
        </Link>
      </div>
    </div>
  </AdminLayout>
</template>
