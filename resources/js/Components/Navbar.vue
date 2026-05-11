<script setup>
import { Link } from '@inertiajs/vue3'
import { Menu } from 'lucide-vue-next'
import NavbarProfile from '@/Components/NavbarProfile.vue'
import NotificationDropdown from '@/Components/NotificationDropdown.vue'

defineEmits(['toggle-sidebar'])
</script>

<template>
  <nav class="fixed top-0 left-0 right-0 h-[70px] bg-white border-b border-[#CDD1E1] px-6 flex items-center justify-between z-50">
    <!-- Brand -->
    <div class="flex items-center gap-4">
      <Link href="/v2/admin/dashboard" class="flex items-center">
        <img src="/images/logo1.png" alt="Logo" class="h-8 hidden md:block" />
        <img src="/images/logomini.png" alt="Logo" class="h-8 md:hidden" />
      </Link>
      <button 
        @click="$emit('toggle-sidebar')"
        class="p-2 hover:bg-gray-100 rounded-lg text-[#1F1F1F]"
      >
        <Menu class="w-6 h-6" />
      </button>
    </div>

    <!-- Right Side -->
    <div class="flex items-center gap-6">
      <!-- Notifications -->
      <NotificationDropdown />

      <!-- User Profile -->
      <NavbarProfile />
    </div>
  </nav>
</template>
