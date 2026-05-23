<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent } from '@/Components/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import { Badge } from '@/Components/ui/badge'
import { Button } from '@/Components/ui/button'
import { Eye, Clock, CheckCircle2 } from 'lucide-vue-next'

const props = defineProps({
  diajukanList: {
    type: Array,
    required: true
  },
  disetujuiList: {
    type: Array,
    required: true
  },
  title: {
    type: String,
    required: true
  }
})

const activeTab = ref('diajukan')

const currentList = computed(() => {
  return activeTab.value === 'diajukan' ? props.diajukanList : props.disetujuiList
})
</script>

<template>
  <AdminLayout>
    <Head :title="title" />

    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-[#1F2937]">{{ title }}</h1>
          <p class="text-[#6B7280]">Kelola persetujuan kontrak kuliah.</p>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="flex items-center gap-4 border-b">
        <button 
          @click="activeTab = 'diajukan'"
          class="px-4 py-2 text-sm font-medium transition-colors border-b-2 focus:outline-none"
          :class="[activeTab === 'diajukan' ? 'border-primary text-primary font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700']"
        >
          Diajukan
        </button>
        <button 
          @click="activeTab = 'disetujui'"
          class="px-4 py-2 text-sm font-medium transition-colors border-b-2 focus:outline-none"
          :class="[activeTab === 'disetujui' ? 'border-primary text-primary font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700']"
        >
          Disetujui
        </button>
      </div>

      <Card class="border-none shadow-sm">
        <CardContent class="p-0">
          <div class="rounded-md border overflow-x-auto">
            <Table>
              <TableHeader class="bg-gray-50/50">
                <TableRow>
                  <TableHead>Mata Kuliah</TableHead>
                  <TableHead>Kelas</TableHead>
                  <TableHead>Dosen</TableHead>
                  <TableHead class="text-center">Status</TableHead>
                  <TableHead class="text-center">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="item in currentList" :key="item.id">
                  <TableCell class="font-medium">{{ item.matkul?.nama_matkul }}</TableCell>
                  <TableCell>{{ item.kelas?.nama_kelas }}</TableCell>
                  <TableCell>{{ item.jadwal?.dosen?.nama }}</TableCell>
                  <TableCell class="text-center">
                    <Badge v-if="item.status === 0" variant="warning" class="gap-1">
                      <Clock class="w-3 h-3" /> Pending
                    </Badge>
                    <Badge v-else variant="success" class="gap-1">
                      <CheckCircle2 class="w-3 h-3" /> Disetujui
                    </Badge>
                  </TableCell>
                  <TableCell class="text-center">
                    <Button variant="outline" size="sm" class="h-8 gap-1.5" disabled>
                      <Eye class="w-4 h-4" /> Detail
                    </Button>
                  </TableCell>
                </TableRow>
                <TableRow v-if="currentList.length === 0">
                  <TableCell colspan="5" class="h-32 text-center text-gray-500">
                    Tidak ada pengajuan rekap kontrak kuliah.
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
