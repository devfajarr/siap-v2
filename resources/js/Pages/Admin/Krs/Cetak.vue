<script setup>
import { computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { ArrowLeft, Printer } from 'lucide-vue-next';

const props = defineProps({
    mahasiswa: Object,
    krs: Object,
    matkulKrs: Array,
});

const toRoman = (num) => {
    const n = parseInt(num);
    if (isNaN(n) || n <= 0) return '-';
    const romanNumerals = [
        { value: 1000, roman: 'M' },
        { value: 900, roman: 'CM' },
        { value: 500, roman: 'D' },
        { value: 400, roman: 'CD' },
        { value: 100, roman: 'C' },
        { value: 90, roman: 'XC' },
        { value: 50, roman: 'L' },
        { value: 40, roman: 'XL' },
        { value: 10, roman: 'X' },
        { value: 9, roman: 'IX' },
        { value: 5, roman: 'V' },
        { value: 4, roman: 'IV' },
        { value: 1, roman: 'I' },
    ];
    let result = '';
    let current = n;
    for (const item of romanNumerals) {
        while (current >= item.value) {
            result += item.roman;
            current -= item.value;
        }
    }
    return result;
};

const formatDate = (dateString) => {
    if (!dateString) return '................';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const totalTeori = computed(() => props.matkulKrs?.reduce((sum, m) => sum + (Number(m.teori) || 0), 0) || 0);
const totalPraktek = computed(() => props.matkulKrs?.reduce((sum, m) => sum + (Number(m.praktek) || 0), 0) || 0);
const totalSks = computed(() => totalTeori.value + totalPraktek.value);

const emptyRowsCount = computed(() => {
    const count = props.matkulKrs?.length || 0;
    return count < 8 ? 8 - count : 0;
});

const triggerPrint = () => {
    window.print();
};

onMounted(() => {
    // Otomatis membuka dialog print sesaat setelah halaman dimuat
    setTimeout(() => {
        window.print();
    }, 500);
});
</script>

<template>
    <Head :title="`Cetak KRS - ${mahasiswa.nim} - ${mahasiswa.nama_lengkap}`" />

    <div class="min-h-screen bg-gray-100 font-sans text-gray-900 pb-12">
        <!-- Top Action Bar (Hanya tampil di peramban, tersembunyi saat cetak) -->
        <div class="print:hidden bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Button variant="outline" as-child class="h-10 rounded-xl border-gray-200">
                    <Link :href="route('v2.admin.krs.show', mahasiswa.kelas_id)">
                        <ArrowLeft class="mr-2 h-4 w-4" /> Kembali ke Daftar Mahasiswa
                    </Link>
                </Button>
                <div class="hidden md:block">
                    <p class="text-xs text-gray-400 font-medium">Pratinjau Cetak KRS Mahasiswa</p>
                    <h2 class="text-sm font-bold text-gray-800">{{ mahasiswa.nama_lengkap }} ({{ mahasiswa.nim }})</h2>
                </div>
            </div>

            <Button @click="triggerPrint" class="bg-[#4B49AC] hover:bg-[#3f3d91] text-white rounded-xl shadow-md px-6 h-10 font-bold text-sm">
                <Printer class="mr-2 h-4 w-4" /> Cetak Dokumen
            </Button>
        </div>

        <!-- Print Sheet Container -->
        <div class="max-w-[210mm] mx-auto my-8 print:my-0 bg-white shadow-lg print:shadow-none p-[15mm] md:p-[20mm] print:p-0 text-black">
            <!-- Header PSA -->
            <div class="flex items-center border-b-[3px] border-black pb-4 mb-6">
                <img src="/images/file.png" alt="Logo POLSA" class="w-16 h-16 mr-4 shrink-0 object-contain" />
                <div>
                    <h1 class="text-xl font-black uppercase tracking-wide leading-tight">Politeknik Sawunggalih Aji</h1>
                    <h2 class="text-base font-bold uppercase tracking-wider text-gray-800">Kartu Rencana Studi (KRS)</h2>
                </div>
            </div>

            <!-- Identitas & Prodi -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 text-xs md:text-sm">
                <!-- Kolom Kiri: Data Mahasiswa -->
                <div class="space-y-1.5">
                    <div class="flex"><span class="w-24 font-bold">NIM</span><span class="mr-2 font-bold">:</span><span class="font-mono font-bold">{{ mahasiswa.nim }}</span></div>
                    <div class="flex"><span class="w-24 font-bold">Nama Lengkap</span><span class="mr-2 font-bold">:</span><span class="font-bold uppercase">{{ mahasiswa.nama_lengkap }}</span></div>
                    <div class="flex"><span class="w-24 font-bold">Kelas / Rombel</span><span class="mr-2 font-bold">:</span><span class="font-medium">{{ mahasiswa.kelas?.nama_kelas || '-' }}</span></div>
                </div>
                <!-- Kolom Kanan: Akademik -->
                <div class="space-y-1.5">
                    <div class="flex"><span class="w-24 font-bold">Program Studi</span><span class="mr-2 font-bold">:</span><span class="font-medium">{{ mahasiswa.kelas?.prodi?.nama_prodi || '-' }}</span></div>
                    <div class="flex"><span class="w-24 font-bold">Semester</span><span class="mr-2 font-bold">:</span><span class="font-medium">{{ toRoman(mahasiswa.kelas?.semester?.semester) }} ({{ (mahasiswa.kelas?.semester?.semester || 1) % 2 === 0 ? 'Genap' : 'Ganjil' }})</span></div>
                    <div class="flex"><span class="w-24 font-bold">Tahun Ajaran</span><span class="mr-2 font-bold">:</span><span class="font-medium">{{ krs?.tahun_ajaran || '-' }}</span></div>
                </div>
            </div>

            <!-- Catatan Kehadiran -->
            <div class="bg-gray-50 border border-gray-300 p-3 mb-6 rounded text-[11px] font-semibold text-gray-800">
                *) Syarat untuk mengikuti ujian semester, persentase kehadiran perkuliahan minimal 75%.
            </div>

            <!-- Tabel Matkul KRS -->
            <table class="w-full border-collapse border border-black text-xs md:text-sm mb-8">
                <thead>
                    <tr class="bg-gray-100">
                        <th rowspan="2" class="border border-black py-2 px-3 text-center w-12 font-bold">No</th>
                        <th rowspan="2" class="border border-black py-2 px-3 text-center w-24 font-bold">Kode</th>
                        <th rowspan="2" class="border border-black py-2 px-4 text-left font-bold">Mata Kuliah</th>
                        <th colspan="3" class="border border-black py-1 px-3 text-center font-bold">SKS</th>
                    </tr>
                    <tr class="bg-gray-100">
                        <th class="border border-black py-1 px-3 text-center w-12 font-bold">T</th>
                        <th class="border border-black py-1 px-3 text-center w-12 font-bold">P</th>
                        <th class="border border-black py-1 px-3 text-center w-16 font-bold">JML</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(matkul, idx) in matkulKrs" :key="matkul.id">
                        <td class="border border-black py-2 px-3 text-center font-medium">{{ idx + 1 }}</td>
                        <td class="border border-black py-2 px-3 text-center font-mono font-medium">{{ matkul.kode }}</td>
                        <td class="border border-black py-2 px-4 text-left font-medium">{{ matkul.nama_matkul }}</td>
                        <td class="border border-black py-2 px-3 text-center">{{ matkul.teori }}</td>
                        <td class="border border-black py-2 px-3 text-center">{{ matkul.praktek }}</td>
                        <td class="border border-black py-2 px-3 text-center font-bold">{{ (Number(matkul.teori) || 0) + (Number(matkul.praktek) || 0) }}</td>
                    </tr>
                    
                    <!-- Empty rows filler for formal layout -->
                    <tr v-for="i in emptyRowsCount" :key="`empty-${i}`" class="h-8">
                        <td class="border border-black"></td>
                        <td class="border border-black"></td>
                        <td class="border border-black"></td>
                        <td class="border border-black"></td>
                        <td class="border border-black"></td>
                        <td class="border border-black"></td>
                    </tr>

                    <!-- Total SKS Row -->
                    <tr class="bg-gray-50 font-bold">
                        <td colspan="3" class="border border-black py-3 px-4 text-right">Jumlah Total SKS</td>
                        <td class="border border-black py-3 px-3 text-center">{{ totalTeori }}</td>
                        <td class="border border-black py-3 px-3 text-center">{{ totalPraktek }}</td>
                        <td class="border border-black py-3 px-3 text-center text-base font-black">{{ totalSks }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Bagian Tanda Tangan -->
            <div class="mt-8 pt-4">
                <div class="text-right text-xs md:text-sm mb-6 pr-6">
                    Purworejo, {{ formatDate(krs?.created_at || new Date()) }}
                </div>

                <div class="grid grid-cols-3 gap-4 text-center text-xs md:text-sm">
                    <!-- Dosen PA -->
                    <div class="space-y-16">
                        <p class="font-semibold">Pembimbing Akademik</p>
                        <p class="font-bold underline decoration-1 underline-offset-4">{{ mahasiswa.pembimbing_akademik?.nama || '................................' }}</p>
                    </div>

                    <!-- Keterangan Status -->
                    <div class="space-y-16">
                        <p class="font-semibold">Status Pengajuan</p>
                        <div class="inline-flex items-center gap-2 border border-black rounded px-3 py-1 font-bold text-xs uppercase tracking-wider bg-gray-50">
                            <input type="checkbox" checked disabled class="accent-black h-3.5 w-3.5" />
                            <span>{{ krs?.keterangan || 'Telah Disetujui' }}</span>
                        </div>
                    </div>

                    <!-- Mahasiswa -->
                    <div class="space-y-16">
                        <p class="font-semibold">Mahasiswa</p>
                        <p class="font-bold underline decoration-1 underline-offset-4 uppercase">{{ mahasiswa.nama_lengkap }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    body {
        background-color: white !important;
        color: black !important;
    }
    @page {
        size: A4 portrait;
        margin: 15mm;
    }
}
</style>
