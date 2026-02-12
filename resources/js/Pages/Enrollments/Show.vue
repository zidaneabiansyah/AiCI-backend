<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";

const props = defineProps({
    enrollment: Object,
    courseClass: Object,
    schedule: Object,
    payment: Object,
});

const cancelForm = useForm({
    cancellation_reason: "",
});

const cancelEnrollment = () => {
    if (confirm("Apakah Anda yakin ingin membatalkan pendaftaran ini?")) {
        cancelForm.post(route('enrollments.cancel', props.enrollment.id));
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 'confirmed':
        case 'paid':
            return 'bg-green-100 text-green-600';
        case 'pending':
            return 'bg-yellow-100 text-yellow-600';
        case 'cancelled':
        case 'failed':
            return 'bg-red-100 text-red-600';
        default:
            return 'bg-gray-100 text-gray-600';
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <Head title="Detail Pendaftaran - AICI-UMG" />

    <MainLayout>
        <section class="pt-32 pb-20 bg-accent/10 min-h-screen">
            <div class="max-w-7xl mx-auto px-6 lg:px-12">
                <!-- Breadcrumbs -->
                <nav class="flex mb-8 text-xs font-black uppercase tracking-widest overflow-x-auto whitespace-nowrap pb-2">
                    <Link href="/dashboard" class="text-gray-400 hover:text-primary transition-colors">Dashboard</Link>
                    <span class="mx-3 text-gray-300">/</span>
                    <span class="text-primary">Enrollment #{{ enrollment.enrollment_number }}</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Enrollment Status Card -->
                        <div class="bg-white rounded-[3rem] p-8 md:p-12 shadow-sm border border-gray-100 overflow-hidden relative">
                            <div class="absolute top-0 right-0 p-8">
                                <span :class="getStatusColor(enrollment.status)" class="px-6 py-2 rounded-full text-xs font-black uppercase tracking-widest">
                                    {{ enrollment.status_label }}
                                </span>
                            </div>

                            <div class="mb-12">
                                <h4 class="text-xs font-black text-gray-300 uppercase tracking-[0.2em] mb-4">Nomor Pendaftaran</h4>
                                <h1 class="text-3xl md:text-5xl font-black text-primary uppercase">#{{ enrollment.enrollment_number }}</h1>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                                <div>
                                    <h3 class="text-lg font-black uppercase text-primary mb-6 border-b border-gray-100 pb-4">Data Siswa</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <span class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Nama Siswa</span>
                                            <span class="block font-bold text-gray-700">{{ enrollment.student_name }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Email & HP</span>
                                            <span class="block font-bold text-gray-700">{{ enrollment.student_email }}</span>
                                            <span class="block text-sm text-gray-500">{{ enrollment.student_phone }}</span>
                                        </div>
                                        <div class="flex gap-12">
                                            <div>
                                                <span class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Usia</span>
                                                <span class="block font-bold text-gray-700">{{ enrollment.student_age }} Tahun</span>
                                            </div>
                                            <div>
                                                <span class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Kelas</span>
                                                <span class="block font-bold text-gray-700">{{ enrollment.student_grade }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="text-lg font-black uppercase text-primary mb-6 border-b border-gray-100 pb-4">Detail Kursus</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <span class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Program & Kelas</span>
                                            <span class="block font-black text-secondary uppercase">{{ courseClass.program_name }}</span>
                                            <span class="block font-bold text-gray-700">{{ courseClass.name }}</span>
                                        </div>
                                        <div v-if="schedule">
                                            <span class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Jadwal & Lokasi</span>
                                            <span class="block font-bold text-gray-700">{{ schedule.batch_name }}</span>
                                            <span class="block text-sm text-gray-500">{{ schedule.day_of_week }}, {{ schedule.time }}</span>
                                            <span class="block text-[10px] font-black text-primary mt-1">{{ schedule.location }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="enrollment.special_requirements" class="mt-12 p-6 bg-accent/20 rounded-2xl border border-primary/5">
                                <h4 class="text-xs font-black text-primary uppercase tracking-widest mb-2">Kebutuhan Khusus</h4>
                                <p class="text-sm text-gray-600 leading-relaxed italic">{{ enrollment.special_requirements }}</p>
                            </div>
                        </div>

                        <!-- Payment Detail Card (If payment exists) -->
                        <div v-if="payment" class="bg-white rounded-[3rem] p-8 md:p-12 shadow-sm border border-gray-100">
                            <h3 class="text-xl font-black uppercase text-primary mb-10 flex items-center justify-between">
                                Informasi Pembayaran
                                <span :class="getStatusColor(payment.status)" class="text-xs px-4 py-1.5 rounded-xl font-black">
                                    {{ payment.status_label }}
                                </span>
                            </h3>

                            <div class="space-y-6 mb-12">
                                <div class="flex justify-between items-center text-sm py-4 border-b border-gray-50">
                                    <span class="text-gray-400 font-bold uppercase tracking-widest">Biaya Kursus</span>
                                    <span class="font-black text-primary">{{ courseClass.price_formatted }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm py-4 border-b border-gray-50">
                                    <span class="text-gray-400 font-bold uppercase tracking-widest">Biaya Administrasi</span>
                                    <span class="font-black text-primary">{{ formatCurrency(payment.admin_fee) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-lg pt-4">
                                    <span class="text-primary font-black uppercase tracking-widest">Total Bayar</span>
                                    <span class="font-black text-secondary">{{ payment.total_amount_formatted }}</span>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-6 mt-12">
                                <a 
                                    v-if="payment.status === 'pending' && payment.xendit_invoice_url"
                                    :href="payment.xendit_invoice_url"
                                    target="_blank"
                                    class="flex-1 bg-secondary text-white py-5 rounded-2xl font-black uppercase tracking-[0.2em] text-center hover:brightness-110 transition-all shadow-xl shadow-secondary/20"
                                >
                                    Bayar Sekarang
                                </a>
                                <a 
                                    v-if="payment.status === 'paid'"
                                    :href="route('payments.receipt', payment.id)"
                                    target="_blank"
                                    class="flex-1 bg-primary text-white py-5 rounded-2xl font-black uppercase tracking-[0.2em] text-center hover:brightness-110 transition-all shadow-xl shadow-primary/20 flex items-center justify-center"
                                >
                                    Download Kuitansi
                                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </a>
                                <div v-if="payment.status === 'pending'" class="text-center py-4 px-6 bg-accent/30 rounded-2xl border border-primary/10 sm:max-w-xs">
                                    <p class="text-[10px] font-bold text-primary uppercase tracking-widest">Batas Waktu Pembayaran</p>
                                    <p class="text-sm font-black text-primary">{{ payment.expired_at }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Stats/Actions -->
                    <div class="space-y-8">
                        <div class="bg-primary text-white rounded-[3rem] p-10 shadow-2xl shadow-primary/20 overflow-hidden relative">
                            <div class="absolute bottom-0 right-0 w-32 h-32 bg-white/5 rounded-full -mb-16 -mr-16"></div>
                            
                            <h3 class="text-lg font-black uppercase tracking-widest mb-10 relative">Status Terkini</h3>
                            
                            <div class="space-y-8 relative">
                                <div class="flex gap-6">
                                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                                        <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] font-black uppercase tracking-widest opacity-50 mb-1">Pendaftaran</span>
                                        <span class="block text-sm font-bold">Diterima pada {{ enrollment.enrolled_at }}</span>
                                    </div>
                                </div>

                                <div class="flex gap-6">
                                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                                        <svg v-if="payment?.status === 'paid'" class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <div v-else class="w-3 h-3 rounded-full bg-yellow-400 animate-pulse"></div>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] font-black uppercase tracking-widest opacity-50 mb-1">Pembayaran</span>
                                        <span class="block text-sm font-bold">{{ payment?.status_label || 'Menunggu Pembayaran' }}</span>
                                    </div>
                                </div>

                                <div class="flex gap-6">
                                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0 opacity-30">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="opacity-30">
                                        <span class="block text-[10px] font-black uppercase tracking-widest mb-1">Konfirmasi Kursus</span>
                                        <span class="block text-sm font-bold">Akan segera dikonfirmasi</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Danger Zone: Cancellation -->
                        <div v-if="['pending', 'confirmed'].includes(enrollment.status)" class="bg-red-50 rounded-[2.5rem] p-8 border border-red-100">
                            <h4 class="text-xs font-black uppercase tracking-widest text-red-500 mb-4">Ingin Membatalkan?</h4>
                            <p class="text-[10px] text-red-400 font-bold leading-relaxed mb-6">
                                Anda masih bisa membatalkan pendaftaran. Jika sudah membayar, dana akan dikonfirmasi lebih lanjut sesuai kebijakan refund.
                            </p>
                            <button 
                                @click="cancelEnrollment"
                                class="w-full py-4 rounded-xl text-xs font-black uppercase tracking-widest text-red-500 border-2 border-red-200 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all"
                            >
                                Batalkan Pendaftaran
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>

<style scoped>
.font-black {
    font-weight: 900;
}
</style>
