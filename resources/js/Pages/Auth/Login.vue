<script setup>
import { ref } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import { Card, CardContent } from '@/Components/ui/card'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Button } from '@/Components/ui/button'
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select'
import { Eye, EyeOff, Loader2, AlertCircle } from 'lucide-vue-next'

const form = useForm({
  username: '',
  password: '',
  role: ''
})

const showPassword = ref(false)
const hasError = ref(false)

const submit = () => {
  hasError.value = false
  form.post('/login', {
    onError: () => {
      hasError.value = true
    }
  })
}
</script>

<template>
  <Head title="Login SIAP POLSA" />

  <div class="min-h-[100dvh] flex font-nunito bg-slate-50">

    <!-- ========== LEFT PANEL (Desktop only) ========== -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-tr from-[#4B49AC] via-[#6366F1] to-[#818CF8] relative overflow-hidden flex-col justify-between p-12 text-white">
      <div class="absolute -top-40 -left-40 w-96 h-96 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -bottom-40 -right-40 w-[500px] h-[500px] bg-indigo-300/20 rounded-full blur-3xl pointer-events-none"></div>

      <!-- Brand -->
      <div class="flex items-center gap-3 z-10">
        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-md">
          <img src="/images/logomini.png" alt="Logo" class="w-6 h-6" />
        </div>
        <span class="font-extrabold text-xl tracking-wider">SIAP POLSA</span>
      </div>

      <!-- Hero -->
      <div class="my-auto z-10 max-w-lg space-y-6">
        <h1 class="text-4xl font-extrabold leading-tight tracking-tight">
          Sistem Informasi Akademik Politeknik Sawunggalih Aji
        </h1>
        <p class="text-indigo-100 text-sm leading-relaxed">
          Platform akademik terintegrasi yang memudahkan manajemen perkuliahan, kehadiran, dan penilaian secara transparan dan efisien.
        </p>
        <div class="flex gap-4 pt-4">
          <div class="flex flex-col border-l-2 border-white/30 pl-4">
            <span class="text-2xl font-bold">V2</span>
            <span class="text-xs text-indigo-200">Version</span>
          </div>
          <div class="flex flex-col border-l-2 border-white/30 pl-4">
            <span class="text-2xl font-bold">SPA</span>
            <span class="text-xs text-indigo-200">Architecture</span>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="text-xs text-indigo-200 z-10">
        &copy; {{ new Date().getFullYear() }} SIAP POLSA. All rights reserved.
      </div>
    </div>

    <!-- ========== RIGHT PANEL (Full width on mobile) ========== -->
    <div class="w-full lg:w-1/2 flex flex-col min-h-[100dvh] lg:min-h-0">

      <!-- Mobile Top Bar -->
      <div class="flex items-center gap-2.5 px-5 pt-5 pb-2 lg:hidden shrink-0">
        <img src="/images/logomini.png" alt="Logo" class="w-7 h-7" />
        <span class="font-extrabold text-base text-slate-800 tracking-wider">SIAP POLSA</span>
      </div>

      <!-- Form Content (centered, scrollable) -->
      <div class="flex-1 flex items-center justify-center px-5 py-6 sm:px-8 sm:py-10 lg:p-12 overflow-y-auto">
        <div class="w-full max-w-sm sm:max-w-md space-y-5 sm:space-y-7">

          <!-- Header -->
          <div class="text-center lg:text-left">
            <div class="mb-3 flex justify-center lg:justify-start">
              <img src="/images/logo1.png" alt="Logo SIAP" class="h-9 sm:h-12 w-auto object-contain" />
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Selamat Datang</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Silakan masuk menggunakan akun terdaftar Anda.</p>
          </div>

          <!-- Error Alert -->
          <div
            v-if="form.errors && Object.keys(form.errors).length > 0"
            :class="{ 'animate-shake': hasError }"
            class="animate-in fade-in duration-300"
          >
            <Alert variant="destructive" class="border-red-200 bg-red-50 text-red-800 rounded-xl">
              <AlertCircle class="w-4 h-4 text-red-600 shrink-0" />
              <div class="flex-1">
                <AlertTitle class="font-bold text-sm">Login Gagal</AlertTitle>
                <AlertDescription class="text-xs mt-1">
                  <ul class="list-disc list-inside space-y-0.5">
                    <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                  </ul>
                </AlertDescription>
              </div>
            </Alert>
          </div>

          <!-- Card Form -->
          <Card class="border-slate-100 shadow-xl rounded-2xl bg-white">
            <CardContent class="p-5 sm:p-8">
              <form @submit.prevent="submit" class="space-y-4 sm:space-y-5">

                <!-- Username -->
                <div class="space-y-1.5">
                  <Label for="username" class="text-[10px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider">
                    Username atau Email
                  </Label>
                  <Input
                    id="username"
                    type="text"
                    v-model="form.username"
                    placeholder="Masukkan username atau email..."
                    class="h-10 sm:h-11 text-sm rounded-lg border-slate-200 focus:ring-[#4B49AC] focus:border-[#4B49AC] transition-all"
                    required
                    autocomplete="username"
                  />
                </div>

                <!-- Password -->
                <div class="space-y-1.5">
                  <div class="flex items-center justify-between">
                    <Label for="password" class="text-[10px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider">
                      Password
                    </Label>
                    <Link
                      :href="route('v2.forgot-password')"
                      class="text-[10px] sm:text-xs font-semibold text-[#4B49AC] hover:text-[#3f3d91] transition-colors font-nunito"
                    >
                      Lupa Password?
                    </Link>
                  </div>
                  <div class="relative">
                    <Input
                      id="password"
                      :type="showPassword ? 'text' : 'password'"
                      v-model="form.password"
                      placeholder="Masukkan password..."
                      class="h-10 sm:h-11 text-sm rounded-lg border-slate-200 focus:ring-[#4B49AC] focus:border-[#4B49AC] transition-all pr-10"
                      required
                      autocomplete="current-password"
                    />
                    <button
                      type="button"
                      @click="showPassword = !showPassword"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                    >
                      <Eye v-if="!showPassword" class="w-4 h-4" />
                      <EyeOff v-else class="w-4 h-4" />
                    </button>
                  </div>
                </div>

                <!-- Role Select -->
                <div class="space-y-1.5">
                  <Label class="text-[10px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider">
                    Level Pengguna
                  </Label>
                  <Select v-model="form.role" required>
                    <SelectTrigger class="h-10 sm:h-11 text-sm rounded-lg border-slate-200 focus:ring-[#4B49AC] focus:border-[#4B49AC]">
                      <SelectValue placeholder="Pilih Level Login..." />
                    </SelectTrigger>
                    <SelectContent class="rounded-xl shadow-lg border-slate-100">
                      <SelectItem value="admin">Admin</SelectItem>
                      <SelectItem value="direktur">Direktur</SelectItem>
                      <SelectItem value="wakil_direktur">Wakil Direktur</SelectItem>
                      <SelectItem value="kaprodi">Kaprodi</SelectItem>
                      <SelectItem value="dosen">Dosen</SelectItem>
                      <SelectItem value="pegawai">Pegawai</SelectItem>
                      <SelectItem value="mahasiswa">Mahasiswa</SelectItem>
                      <SelectItem value="orang_tua">Orang Tua</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <!-- Submit Button -->
                <Button
                  type="submit"
                  :disabled="form.processing"
                  class="w-full h-10 sm:h-11 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg font-bold text-sm shadow-lg shadow-indigo-600/10 transition-all flex items-center justify-center gap-2 mt-1"
                >
                  <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                  <span>{{ form.processing ? 'LOGGING IN...' : 'LOGIN' }}</span>
                </Button>

              </form>
            </CardContent>
          </Card>

        </div>
      </div>

      <!-- Mobile Bottom Footer -->
      <div class="text-center text-xs text-slate-400 py-4 px-5 lg:hidden shrink-0">
        &copy; {{ new Date().getFullYear() }} SIAP POLSA. All rights reserved.
      </div>
    </div>

  </div>
</template>

<style scoped>
@keyframes shake {
  10%, 90% { transform: translate3d(-1px, 0, 0); }
  20%, 80% { transform: translate3d(2px, 0, 0); }
  30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
  40%, 60% { transform: translate3d(4px, 0, 0); }
}

.animate-shake {
  animation: shake 0.5s cubic-bezier(.36, .07, .19, .97) both;
}
</style>
