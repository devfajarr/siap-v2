<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'

import { 
  LayoutDashboard, 
  Folder, 
  Users, 
  Calendar, 
  History, 
  ClipboardCheck, 
  Star, 
  FileText,
  CreditCard,
  CreditCard as KrsIcon,
  Mail,
  PlusCircle,
  ChevronDown,
  GraduationCap,
  UserCheck
} from 'lucide-vue-next'

defineProps({
  isOpen: Boolean
})

const page = usePage()
const expandedMenus = ref([])

const toggleMenu = (title) => {
  if (expandedMenus.value.includes(title)) {
    expandedMenus.value = expandedMenus.value.filter(t => t !== title)
  } else {
    expandedMenus.value.push(title)
  }
}

const isMenuActive = (item) => {
  const currentPath = page.url.split('?')[0]
  if (item.href && item.href !== '#') {
    // Exact match or sub-path match (e.g. /v2/admin/data-mahasiswa matches /v2/admin/data-mahasiswa/3)
    if (currentPath === item.href || currentPath.startsWith(item.href + '/')) return true
  }
  if (item.children) {
    return item.children.some(child => isMenuActive(child))
  }
  return false
}

const expandActiveMenu = () => {
  menuItems.value.forEach(item => {
    if (item.children && isMenuActive(item)) {
      if (!expandedMenus.value.includes(item.title)) {
        expandedMenus.value.push(item.title)
      }
      // Level 2 Check
      item.children.forEach(child => {
        if (child.children && isMenuActive(child)) {
          if (!expandedMenus.value.includes(child.title)) {
            expandedMenus.value.push(child.title)
          }
        }
      })
    }
  })
}

onMounted(() => {
  expandActiveMenu()
})

watch(() => page.url, () => {
  expandActiveMenu()
})


const menuItems = computed(() => {
  const role = page.props.auth?.user?.role
  if (role === 'Mahasiswa') {
    const riwayatChildren = (page.props.auth?.semesters || []).map(s => ({
      title: s.title,
      href: s.href
    }))

    return [
      { title: 'Dashboard', icon: LayoutDashboard, href: '/v2/mahasiswa/dashboard' },
      { title: 'Nilai KHS', icon: Star, href: '/v2/mahasiswa/nilai' },
      { 
        title: 'Riwayat Nilai', 
        icon: Folder, 
        children: riwayatChildren.length > 0 ? riwayatChildren : [{ title: 'Belum ada riwayat', href: '#' }]
      },
      { title: 'KRS & Pembayaran', icon: KrsIcon, href: '/v2/mahasiswa/krs_pembayaran' },
      { title: 'Permohonan Surat', icon: Mail, href: '/presensi/mahasiswa/permohonan_surat' },
    ]
  }

  return [
    { title: 'Dashboard', icon: LayoutDashboard, href: '/v2/admin/dashboard' },
    { 
      title: 'Data Master', 
      icon: Folder, 
      children: [
        {
          title: 'Akademik',
          icon: GraduationCap,
          children: [
            { title: 'Mata Kuliah', href: '/v2/admin/data-master/data-matkul' },

            { title: 'Program Studi', href: '/v2/admin/data-master/data-prodi' },
            { title: 'Semester', href: '/v2/admin/data-master/data-semester' },
            { title: 'Tahun Akademik', href: '/v2/admin/data-master/tahun-akademik' },
            { title: 'Kelas', href: '/v2/admin/data-master/data-kelas' },
            { title: 'Ruangan', href: '/v2/admin/data-master/data-ruangan' },
          ]
        },
        {
          title: 'Kepegawaian',
          icon: UserCheck,
          children: [
            { title: 'Pegawai', href: '/v2/admin/data-master/data-pegawai' },
            { title: 'Dosen', href: '/v2/admin/data-master/data-dosen' },
            { title: 'Kaprodi', href: '/v2/admin/data-master/data-kaprodi' },
            { title: 'Wakil Direktur', href: '/v2/admin/data-master/data-wadir' },
            { title: 'Direktur', href: '/v2/admin/data-master/data-direktur' },
          ]
        }
      ]
    },
    { title: 'Mahasiswa', icon: Users, href: '/v2/admin/data-mahasiswa' },
    { 
      title: 'Jadwal', 
      icon: Calendar,
      children: [
        { title: 'Jadwal Mengajar', href: '/v2/admin/jadwal-mengajar' },
        { title: 'Jadwal Ujian', href: '/v2/admin/jadwal-ujian' },
      ]
    },
    { title: 'Pengajuan Edit Presensi', icon: History, href: '/v2/admin/pengajuan-edit-presensi' },
    { title: 'Data Perkuliahan', icon: ClipboardCheck, href: '/v2/admin/data-perkuliahan' },
    { title: 'Data Nilai', icon: Star, href: '/v2/admin/data-nilai' },
    { 
      title: 'Rekap Nilai', 
      icon: FileText, 
      children: [
        { title: 'Pengajuan Nilai', href: '/v2/admin/rekap-nilai/pengajuan' },
        { title: 'Nilai Disetujui', href: '/v2/admin/rekap-nilai/disetujui' },
      ]
    },
    { 
      title: 'Pembayaran', 
      icon: CreditCard, 
      children: [
        { title: 'Pembayaran Diajukan', href: '/v2/admin/pembayaran/diajukan' },
        { title: 'Pembayaran Disetujui', href: '/v2/admin/pembayaran/disetujui' },
      ]
    },
    { title: 'KRS', icon: KrsIcon, href: '/v2/admin/krs/kategori' },
    { 
      title: 'Permohonan Surat', 
      icon: Mail, 
      children: [
        { title: 'Cetak Surat', href: '/v2/admin/permohonan-surat/cetak' },
        { title: 'Surat Selesai', href: '/v2/admin/permohonan-surat/selesai' },
      ]
    },
    { title: 'Informasi Tambahan', icon: PlusCircle, href: '/v2/admin/informasi-tambahan' },
  ]
})
</script>

