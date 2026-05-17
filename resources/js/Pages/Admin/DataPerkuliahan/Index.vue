<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { UserCircle, BookOpen } from 'lucide-vue-next'

defineProps({
    dosens: {
        type: Array,
        required: true,
    }
})
</script>

<template>
    <Head title="Data Perkuliahan" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Data Perkuliahan
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
                                <Link :href="route('v2.admin.data-perkuliahan.show', dosen.id)">
                                    Lihat Jadwal
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>

                    <div v-if="dosens.length === 0" class="col-span-full py-12 text-center text-gray-500">
                        Belum Ada Data Dosen yang Memiliki Jadwal 🚀🚀....
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
