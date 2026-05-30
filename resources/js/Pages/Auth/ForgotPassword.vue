<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
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
import {
  Eye,
  EyeOff,
  Loader2,
  AlertCircle,
  Check,
  X,
  KeyRound,
  Smartphone,
  ArrowLeft
} from 'lucide-vue-next'
import axios from 'axios'

// Form State
const username = ref('')
const role = ref('mahasiswa')
const code = ref('')
const password = ref('')
const password_confirmation = ref('')

// UI State
const step = ref(1) // 1 = Request OTP, 2 = Verify OTP & Reset Password
const isProcessing = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const maskedPhone = ref('')
const hasError = ref(false)

// Real-time password requirement validator (similar to ForceChangePassword)
const passwordRequirements = computed(() => {
  const p = password.value
  return [
    { label: 'Minimal 8 karakter', valid: p.length >= 8 },
    { label: 'Mengandung huruf besar & kecil', valid: /[a-z]/.test(p) && /[A-Z]/.test(p) },
    { label: 'Mengandung angka', valid: /[0-9]/.test(p) },
    { label: 'Mengandung simbol (misal: @, #, $, !)', valid: /[^A-Za-z0-9]/.test(p) }
  ]
})

const isPasswordValid = computed(() => {
  return passwordRequirements.value.every(req => req.valid)
})

// Phase 1: Request OTP
const requestOtp = () => {
  isProcessing.value = true
  errorMessage.value = ''
  successMessage.value = ''
  hasError.value = false

  axios.post(route('v2.forgot-password.send-otp'), {
    username: username.value,
    role: role.value
  })
  .then(response => {
    isProcessing.value = false
    if (response.data.success) {
      maskedPhone.value = response.data.masked_phone
      successMessage.value = response.data.message
      step.value = 2
    }
  })
  .catch(error => {
    isProcessing.value = false
    hasError.value = true
    errorMessage.value = error.response?.data?.message || 'Terjadi kesalahan. Silakan coba lagi.'
  })
}

// Phase 2: Verify & Reset
const verifyAndReset = () => {
  isProcessing.value = true
  errorMessage.value = ''
  hasError.value = false

  axios.post(route('v2.forgot-password.verify-and-reset'), {
    username: username.value,
    role: role.value,
    code: code.value,
    password: password.value,
    password_confirmation: password_confirmation.value
  })
  .then(response => {
    isProcessing.value = false
    if (response.data.success) {
      router.visit(route('login'))
    }
  })
  .catch(error => {
    isProcessing.value = false
    hasError.value = true
    errorMessage.value = error.response?.data?.message || 'Gagal menyetel ulang kata sandi. Silakan coba lagi.'
  })
}

const goBack = () => {
  step.value = 1
  code.value = ''
  password.value = ''
  password_confirmation.value = ''
  errorMessage.value = ''
  successMessage.value = ''
}
</script>

