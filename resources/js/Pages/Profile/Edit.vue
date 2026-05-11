<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/Components/ui/card'
import { 
  User as UserIcon, 
  Mail as MailIcon, 
  Shield as ShieldIcon, 
  Camera as CameraIcon, 
  Lock as LockIcon, 
  Key as KeyIcon 
} from 'lucide-vue-next'
import { computed, ref } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth.user)

const activeTab = ref('profile')
</script>

<template>
  <Head title="My Profile" />

  <AdminLayout>
    <div v-if="user" class="max-w-5xl mx-auto space-y-6">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-8 rounded-3xl border border-[#CDD1E1] shadow-sm">
        <div class="flex items-center gap-6">
          <div class="relative group">
            <div class="w-24 h-24 rounded-3xl overflow-hidden border-4 border-white shadow-xl">
              <img :src="user.avatar" alt="Avatar" class="w-full h-full object-cover" />
            </div>
            <button class="absolute -bottom-2 -right-2 p-2 bg-blue-600 text-white rounded-xl shadow-lg hover:bg-blue-700 transition-colors">
              <CameraIcon class="w-4 h-4" />
            </button>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-[#1F2937]">{{ user.nama }}</h1>
            <p class="text-gray-500 font-medium">{{ user.role }}</p>
            <div class="mt-2 flex items-center gap-2">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Active
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar Navigation -->
        <div class="lg:col-span-1 space-y-2">
          <button 
            @click="activeTab = 'profile'"
            :class="[activeTab === 'profile' ? 'bg-blue-50 text-blue-600 border-blue-200' : 'bg-white text-gray-600 border-transparent hover:bg-gray-50']"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl border transition-all text-sm font-semibold"
          >
            <UserIcon class="w-4 h-4" />
            Informasi Profil
          </button>
          <button 
            @click="activeTab = 'security'"
            :class="[activeTab === 'security' ? 'bg-blue-50 text-blue-600 border-blue-200' : 'bg-white text-gray-600 border-transparent hover:bg-gray-50']"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl border transition-all text-sm font-semibold"
          >
            <ShieldIcon class="w-4 h-4" />
            Keamanan Akun
          </button>
        </div>

        <!-- Content Area -->
        <div class="lg:col-span-2">
          <Transition name="fade" mode="out-in">
            <div v-if="activeTab === 'profile'" key="profile">
              <Card class="rounded-3xl border-[#CDD1E1] shadow-sm overflow-hidden">
                <CardHeader class="bg-gray-50/50 border-b border-gray-100 p-6">
                  <CardTitle class="text-lg font-bold">Informasi Pribadi</CardTitle>
                  <CardDescription>Perbarui data diri dan preferensi profil Anda.</CardDescription>
                </CardHeader>
                <CardContent class="p-6 space-y-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                      <label class="text-sm font-bold text-gray-700">Nama Lengkap</label>
                      <div class="relative">
                        <UserIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <input 
                          type="text" 
                          :value="user.nama"
                          class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
                          placeholder="Masukkan nama lengkap"
                        />
                      </div>
                    </div>
                    <div class="space-y-2">
                      <label class="text-sm font-bold text-gray-700">Role Sistem</label>
                      <div class="relative">
                        <ShieldIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <input 
                          type="text" 
                          :value="user.role"
                          disabled
                          class="w-full pl-10 pr-4 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-sm text-gray-500 cursor-not-allowed"
                        />
                      </div>
                    </div>
                  </div>
                </CardContent>
                <CardFooter class="p-6 border-t border-gray-100 flex justify-end gap-3 bg-gray-50/50">
                  <button class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-500/20">
                    Simpan Perubahan
                  </button>
                </CardFooter>
              </Card>
            </div>

            <div v-else-if="activeTab === 'security'" key="security">
              <Card class="rounded-3xl border-[#CDD1E1] shadow-sm overflow-hidden">
                <CardHeader class="bg-gray-50/50 border-b border-gray-100 p-6">
                  <CardTitle class="text-lg font-bold">Keamanan & Password</CardTitle>
                  <CardDescription>Kelola kredensial login dan keamanan akun Anda.</CardDescription>
                </CardHeader>
                <CardContent class="p-6 space-y-6">
                  <div class="space-y-4">
                    <div class="space-y-2">
                      <label class="text-sm font-bold text-gray-700">Password Saat Ini</label>
                      <div class="relative">
                        <KeyIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <input 
                          type="password" 
                          class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
                          placeholder="••••••••"
                        />
                      </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                      <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700">Password Baru</label>
                        <div class="relative">
                          <LockIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                          <input 
                            type="password" 
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
                            placeholder="Minimal 8 karakter"
                          />
                        </div>
                      </div>
                      <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700">Konfirmasi Password Baru</label>
                        <div class="relative">
                          <LockIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                          <input 
                            type="password" 
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"
                            placeholder="Ulangi password baru"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                </CardContent>
                <CardFooter class="p-6 border-t border-gray-100 flex justify-end gap-3 bg-gray-50/50">
                  <button class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-500/20">
                    Update Password
                  </button>
                </CardFooter>
              </Card>
            </div>
          </Transition>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
