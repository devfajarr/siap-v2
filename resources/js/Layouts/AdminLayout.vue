<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import Navbar from '@/Components/Navbar.vue'
import Sidebar from '@/Components/Sidebar.vue'

const isSidebarOpen = ref(true)

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}
</script>

<template>
  <div class="min-h-screen bg-[#F5F7FF] flex flex-col">
    <!-- Navbar -->
    <Navbar @toggle-sidebar="toggleSidebar" />

    <div class="flex flex-1 pt-[70px]">
      <!-- Sidebar -->
      <Sidebar :is-open="isSidebarOpen" />

      <!-- Main Content -->
      <main 
        class="flex-1 transition-all duration-300"
        :class="[isSidebarOpen ? 'ml-[260px]' : 'ml-[70px]']"
      >
        <div class="p-6">
          <slot />
        </div>
        
        <!-- Footer -->
        <footer class="p-6 text-center text-[#6C7383] text-sm mt-auto">
          Copyright © 2026 Siap Polsa. All rights reserved.
        </footer>
      </main>
    </div>
  </div>
</template>

<style scoped>
/* Transition for main content margin */
main {
  transition: margin-left 0.3s ease;
}
</style>
