<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { Loader2, Save, AlertCircle, Wand2, Eye, EyeOff, GraduationCap, BookOpen, Clock } from 'lucide-vue-next';

const props = defineProps({
    mahasiswa: {
        type: Object,
        default: null
    },
    dosens: {
        type: Array,
        required: true
    },
    allKelas: {
        type: Array,
        required: true
    },
    currentKelasId: {
        type: [Number, String],
        default: null
    }
});

const emit = defineEmits(['success', 'cancel']);

const form = useForm({
    nama_lengkap: props.mahasiswa?.nama_lengkap ?? '',
    nim: props.mahasiswa?.nim ?? '',
    nisn: props.mahasiswa?.nisn ?? '',
    nik: props.mahasiswa?.nik ?? '',
    email: props.mahasiswa?.email ?? '',
    no_telephone: props.mahasiswa?.no_telephone ?? '',
    tempat_lahir: props.mahasiswa?.tempat_lahir ?? '',
    tanggal_lahir: props.mahasiswa?.tanggal_lahir ?? '',
    jenis_kelamin: props.mahasiswa?.jenis_kelamin ?? '',
    tahun_masuk: props.mahasiswa?.tahun_masuk ?? new Date().getFullYear().toString(),
    nama_ibu: props.mahasiswa?.nama_ibu ?? '',
    dosen_pembimbing_id: props.mahasiswa?.dosen_pembimbing_id?.toString() ?? '',
    kelas_id: props.mahasiswa?.kelas_id?.toString() ?? props.currentKelasId?.toString() ?? '',
    alamat: props.mahasiswa?.alamat ?? '',
    password: ''
});

const showPassword = ref(false);

const selectedKelas = computed(() => {
    if (!form.kelas_id) return null;
    return props.allKelas.find(k => k.id.toString() === form.kelas_id.toString());
});

const page = usePage();
const isLocal = computed(() => page.props.app_env === 'local');

const autoFill = () => {
    const randomSuffix = Math.floor(1000 + Math.random() * 9000);
    const firstNames = ['Budi', 'Siti', 'Agus', 'Dewi', 'Joko', 'Lani', 'Rudi', 'Ani', 'Eko', 'Sari'];
    const lastNames = ['Santoso', 'Pratiwi', 'Kurniawan', 'Lestari', 'Hidayat', 'Saputri', 'Wijaya', 'Utami'];
    
    const firstName = firstNames[Math.floor(Math.random() * firstNames.length)];
    const lastName = lastNames[Math.floor(Math.random() * lastNames.length)];
    const fullName = `${firstName} ${lastName}`;
    
    form.nama_lengkap = fullName;
    form.nim = `2024${randomSuffix}`;
    form.nisn = `00${randomSuffix}${Math.floor(Math.random() * 1000)}`;
    form.nik = `3201${randomSuffix}${randomSuffix}${Math.floor(Math.random() * 1000)}`;
    form.email = `${firstName.toLowerCase()}.${lastName.toLowerCase()}${randomSuffix}@example.test`;
    form.no_telephone = `0812${randomSuffix}${randomSuffix}`;
    form.tempat_lahir = ['Jakarta', 'Bandung', 'Surabaya', 'Semarang', 'Yogyakarta', 'Medan'][Math.floor(Math.random() * 6)];
    form.tanggal_lahir = '2005-01-01';
    form.jenis_kelamin = Math.random() > 0.5 ? 'Laki-Laki' : 'Perempuan';
    form.tahun_masuk = '2024';
    form.nama_ibu = 'Ibu ' + lastName;
    form.alamat = 'Jl. Contoh No. ' + Math.floor(Math.random() * 100);
    form.password = 'password123';

    if (props.dosens.length > 0) {
        form.dosen_pembimbing_id = props.dosens[Math.floor(Math.random() * props.dosens.length)].id.toString();
    }
};