<template>
  <aside 
    class="fixed left-0 top-[70px] bottom-0 bg-white border-r border-[#CDD1E1] transition-all duration-300 z-40 overflow-y-auto"
    :class="[isOpen ? 'w-[260px]' : 'w-[70px]']"
  >
    <ul class="py-4">
      <li v-for="item in menuItems" :key="item.title" class="mb-1">
        <!-- Parent with Children -->
        <template v-if="item.children">
          <button 
            @click="toggleMenu(item.title)"
            class="w-full flex items-center gap-4 px-6 py-3 text-[#1F1F1F] hover:bg-[#F5F7FF] transition-all group relative"
            :class="[
              { 'justify-center px-0': !isOpen },
              { 'text-[#4B49AC] font-semibold': isMenuActive(item) }
            ]"
          >
            <component 
              :is="item.icon" 
              class="w-5 h-5 text-[#4B49AC] group-hover:scale-110 transition-transform flex-shrink-0" 
            />
            <span 
              v-if="isOpen"
              class="text-sm font-medium whitespace-nowrap flex-1 text-left"
            >
              {{ item.title }}
            </span>
            <ChevronDown 
              v-if="isOpen"
              class="w-4 h-4 transition-transform duration-200"
              :class="{ 'rotate-180': expandedMenus.includes(item.title) }"
            />
            
            <div 
              v-if="!isOpen"
              class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
            >
              {{ item.title }}
            </div>
          </button>
          
          <!-- Submenu -->
          <div 
            v-if="isOpen && expandedMenus.includes(item.title)" 
            class="bg-gray-50/50"
          >
            <div v-for="child in item.children" :key="child.title">
              <!-- Nested Children (Level 2) -->
              <template v-if="child.children">
                <button 
                  @click="toggleMenu(child.title)"
                  class="w-full flex items-center gap-3 pl-12 pr-6 py-2.5 text-[#1F1F1F] hover:bg-[#F5F7FF] transition-all group relative"
                  :class="{ 'text-[#4B49AC] font-medium': isMenuActive(child) }"
                >
                  <component :is="child.icon" class="w-4 h-4 text-[#4B49AC]" />
                  <span class="text-xs font-medium flex-1 text-left">{{ child.title }}</span>
                  <ChevronDown 
                    class="w-3 h-3 transition-transform duration-200"
                    :class="{ 'rotate-180': expandedMenus.includes(child.title) }"
                  />
                </button>
                <div v-if="expandedMenus.includes(child.title)" class="bg-white/50">
                  <Link 
                    v-for="grandChild in child.children" 
                    :key="grandChild.title"
                    :href="grandChild.href"
                    class="flex items-center gap-3 pl-20 pr-6 py-2 text-[#6C7383] hover:text-[#4B49AC] hover:bg-[#F5F7FF] transition-all text-xs"
                    :class="{ 'text-[#4B49AC] font-bold bg-[#F5F7FF]': page.url.split('?')[0] === grandChild.href }"
                  >
                    {{ grandChild.title }}
                  </Link>

                </div>
              </template>
              
              <!-- Direct Child (Level 2) -->
              <Link 
                v-else
                :href="child.href"
                class="flex items-center gap-3 pl-12 pr-6 py-2.5 text-[#1F1F1F] hover:bg-[#F5F7FF] transition-all group relative"
                :class="{ 'bg-[#F5F7FF] text-[#4B49AC] border-r-4 border-[#4B49AC] shadow-sm': page.url.split('?')[0] === child.href }"
              >
                <component :is="child.icon" v-if="child.icon" class="w-4 h-4 text-[#4B49AC]" />
                <span class="text-xs font-medium">{{ child.title }}</span>
              </Link>

            </div>
          </div>
        </template>

        <!-- Direct Link (No Children) -->
        <Link 
          v-else
          :href="item.href"
          class="flex items-center gap-4 px-6 py-3 text-[#1F1F1F] hover:bg-[#F5F7FF] transition-all group relative"
          :class="[
            { 'justify-center px-0': !isOpen },
            { 'bg-[#F5F7FF] text-[#4B49AC] border-r-4 border-[#4B49AC] shadow-sm': isMenuActive(item) }
          ]"

        >
          <component 
            :is="item.icon" 
            class="w-5 h-5 text-[#4B49AC] group-hover:scale-110 transition-transform flex-shrink-0" 
            :class="{ 'text-[#4B49AC]': isMenuActive(item) }"
          />

          <span 
            v-if="isOpen"
            class="text-sm font-medium whitespace-nowrap"
          >
            {{ item.title }}
          </span>
          
          <div 
            v-if="!isOpen"
            class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50"
          >
            {{ item.title }}
          </div>
        </Link>
      </li>
    </ul>
  </aside>
</template>

<style scoped>
/* Custom scrollbar for sidebar */
aside::-webkit-scrollbar {
  width: 4px;
}
aside::-webkit-scrollbar-thumb {
  background: #CED4DA;
  border-radius: 10px;
}

.rotate-180 {
  transform: rotate(180deg);
}
</style>