<template>
  <Head title="Lupa Password - SIAP POLSA" />

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
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold uppercase tracking-wider">
          <KeyRound class="w-4 h-4 text-amber-300" />
          <span>Pemulihan Akun Aman</span>
        </div>
        <h1 class="text-4xl font-extrabold leading-tight tracking-tight">
          Pulihkan Akses ke Akun SIAP Anda
        </h1>
        <p class="text-indigo-100 text-sm leading-relaxed">
          Jangan khawatir jika Anda lupa kata sandi Anda. Anda dapat memulihkannya secara mandiri melalui kode OTP yang dikirimkan langsung ke nomor WhatsApp terdaftar Anda.
        </p>

        <div class="flex flex-col gap-3 pt-4">
          <div class="flex items-center gap-3 text-sm text-indigo-100">
            <div class="w-6 h-6 bg-white/10 rounded-full flex items-center justify-center border border-white/20 shrink-0">
              <span class="font-bold text-xs">1</span>
            </div>
            <span>Masukkan NIM Mahasiswa terdaftar Anda.</span>
          </div>
          <div class="flex items-center gap-3 text-sm text-indigo-100">
            <div class="w-6 h-6 bg-white/10 rounded-full flex items-center justify-center border border-white/20 shrink-0">
              <span class="font-bold text-xs">2</span>
            </div>
            <span>Masukkan 6 digit kode OTP yang dikirim ke WhatsApp.</span>
          </div>
          <div class="flex items-center gap-3 text-sm text-indigo-100">
            <div class="w-6 h-6 bg-white/10 rounded-full flex items-center justify-center border border-white/20 shrink-0">
              <span class="font-bold text-xs">3</span>
            </div>
            <span>Atur kata sandi baru yang kuat untuk mengamankan akun Anda.</span>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="text-xs text-indigo-200 z-10">
        &copy; {{ new Date().getFullYear() }} SIAP POLSA. All rights reserved.
      </div>
    </div>

    <!-- ========== RIGHT PANEL ========== -->
    <div class="w-full lg:w-1/2 flex flex-col min-h-[100dvh] lg:min-h-0">

      <!-- Mobile Top Bar -->
      <div class="flex items-center gap-2.5 px-5 pt-5 pb-2 lg:hidden shrink-0">
        <img src="/images/logomini.png" alt="Logo" class="w-7 h-7" />
        <span class="font-extrabold text-base text-slate-800 tracking-wider">SIAP POLSA</span>
      </div>

      <!-- Form Content -->
      <div class="flex-1 flex items-center justify-center px-5 py-6 sm:px-8 sm:py-10 lg:p-12 overflow-y-auto">
        <div class="w-full max-w-sm sm:max-w-md space-y-5 sm:space-y-6">

          <!-- Back to Login / Go Back to Step 1 -->
          <div>
            <button
              v-if="step === 2"
              @click="goBack"
              class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#4B49AC] transition-colors"
            >
              <ArrowLeft class="w-4 h-4" />
              Kembali ke Pengisian Akun
            </button>
            <Link
              v-else
              :href="route('login')"
              class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#4B49AC] transition-colors"
            >
              <ArrowLeft class="w-4 h-4" />
              Kembali ke Login
            </Link>
          </div>

          <!-- Header -->
          <div class="text-center lg:text-left">
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
              {{ step === 1 ? 'Lupa Kata Sandi?' : 'Verifikasi OTP & Reset Sandi' }}
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
              {{ step === 1 
                ? 'Masukkan NIM Mahasiswa Anda untuk menerima kode OTP via WhatsApp.' 
                : `Masukkan kode OTP yang telah dikirim ke nomor WhatsApp Anda (${maskedPhone}).` 
              }}
            </p>
          </div>

          <!-- Error Alert -->
          <div
            v-if="errorMessage"
            :class="{ 'animate-shake': hasError }"
            class="animate-in fade-in duration-300"
          >
            <Alert variant="destructive" class="border-red-200 bg-red-50 text-red-800 rounded-xl">
              <AlertCircle class="w-4 h-4 text-red-600 shrink-0" />
              <div class="flex-1">
                <AlertTitle class="font-bold text-sm">Terjadi Kesalahan</AlertTitle>
                <AlertDescription class="text-xs mt-1">
                  {{ errorMessage }}
                </AlertDescription>
              </div>
            </Alert>
          </div>

          <!-- Success Alert (OTP Sent) -->
          <div
            v-if="successMessage && step === 2"
            class="animate-in fade-in duration-300"
          >
            <Alert class="border-emerald-200 bg-emerald-50 text-emerald-800 rounded-xl">
              <Check class="w-4 h-4 text-emerald-600 shrink-0" />
              <div class="flex-1">
                <AlertTitle class="font-bold text-sm">OTP Terkirim</AlertTitle>
                <AlertDescription class="text-xs mt-1">
                  {{ successMessage }}
                </AlertDescription>
              </div>
            </Alert>
          </div>

          <!-- Card Form -->
          <Card class="border-slate-100 shadow-xl rounded-2xl bg-white">
            <CardContent class="p-5 sm:p-8">
              
              <!-- STEP 1: REQUEST OTP -->
              <form v-if="step === 1" @submit.prevent="requestOtp" class="space-y-4 sm:space-y-5">
                
                <!-- NIM Mahasiswa -->
                <div class="space-y-1.5">
                  <Label for="username" class="text-[10px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider font-nunito">
                    NIM Mahasiswa
                  </Label>
                  <Input
                    id="username"
                    type="text"
                    v-model="username"
                    placeholder="Masukkan NIM Anda..."
                    class="h-10 sm:h-11 text-sm rounded-lg border-slate-200 focus:ring-[#4B49AC] focus:border-[#4B49AC]"
                    required
                    autocomplete="username"
                  />
                </div>

                <!-- Submit Button -->
                <Button
                  type="submit"
                  :disabled="isProcessing"
                  class="w-full h-10 sm:h-11 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg font-bold text-sm shadow-lg shadow-indigo-600/10 transition-all flex items-center justify-center gap-2 mt-2"
                >
                  <Loader2 v-if="isProcessing" class="w-4 h-4 animate-spin" />
                  <Smartphone class="w-4 h-4" v-else />
                  <span>{{ isProcessing ? 'MENGIRIM OTP...' : 'KIRIM KODE OTP' }}</span>
                </Button>
              </form>

              <!-- STEP 2: VERIFY OTP & RESET PASSWORD -->
              <form v-else @submit.prevent="verifyAndReset" class="space-y-4 sm:space-y-5">
                
                <!-- OTP Code -->
                <div class="space-y-1.5">
                  <Label for="code" class="text-[10px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider">
                    Kode OTP (6 Digit)
                  </Label>
                  <Input
                    id="code"
                    type="text"
                    v-model="code"
                    maxlength="6"
                    placeholder="Masukkan 6 digit OTP..."
                    class="h-10 sm:h-11 text-center font-mono font-bold tracking-[0.5em] text-lg rounded-lg border-slate-200 focus:ring-[#4B49AC] focus:border-[#4B49AC] placeholder:text-left placeholder:tracking-normal placeholder:font-sans placeholder:text-sm placeholder:font-normal"
                    required
                  />
                </div>

                <!-- New Password -->
                <div class="space-y-1.5">
                  <Label for="password" class="text-[10px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider">
                    Kata Sandi Baru
                  </Label>
                  <div class="relative">
                    <Input
                      id="password"
                      :type="showPassword ? 'text' : 'password'"
                      v-model="password"
                      placeholder="Masukkan kata sandi baru..."
                      class="h-10 sm:h-11 text-sm rounded-lg border-slate-200 focus:ring-[#4B49AC] focus:border-[#4B49AC] transition-all pr-10"
                      required
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

                <!-- Live Checklist -->
                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 sm:p-4 space-y-2">
                  <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Syarat Kata Sandi:</span>
                  <div
                    v-for="(req, index) in passwordRequirements"
                    :key="index"
                    class="flex items-center gap-2 text-xs transition-colors"
                    :class="req.valid ? 'text-emerald-600' : 'text-slate-400'"
                  >
                    <div class="w-4 h-4 rounded-full flex items-center justify-center border shrink-0 transition-all"
                         :class="req.valid ? 'bg-emerald-50 border-emerald-300' : 'border-slate-200 bg-white'"
                    >
                      <Check v-if="req.valid" class="w-2.5 h-2.5 text-emerald-600 stroke-[3px]" />
                      <X v-else class="w-2.5 h-2.5 text-slate-300 stroke-[3px]" />
                    </div>
                    <span>{{ req.label }}</span>
                  </div>
                </div>

                <!-- Confirm Password -->
                <div class="space-y-1.5">
                  <Label for="password_confirmation" class="text-[10px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider">
                    Konfirmasi Kata Sandi Baru
                  </Label>
                  <div class="relative">
                    <Input
                      id="password_confirmation"
                      :type="showConfirmPassword ? 'text' : 'password'"
                      v-model="password_confirmation"
                      placeholder="Ulangi kata sandi baru..."
                      class="h-10 sm:h-11 text-sm rounded-lg border-slate-200 focus:ring-[#4B49AC] focus:border-[#4B49AC] transition-all pr-10"
                      required
                    />
                    <button
                      type="button"
                      @click="showConfirmPassword = !showConfirmPassword"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                    >
                      <Eye v-if="!showConfirmPassword" class="w-4 h-4" />
                      <EyeOff v-else class="w-4 h-4" />
                    </button>
                  </div>
                </div>

                <!-- Submit Button -->
                <Button
                  type="submit"
                  :disabled="isProcessing || !isPasswordValid"
                  class="w-full h-10 sm:h-11 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg font-bold text-sm shadow-lg shadow-indigo-600/10 transition-all flex items-center justify-center gap-2 mt-2 disabled:bg-slate-200 disabled:text-slate-400 disabled:shadow-none"
                >
                  <Loader2 v-if="isProcessing" class="w-4 h-4 animate-spin" />
                  <span>{{ isProcessing ? 'MENYIMPAN...' : 'SIMPAN PASSWORD' }}</span>
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
