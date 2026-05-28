<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'

import { Button } from '@/Components/ui/button'
import { Card, CardContent } from '@/Components/ui/card'
import { Input } from '@/Components/ui/input'
import { Textarea } from '@/Components/ui/textarea'
import { Label } from '@/Components/ui/label'
import { 
  ClipboardCheck, 
  Send, 
  Loader2,
  AlertCircle,
  ChevronLeft,
  ChevronRight
} from 'lucide-vue-next'

const props = defineProps({
  questionnaire: Object, // Berisi sections.questions
  isPreview: {
    type: Boolean,
    default: false
  },
  dosen: Object,
  matkul: Object,
  dosenId: Number,
  matkulId: Number,
  jadwalId: Number
})

const currentStep = ref(0)
const totalSteps = props.questionnaire.sections.length
const stepErrors = ref({})

// Inisialisasi jawaban kosong berdasarkan tipe pertanyaan di seluruh seksi
const initialAnswers = {}
props.questionnaire.sections.forEach(s => {
  s.questions.forEach(q => {
    if (q.question_type === 'checkbox') {
      initialAnswers[q.id] = []
    } else {
      initialAnswers[q.id] = ''
    }
  })
})

const form = useForm({
  answers: initialAnswers,
  dosen_id: props.dosenId,
  matkul_id: props.matkulId,
  jadwal_id: props.jadwalId
})

// Fungsi validasi halaman aktif saat ini
const validateCurrentStep = () => {
  stepErrors.value = {}
  let isValid = true
  const currentSection = props.questionnaire.sections[currentStep.value]

  currentSection.questions.forEach(q => {
    const answer = form.answers[q.id]
    if (q.is_required) {
      if (
        answer === undefined || 
        answer === null || 
        answer === '' || 
        (Array.isArray(answer) && answer.length === 0)
      ) {
        stepErrors.value[q.id] = 'Pertanyaan ini wajib diisi.'
        isValid = false;
      }
    }
  })

  return isValid
}

