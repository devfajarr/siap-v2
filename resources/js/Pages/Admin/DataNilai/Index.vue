<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { UserCircle, BookOpen, FolderSearch } from 'lucide-vue-next'

defineProps({
    dosens: {
        type: Array,
        required: true,
    }
})
</script>

<template>
    <Head title="Data Nilai" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Data Nilai
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Card v-for="dosen in dosens" :key="dosen.id" class="shadow-sm hover:shadow-md transition-shadow">
                        <CardHeader class="flex flex-row items-center gap-4 pb-2">
                            <div class="p-2 bg-primary/10 rounded-full shrink-0">
                                <UserCircle class="w-8 h-8 text-[#4B49AC]" />
                            </div>
                            <div>
                                <CardTitle class="text-base leading-tight">{{ dosen.nama }}</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center gap-2 text-sm text-gray-600 mb-4 mt-2">
                                <BookOpen class="w-4 h-4 shrink-0" />
                                <span>Jumlah Mata Kuliah: <span class="font-bold">{{ dosen.total_matkul }}</span></span>
                            </div>
                            <Button as-child variant="default" class="w-full bg-[#4B49AC] hover:bg-[#4B49AC]/90">
                                <Link :href="route('v2.admin.data-nilai.show', dosen.id)">
                                    Lihat Mata Kuliah
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>

                    <div v-if="dosens.length === 0" class="col-span-full py-16 px-4">
                        <div class="max-w-md mx-auto text-center bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
                            <div class="w-16 h-16 bg-[#4B49AC]/10 rounded-full flex items-center justify-center mx-auto mb-4 text-[#4B49AC]">
                                <FolderSearch class="w-8 h-8 text-[#4B49AC]" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Data Rekap Nilai Kosong</h3>
                            <p class="text-sm text-gray-500 leading-relaxed">
                                Belum ada data dosen aktif beserta jadwal perkuliahan yang tercatat di sistem. Rekap penilaian akan otomatis tersedia setelah jadwal perkuliahan dan kelas diatur oleh pihak administrasi.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
