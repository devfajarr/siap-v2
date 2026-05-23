<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { 
  DropdownMenuRoot, 
  DropdownMenuTrigger, 
  DropdownMenuContent, 
  DropdownMenuItem, 
  DropdownMenuSeparator,
  DropdownMenuPortal
} from 'radix-vue'
import { User, Settings, LogOut, ChevronDown } from 'lucide-vue-next'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth?.user || { nama: 'Guest', role: 'Unknown', avatar: '/images/user.png' })

const logout = () => {
  router.post('/logout')
}
</script>

<template>
  <DropdownMenuRoot>
    <DropdownMenuTrigger
      class="flex items-center gap-3 pl-4 border-l border-gray-200 outline-none cursor-pointer group"
    >
      <div class="text-right hidden sm:block">
        <p class="text-sm font-bold text-[#1F2937] leading-tight">{{ user.nama }}</p>
        <p class="text-[10px] text-gray-500 font-medium uppercase tracking-tighter">{{ user.role }}</p>
      </div>
      
      <div class="relative">
        <div class="w-10 h-10 rounded-xl border border-gray-200 overflow-hidden shadow-sm group-hover:border-blue-500 transition-colors">
          <img :src="user.avatar" alt="Avatar" class="w-full h-full object-cover" />
        </div>
        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-white rounded-full flex items-center justify-center shadow-sm">
          <ChevronDown class="w-3 h-3 text-gray-400" />
        </div>
      </div>
    </DropdownMenuTrigger>

    <DropdownMenuPortal>
      <DropdownMenuContent
        class="z-[100] min-w-[200px] bg-white rounded-2xl shadow-2xl border border-gray-100 p-2 mt-2 animate-in fade-in zoom-in duration-200"
        align="end"
        :side-offset="5"
      >
        <Link href="/v2/profile">
          <DropdownMenuItem
            class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-600 rounded-xl hover:bg-gray-50 hover:text-blue-600 outline-none cursor-pointer transition-colors"
          >
            <User class="w-4 h-4" />
            <span>My Profile</span>
          </DropdownMenuItem>
        </Link>

        <DropdownMenuItem
          @click="logout"
          class="flex items-center gap-3 px-3 py-2.5 text-sm text-red-600 rounded-xl hover:bg-red-50 outline-none cursor-pointer transition-colors"
        >
          <LogOut class="w-4 h-4" />
          <span>Logout</span>
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenuPortal>
  </DropdownMenuRoot>
</template>