const nextStep = () => {
  if (validateCurrentStep()) {
    if (currentStep.value < totalSteps - 1) {
      currentStep.value++
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  }
}

const prevStep = () => {
  if (currentStep.value > 0) {
    currentStep.value--
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const submit = () => {
  if (validateCurrentStep()) {
    if (props.isPreview) {
      alert('Ini adalah mode pratinjau kuisioner. Tanggapan Anda tidak akan disimpan.')
      return
    }
    form.post(`/v2/isi-kuisioner/${props.questionnaire.id}/submit`, {
      onSuccess: () => {
        // Redirect ditangani di backend
      }
    })
  }
}

const toggleCheckbox = (questionId, option) => {
  const currentAnswers = form.answers[questionId]
  const index = currentAnswers.indexOf(option)
  if (index === -1) {
    currentAnswers.push(option)
  } else {
    currentAnswers.splice(index, 1)
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#F5F7FF] py-10 px-4 sm:px-6 lg:px-8 font-['Nunito']">
    <Head :title="questionnaire.title" />

    <div class="max-w-2xl mx-auto space-y-6">
      <!-- Preview Mode Banner -->
      <div 
        v-if="isPreview" 
        class="bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-lg shadow-sm flex items-start sm:items-center gap-3 font-semibold"
      >
        <AlertCircle class="w-5 h-5 text-amber-600 shrink-0 mt-0.5 sm:mt-0" />
        <div class="flex-1 text-xs sm:text-sm">
          <strong>Mode Pratinjau</strong>: Anda sedang melihat tampilan kuisioner ini sebagai Pembuat/Admin. Pengiriman tanggapan dinonaktifkan dan tidak akan disimpan.
        </div>
      </div>

      <!-- Context Banner for Teacher Performance Evaluation (Shows on all steps) -->
      <div 
        v-if="questionnaire.type === 'kinerja_pengajar' && dosen && matkul" 
        class="bg-[#4B49AC]/5 border border-[#4B49AC]/10 text-[#4B49AC] p-4 rounded-xl shadow-xs space-y-1"
      >
        <div class="text-[10px] uppercase tracking-wider font-extrabold text-[#4B49AC]/80">Dosen yang Sedang Dinilai:</div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
          <div class="font-extrabold text-slate-800 text-sm sm:text-base">{{ dosen.nama }}</div>
          <div class="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md sm:self-center w-fit">
            {{ matkul.nama_matkul }} ({{ matkul.kode }})
          </div>
        </div>
      </div>

      <!-- Questionnaire Header Card (Hanya muncul di halaman pertama) -->
      <Card 
        v-if="currentStep === 0"
        class="border-t-8 border-t-[#4B49AC] border-x-none border-b-none shadow-md rounded-lg overflow-hidden bg-white"
      >
        <CardContent class="p-6 sm:p-8 space-y-4">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-[#4B49AC]/10 text-[#4B49AC] rounded-xl">
              <ClipboardCheck class="w-8 h-8" />
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 leading-tight">
              {{ questionnaire.title }}
            </h1>
          </div>
          
          <div class="text-gray-600 whitespace-pre-line leading-relaxed text-sm sm:text-base" v-if="questionnaire.description">
            {{ questionnaire.description }}
          </div>

          <div class="text-xs text-rose-500 font-medium">
            * Menunjukkan pertanyaan yang wajib diisi
          </div>
        </CardContent>
      </Card>

      <!-- Progress Header (Indikator Halaman) -->
      <div class="flex items-center justify-between px-2 text-slate-500 text-sm">
        <span class="font-bold text-[#4B49AC]">
          {{ questionnaire.sections[currentStep].title }}
        </span>
        <span class="font-semibold">
          Halaman {{ currentStep + 1 }} dari {{ totalSteps }}
        </span>
      </div>

      <!-- Progress Bar Visual -->
      <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden shrink-0 shadow-sm">
        <div 
          class="bg-[#4B49AC] h-full rounded-full transition-all duration-300"
          :style="{ width: `${((currentStep + 1) / totalSteps) * 100}%` }"
        />
      </div>

      <!-- Section Description (Jika ada) -->
      <Card 
        v-if="questionnaire.sections[currentStep].description"
        class="border-none shadow-sm rounded-lg bg-indigo-50/50 p-4 border border-indigo-100"
      >
        <p class="text-xs sm:text-sm text-[#4B49AC] font-medium leading-relaxed">
          {{ questionnaire.sections[currentStep].description }}
        </p>
      </Card>

      <!-- Questions List for Current Section -->
      <div class="space-y-4">
        <div v-for="(q, index) in questionnaire.sections[currentStep].questions" :key="q.id">
          <Card class="border-none shadow-sm rounded-lg bg-white overflow-hidden">
            <CardContent class="p-6 space-y-4">
              
              <!-- Question Text -->
              <div class="space-y-1">
                <h2 class="text-base sm:text-lg font-bold text-gray-800 leading-snug">
                  {{ index + 1 }}. {{ q.question_text }}
                  <span class="text-rose-500" v-if="q.is_required">*</span>
                </h2>
              </div>

              <!-- Input Types -->
              
              <!-- 1. Text (Short Answer) -->
              <div v-if="q.question_type === 'text'">
                <Input 
                  v-model="form.answers[q.id]"
                  placeholder="Jawaban singkat Anda..."
                  class="h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 rounded-lg"
                  :required="q.is_required"
                />
              </div>

              <!-- 2. Paragraph (Long Answer) -->
              <div v-else-if="q.question_type === 'paragraph'">
                <Textarea 
                  v-model="form.answers[q.id]"
                  placeholder="Jawaban panjang Anda..."
                  class="min-h-[100px] border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 rounded-lg"
                  :required="q.is_required"
                />
              </div>

              <!-- 3. Radio (Single Choice) -->
              <div v-else-if="q.question_type === 'radio'" class="space-y-3">
                <div v-for="option in q.options" :key="option" class="flex items-center gap-3">
                  <input 
                    type="radio" 
                    :id="`opt-${q.id}-${option}`"
                    :name="`question-${q.id}`"
                    :value="option"
                    v-model="form.answers[q.id]"
                    class="w-4 h-4 text-[#4B49AC] border-gray-300 focus:ring-[#4B49AC] focus:ring-2 cursor-pointer"
                  />
                  <Label :for="`opt-${q.id}-${option}`" class="text-sm text-gray-700 cursor-pointer font-medium select-none">
                    {{ option }}
                  </Label>
                </div>
              </div>

              <!-- 4. Checkbox (Multiple Choices) -->
              <div v-else-if="q.question_type === 'checkbox'" class="space-y-3">
                <div v-for="option in q.options" :key="option" class="flex items-center gap-3">
                  <input 
                    type="checkbox" 
                    :id="`opt-${q.id}-${option}`"
                    :value="option"
                    :checked="form.answers[q.id].includes(option)"
                    @change="toggleCheckbox(q.id, option)"
                    class="w-4 h-4 text-[#4B49AC] border-gray-300 rounded focus:ring-[#4B49AC] focus:ring-2 cursor-pointer"
                  />
                  <Label :for="`opt-${q.id}-${option}`" class="text-sm text-gray-700 cursor-pointer font-medium select-none">
                    {{ option }}
                  </Label>
                </div>
              </div>

              <!-- 5. Select (Dropdown) -->
              <div v-else-if="q.question_type === 'select'">
                <select 
                  v-model="form.answers[q.id]"
                  class="w-full h-11 border border-gray-200 rounded-lg px-3 text-sm text-gray-700 focus:border-[#4B49AC] focus:outline-none bg-white"
                  :required="q.is_required"
                >
                  <option value="" disabled selected>Pilih salah satu...</option>
                  <option v-for="option in q.options" :key="option" :value="option">
                    {{ option }}
                  </option>
                </select>
              </div>

              <!-- Dynamic Validation Error Alert -->
              <div v-if="stepErrors[q.id] || form.errors[`answers.${q.id}`]" class="flex items-center gap-2 text-xs text-red-500 font-semibold bg-red-50 p-2.5 rounded-lg border border-red-100 mt-2">
                <AlertCircle class="w-4 h-4 text-red-500 flex-shrink-0" />
                <span>{{ stepErrors[q.id] || form.errors[`answers.${q.id}`] }}</span>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Navigation Step Controls -->
      <div class="flex items-center justify-between py-4 border-t border-slate-200">
        <!-- Back Button -->
        <Button 
          type="button" 
          variant="outline" 
          @click="prevStep" 
          :disabled="currentStep === 0"
          class="h-11 px-5 border-gray-200 text-gray-600 rounded-lg font-bold"
        >
          <ChevronLeft class="w-4 h-4 mr-1" />
          Kembali
        </Button>

        <!-- Next / Submit Button -->
        <Button 
          v-if="currentStep < totalSteps - 1"
          type="button" 
          @click="nextStep"
          class="h-11 px-6 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg font-bold shadow-sm"
        >
          Berikutnya
          <ChevronRight class="w-4 h-4 ml-1" />
        </Button>

        <Button 
          v-else
          type="button"
          @click="submit"
          :disabled="form.processing"
          class="h-11 px-8 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-lg shadow-indigo-100 transition-all font-bold"
        >
          <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
          <Send class="w-4 h-4 mr-2" v-else />
          {{ isPreview ? 'Selesai Pratinjau' : 'Kirim Tanggapan' }}
        </Button>
      </div>

    </div>
  </div>
</template>
