<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'
import axios from 'axios'

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
  UserCheck,
  BarChart3,
  BookOpenCheck,
  MessageSquare,
} from 'lucide-vue-next'

defineProps({
  isOpen: Boolean
})

defineEmits(['close'])

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

// ─── Dosen Unread Guidance Badge ───────────────────────────────
const dosenUnreadGuidance = ref(0)
let guidancePollTimer = null

const fetchDosenUnreadGuidance = async () => {
  const role = page.props.auth?.user?.role
  if (role !== 'Dosen') return
  try {
    const res = await axios.get('/presensi/pemberitahuan/contacts-guidance')
    dosenUnreadGuidance.value = res.data.reduce((sum, c) => sum + (c.unread_count || 0), 0)
  } catch {}
}

onMounted(() => {
  fetchDosenUnreadGuidance()
  guidancePollTimer = setInterval(fetchDosenUnreadGuidance, 30000)
})

onUnmounted(() => {
  if (guidancePollTimer) clearInterval(guidancePollTimer)
})

// ─── Admin Pending Counts & Badge Helper ────────────────────────
const pendingKhsCount = computed(() => page.props.auth?.user?.pending_khs_count || 0)
const pendingSuratCount = computed(() => page.props.auth?.user?.pending_surat_count || 0)

const formatRoleName = (role) => {
  const map = {
    bpmi: 'BPMI',
    kemahasiswaan: 'Kemahasiswaan',
    perpustakaan: 'Perpustakaan',
    sarpras: 'Sarpras',
    personalia: 'Personalia'
  }
  return map[role.toLowerCase()] || role.toUpperCase()
}

