<script setup>
import { ref, watch } from 'vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table'
import { Card, CardContent } from '@/Components/ui/card'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/Components/ui/dialog'
import { 
  Plus, 
  Search, 
  Pencil, 
  Trash2, 
  BarChart3, 
  ClipboardCheck, 
  Eye, 
  AlertCircle,
  CheckCircle2,
  XCircle
} from 'lucide-vue-next'

const props = defineProps({
  questionnaires: Object,
  category: String,
  categoryName: String,
  filters: Object,
})

const page = usePage()

// Search
const search = ref(props.filters.search || '')
let filterTimeout = null

const updateFilters = () => {
  if (filterTimeout) clearTimeout(filterTimeout)
  filterTimeout = setTimeout(() => {
    router.get(`/v2/admin/kuisioner/${props.category}`, {
      search: search.value,
    }, {
      preserveState: true,
      replace: true
    })
  }, 300)
}

watch(search, () => {
  updateFilters()
})

// Delete Modal State
const isDeleteModalOpen = ref(false)
const selectedQuestionnaire = ref(null)

// Toast state
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
  if (flash?.success) {
    toastMessage.value = flash.success
    toastType.value = 'success'
    showToast.value = true
    setTimeout(() => { showToast.value = false }, 3000)
  } else if (flash?.error) {
    toastMessage.value = flash.error
    toastType.value = 'error'
    showToast.value = true
    setTimeout(() => { showToast.value = false }, 3000)
  }
}, { deep: true, immediate: true })

const confirmDelete = (questionnaire) => {
  selectedQuestionnaire.value = questionnaire
  isDeleteModalOpen.value = true
}

const submitDelete = () => {
  router.delete(`/v2/admin/kuisioner/${props.category}/${selectedQuestionnaire.value.id}`, {
    onSuccess: () => {
      isDeleteModalOpen.value = false
    }
  })
}

const formatRespondent = (target) => {
  const map = {
    all: 'Semua Responden',
    mahasiswa: 'Khusus Mahasiswa',
    dosen: 'Khusus Dosen',
    pegawai: 'Khusus Pegawai / Staf',
    dosen_pegawai: 'Dosen & Pegawai',
  }
  return map[target] || target
}
</script>

