<script setup>
import { Head, usePage, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/Components/ui/card'
import { 
  User as UserIcon, 
  Mail as MailIcon, 
  Shield as ShieldIcon, 
  Camera as CameraIcon, 
  Lock as LockIcon, 
  Key as KeyIcon,
  Check as CheckIcon,
  AlertCircle as AlertCircleIcon
} from 'lucide-vue-next'
import { computed, ref, onUnmounted, watch } from 'vue'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogClose,
} from '@/Components/ui/dialog'
import axios from 'axios'

const page = usePage()
const user = computed(() => page.props.auth.user)

const fileInput = ref(null)
const avatarForm = useForm({
  profile_picture: null,
})

const triggerFileInput = () => {
  fileInput.value.click()
}

const onFileSelected = (event) => {
  const file = event.target.files[0]
  if (!file) return

  if (file.size > 2 * 1024 * 1024) {
    alert('Ukuran file maksimal adalah 2MB.')
    return
  }

  avatarForm.profile_picture = file
  avatarForm.post(route('v2.profile.update-avatar'), {
    forceFormData: true,
    preserveScroll: true,
    onError: (errors) => {
      alert(errors.profile_picture || 'Gagal mengunggah foto profil.')
    }
  })
}

const activeTab = ref('profile')

// Helper formatting phone
const getInitialPhone = (phone) => {
  if (!phone) return ''
  let digits = phone.replace(/[^0-9]/g, '')
  if (digits.startsWith('62')) {
    return digits.substring(2)
  }
  if (digits.startsWith('0')) {
    return digits.substring(1)
  }
  return digits
}

// State
const whatsappInput = ref(getInitialPhone(user.value?.no_telephone))
const isSendingOtp = ref(false)
const isVerifying = ref(false)
const isModalOpen = ref(false)
const errorMsg = ref('')
const countdown = ref(0)
const otpDigits = ref(['', '', '', '', '', ''])
const otpCode = computed(() => otpDigits.value.join(''))

let timer = null

const startTimer = () => {
  countdown.value = 60
  if (timer) clearInterval(timer)
  timer = setInterval(() => {
    if (countdown.value > 0) {
      countdown.value--
    } else {
      clearInterval(timer)
    }
  }, 1000)
}

onUnmounted(() => {
  if (timer) clearInterval(timer)
})

watch(isModalOpen, (isOpen) => {
  if (!isOpen) {
    if (timer) {
      clearInterval(timer)
      timer = null
    }
    countdown.value = 0
    errorMsg.value = ''
    otpDigits.value = ['', '', '', '', '', '']
  }
})

const isEditingWhatsapp = ref(false)

const enableWhatsappEdit = () => {
  isEditingWhatsapp.value = true
}

const cancelWhatsappEdit = () => {
  isEditingWhatsapp.value = false
  whatsappInput.value = getInitialPhone(user.value?.no_telephone)
}

// Handlers
const initiateVerification = async () => {
  if (!whatsappInput.value) return
  isSendingOtp.value = true
  errorMsg.value = ''
  
  try {
    const fullPhone = '62' + whatsappInput.value.replace(/[^0-9]/g, '')
    const response = await axios.post(route('v2.whatsapp.send-otp'), {
      no_telephone: fullPhone
    })
    
    if (response.data.success) {
      isModalOpen.value = true
      otpDigits.value = ['', '', '', '', '', '']
      startTimer()
    } else {
      errorMsg.value = response.data.message || 'Gagal mengirim OTP.'
    }
  } catch (error) {
    errorMsg.value = error.response?.data?.message || 'Terjadi kesalahan sistem saat mengirim OTP.'
  } finally {
    isSendingOtp.value = false
  }
}

const resendOtp = async () => {
  if (countdown.value > 0 || isSendingOtp.value) return
  await initiateVerification()
}

const submitOtp = async () => {
  if (otpCode.value.length !== 6 || isVerifying.value) return
  isVerifying.value = true
  errorMsg.value = ''
  
  try {
    const response = await axios.post(route('v2.whatsapp.verify-otp'), {
      code: otpCode.value
    })
    
    if (response.data.success) {
      // Update shared state
      if (page.props.auth?.user) {
        page.props.auth.user.whatsapp_verified_at = response.data.whatsapp_verified_at
        page.props.auth.user.no_telephone = response.data.no_telephone
      }
      isModalOpen.value = false
      isEditingWhatsapp.value = false
      router.reload({ only: ['auth'] })
    } else {
      errorMsg.value = response.data.message || 'OTP salah.'
    }
  } catch (error) {
    errorMsg.value = error.response?.data?.message || 'Kode OTP yang dimasukkan salah.'
  } finally {
    isVerifying.value = false
  }
}