const getBadgeValue = (badge) => {
  if (badge === null || badge === undefined) return 0
  if (typeof badge === 'object' && badge !== null && 'value' in badge) {
    return badge.value
  }
  return badge
}


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
      { title: 'Permohonan Surat', icon: Mail, href: '/v2/mahasiswa/permohonan-surat' },
      { title: 'Konsultasi DPA', icon: MessageSquare, href: '/v2/mahasiswa/dashboard?open_guidance=true' },
      { title: 'Daftar Kuisioner', icon: ClipboardCheck, href: '/v2/mahasiswa/kuisioner' },
    ]
  }

  if (role === 'Dosen') {
    const items = [
      { title: 'Dashboard',  icon: LayoutDashboard, href: '/v2/dosen/dashboard' },
      { title: 'Presensi',   icon: ClipboardCheck,  href: '/v2/dosen/data-presensi' },
      { title: 'Kontrak',    icon: BookOpenCheck,   href: '/v2/dosen/kontrak' },
      { title: 'Nilai',      icon: BarChart3,       href: '/v2/dosen/nilai' },
      { title: 'Kuisioner',  icon: FileText,        href: '/v2/dosen/kuisioner' },
    ]

    if (page.props.auth?.user?.status_pa == 1) {
      items.push({ title: 'Akses Dosen Pembimbing', isSeparator: true })
      items.push({ title: 'Validasi KRS', icon: KrsIcon, href: '/v2/dosen/krs' })
      items.push({ title: 'Bimbingan Mahasiswa', icon: MessageSquare, href: '/v2/dosen/bimbingan', badge: dosenUnreadGuidance })
    }

    const jabatans = page.props.auth?.user?.jabatans || []
    jabatans.forEach(j => {
      const formattedName = formatRoleName(j)
      items.push({ title: 'Akses ' + formattedName, isSeparator: true })
      if (j.toLowerCase() === 'bpmi') {
        items.push({
          title: 'Manajemen Kuisioner',
          icon: ClipboardCheck,
          children: [
            { title: 'Kuis Pelayanan', href: '/v2/admin/kuisioner/pelayanan' },
            { title: 'Kinerja Pengajar', href: '/v2/admin/kuisioner/kinerja-pengajar' },
            { title: 'Kuisioner AMI', href: '/v2/admin/kuisioner/ami' },
          ]
        })
      }
    })

    return items
  }

  if (['Pegawai', 'BPMI', 'Kemahasiswaan', 'Perpustakaan', 'Sarpras', 'Personalia'].includes(role)) {
    const items = [
      { title: 'Dashboard', icon: LayoutDashboard, href: role === 'Pegawai' || ['BPMI', 'Kemahasiswaan', 'Perpustakaan', 'Sarpras', 'Personalia'].includes(role) ? '/v2/pegawai/dashboard' : '/v2/dosen/dashboard' },
      { title: 'Kuisioner AMI', icon: FileText, href: '/v2/pegawai/kuisioner' },
    ]

    if (role === 'BPMI') {
      items.push({
        title: 'Manajemen Kuisioner',
        icon: ClipboardCheck,
        children: [
          { title: 'Kuis Pelayanan', href: '/v2/admin/kuisioner/pelayanan' },
          { title: 'Kinerja Pengajar', href: '/v2/admin/kuisioner/kinerja-pengajar' },
          { title: 'Kuisioner AMI', href: '/v2/admin/kuisioner/ami' },
        ]
      })
    }

    const jabatans = page.props.auth?.user?.jabatans || []
    const activeRole = role.toLowerCase()
    const otherJabatans = jabatans.filter(j => j.toLowerCase() !== activeRole)

    otherJabatans.forEach(j => {
      const formattedName = formatRoleName(j)
      items.push({ title: 'Akses ' + formattedName, isSeparator: true })
      if (j.toLowerCase() === 'bpmi') {
        items.push({
          title: 'Manajemen Kuisioner',
          icon: ClipboardCheck,
          children: [
            { title: 'Kuis Pelayanan', href: '/v2/admin/kuisioner/pelayanan' },
            { title: 'Kinerja Pengajar', href: '/v2/admin/kuisioner/kinerja-pengajar' },
            { title: 'Kuisioner AMI', href: '/v2/admin/kuisioner/ami' },
          ]
        })
      }
    })

    if (page.props.auth?.user?.is_dosen && role !== 'Dosen') {
      items.push({ title: 'Akses Dosen', isSeparator: true })
      items.push({ title: 'Presensi',   icon: ClipboardCheck,  href: '/v2/dosen/data-presensi' })
      items.push({ title: 'Kontrak',    icon: BookOpenCheck,   href: '/v2/dosen/kontrak' })
      items.push({ title: 'Nilai',      icon: BarChart3,       href: '/v2/dosen/nilai' })
      if (page.props.auth?.user?.status_pa == 1) {
        items.push({ title: 'Validasi KRS', icon: KrsIcon, href: '/v2/dosen/krs' })
        items.push({ title: 'Bimbingan Mahasiswa', icon: MessageSquare, href: '/v2/dosen/bimbingan', badge: dosenUnreadGuidance })
      }
    }

    if (page.props.auth?.user?.is_pegawai && role !== 'Pegawai') {
      items.push({ title: 'Akses Pegawai', isSeparator: true })
      items.push({ title: 'Dashboard Pegawai', icon: LayoutDashboard, href: '/v2/pegawai/dashboard' })
    }

    return items
  }

  if (role === 'Kaprodi') {
    return [
      { title: 'Dashboard', icon: LayoutDashboard, href: '/v2/kaprodi/dashboard' },
      { title: 'Data Matkul', icon: BookOpenCheck, href: '/v2/kaprodi/monitoring/matkul' },
      { title: 'Permohonan Surat', icon: Mail, href: '/v2/kaprodi/permohonan-surat/diajukan' },
      { title: 'Data Perkuliahan', icon: ClipboardCheck, href: '/v2/kaprodi/data-perkuliahan' },
      { title: 'Monitoring Perkuliahan', icon: ClipboardCheck, href: '/v2/kaprodi/monitoring/perkuliahan' },
      { title: 'Data Nilai', icon: Star, href: '/v2/kaprodi/monitoring/nilai' },
      { title: 'Kuisioner AMI', icon: FileText, href: '#' },
      { 
        title: 'Rekap Presensi', 
        icon: ClipboardCheck, 
        href: '/v2/kaprodi/rekap-presensi'
      },
      { 
        title: 'Rekap Berita Acara', 
        icon: FileText, 
        href: '/v2/kaprodi/rekap-berita'
      },
      { 
        title: 'Rekap Kontrak Kuliah', 
        icon: BookOpenCheck, 
        href: '/v2/kaprodi/rekap-kontrak'
      },
    ]
  }

  if (role === 'Direktur' || role === 'Wakil Direktur') {
    return [
      { title: 'Dashboard', icon: LayoutDashboard, href: '/v2/direktur/dashboard' },
      { title: 'Monitoring Perkuliahan', icon: ClipboardCheck, href: '/v2/direktur/monitoring/perkuliahan' },
      { title: 'Monitoring Nilai', icon: Star, href: '/v2/direktur/monitoring/nilai' },
      { title: 'Kuisioner AMI', icon: FileText, href: '#' },
      { title: 'Rekap Presensi', icon: ClipboardCheck, href: '/v2/direktur/rekap-presensi' },
      { title: 'Rekap Berita Acara', icon: FileText, href: '/v2/direktur/rekap-berita' },
      { title: 'Rekap Kontrak Kuliah', icon: BookOpenCheck, href: '/v2/direktur/rekap-kontrak' },
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
            { title: 'Jabatan Struktural', href: '/v2/admin/data-master/data-jabatan' },
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
      title: 'Pengajuan Cetak KHS', 
      icon: Star, 
      href: '/v2/admin/pengajuan-khs',
      badge: pendingKhsCount.value
    },
    { 
      title: 'Permohonan Surat', 
      icon: Mail, 
      badge: pendingSuratCount.value,
      children: [
        { 
          title: 'Cetak Surat', 
          href: '/v2/admin/permohonan-surat/cetak',
          badge: pendingSuratCount.value
        },
        { title: 'Surat Selesai', href: '/v2/admin/permohonan-surat/selesai' },
      ]
    },
    { title: 'Informasi Tambahan', icon: PlusCircle, href: '/v2/admin/informasi-tambahan' },
    {
      title: 'Kuisioner',
      icon: ClipboardCheck,
      children: [
        { title: 'Kuis Pelayanan', href: '/v2/admin/kuisioner/pelayanan' },
        { title: 'Kinerja Pengajar', href: '/v2/admin/kuisioner/kinerja-pengajar' },
        { title: 'Kuisioner AMI', href: '/v2/admin/kuisioner/ami' },
      ]
    },
  ]
})
</script>