const submit = () => {
    if (props.mahasiswa) {
        form.put(route('v2.admin.data-mahasiswa.update', props.mahasiswa.id), {
            onSuccess: () => emit('success'),
        });
    } else {
        form.post(route('v2.admin.data-mahasiswa.store'), {
            onSuccess: () => {
                form.reset();
                emit('success');
            },
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="flex flex-col h-full flex-1 min-h-0">
        <div class="flex-1 overflow-y-auto p-6 pb-12 space-y-6">
            <div v-if="Object.keys(form.errors).length > 0" class="p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
                <AlertCircle class="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
                <div>
                    <h5 class="text-sm font-bold text-red-800">Terdapat Kesalahan Input</h5>
                    <p class="text-xs text-red-600 mt-0.5">Silakan periksa kembali data yang Anda masukkan.</p>
                </div>
            </div>
            <!-- Basic Info Section -->
            <div class="space-y-4">
                <div class="space-y-2">
                    <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Lengkap <span class="text-destructive">*</span></Label>
                    <Input v-model="form.nama_lengkap" placeholder="Nama Sesuai KTP" :disabled="form.processing" class="h-11 rounded-lg" required />
                    <p v-if="form.errors.nama_lengkap" class="text-xs text-red-500 font-medium">{{ form.errors.nama_lengkap }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">NIM <span class="text-destructive">*</span></Label>
                        <Input type="number" v-model="form.nim" placeholder="Nomor Induk Mahasiswa" :disabled="form.processing" class="h-11 rounded-lg" required />
                        <p v-if="form.errors.nim" class="text-xs text-red-500 font-medium">{{ form.errors.nim }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">NISN</Label>
                        <Input type="number" v-model="form.nisn" placeholder="NISN (Opsional)" :disabled="form.processing" class="h-11 rounded-lg" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">NIK <span class="text-destructive">*</span></Label>
                        <Input type="number" v-model="form.nik" placeholder="NIK" :disabled="form.processing" class="h-11 rounded-lg" required />
                        <p v-if="form.errors.nik" class="text-xs text-red-500 font-medium">{{ form.errors.nik }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">No. WhatsApp <span class="text-destructive">*</span></Label>
                        <Input v-model="form.no_telephone" placeholder="08xxxxxxxx" :disabled="form.processing" class="h-11 rounded-lg" required />
                        <p v-if="form.errors.no_telephone" class="text-xs text-red-500 font-medium">{{ form.errors.no_telephone }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Email (Akun Login) <span class="text-destructive">*</span></Label>
                    <Input type="email" v-model="form.email" placeholder="email@contoh.com" :disabled="form.processing" class="h-11 rounded-lg" required />
                    <p v-if="form.errors.email" class="text-xs text-red-500 font-medium">{{ form.errors.email }}</p>
                </div>

                <!-- Personal Info Section -->
                <div class="pt-4 border-t border-gray-100">
                    <div class="grid grid-cols-2 gap-4 pt-4">
                        <div class="space-y-2">
                            <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tempat Lahir <span class="text-destructive">*</span></Label>
                            <Input v-model="form.tempat_lahir" placeholder="Kota" :disabled="form.processing" class="h-11 rounded-lg" required />
                        </div>
                        <div class="space-y-2">
                            <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal Lahir <span class="text-destructive">*</span></Label>
                            <Input type="date" v-model="form.tanggal_lahir" :disabled="form.processing" class="h-11 rounded-lg" required />
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis Kelamin <span class="text-destructive">*</span></Label>
                        <Select v-model="form.jenis_kelamin" required>
                            <SelectTrigger class="h-11 border-gray-200 focus:ring-[#4B49AC]/20 rounded-lg">
                                <SelectValue placeholder="Pilih..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Laki-Laki">Laki-Laki</SelectItem>
                                <SelectItem value="Perempuan">Perempuan</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tahun Masuk <span class="text-destructive">*</span></Label>
                        <Input v-model="form.tahun_masuk" placeholder="202x" :disabled="form.processing" class="h-11 rounded-lg" required />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Ibu Kandung <span class="text-destructive">*</span></Label>
                    <Input v-model="form.nama_ibu" placeholder="Nama Ibu" :disabled="form.processing" class="h-11 rounded-lg" required />
                </div>

                <!-- Academic Info Section -->
                <div class="pt-6 border-t border-gray-100 space-y-6">
                    <div class="space-y-2">
                        <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kelas <span class="text-destructive">*</span></Label>
                        <Select v-model="form.kelas_id" required>
                            <SelectTrigger class="h-11 border-gray-200 focus:ring-[#4B49AC]/20 rounded-lg">
                                <SelectValue placeholder="Pilih Kelas..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="kelas in allKelas" :key="kelas.id" :value="kelas.id.toString()">
                                    {{ kelas.nama_kelas }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Readonly Academic Details -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 space-y-1">
                            <Label class="text-[10px] font-bold text-gray-400 uppercase">Program Studi</Label>
                            <div class="text-xs font-semibold text-gray-700 truncate">
                                {{ selectedKelas?.prodi?.nama_prodi || '-' }}
                            </div>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 space-y-1">
                            <Label class="text-[10px] font-bold text-gray-400 uppercase">Semester</Label>
                            <div class="text-xs font-semibold text-gray-700">
                                {{ selectedKelas?.semester?.semester ? 'Semester ' + selectedKelas.semester.semester : '-' }}
                            </div>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 space-y-1">
                            <Label class="text-[10px] font-bold text-gray-400 uppercase">Jenis Kelas</Label>
                            <div class="text-xs font-semibold text-gray-700">
                                {{ selectedKelas?.jenis_kelas || '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pembimbing Akademik <span class="text-destructive">*</span></Label>
                        <Select v-model="form.dosen_pembimbing_id" required>
                            <SelectTrigger class="h-11 border-gray-200 focus:ring-[#4B49AC]/20 rounded-lg">
                                <SelectValue placeholder="Pilih Dosen..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="dosen in dosens" :key="dosen.id" :value="dosen.id.toString()">
                                    {{ dosen.nama }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.dosen_pembimbing_id" class="text-xs text-red-500 font-medium">{{ form.errors.dosen_pembimbing_id }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Alamat Lengkap <span class="text-destructive">*</span></Label>
                    <textarea 
                        v-model="form.alamat" 
                        class="flex min-h-[80px] w-full rounded-lg border border-gray-200 bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4B49AC]/20 disabled:cursor-not-allowed disabled:opacity-50 transition-all" 
                        placeholder="Alamat Domisili" 
                        :disabled="form.processing"
                        required
                    ></textarea>
                </div>

                <div class="space-y-2 pt-6 border-t border-gray-100">
                    <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Password</Label>
                    <div class="relative">
                        <Input 
                            :type="showPassword ? 'text' : 'password'" 
                            v-model="form.password" 
                            :placeholder="mahasiswa ? 'Kosongkan jika tidak diubah' : 'Min. 6 karakter'" 
                            :disabled="form.processing" 
                            class="h-11 rounded-lg pr-10" 
                            :required="!mahasiswa" 
                        />
                        <button 
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <Eye v-if="!showPassword" class="h-4 w-4" />
                            <EyeOff v-else class="h-4 w-4" />
                        </button>
                    </div>
                    <p v-if="form.errors.password" class="text-xs text-red-500 font-medium">{{ form.errors.password }}</p>
                </div>
            </div>
        </div>

        <!-- Sticky Footer -->
        <div class="p-6 border-t border-gray-100 bg-gray-50/50 shrink-0 mt-auto">
            <div class="flex flex-row items-center justify-end gap-3 w-full">
                <Button 
                    v-if="isLocal"
                    type="button" 
                    variant="outline" 
                    @click="autoFill" 
                    class="h-11 px-4 border-dashed border-indigo-200 text-indigo-600 hover:bg-indigo-50 transition-all flex items-center mr-auto"
                >
                    <Wand2 class="mr-2 h-4 w-4" />
                    Auto Fill
                </Button>

                <Button type="button" variant="ghost" @click="emit('cancel')" class="h-11 px-6 rounded-lg text-gray-500 hover:bg-gray-100 transition-all font-semibold">
                    Batal
                </Button>
                <Button type="submit" :disabled="form.processing" class="h-11 px-8 bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-lg shadow-lg shadow-indigo-100 transition-all font-semibold">
                    <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    {{ mahasiswa ? 'Simpan Perubahan' : 'Simpan' }}
                </Button>
            </div>
        </div>
    </form>
</template>