const handleOtpInput = (e, index) => {
  const val = e.target.value
  otpDigits.value[index] = val.replace(/[^0-9]/g, '')
  
  if (otpDigits.value[index] && index < 5) {
    const nextInput = document.getElementById(`otp-input-${index + 1}`)
    if (nextInput) {
      nextInput.focus()
    }
  }
}

const handleOtpKeydown = (e, index) => {
  if (e.key === 'Backspace' && !otpDigits.value[index] && index > 0) {
    otpDigits.value[index - 1] = ''
    const prevInput = document.getElementById(`otp-input-${index - 1}`)
    if (prevInput) {
      prevInput.focus()
    }
  }
}

const handleOtpPaste = (e) => {
  e.preventDefault()
  const pasteData = e.clipboardData.getData('text')
  const digits = pasteData.replace(/[^0-9]/g, '').substring(0, 6)
  
  for (let i = 0; i < digits.length; i++) {
    otpDigits.value[i] = digits[i]
  }
  
  const targetIndex = Math.min(digits.length, 5)
  const targetInput = document.getElementById(`otp-input-${targetIndex}`)
  if (targetInput) {
    targetInput.focus()
  }
}
</script>

<template>
  <Head title="My Profile" />

  <AdminLayout>
    <div v-if="user" class="max-w-5xl mx-auto space-y-6">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-8 rounded-lg border border-[#CDD1E1] shadow-sm">
        <div class="flex items-center gap-6">
          <div class="relative group">
            <div class="w-24 h-24 rounded-lg overflow-hidden border-4 border-white shadow-xl bg-gray-100 flex items-center justify-center relative">
              <img :src="user.avatar" alt="Avatar" class="w-full h-full object-cover" />
              <div v-if="avatarForm.processing" class="absolute inset-0 bg-black/50 flex items-center justify-center text-white text-xs font-semibold">
                <span class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              </div>
            </div>
            <input 
              type="file" 
              ref="fileInput" 
              @change="onFileSelected" 
              accept="image/png, image/jpeg, image/jpg" 
              class="hidden" 
            />
            <button 
              v-if="user.role === 'Mahasiswa'"
              type="button"
              @click="triggerFileInput"
              :disabled="avatarForm.processing"
              class="absolute -bottom-2 -right-2 p-2 bg-primary text-white rounded-lg shadow-lg hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
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
            :class="[activeTab === 'profile' ? 'bg-primary/10 text-primary border-primary/20' : 'bg-white text-gray-600 border-transparent hover:bg-gray-50']"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-lg border transition-all text-sm font-semibold"
          >
            <UserIcon class="w-4 h-4" />
            Informasi Profil
          </button>
          <button 
            @click="activeTab = 'security'"
            :class="[activeTab === 'security' ? 'bg-primary/10 text-primary border-primary/20' : 'bg-white text-gray-600 border-transparent hover:bg-gray-50']"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-lg border transition-all text-sm font-semibold"
          >
            <ShieldIcon class="w-4 h-4" />
            Keamanan Akun
          </button>
        </div>

        <!-- Content Area -->
        <div class="lg:col-span-2">
          <Transition name="fade" mode="out-in">
            <div v-if="activeTab === 'profile'" key="profile">
              <Card class="rounded-lg border-[#CDD1E1] shadow-sm overflow-hidden">
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
                          :disabled="user.role === 'Mahasiswa'"
                          class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                          :class="user.role === 'Mahasiswa' ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'bg-gray-50'"
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
                          class="w-full pl-10 pr-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-500 cursor-not-allowed"
                        />
                      </div>
                    </div>

                    <div class="space-y-2 col-span-1 md:col-span-2">
                      <label class="text-sm font-bold text-gray-700">Nomor WhatsApp</label>
                      <div class="flex flex-col sm:flex-row gap-2">
                        <div class="relative flex-1">
                          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">+62</span>
                          <input 
                            type="text" 
                            v-model="whatsappInput"
                            :disabled="user.whatsapp_verified_at && !isEditingWhatsapp"
                            class="w-full pl-12 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed"
                            placeholder="8123456789"
                          />
                        </div>
                        <div class="flex items-center gap-2">
                          <template v-if="user.whatsapp_verified_at && !isEditingWhatsapp">
                            <span class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-lg text-xs font-bold bg-green-50 text-green-700 border border-green-200 shrink-0">
                              <CheckIcon class="w-4 h-4 text-green-600" />
                              Terverifikasi
                            </span>
                            <button 
                              @click="enableWhatsappEdit"
                              class="px-3 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold transition-colors shadow-sm shrink-0"
                            >
                              Ubah Nomor
                            </button>
                          </template>
                          
                          <template v-else>
                            <span v-if="!user.whatsapp_verified_at" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 shrink-0">
                              <AlertCircleIcon class="w-4 h-4 text-amber-600 animate-pulse" />
                              Belum Terverifikasi
                            </span>
                            <button 
                              @click="initiateVerification"
                              :disabled="isSendingOtp || !whatsappInput"
                              class="px-4 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm shrink-0"
                            >
                              {{ isSendingOtp ? 'Mengirim...' : 'Verifikasi Sekarang' }}
                            </button>
                            <button 
                              v-if="isEditingWhatsapp"
                              @click="cancelWhatsappEdit"
                              class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-bold transition-colors shrink-0"
                            >
                              Batal
                            </button>
                          </template>
                        </div>
                      </div>
                      <p class="text-xs text-gray-500">Nomor WhatsApp diperlukan untuk mengirimkan notifikasi penting seperti tagihan pembayaran, persetujuan KRS, dan status surat akademik.</p>
                    </div>
                  </div>
                </CardContent>
                <CardFooter class="p-6 border-t border-gray-100 flex justify-end gap-3 bg-gray-50/50">
                  <button class="px-6 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20">
                    Simpan Perubahan
                  </button>
                </CardFooter>
              </Card>
            </div>

            <div v-else-if="activeTab === 'security'" key="security">
              <Card class="rounded-lg border-[#CDD1E1] shadow-sm overflow-hidden">
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
                          class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
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
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
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
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
                            placeholder="Ulangi password baru"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                </CardContent>
                <CardFooter class="p-6 border-t border-gray-100 flex justify-end gap-3 bg-gray-50/50">
                  <button class="px-6 py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20">
                    Update Password
                  </button>
                </CardFooter>
              </Card>
            </div>
          </Transition>
        </div>
      </div>
    </div>

    <!-- OTP Verification Dialog -->
    <Dialog v-model:open="isModalOpen">
      <DialogContent class="sm:max-w-[425px] rounded-lg border-[#CDD1E1] p-0 overflow-hidden">
        <DialogHeader class="bg-primary text-white p-6 text-left">
          <DialogTitle class="text-lg font-bold text-white">Verifikasi Nomor WhatsApp</DialogTitle>
          <DialogDescription class="text-white/80 text-sm">
            Kami telah mengirimkan kode OTP 6-digit ke nomor WhatsApp +62 {{ whatsappInput }}
          </DialogDescription>
        </DialogHeader>
        
        <div class="p-6 space-y-6">
          <div v-if="errorMsg" class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs font-semibold rounded-lg flex items-center gap-2">
            <AlertCircleIcon class="w-4 h-4 text-red-600 shrink-0" />
            <span>{{ errorMsg }}</span>
          </div>
          
          <div class="space-y-4 text-center">
            <label class="text-sm font-bold text-gray-700 block">Masukkan 6 Digit Kode OTP</label>
            <div class="flex justify-center gap-2">
              <input 
                v-for="(digit, idx) in 6" 
                :key="idx"
                :id="'otp-input-' + idx"
                type="text" 
                inputmode="numeric"
                pattern="[0-9]*"
                maxlength="1"
                v-model="otpDigits[idx]"
                @input="handleOtpInput($event, idx)"
                @keydown="handleOtpKeydown($event, idx)"
                @paste="handleOtpPaste"
                class="w-12 h-12 text-center text-lg font-bold border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all"
              />
            </div>
          </div>
        </div>

        <DialogFooter class="p-6 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4 bg-gray-50/50">
          <button 
            @click="resendOtp"
            :disabled="countdown > 0 || isSendingOtp"
            class="w-full sm:w-auto text-xs font-bold text-primary hover:underline disabled:text-gray-400 disabled:no-underline text-left sm:text-center"
          >
            {{ countdown > 0 ? `Kirim ulang OTP dalam ${countdown}s` : 'Kirim Ulang OTP' }}
          </button>
          
          <div class="flex w-full sm:w-auto gap-2 justify-end">
            <DialogClose as-child>
              <button 
                class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors"
              >
                Batal
              </button>
            </DialogClose>
            <button 
              @click="submitOtp"
              :disabled="isVerifying || otpCode.length !== 6"
              class="px-5 py-2 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-primary/10 flex items-center gap-1.5"
            >
              <span v-if="isVerifying" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              {{ isVerifying ? 'Memverifikasi...' : 'Verifikasi' }}
            </button>
          </div>
        </DialogFooter>
      </DialogContent>
    </Dialog>
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
