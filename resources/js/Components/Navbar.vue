<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { Menu, ChevronDown } from 'lucide-vue-next'
import NavbarProfile from '@/Components/NavbarProfile.vue'
import NotificationDropdown from '@/Components/NotificationDropdown.vue'
import { computed } from 'vue'
import { 
  DropdownMenuRoot, 
  DropdownMenuTrigger, 
  DropdownMenuContent, 
  DropdownMenuItem, 
  DropdownMenuSeparator,
  DropdownMenuPortal
} from 'radix-vue'

defineEmits(['toggle-sidebar'])

const page = usePage()
const dashboardUrl = computed(() => {
  return page.props.auth?.user?.role === 'Mahasiswa' ? '/v2/mahasiswa/dashboard' : '/v2/admin/dashboard'
})

const user = computed(() => page.props.auth?.user)
const prodis = computed(() => user.value?.prodis || [])
const activeProdi = computed(() => {
  return prodis.value.find(p => p.id === user.value?.activeProdiId)
})

const switchProdi = (prodiId) => {
  router.post(route('v2.kaprodi.switch-prodi'), { prodi_id: prodiId })
}
</script>

<template>
  <nav class="fixed top-0 left-0 right-0 h-[70px] bg-white border-b border-[#CDD1E1] px-3 sm:px-6 flex items-center justify-between z-50">
    <!-- Brand -->
    <div class="flex items-center gap-4">
      <Link :href="dashboardUrl" class="flex items-center">
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
    <div class="flex items-center gap-2 sm:gap-4">
      <!-- Prodi Switcher for Kaprodi -->
      <div v-if="user?.role === 'Kaprodi' && activeProdi" class="flex items-center">
        <div v-if="prodis.length > 1">
          <DropdownMenuRoot>
            <DropdownMenuTrigger class="flex items-center gap-1.5 px-2 sm:px-3 py-2 bg-[#4B49AC]/10 hover:bg-[#4B49AC]/20 text-[#4B49AC] rounded-xl text-xs font-bold transition-all outline-none cursor-pointer border border-[#4B49AC]/20 max-w-[160px] sm:max-w-none">
              <span class="truncate"><span class="hidden sm:inline">Prodi: </span>{{ activeProdi.nama_prodi }}</span>
              <ChevronDown class="w-3.5 h-3.5 shrink-0" />
            </DropdownMenuTrigger>
            <DropdownMenuPortal>
              <DropdownMenuContent class="z-[100] min-w-[220px] bg-white rounded-2xl shadow-2xl border border-gray-100 p-2 mt-2 animate-in fade-in zoom-in duration-200" align="end" :side-offset="5">
                <p class="px-3 py-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pilih Program Studi</p>
                <DropdownMenuSeparator class="h-px bg-gray-100 my-1" />
                <DropdownMenuItem 
                  v-for="prodi in prodis" 
                  :key="prodi.id"
                  @click="switchProdi(prodi.id)"
                  class="flex items-center justify-between px-3 py-2.5 text-xs text-gray-600 rounded-xl hover:bg-gray-50 hover:text-[#4B49AC] outline-none cursor-pointer transition-colors"
                  :class="prodi.id === activeProdi.id ? 'bg-[#4B49AC]/5 text-[#4B49AC] font-bold' : ''"
                >
                  <span>{{ prodi.nama_prodi }}</span>
                  <span v-if="prodi.id === activeProdi.id" class="w-1.5 h-1.5 bg-[#4B49AC] rounded-full"></span>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenuPortal>
          </DropdownMenuRoot>
        </div>
        <div v-else class="px-2 sm:px-3 py-2 bg-gray-50 text-gray-500 rounded-xl text-xs font-medium border border-gray-100 max-w-[120px] sm:max-w-none truncate">
          <span class="hidden sm:inline">Prodi: </span>{{ activeProdi.nama_prodi }}
        </div>
      </div>

      <!-- Notifications -->
      <NotificationDropdown />

      <!-- User Profile -->
      <NavbarProfile />
    </div>
  </nav>
</template>
