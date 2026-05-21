<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { UserCircle, BookOpen, Calendar, Search } from 'lucide-vue-next'
import { ref, computed } from 'vue'

const props = defineProps({
    dosens: {
        type: Array,
        required: true,
    },
    tahunAkademik: {
        type: String,
        default: '-'
    }
})

const searchQuery = ref('')

const filteredDosens = computed(() => {
    if (!searchQuery.value) return props.dosens
    return props.dosens.filter(dosen => 
        dosen.nama.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})
</script>

<template>
    <Head title="Data Perkuliahan" />

    <AdminLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold leading-tight text-gray-800">
                        Data Perkuliahan
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Monitoring pengajaran dosen di Program Studi Anda</p>
                </div>
                <div class="flex items-center gap-2">
                    <Badge variant="secondary" class="bg-indigo-50 text-[#4B49AC] border-indigo-100 px-3 py-1">
                        <Calendar class="w-3.5 h-3.5 mr-1.5" />
                        Tahun Akademik: {{ tahunAkademik }}
                    </Badge>
                </div>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <!-- Search & Filter Bar -->
            <div class="mb-8 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <div class="relative w-full sm:max-w-md">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Cari nama dosen..." 
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#4B49AC] focus:border-transparent transition-all"
                    >
                </div>
                <div class="text-sm text-gray-500 font-medium">
                    Menampilkan <span class="text-[#4B49AC] font-bold">{{ filteredDosens.length }}</span> Dosen
                </div>
            </div>

            <!-- Dosen Grid -->
            <div v-if="filteredDosens.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <Card v-for="dosen in filteredDosens" :key="dosen.id" class="group border-none shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden bg-white">
                    <CardHeader class="pb-4 relative">
                        <div class="absolute right-0 top-0 p-3 opacity-5 group-hover:opacity-10 transition-opacity">
                            <UserCircle class="w-20 h-20 text-[#4B49AC]" />
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-[#4B49AC] flex items-center justify-center shadow-inner group-hover:bg-[#4B49AC] group-hover:text-white transition-colors duration-300">
                                <UserCircle class="w-7 h-7" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <CardTitle class="text-base font-bold text-gray-900 truncate group-hover:text-[#4B49AC] transition-colors">
                                    {{ dosen.nama }}
                                </CardTitle>
                                <p class="text-xs text-gray-500 mt-0.5">Dosen Pengampu</p>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="pt-0">
                        <div class="bg-gray-50 rounded-xl p-4 mb-5 border border-gray-100 group-hover:bg-indigo-50/50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <BookOpen class="w-4 h-4 text-[#4B49AC]" />
                                    <span>Beban Mengajar</span>
                                </div>
                                <span class="text-lg font-black text-[#4B49AC]">{{ dosen.total_matkul }} <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider ml-0.5">MK</span></span>
                            </div>
                        </div>
                        <Button as-child variant="default" class="w-full bg-[#4B49AC] hover:bg-[#3f3d91] rounded-xl shadow-md shadow-indigo-200 transition-all font-bold">
                            <Link :href="route('v2.kaprodi.data-perkuliahan.show', dosen.id)">
                                Lihat Detail Jadwal
                            </Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center py-20 bg-white rounded-2xl border-2 border-dashed border-gray-100 shadow-sm">
                <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mb-4 text-[#4B49AC]">
                    <Search class="w-10 h-10" />
                </div>
                <h3 class="text-lg font-bold text-gray-900">Dosen Tidak Ditemukan</h3>
                <p class="text-gray-500 max-w-xs text-center mt-2">
                    {{ searchQuery ? `Tidak ada dosen dengan nama "${searchQuery}" di Prodi Anda semester ini.` : 'Belum ada data dosen pengampu untuk tahun akademik ini.' }}
                </p>
                <Button v-if="searchQuery" variant="outline" @click="searchQuery = ''" class="mt-6 rounded-lg">
                    Reset Pencarian
                </Button>
            </div>
        </div>
    </AdminLayout>
</template>
