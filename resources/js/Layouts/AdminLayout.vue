<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import Navbar from '@/Components/Navbar.vue'
import Sidebar from '@/Components/Sidebar.vue'
import { AlertTriangle as AlertTriangleIcon } from 'lucide-vue-next'

const page = usePage()
const user = computed(() => page.props.auth.user)

const isSidebarOpen = ref(false) // default tertutup, akan dibuka di desktop saat mount

const handleResize = () => {
  if (window.innerWidth >= 1024) {
    // Desktop: buka sidebar jika sebelumnya tertutup karena mobile
    isSidebarOpen.value = true
  } else {
    // Mobile: tutup sidebar
    isSidebarOpen.value = false
  }
}

onMounted(() => {
  // Set initial state berdasarkan ukuran layar saat pertama kali load
  isSidebarOpen.value = window.innerWidth >= 1024
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

const closeSidebar = () => {
  isSidebarOpen.value = false
}
</script>

<template>
  <div class="min-h-screen bg-[#F5F7FF] flex flex-col">
    <!-- Navbar -->
    <Navbar @toggle-sidebar="toggleSidebar" />

    <div class="flex flex-1 pt-[70px]">
      <!-- Sidebar -->
      <Sidebar :is-open="isSidebarOpen" @close="closeSidebar" />

      <!-- Main Content -->
      <main
        class="flex-1 transition-all duration-300 min-w-0"
        :class="[
          // Desktop: margin mengikuti lebar sidebar
          isSidebarOpen ? 'lg:ml-[260px]' : 'lg:ml-[70px]',
          // Mobile: tidak ada margin (sidebar overlay)
          'ml-0'
        ]"
      >
        <div class="p-4 sm:p-6">
          <!-- Warning Banner WhatsApp -->
          <div v-if="user && user.role === 'Mahasiswa' && !user.whatsapp_verified_at" class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-sm">
            <div class="flex items-start gap-3">
              <div class="p-2 bg-amber-100 text-amber-700 rounded-lg mt-0.5 shrink-0">
                <AlertTriangleIcon class="w-5 h-5 animate-pulse text-amber-600" />
              </div>
              <div>
                <h4 class="font-bold text-amber-900 text-sm">Verifikasi WhatsApp Diperlukan</h4>
                <p class="text-amber-700 text-xs mt-0.5">Nomor WhatsApp Anda belum diverifikasi. Aksi penting (KRS, Unggah Pembayaran, Permohonan Surat) ditangguhkan sampai nomor terverifikasi.</p>
              </div>
            </div>
            <Link :href="route('v2.profile.edit')" class="shrink-0 text-xs font-bold text-amber-950 bg-amber-200 hover:bg-amber-300 px-3.5 py-2.5 rounded-lg transition-colors text-center shadow-sm">
              Verifikasi Sekarang
            </Link>
          </div>

          <slot />
        </div>

        <!-- Footer -->
        <footer class="px-4 sm:px-6 pb-6 text-center text-[#6C7383] text-sm mt-auto">
          Copyright © 2026 Siap Polsa. All rights reserved.
        </footer>
      </main>
    </div>
  </div>
</template>

<style scoped>
main {
  transition: margin-left 0.3s ease;
}
</style>
