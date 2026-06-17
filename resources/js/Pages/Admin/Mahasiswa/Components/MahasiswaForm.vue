<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
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
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/Components/ui/popover';
import { Loader2, Save, AlertCircle, Wand2, Eye, EyeOff, GraduationCap, BookOpen, Clock, Check, ChevronsUpDown, Search, X } from 'lucide-vue-next';

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
    },
    agamas: {
        type: Array,
        required: true
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
    id_agama: props.mahasiswa?.id_agama?.toString() ?? '',
    id_wilayah: props.mahasiswa?.id_wilayah ?? '',
    tahun_masuk: props.mahasiswa?.tahun_masuk ?? new Date().getFullYear().toString(),
    nama_ibu: props.mahasiswa?.nama_ibu ?? '',
    dosen_pembimbing_id: props.mahasiswa?.dosen_pembimbing_id?.toString() ?? '',
    kelas_id: props.mahasiswa?.kelas_id?.toString() ?? props.currentKelasId?.toString() ?? '',
    alamat: props.mahasiswa?.alamat ?? '',
    password: ''
});

const showPassword = ref(false);

const openPopoverDosen = ref(false);
const searchDosen = ref('');

const filteredDosens = computed(() => {
    if (!searchDosen.value) {
        return props.dosens;
    }
    const q = searchDosen.value.toLowerCase();
    return props.dosens.filter(d => 
        d.nama.toLowerCase().includes(q) || 
        (d.nidn && d.nidn.toLowerCase().includes(q))
    );
});

watch(openPopoverDosen, (val) => {
    if (!val) {
        searchDosen.value = '';
    }
});

const getDosenLabel = (dosenId) => {
    if (!dosenId) {
        return 'Pilih Dosen...';
    }
    const d = props.dosens.find(x => String(x.id) === String(dosenId));
    if (!d) {
        return 'Pilih Dosen...';
    }
    return `${d.nama} (${d.nidn || 'Tanpa NIDN'})`;
};

const getInitialWilayahLabel = () => {
    if (!props.mahasiswa || !props.mahasiswa.feeder_wilayah) return '';
    const w = props.mahasiswa.feeder_wilayah;
    const kabName = w.parent ? w.parent.nama_wilayah : '';
    const provName = (w.parent && w.parent.parent) ? w.parent.parent.nama_wilayah : '';
    let label = w.nama_wilayah;
    if (kabName) {
        label += `, ${kabName}`;
    }
    if (provName) {
        label += `, ${provName}`;
    }
    return label;
};

const wilayahSearch = ref(getInitialWilayahLabel());
const wilayahResults = ref([]);
const isSearchingWilayah = ref(false);
const showWilayahDropdown = ref(false);

let searchTimeout = null;

watch(wilayahSearch, (newVal) => {
    if (!newVal || newVal.length < 3) {
        wilayahResults.value = [];
        return;
    }

    if (props.mahasiswa && newVal === getInitialWilayahLabel()) {
        return;
    }

    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        isSearchingWilayah.value = true;
        try {
            const response = await axios.get(route('v2.admin.feeder.search-wilayah'), {
                params: { q: newVal }
            });
            wilayahResults.value = response.data;
            showWilayahDropdown.value = true;
        } catch (error) {
            console.error('Error fetching wilayah:', error);
        } finally {
            isSearchingWilayah.value = false;
        }
    }, 400);
});

