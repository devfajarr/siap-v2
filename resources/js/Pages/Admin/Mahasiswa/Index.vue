<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Card, CardHeader, CardTitle, CardContent, CardDescription, CardFooter } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Users, GraduationCap, ArrowRight, LayoutGrid } from 'lucide-vue-next';

const props = defineProps({
    kelass: Array,
});
</script>

<template>
    <Head title="Data Mahasiswa" />

    <AdminLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight text-primary">Data Mahasiswa</h2>
                    <p class="text-muted-foreground">Pilih kelas untuk mengelola data mahasiswa.</p>
                </div>
                <div v-if="kelass.length > 0">
                    <Button variant="outline" as-child>
                        <a :href="route('v2.admin.data-mahasiswa.export-all')">
                            <GraduationCap class="mr-2 h-4 w-4" />
                            Export Semua
                        </a>
                    </Button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="kelass.length === 0" class="flex flex-col items-center justify-center min-h-[400px] p-8 text-center bg-white rounded-lg border-2 border-dashed border-gray-200">
                <div class="rounded-full bg-primary/10 p-6 mb-6">
                    <LayoutGrid class="h-12 w-12 text-primary" />
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Data Kelas Belum Tersedia</h3>
                <p class="text-muted-foreground max-w-sm mb-8">
                    Anda perlu membuat data kelas terlebih dahulu sebelum dapat menambahkan atau mengelola data mahasiswa.
                </p>
                <Button as-child size="lg" class="shadow-lg hover:shadow-xl transition-all px-8">
                    <Link :href="route('v2.admin.data-kelas.index')">
                        <LayoutGrid class="mr-2 h-5 w-5" />
                        Kelola Data Kelas
                    </Link>
                </Button>
            </div>

            <!-- Classes Grid -->
            <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Card v-for="kelas in kelass" :key="kelas.id" class="group hover:shadow-md transition-all border-l-4 border-l-primary flex flex-col h-full">
                    <CardHeader class="pb-2">
                        <CardTitle class="text-xl font-bold flex items-center justify-between">
                            Kelas {{ kelas.nama_kelas }}
                            <div class="p-2 rounded-lg bg-primary/5 group-hover:bg-primary/10 transition-colors">
                                <Users class="h-5 w-5 text-primary" />
                            </div>
                        </CardTitle>
                        <CardDescription class="font-medium text-primary/80">
                            {{ kelas.prodi }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pb-4">
                        <div class="flex items-center text-sm text-muted-foreground mb-1">
                            <GraduationCap class="mr-2 h-4 w-4" />
                            Semester {{ kelas.semester }}
                        </div>
                        <div class="text-sm font-medium">
                            <span class="text-primary font-bold">{{ kelas.mahasiswa_count }}</span> Mahasiswa Terdaftar
                        </div>
                    </CardContent>
                    <div class="p-6 pt-0 mt-auto">
                        <Link 
                            :href="route('v2.admin.data-mahasiswa.show', kelas.id)"
                            class="flex items-center justify-center w-full px-4 py-3 bg-[#4B49AC] hover:bg-[#3f3da0] text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all"
                        >
                            <Users class="mr-2 h-4 w-4" />
                            Kelola Mahasiswa
                        </Link>
                    </div>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
