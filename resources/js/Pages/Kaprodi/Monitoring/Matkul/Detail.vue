<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Input } from '@/Components/ui/input'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import { Search, ChevronLeft } from 'lucide-vue-next'

const props = defineProps({
  semester: {
    type: Object,
    required: true
  },
  matkuls: {
    type: Array,
    required: true
  }
})

const search = ref('')

const filteredMatkuls = computed(() => {
  if (!search.value) return props.matkuls
  
  const searchLower = search.value.toLowerCase()
  return props.matkuls.filter(m => 
    m.nama_matkul.toLowerCase().includes(searchLower) || 
    m.kode.toLowerCase().includes(searchLower)
  )
})
</script>

<template>
  <AdminLayout>
    <Head :title="`Data Mata Kuliah Semester ${semester.semester}`" />

    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <Link 
              :href="route('v2.kaprodi.monitoring.matkul.index')"
              class="p-1 hover:bg-gray-100 rounded-full transition-colors"
            >
              <ChevronLeft class="w-6 h-6 text-gray-500" />
            </Link>
            <h1 class="text-2xl font-bold text-[#1F2937]">Mata Kuliah Semester {{ semester.semester }}</h1>
          </div>
          <p class="text-[#6B7280] ml-8 text-sm">Berikut adalah daftar mata kuliah yang ditawarkan pada semester ini.</p>
        </div>

        <div class="relative w-64">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <Input 
            v-model="search"
            placeholder="Cari mata kuliah..." 
            class="pl-10 pr-4 py-2 border-gray-200 focus:ring-primary focus:border-primary rounded-lg"
          />
        </div>
      </div>

      <Card class="border-none shadow-sm">
        <CardContent class="p-0">
          <div class="rounded-md border overflow-x-auto">
            <Table>
              <TableHeader class="bg-gray-50/50">
                <TableRow>
                  <TableHead class="w-12 text-center">#</TableHead>
                  <TableHead>Kode</TableHead>
                  <TableHead>Nama Mata Kuliah</TableHead>
                  <TableHead class="text-center">T</TableHead>
                  <TableHead class="text-center">P</TableHead>
                  <TableHead class="text-center">Total SKS</TableHead>
                  <TableHead>Program Studi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="(matkul, index) in filteredMatkuls" :key="matkul.id">
                  <TableCell class="text-center text-gray-500">{{ index + 1 }}</TableCell>
                  <TableCell class="font-mono font-medium text-primary">{{ matkul.kode }}</TableCell>
                  <TableCell class="font-medium">{{ matkul.nama_matkul }}</TableCell>
                  <TableCell class="text-center">{{ matkul.teori }}</TableCell>
                  <TableCell class="text-center">{{ matkul.praktek }}</TableCell>
                  <TableCell class="text-center">
                    <span class="px-2 py-1 bg-primary/10 text-primary rounded-md font-bold text-xs">
                      {{ matkul.teori + matkul.praktek }}
                    </span>
                  </TableCell>
                  <TableCell class="text-sm text-gray-600">
                    {{ matkul.prodi?.nama_prodi || '-' }}
                  </TableCell>
                </TableRow>
                <TableRow v-if="filteredMatkuls.length === 0">
                  <TableCell colspan="7" class="h-32 text-center text-gray-500">
                    Tidak ada mata kuliah ditemukan.
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>