const selectWilayah = (w) => {
    form.id_wilayah = w.id_wilayah;
    wilayahSearch.value = w.label;
    showWilayahDropdown.value = false;
    wilayahResults.value = [];
};

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

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Agama <span class="text-destructive">*</span></Label>
                        <Select v-model="form.id_agama" required>
                            <SelectTrigger class="h-11 border-gray-200 focus:ring-[#4B49AC]/20 rounded-lg">
                                <SelectValue placeholder="Pilih Agama..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="agama in agamas" :key="agama.id_agama" :value="agama.id_agama.toString()">
                                    {{ agama.nama_agama }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.id_agama" class="text-xs text-red-500 font-medium">{{ form.errors.id_agama }}</p>
                    </div>

                    <div class="space-y-2 relative">
                        <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kecamatan Domisili <span class="text-destructive">*</span></Label>
                        <div class="relative">
                            <Input 
                                v-model="wilayahSearch" 
                                placeholder="Cari kecamatan..." 
                                :disabled="form.processing" 
                                class="h-11 rounded-lg pr-10"
                                @focus="showWilayahDropdown = wilayahResults.length > 0"
                                required 
                            />
                            <div v-if="isSearchingWilayah" class="absolute right-3 top-1/2 -translate-y-1/2">
                                <Loader2 class="h-4 w-4 animate-spin text-gray-400" />
                            </div>
                        </div>
                        <p v-if="form.errors.id_wilayah" class="text-xs text-red-500 font-medium">{{ form.errors.id_wilayah }}</p>

                        <!-- Autocomplete Dropdown List -->
                        <div 
                            v-if="showWilayahDropdown && wilayahResults.length > 0" 
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                        >
                            <ul class="py-1 text-sm text-gray-700">
                                <li 
                                    v-for="w in wilayahResults" 
                                    :key="w.id_wilayah"
                                    @click="selectWilayah(w)"
                                    class="px-4 py-2 hover:bg-indigo-50 cursor-pointer transition-colors"
                                >
                                    {{ w.label }}
                                </li>
                            </ul>
                        </div>
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
                        <Popover v-model:open="openPopoverDosen">
                            <PopoverTrigger as-child>
                                <Button
                                    type="button"
                                    variant="outline"
                                    role="combobox"
                                    :aria-expanded="openPopoverDosen"
                                    class="w-full justify-between h-11 border-gray-200 focus:border-[#4B49AC] focus:ring-[#4B49AC]/20 font-normal rounded-lg text-left bg-white px-3"
                                    :class="!form.dosen_pembimbing_id ? 'text-gray-400' : 'text-gray-900'"
                                >
                                    <span class="truncate">{{ getDosenLabel(form.dosen_pembimbing_id) }}</span>
                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-gray-500" />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-[380px] sm:w-[480px] p-0 bg-white border border-gray-200 shadow-lg z-50 rounded-lg" align="start">
                                <div class="flex flex-col">
                                    <div class="flex items-center border-b px-3 py-2 sticky top-0 bg-white z-10">
                                        <Search class="mr-2 h-4 w-4 shrink-0 opacity-50 text-gray-500" />
                                        <input
                                            v-model="searchDosen"
                                            type="text"
                                            placeholder="Cari nama atau NIDN dosen..."
                                            class="flex h-9 w-full rounded-md bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50"
                                        />
                                        <button
                                            v-if="searchDosen"
                                            type="button"
                                            @click="searchDosen = ''"
                                            class="ml-1 text-gray-400 hover:text-gray-600"
                                        >
                                            <X class="w-4 h-4" />
                                        </button>
                                    </div>
                                    
                                    <div class="max-h-[300px] overflow-y-auto p-1">
                                        <div v-if="filteredDosens.length === 0" class="py-6 text-center text-sm text-gray-500">
                                            Dosen tidak ditemukan.
                                        </div>
                                        <button
                                            v-else
                                            v-for="dosen in filteredDosens"
                                            :key="dosen.id"
                                            type="button"
                                            @click="() => {
                                                form.dosen_pembimbing_id = String(dosen.id)
                                                openPopoverDosen = false
                                                searchDosen = ''
                                            }"
                                            class="w-full text-left flex items-center px-3 py-2 text-sm rounded-md hover:bg-gray-100 transition-colors"
                                            :class="form.dosen_pembimbing_id === String(dosen.id) ? 'bg-[#4b49ac]/10 text-[#4B49AC] font-semibold' : 'text-gray-700'"
                                        >
                                            <Check
                                                :class="form.dosen_pembimbing_id === String(dosen.id) ? 'opacity-100' : 'opacity-0'"
                                                class="mr-2 h-4 w-4 text-[#4B49AC] shrink-0"
                                            />
                                            <span class="truncate">{{ dosen.nama }} ({{ dosen.nidn || 'Tanpa NIDN' }})</span>
                                        </button>
                                    </div>
                                </div>
                            </PopoverContent>
                        </Popover>
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