<template>
  <AdminLayout>
    <Head :title="categoryName" />

    <div class="space-y-6">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-[#1F2937]">{{ categoryName }}</h1>
          <p class="text-[#6B7280]">Kelola kuis dan evaluasi berkala untuk mahasiswa dan staf.</p>
        </div>
        <Link :href="`/v2/admin/kuisioner/${category}/create`">
          <Button class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-sm">
            <Plus class="w-4 h-4 mr-2" />
            Buat Kuis Baru
          </Button>
        </Link>
      </div>

      <!-- Filters Card -->
      <Card class="border-none shadow-sm">
        <CardContent class="p-4">
          <div class="flex items-center gap-4">
            <div class="relative flex-1 max-w-md">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#9CA3AF]" />
              <Input 
                v-model="search"
                placeholder="Cari kuisioner..." 
                class="pl-10 border-gray-200 focus:ring-[#4B49AC] focus:border-[#4B49AC] rounded-lg"
              />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Table Section -->
      <Card class="border-none shadow-sm overflow-hidden">
        <Table>
          <TableHeader class="bg-[#F9FAFB]">
            <TableRow>
              <TableHead class="font-bold text-[#374151]">Judul Kuisioner</TableHead>
              <TableHead class="font-bold text-[#374151]">Target Responden</TableHead>
              <TableHead class="font-bold text-[#374151]">Pembuat</TableHead>
              <TableHead class="font-bold text-[#374151] text-center">Tanggapan</TableHead>
              <TableHead class="font-bold text-[#374151] text-center">Status</TableHead>
              <TableHead class="text-right font-bold text-[#374151]">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="q in questionnaires.data" :key="q.id" class="hover:bg-gray-50/50 transition-colors">
              <TableCell>
                <div>
                  <div class="font-semibold text-[#1F2937]">{{ q.title }}</div>
                  <div class="text-xs text-[#6B7280] truncate max-w-sm" v-if="q.description">
                    {{ q.description }}
                  </div>
                </div>
              </TableCell>
              <TableCell>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="{
                    'bg-indigo-50 text-indigo-700': q.target_respondent === 'all',
                    'bg-blue-50 text-blue-700': q.target_respondent === 'mahasiswa',
                    'bg-amber-50 text-amber-700': q.target_respondent === 'dosen',
                    'bg-emerald-50 text-emerald-700': q.target_respondent === 'pegawai',
                    'bg-purple-50 text-purple-700': q.target_respondent === 'dosen_pegawai'
                  }"
                >
                  {{ formatRespondent(q.target_respondent) }}
                </span>
              </TableCell>
              <TableCell class="text-[#4B5563]">
                <div class="text-sm font-medium">
                  {{ q.created_by ? (q.created_by.nama || q.created_by.nama_lengkap || q.created_by.name) : 'Sistem' }}
                </div>
                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                  {{ q.created_by_type ? q.created_by_type.split('\\').pop() : 'Staf' }}
                </div>
              </TableCell>
              <TableCell class="text-center font-semibold text-[#1F2937]">
                {{ q.responses_count }}
              </TableCell>
              <TableCell class="text-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                  :class="{
                    'bg-gray-100 text-gray-800': q.status === 'draft',
                    'bg-green-100 text-green-800': q.status === 'published',
                    'bg-red-100 text-red-800': q.status === 'closed'
                  }"
                >
                  {{ q.status === 'published' ? 'Aktif' : (q.status === 'draft' ? 'Draft' : 'Ditutup') }}
                </span>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <!-- Pratinjau / View Link -->
                  <a v-if="q.status === 'published'" :href="`/v2/isi-kuisioner/${q.id}`" target="_blank" title="Lihat Formulir">
                    <Button variant="ghost" size="sm" class="text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 h-8 w-8 p-0">
                      <Eye class="w-4 h-4" />
                    </Button>
                  </a>
                  <!-- Analisa Jawaban -->
                  <Link :href="`/v2/admin/kuisioner/${category}/${q.id}/analytics`" title="Analisa Tanggapan">
                    <Button variant="ghost" size="sm" class="text-purple-600 hover:text-purple-700 hover:bg-purple-50 h-8 w-8 p-0">
                      <BarChart3 class="w-4 h-4" />
                    </Button>
                  </Link>
                  <!-- Edit -->
                  <Link :href="`/v2/admin/kuisioner/${category}/${q.id}/edit`" title="Edit Kuisioner">
                    <Button variant="ghost" size="sm" class="text-blue-600 hover:text-blue-700 hover:bg-blue-50 h-8 w-8 p-0">
                      <Pencil class="w-4 h-4" />
                    </Button>
                  </Link>
                  <!-- Hapus -->
                  <Button variant="ghost" size="sm" @click="confirmDelete(q)" class="text-red-600 hover:text-red-700 hover:bg-red-50 h-8 w-8 p-0" title="Hapus">
                    <Trash2 class="w-4 h-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="questionnaires.data.length === 0">
              <TableCell colspan="6" class="h-32 text-center text-[#9CA3AF]">
                <div class="flex flex-col items-center justify-center space-y-2">
                  <ClipboardCheck class="w-8 h-8 opacity-20" />
                  <p>Belum ada kuisioner yang dibuat.</p>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex items-center justify-between bg-white" v-if="questionnaires.total > 0">
          <p class="text-sm text-[#6B7280]">
            Menampilkan {{ questionnaires.from || 0 }} sampai {{ questionnaires.to || 0 }} dari {{ questionnaires.total }} data
          </p>
          <div class="flex items-center gap-2">
            <Link :href="questionnaires.prev_page_url" v-if="questionnaires.prev_page_url">
              <Button variant="outline" size="sm" class="rounded-lg h-8 px-3">
                Sebelumnya
              </Button>
            </Link>
            <Button variant="outline" size="sm" disabled v-else class="rounded-lg h-8 px-3">
              Sebelumnya
            </Button>
            
            <Link :href="questionnaires.next_page_url" v-if="questionnaires.next_page_url">
              <Button variant="outline" size="sm" class="rounded-lg h-8 px-3">
                Berikutnya
              </Button>
            </Link>
            <Button variant="outline" size="sm" disabled v-else class="rounded-lg h-8 px-3">
              Berikutnya
            </Button>
          </div>
        </div>
      </Card>
    </div>

    <!-- Delete Dialog -->
    <Dialog :open="isDeleteModalOpen" @update:open="isDeleteModalOpen = $event">
      <DialogContent class="sm:max-w-[450px] p-0 overflow-hidden border-none shadow-2xl bg-white rounded-lg">
        <div class="p-8 text-center">
          <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
            <AlertCircle class="w-10 h-10 text-danger" />
          </div>
          <DialogHeader>
            <DialogTitle class="text-2xl font-bold text-gray-800 text-center">Hapus Kuisioner</DialogTitle>
            <DialogDescription class="text-gray-500 text-center mt-3 px-2 text-base">
              Apakah Anda yakin ingin menghapus kuisioner <span class="text-danger font-bold">"{{ selectedQuestionnaire?.title }}"</span>? 
              <br><span class="text-xs mt-3 block italic text-gray-400 font-medium tracking-wide">Semua pertanyaan dan tanggapan responden terkait kuis ini akan dihapus secara permanen.</span>
            </DialogDescription>
          </DialogHeader>

          <div class="flex items-center justify-center gap-4 mt-10">
            <Button 
              type="button" 
              variant="ghost" 
              @click="isDeleteModalOpen = false" 
              class="h-12 px-8 rounded-lg text-gray-500 hover:bg-gray-100 transition-all font-semibold"
            >
              Batal
            </Button>
            <Button 
              @click="submitDelete" 
              class="h-12 px-10 bg-danger hover:bg-danger/90 text-white rounded-lg shadow-lg shadow-danger/20 transition-all font-semibold"
            >
              Ya, Hapus Permanen
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="showToast" class="fixed bottom-10 right-10 z-[100]">
        <div class="bg-[#1F2937] text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 border border-gray-700 backdrop-blur-sm bg-opacity-95">
          <div :class="toastType === 'error' ? 'bg-red-500' : 'bg-green-500'" class="p-1.5 rounded-full">
            <CheckCircle2 v-if="toastType === 'success'" class="w-4 h-4 text-white" />
            <XCircle v-else class="w-4 h-4 text-white" />
          </div>
          <span class="text-sm font-medium">{{ toastMessage }}</span>
        </div>
      </div>
    </transition>
  </AdminLayout>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-enter-from {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

.toast-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}
</style>