<template>
  <!-- Backdrop overlay (mobile only) -->
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black/40 z-30 lg:hidden"
    @click="$emit('close')"
  />

  <aside
    class="fixed left-0 top-[70px] bottom-0 bg-white border-r border-[#CDD1E1] z-40 overflow-y-auto transition-all duration-300"
    :class="[
      // Desktop: icon-only collapse behavior
      isOpen ? 'lg:w-[260px]' : 'lg:w-[70px]',
      // Mobile: drawer slide in/out (always 260px wide)
      'w-[260px]',
      isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]"
  >
    <ul class="py-4">
      <li v-for="item in menuItems" :key="item.title" class="mb-1">
        <!-- Separator -->
        <div v-if="item.isSeparator" class="px-6 py-4 mt-2 mb-1 text-xs font-bold text-[#8A92A6] uppercase tracking-wider flex items-center">
          <span v-if="isOpen">{{ item.title }}</span>
          <hr v-else class="w-full border-[#CDD1E1]" />
        </div>
        
        <!-- Parent with Children -->
        <template v-else-if="item.children">
          <button 
            @click="toggleMenu(item.title)"
            class="w-full flex items-center gap-4 px-6 py-3 text-[#1F1F1F] hover:bg-[#F5F7FF] transition-all group relative"
            :class="[
              { 'justify-center px-0': !isOpen },
              { 'text-[#4B49AC] font-semibold': isMenuActive(item) }
            ]"
          >
            <div class="relative flex-shrink-0">
              <component 
                :is="item.icon" 
                class="w-5 h-5 text-[#4B49AC] group-hover:scale-110 transition-transform" 
              />
              <!-- Collapse Badge Dot -->
              <div 
                v-if="!isOpen && item.badge && getBadgeValue(item.badge) > 0"
                class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-rose-500 rounded-full ring-2 ring-white"
              />
            </div>
            <span 
              v-if="isOpen"
              class="text-sm font-medium whitespace-nowrap flex-1 text-left"
            >
              {{ item.title }}
            </span>
            <span 
              v-if="isOpen && item.badge && getBadgeValue(item.badge) > 0" 
              class="mr-2 bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shrink-0"
            >
              {{ getBadgeValue(item.badge) }}
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
                    :class="{ 'text-[#4B49AC] font-bold bg-[#F5F7FF]': isMenuActive(grandChild) }"
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
                :class="{ 'bg-[#F5F7FF] text-[#4B49AC] border-r-4 border-[#4B49AC] shadow-sm': isMenuActive(child) }"
              >
                <component :is="child.icon" v-if="child.icon" class="w-4 h-4 text-[#4B49AC]" />
                <span class="text-xs font-medium">{{ child.title }}</span>
                <span 
                  v-if="child.badge && getBadgeValue(child.badge) > 0" 
                  class="ml-auto bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shrink-0"
                >
                  {{ getBadgeValue(child.badge) }}
                </span>
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
          <div class="relative flex-shrink-0">
            <component 
              :is="item.icon" 
              class="w-5 h-5 text-[#4B49AC] group-hover:scale-110 transition-transform" 
              :class="{ 'text-[#4B49AC]': isMenuActive(item) }"
            />
            <!-- Collapse Badge Dot -->
            <div 
              v-if="!isOpen && item.badge && getBadgeValue(item.badge) > 0"
              class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-rose-500 rounded-full ring-2 ring-white"
            />
          </div>

          <span 
            v-if="isOpen"
            class="text-sm font-medium whitespace-nowrap"
          >
            {{ item.title }}
          </span>
          <span 
            v-if="isOpen && item.badge && getBadgeValue(item.badge) > 0" 
            class="ml-auto bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shrink-0"
          >
            {{ getBadgeValue(item.badge) }}
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
