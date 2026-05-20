<script setup>
import { ref, watch, onMounted } from 'vue'
import { Head, router, usePage, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

// UI Components
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog'
import { Eye, Loader2, CheckCircle2 } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
    krss: Array,
    currentTab: String,
})

const page = usePage()

const isDetailModalOpen = ref(false)
const selectedKrs = ref(null)
const selectedMatkuls = ref([])
const isLoadingDetail = ref(false)
const isApproving = ref(false)

const handleTabChange = (val) => {
    router.get(
        route('v2.dosen.krs.index'),
        { tab: val },
        { preserveState: true, replace: true }
    )
}

const showDetail = async (krs) => {
    selectedKrs.value = krs
    selectedMatkuls.value = []
    isDetailModalOpen.value = true
    isLoadingDetail.value = true

    try {
        const response = await axios.get(route('v2.dosen.krs.detail', { id: krs.id }))
        selectedMatkuls.value = response.data.matkuls
    } catch (error) {
        alert('Terjadi kesalahan saat mengambil detail KRS.')
        isDetailModalOpen.value = false
    } finally {
        isLoadingDetail.value = false
    }
}

const form = useForm({})

const approveKrs = () => {
    if (!selectedKrs.value) return

    form.put(route('v2.dosen.krs.approve', { id: selectedKrs.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            isDetailModalOpen.value = false
        },
        onError: () => {
            alert('Terjadi kesalahan saat memvalidasi KRS.')
        }
    })
}
</script>

