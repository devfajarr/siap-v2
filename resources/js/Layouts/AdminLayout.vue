<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import Navbar from '@/Components/Navbar.vue'
import Sidebar from '@/Components/Sidebar.vue'

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
