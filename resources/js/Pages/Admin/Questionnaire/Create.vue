<script setup>
import { ref } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'

import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Card, CardContent } from '@/Components/ui/card'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select'
import { Textarea } from '@/Components/ui/textarea'
import { Switch } from '@/Components/ui/switch'
import { 
  Plus, 
  Trash2, 
  ArrowUp, 
  ArrowDown, 
  ArrowLeft,
  Save,
  Loader2,
  X,
  Layers
} from 'lucide-vue-next'

const props = defineProps({
  category: String,
  categoryName: String,
  questionnaire: Object, // Null jika tambah kuis baru
})

const isEdit = !!props.questionnaire

// Setup Form
const form = useForm({
  title: props.questionnaire?.title || '',
  description: props.questionnaire?.description || '',
  status: props.questionnaire?.status || 'draft',
  target_respondent: props.questionnaire?.target_respondent || 'all',
  sections: props.questionnaire?.sections?.map((s, sIdx) => ({
    id: s.id,
    title: s.title,
    description: s.description || '',
    order: s.order,
    questions: s.questions?.map(q => ({
      id: q.id,
      question_text: q.question_text,
      question_type: q.question_type,
      options: q.options || ['Opsi 1'],
      is_required: q.is_required,
      order: q.order,
    })) || []
  })) || [
    {
      title: 'Bagian 1',
      description: 'Silakan isi bagian ini.',
      order: 0,
      questions: [
        {
          question_text: '',
          question_type: 'radio',
          options: ['Opsi 1'],
          is_required: false,
          order: 0,
        }
      ]
    }
  ]
})

// Tambah Seksi Halaman Baru
const addSection = () => {
  form.sections.push({
    title: `Bagian ${form.sections.length + 1}`,
    description: '',
    order: form.sections.length,
    questions: [
      {
        question_text: '',
        question_type: 'radio',
        options: ['Opsi 1'],
        is_required: false,
        order: 0,
      }
    ]
  })
}

// Hapus Seksi Halaman
const removeSection = (sIndex) => {
  form.sections.splice(sIndex, 1)
  // Re-index order seksi
  form.sections.forEach((s, idx) => {
    s.order = idx
  })
}

// Tambah Pertanyaan Baru dalam Seksi
const addQuestion = (sIndex) => {
  form.sections[sIndex].questions.push({
    question_text: '',
    question_type: 'radio',
    options: ['Opsi 1'],
    is_required: false,
    order: form.sections[sIndex].questions.length,
  })
}

// Hapus Pertanyaan dari Seksi
const removeQuestion = (sIndex, qIndex) => {
  form.sections[sIndex].questions.splice(qIndex, 1)
  // Re-index order pertanyaan di dalam seksi
  form.sections[sIndex].questions.forEach((q, idx) => {
    q.order = idx
  })
}

// Tambah Opsi Pilihan Jawaban
const addOption = (sIndex, qIndex) => {
  form.sections[sIndex].questions[qIndex].options.push(`Opsi ${form.sections[sIndex].questions[qIndex].options.length + 1}`)
}

// Hapus Opsi
const removeOption = (sIndex, qIndex, optIndex) => {
  if (form.sections[sIndex].questions[qIndex].options.length > 1) {
    form.sections[sIndex].questions[qIndex].options.splice(optIndex, 1)
  }
}

// Re-ordering Sections
const moveSectionUp = (sIndex) => {
  if (sIndex > 0) {
    const temp = form.sections[sIndex]
    form.sections[sIndex] = form.sections[sIndex - 1]
    form.sections[sIndex - 1] = temp
    
    form.sections.forEach((s, idx) => {
      s.order = idx
    })
  }
}

const moveSectionDown = (sIndex) => {
  if (sIndex < form.sections.length - 1) {
    const temp = form.sections[sIndex]
    form.sections[sIndex] = form.sections[sIndex + 1]
    form.sections[sIndex + 1] = temp
    
    form.sections.forEach((s, idx) => {
      s.order = idx
    })
  }
}

// Re-ordering Questions inside a Section
const moveQuestionUp = (sIndex, qIndex) => {
  if (qIndex > 0) {
    const temp = form.sections[sIndex].questions[qIndex]
    form.sections[sIndex].questions[qIndex] = form.sections[sIndex].questions[qIndex - 1]
    form.sections[sIndex].questions[qIndex - 1] = temp
    
    form.sections[sIndex].questions.forEach((q, idx) => {
      q.order = idx
    })
  }
}