<template>
    <Head title="Validasi KRS Mahasiswa" />

    <AdminLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#1F1F1F]">Validasi KRS Mahasiswa</h1>
                <p class="text-sm text-gray-500">
                    Kelola dan verifikasi pengajuan Kartu Rencana Studi mahasiswa bimbingan Anda.
                </p>
            </div>

            <Tabs :default-value="currentTab" @update:model-value="handleTabChange" class="w-full">
                <TabsList class="grid w-full grid-cols-2 md:w-[400px]">
                    <TabsTrigger value="diajukan">Menunggu Validasi</TabsTrigger>
                    <TabsTrigger value="disetujui">Riwayat Disetujui</TabsTrigger>
                </TabsList>
                
                <TabsContent value="diajukan" class="mt-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Daftar Pengajuan KRS</CardTitle>
                            <CardDescription>Mahasiswa yang sedang menunggu persetujuan KRS dari Anda.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>NIM</TableHead>
                                            <TableHead>Nama Mahasiswa</TableHead>
                                            <TableHead>Kelas/Prodi</TableHead>
                                            <TableHead>Tanggal Pengajuan</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead class="text-right">Aksi</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="krs in krss" :key="krs.id">
                                            <TableCell class="font-medium">{{ krs.nim }}</TableCell>
                                            <TableCell>{{ krs.nama_mahasiswa }}</TableCell>
                                            <TableCell>
                                                <div class="flex flex-col text-sm">
                                                    <span>{{ krs.kelas }}</span>
                                                    <span class="text-gray-500">{{ krs.prodi }}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>{{ krs.created_at }}</TableCell>
                                            <TableCell>
                                                <Badge variant="secondary" class="bg-yellow-100 text-yellow-800 border-yellow-200">Menunggu</Badge>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <Button size="sm" @click="showDetail(krs)" class="bg-[#4B49AC] hover:bg-[#3A3888] text-white">
                                                    <Eye class="w-4 h-4 mr-2" />
                                                    Tinjau
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                        <TableRow v-if="krss.length === 0">
                                            <TableCell colspan="6" class="h-24 text-center">
                                                Tidak ada data pengajuan KRS.
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
                
                <TabsContent value="disetujui" class="mt-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Riwayat KRS Disetujui</CardTitle>
                            <CardDescription>Daftar KRS mahasiswa yang telah berhasil Anda validasi.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>NIM</TableHead>
                                            <TableHead>Nama Mahasiswa</TableHead>
                                            <TableHead>Kelas/Prodi</TableHead>
                                            <TableHead>Tanggal Persetujuan</TableHead>
                                            <TableHead>Status</TableHead>
                                            <TableHead class="text-right">Aksi</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="krs in krss" :key="krs.id">
                                            <TableCell class="font-medium">{{ krs.nim }}</TableCell>
                                            <TableCell>{{ krs.nama_mahasiswa }}</TableCell>
                                            <TableCell>
                                                <div class="flex flex-col text-sm">
                                                    <span>{{ krs.kelas }}</span>
                                                    <span class="text-gray-500">{{ krs.prodi }}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>{{ krs.created_at }}</TableCell>
                                            <TableCell>
                                                <Badge variant="secondary" class="bg-green-100 text-green-800 border-green-200">Disetujui</Badge>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <Button size="sm" variant="outline" @click="showDetail(krs)">
                                                    <Eye class="w-4 h-4 mr-2" />
                                                    Detail
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                        <TableRow v-if="krss.length === 0">
                                            <TableCell colspan="6" class="h-24 text-center">
                                                Tidak ada riwayat KRS disetujui.
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>

        <!-- Detail Modal -->
        <Dialog :open="isDetailModalOpen" @update:open="isDetailModalOpen = $event">
            <DialogContent class="sm:max-w-[700px] p-0 overflow-hidden">
                <div class="bg-[#4B49AC] px-6 py-4 flex items-center justify-between">
                    <DialogTitle class="text-white text-lg">Detail Kartu Rencana Studi</DialogTitle>
                </div>
                
                <div class="p-6">
                    <div v-if="selectedKrs" class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Nama Mahasiswa</p>
                            <p class="text-base font-semibold">{{ selectedKrs.nama_mahasiswa }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">NIM</p>
                            <p class="text-base font-semibold">{{ selectedKrs.nim }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Program Studi</p>
                            <p class="text-base font-semibold">{{ selectedKrs.prodi }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Kelas</p>
                            <p class="text-base font-semibold">{{ selectedKrs.kelas }}</p>
                        </div>
                    </div>

                    <div class="rounded-md border h-[300px] overflow-y-auto">
                        <div v-if="isLoadingDetail" class="flex flex-col items-center justify-center h-full text-gray-500">
                            <Loader2 class="w-8 h-8 animate-spin mb-2" />
                            <p>Memuat daftar mata kuliah...</p>
                        </div>
                        <Table v-else>
                            <TableHeader class="sticky top-0 bg-gray-50">
                                <TableRow>
                                    <TableHead class="w-[100px]">Kode</TableHead>
                                    <TableHead>Mata Kuliah</TableHead>
                                    <TableHead class="text-center">SKS</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="matkul in selectedMatkuls" :key="matkul.id">
                                    <TableCell class="font-medium">{{ matkul.kode_matkul }}</TableCell>
                                    <TableCell>{{ matkul.nama_matkul }}</TableCell>
                                    <TableCell class="text-center">{{ matkul.sks }}</TableCell>
                                </TableRow>
                                <TableRow v-if="selectedMatkuls.length === 0">
                                    <TableCell colspan="3" class="text-center h-16">
                                        Tidak ada mata kuliah yang ditemukan.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>

                <DialogFooter class="px-6 py-4 border-t bg-gray-50">
                    <Button variant="outline" @click="isDetailModalOpen = false" :disabled="form.processing">
                        Tutup
                    </Button>
                    <Button 
                        v-if="selectedKrs && selectedKrs.status_krs == 0" 
                        class="bg-[#4B49AC] hover:bg-[#3A3888] text-white" 
                        @click="approveKrs" 
                        :disabled="form.processing || isLoadingDetail"
                    >
                        <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                        <CheckCircle2 v-else class="w-4 h-4 mr-2" />
                        Validasi KRS
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
