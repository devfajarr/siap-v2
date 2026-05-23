<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { 
  Users, 
  GraduationCap, 
  Percent, 
  ClipboardCheck, 
  FileText, 
  BookOpenCheck,
  ChevronRight,
  TrendingUp
} from 'lucide-vue-next'

defineProps({
  stats: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <AdminLayout>
    <Head title="Dashboard Pimpinan" />

    <div class="space-y-8">
      <!-- Title -->
      <div>
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Dashboard Pimpinan</h1>
        <p class="text-gray-500 mt-1 text-sm">Selamat datang kembali. Berikut adalah ringkasan performa akademik dan persetujuan hari ini.</p>
      </div>

      <!-- Stats Grid -->
      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        <Card class="border-none shadow-sm hover:shadow-md transition-shadow">
          <CardContent class="p-6 flex items-center justify-between">
            <div class="space-y-1">
              <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Mahasiswa</span>
              <p class="text-3xl font-bold text-gray-900">{{ stats.totalMahasiswa }}</p>
            </div>
            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
              <Users class="w-6 h-6" />
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm hover:shadow-md transition-shadow">
          <CardContent class="p-6 flex items-center justify-between">
            <div class="space-y-1">
              <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Dosen Aktif</span>
              <p class="text-3xl font-bold text-gray-900">{{ stats.totalDosen }}</p>
            </div>
            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
              <GraduationCap class="w-6 h-6" />
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm hover:shadow-md transition-shadow">
          <CardContent class="p-6 flex items-center justify-between">
            <div class="space-y-1">
              <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Kehadiran Hari Ini</span>
              <p class="text-3xl font-bold text-gray-900">{{ stats.totalHadirHariIni }}</p>
            </div>
            <div class="p-3 bg-green-50 text-green-600 rounded-xl">
              <TrendingUp class="w-6 h-6" />
            </div>
          </CardContent>
        </Card>

        <Card class="border-none shadow-sm hover:shadow-md transition-shadow">
          <CardContent class="p-6 flex items-center justify-between">
            <div class="space-y-1">
              <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Persentase Kehadiran</span>
              <p class="text-3xl font-bold text-gray-900">{{ stats.persentaseKehadiran }}%</p>
            </div>
            <div class="p-3 bg-teal-50 text-teal-600 rounded-xl">
              <Percent class="w-6 h-6" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Action Required Section -->
      <div>
        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
          <span>Menunggu Persetujuan Anda</span>
          <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full">Tindakan Diperlukan</span>
        </h2>

        <div class="grid gap-6 md:grid-cols-3">
          <!-- Rekap Presensi -->
          <Card class="border-none shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition-shadow">
            <CardHeader class="p-6 pb-2">
              <div class="flex items-center justify-between">
                <div class="p-3.5 bg-orange-50 text-orange-600 rounded-xl">
                  <ClipboardCheck class="w-6 h-6" />
                </div>
                <Badge variant="outline" class="font-semibold" :class="[stats.pendingPresensi > 0 ? 'bg-red-50 text-red-700 border-red-200' : 'bg-green-50 text-green-700 border-green-200']">
                  {{ stats.pendingPresensi }} Antrean
                </Badge>
              </div>
              <CardTitle class="mt-4 text-lg font-bold text-gray-900">Rekap Presensi</CardTitle>
            </CardHeader>
            <CardContent class="p-6 pt-0 space-y-4">
              <p class="text-sm text-gray-500">Persetujuan rekapitulasi kehadiran mahasiswa oleh dosen pengampu.</p>
              <Link href="/v2/direktur/rekap-presensi">
                <Button class="w-full bg-[#4B49AC] hover:bg-[#3f3da3] text-white shadow-sm flex items-center justify-center gap-1.5 h-10 mt-2">
                  <span>Proses Persetujuan</span>
                  <ChevronRight class="w-4 h-4" />
                </Button>
              </Link>
            </CardContent>
          </Card>

          <!-- Rekap Berita Acara -->
          <Card class="border-none shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition-shadow">
            <CardHeader class="p-6 pb-2">
              <div class="flex items-center justify-between">
                <div class="p-3.5 bg-pink-50 text-pink-600 rounded-xl">
                  <FileText class="w-6 h-6" />
                </div>
                <Badge variant="outline" class="font-semibold" :class="[stats.pendingBerita > 0 ? 'bg-red-50 text-red-700 border-red-200' : 'bg-green-50 text-green-700 border-green-200']">
                  {{ stats.pendingBerita }} Antrean
                </Badge>
              </div>
              <CardTitle class="mt-4 text-lg font-bold text-gray-900">Berita Acara</CardTitle>
            </CardHeader>
            <CardContent class="p-6 pt-0 space-y-4">
              <p class="text-sm text-gray-500">Persetujuan jurnal dan berita acara pelaksanaan perkuliahan mingguan.</p>
              <Link href="/v2/direktur/rekap-berita">
                <Button class="w-full bg-[#4B49AC] hover:bg-[#3f3da3] text-white shadow-sm flex items-center justify-center gap-1.5 h-10 mt-2">
                  <span>Proses Persetujuan</span>
                  <ChevronRight class="w-4 h-4" />
                </Button>
              </Link>
            </CardContent>
          </Card>

          <!-- Rekap Kontrak Kuliah -->
          <Card class="border-none shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition-shadow">
            <CardHeader class="p-6 pb-2">
              <div class="flex items-center justify-between">
                <div class="p-3.5 bg-cyan-50 text-cyan-600 rounded-xl">
                  <BookOpenCheck class="w-6 h-6" />
                </div>
                <Badge variant="outline" class="font-semibold" :class="[stats.pendingKontrak > 0 ? 'bg-red-50 text-red-700 border-red-200' : 'bg-green-50 text-green-700 border-green-200']">
                  {{ stats.pendingKontrak }} Antrean
                </Badge>
              </div>
              <CardTitle class="mt-4 text-lg font-bold text-gray-900">Kontrak Kuliah</CardTitle>
            </CardHeader>
            <CardContent class="p-6 pt-0 space-y-4">
              <p class="text-sm text-gray-500">Persetujuan rekapitulasi materi kontrak kuliah oleh dosen pengampu.</p>
              <Link href="/v2/direktur/rekap-kontrak">
                <Button class="w-full bg-[#4B49AC] hover:bg-[#3f3da3] text-white shadow-sm flex items-center justify-center gap-1.5 h-10 mt-2">
                  <span>Proses Persetujuan</span>
                  <ChevronRight class="w-4 h-4" />
                </Button>
              </Link>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