const moveQuestionDown = (sIndex, qIndex) => {
  if (qIndex < form.sections[sIndex].questions.length - 1) {
    const temp = form.sections[sIndex].questions[qIndex]
    form.sections[sIndex].questions[qIndex] = form.sections[sIndex].questions[qIndex + 1]
    form.sections[sIndex].questions[qIndex + 1] = temp
    
    form.sections[sIndex].questions.forEach((q, idx) => {
      q.order = idx
    })
  }
}

// Submit Form
const submit = () => {
  // Re-index order final
  form.sections.forEach((s, sIdx) => {
    s.order = sIdx
    s.questions.forEach((q, qIdx) => {
      q.order = qIdx
    })
  })

  if (isEdit) {
    form.put(`/v2/admin/kuisioner/${props.category}/${props.questionnaire.id}`, {
      onSuccess: () => {
        // Redirect handled by controller
      }
    })
  } else {
    form.post(`/v2/admin/kuisioner/${props.category}`, {
      onSuccess: () => {
        // Redirect handled by controller
      }
    })
  }
}

const isChoiceType = (type) => {
  return ['radio', 'checkbox', 'select'].includes(type)
}
</script>

<template>
  <AdminLayout>
    <Head :title="isEdit ? 'Perbarui Kuisioner' : 'Buat Kuisioner'" />

    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Link :href="`/v2/admin/kuisioner/${category}`">
          <Button variant="outline" size="sm" class="border-gray-200 text-gray-500 rounded-lg">
            <ArrowLeft class="w-4 h-4 mr-2" />
            Kembali
          </Button>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ isEdit ? 'Edit Kuisioner' : 'Buat Kuisioner' }} ({{ categoryName }})</h1>
          <p class="text-[#6B7280]">Desain formulir evaluasi multi-halaman secara interaktif.</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Main Form Card (Details) -->
        <Card class="border-none shadow-sm">
          <CardContent class="p-6 space-y-4">
            <div class="space-y-2">
              <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Judul Kuisioner</Label>
              <Input 
                v-model="form.title" 
                placeholder="Survei Kepuasan Layanan Akademik 2026" 
                class="h-12 text-lg font-semibold border-gray-200 focus:border-[#4B49AC] rounded-lg"
                required
              />
              <p v-if="form.errors.title" class="text-xs text-red-500 font-medium">{{ form.errors.title }}</p>
            </div>

            <div class="space-y-2">
              <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Deskripsi Kuis Utama</Label>
              <Textarea 
                v-model="form.description" 
                placeholder="Tulis instruksi kuis utama di sini..." 
                class="min-h-[80px] border-gray-200 focus:border-[#4B49AC] rounded-lg"
              />
              <p v-if="form.errors.description" class="text-xs text-red-500 font-medium">{{ form.errors.description }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Target Responden</Label>
                <Select v-model="form.target_respondent">
                  <SelectTrigger class="h-11 border-gray-200 rounded-lg">
                    <SelectValue placeholder="Pilih Target Responden" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="all">Semua Responden (Publik)</SelectItem>
                    <SelectItem value="mahasiswa">Khusus Mahasiswa</SelectItem>
                    <SelectItem value="dosen">Khusus Dosen</SelectItem>
                    <SelectItem value="pegawai">Khusus Pegawai / Staf</SelectItem>
                    <SelectItem value="dosen_pegawai">Dosen dan Pegawai</SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.target_respondent" class="text-xs text-red-500 font-medium">{{ form.errors.target_respondent }}</p>
              </div>

              <div class="space-y-2">
                <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Status</Label>
                <Select v-model="form.status">
                  <SelectTrigger class="h-11 border-gray-200 rounded-lg">
                    <SelectValue placeholder="Pilih Status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="draft">Draft</SelectItem>
                    <SelectItem value="published">Aktif (Published)</SelectItem>
                    <SelectItem value="closed">Ditutup (Closed)</SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.status" class="text-xs text-red-500 font-medium">{{ form.errors.status }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Dynamic Sections & Questions -->
        <div class="space-y-8">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
              <Layers class="w-5 h-5 text-[#4B49AC]" />
              Bagian Halaman Formulir ({{ form.sections.length }})
            </h2>
            <Button type="button" @click="addSection" variant="outline" class="border-[#4B49AC] text-[#4B49AC] hover:bg-[#4B49AC]/5 rounded-lg">
              <Plus class="w-4 h-4 mr-2" />
              Tambah Halaman / Bagian Baru
            </Button>
          </div>

          <!-- Section Card Loop -->
          <div v-for="(section, sIndex) in form.sections" :key="sIndex" class="space-y-4 p-6 bg-slate-50 border border-slate-200 rounded-xl relative">
            
            <!-- Section Header (Title & Desc) -->
            <div class="flex flex-col sm:flex-row sm:items-start gap-4 justify-between border-b border-slate-200 pb-4">
              <div class="flex-1 space-y-3">
                <div class="flex items-center gap-2">
                  <span class="bg-[#4B49AC] text-white font-bold text-xs px-2.5 py-1 rounded-full uppercase">
                    Halaman {{ sIndex + 1 }}
                  </span>
                  <div class="flex items-center gap-1">
                    <Button 
                      type="button" 
                      variant="ghost" 
                      size="sm" 
                      @click="moveSectionUp(sIndex)" 
                      :disabled="sIndex === 0"
                      class="h-7 w-7 p-0 text-slate-500 rounded hover:bg-slate-200"
                    >
                      <ArrowUp class="w-3.5 h-3.5" />
                    </Button>
                    <Button 
                      type="button" 
                      variant="ghost" 
                      size="sm" 
                      @click="moveSectionDown(sIndex)" 
                      :disabled="sIndex === form.sections.length - 1"
                      class="h-7 w-7 p-0 text-slate-500 rounded hover:bg-slate-200"
                    >
                      <ArrowDown class="w-3.5 h-3.5" />
                    </Button>
                  </div>
                </div>

                <div class="space-y-2">
                  <Input 
                    v-model="section.title" 
                    placeholder="Judul Bagian/Halaman Kuis..." 
                    class="h-12 text-lg font-bold bg-white border-slate-200 focus:border-[#4B49AC] rounded-lg px-4 shadow-sm"
                    required
                  />
                  <Textarea 
                    v-model="section.description" 
                    placeholder="Deskripsi atau instruksi untuk bagian halaman ini (opsional)..." 
                    class="min-h-[48px] text-sm bg-white border-slate-200 focus:border-[#4B49AC] rounded-lg px-4 py-2.5 resize-none shadow-xs text-slate-600"
                  />
                </div>
              </div>

              <!-- Delete Section -->
              <Button 
                type="button" 
                variant="ghost" 
                size="sm" 
                @click="removeSection(sIndex)"
                :disabled="form.sections.length <= 1"
                class="text-red-500 hover:text-red-600 hover:bg-red-50 rounded-lg shrink-0 mt-8 sm:mt-1 px-3"
              >
                <Trash2 class="w-4 h-4 mr-2" />
                Hapus Halaman
              </Button>
            </div>

            <!-- Questions in this Section -->
            <div class="space-y-4 pl-0 sm:pl-4 border-l-0 sm:border-l-2 sm:border-slate-200">
              <div v-for="(q, qIndex) in section.questions" :key="qIndex" class="bg-white border border-slate-100 rounded-lg shadow-sm p-4 space-y-4">
                
                <!-- Question text and type -->
                <div class="flex flex-col md:flex-row md:items-start gap-4">
                  <div class="flex-1 space-y-2">
                    <Label class="text-[10px] font-bold text-slate-400 uppercase">Pertanyaan {{ qIndex + 1 }}</Label>
                    <Input 
                      v-model="q.question_text" 
                      placeholder="Kalimat Pertanyaan..." 
                      class="h-10 border-slate-200 focus:border-[#4B49AC] rounded-lg"
                      required
                    />
                  </div>

                  <div class="w-full md:w-[200px] space-y-2">
                    <Label class="text-[10px] font-bold text-slate-400 uppercase">Tipe Jawaban</Label>
                    <Select v-model="q.question_type">
                      <SelectTrigger class="h-10 border-slate-200 rounded-lg">
                        <SelectValue placeholder="Pilih Tipe" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="text">Jawaban Pendek</SelectItem>
                        <SelectItem value="paragraph">Jawaban Panjang</SelectItem>
                        <SelectItem value="radio">Pilihan Ganda (Single Choice)</SelectItem>
                        <SelectItem value="checkbox">Kotak Centang (Multiple Choices)</SelectItem>
                        <SelectItem value="select">Dropdown Menu</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>

                <!-- Choice options -->
                <div v-if="isChoiceType(q.question_type)" class="pl-4 border-l-2 border-indigo-50 space-y-3">
                  <Label class="text-[10px] font-bold text-[#4B49AC] uppercase">Opsi Pilihan</Label>
                  <div v-for="(opt, optIndex) in q.options" :key="optIndex" class="flex items-center gap-2 max-w-lg">
                    <div class="w-3.5 h-3.5 rounded-full border border-gray-300 flex-shrink-0" v-if="q.question_type === 'radio'" />
                    <div class="w-3.5 h-3.5 rounded border border-gray-300 flex-shrink-0" v-if="q.question_type === 'checkbox'" />
                    <span class="text-xs text-gray-400" v-if="q.question_type === 'select'">{{ optIndex + 1 }}.</span>
                    <Input 
                      v-model="q.options[optIndex]" 
                      class="h-8 border-gray-200 rounded-lg text-xs"
                      required
                    />
                    <Button 
                      type="button" 
                      variant="ghost" 
                      size="sm" 
                      @click="removeOption(sIndex, qIndex, optIndex)"
                      :disabled="q.options.length <= 1"
                      class="text-red-500 hover:text-red-600 hover:bg-red-50 h-7 w-7 p-0"
                    >
                      <X class="w-3.5 h-3.5" />
                    </Button>
                  </div>
                  <Button type="button" @click="addOption(sIndex, qIndex)" variant="ghost" size="sm" class="text-[11px] text-[#4B49AC] hover:text-[#3f3d91] font-semibold p-0">
                    <Plus class="w-3 h-3 mr-1" />
                    Tambah Opsi
                  </Button>
                </div>

                <!-- Question Controls (ordering, required switch, delete question) -->
                <div class="border-t border-slate-100 pt-3 flex flex-wrap items-center justify-between gap-4">
                  <div class="flex items-center gap-1">
                    <Button 
                      type="button" 
                      variant="ghost" 
                      size="sm" 
                      @click="moveQuestionUp(sIndex, qIndex)" 
                      :disabled="qIndex === 0"
                      class="h-7 w-7 p-0 text-slate-500 rounded hover:bg-slate-100"
                    >
                      <ArrowUp class="w-3.5 h-3.5" />
                    </Button>
                    <Button 
                      type="button" 
                      variant="ghost" 
                      size="sm" 
                      @click="moveQuestionDown(sIndex, qIndex)" 
                      :disabled="qIndex === section.questions.length - 1"
                      class="h-7 w-7 p-0 text-slate-500 rounded hover:bg-slate-100"
                    >
                      <ArrowDown class="w-3.5 h-3.5" />
                    </Button>
                  </div>

                  <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                      <Label class="text-xs font-semibold text-slate-500 cursor-pointer" :for="`required-${sIndex}-${qIndex}`">Wajib diisi</Label>
                      <Switch v-model:checked="q.is_required" :id="`required-${sIndex}-${qIndex}`" />
                    </div>

                    <Button 
                      type="button" 
                      variant="ghost" 
                      size="sm" 
                      @click="removeQuestion(sIndex, qIndex)"
                      :disabled="section.questions.length <= 1"
                      class="text-red-500 hover:text-red-600 hover:bg-red-50 rounded px-2.5 h-8 text-xs font-medium"
                    >
                      <Trash2 class="w-3.5 h-3.5 mr-1" />
                      Hapus Pertanyaan
                    </Button>
                  </div>
                </div>

              </div>
            </div>

            <!-- Add Question specifically to this Section -->
            <div class="flex justify-center pt-2">
              <Button type="button" @click="addQuestion(sIndex)" variant="outline" size="sm" class="border-slate-300 text-slate-600 hover:bg-slate-100 rounded-lg">
                <Plus class="w-3.5 h-3.5 mr-1.5" />
                Tambah Pertanyaan ke Halaman {{ sIndex + 1 }}
              </Button>
            </div>
            
          </div>
        </div>

        <!-- Sticky Footer Action -->
        <div class="border-t border-gray-200 bg-white p-4 flex items-center justify-end gap-3 rounded-xl shadow-sm">
          <Link :href="`/v2/admin/kuisioner/${category}`">
            <Button type="button" variant="ghost" class="h-11 px-6 rounded-lg text-gray-500 hover:bg-gray-100 transition-all font-semibold">
              Batal
            </Button>
          </Link>
          <Button 
            type="submit" 
            :disabled="form.processing"
            class="h-11 px-8 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-lg shadow-indigo-100 transition-all font-semibold"
          >
            <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
            <Save class="w-4 h-4 mr-2" v-else />
            {{ isEdit ? 'Simpan Perubahan' : 'Terbitkan Kuisioner' }}
          </Button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
