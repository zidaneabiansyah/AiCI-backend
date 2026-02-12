<script setup>
import { Head, Link } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";

const props = defineProps({
    payment: Object,
    enrollment: Object,
    courseClass: Object,
    schedule: Object,
});

const getStatusColor = (status) => {
    switch (status) {
        case 'paid': return 'bg-green-100 text-green-600';
        case 'pending': return 'bg-yellow-100 text-yellow-600';
        case 'expired':
        case 'failed':
        case 'refunded': return 'bg-red-100 text-red-600';
        default: return 'bg-gray-100 text-gray-600';
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
    <Head :title="`Invoice #${payment.invoice_number} - AICI-UMG`" />

    <MainLayout>
        <section class="pt-32 pb-20 bg-accent/10 min-h-screen">
            <div class="max-w-4xl mx-auto px-6 lg:px-12">
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                    <div>
                        <nav class="flex mb-4 text-[10px] font-black uppercase tracking-[0.2em]">
                            <Link href="/dashboard" class="text-gray-400 hover:text-primary transition-colors">Dashboard</Link>
                            <span class="mx-2 text-gray-300">/</span>
                            <Link :href="route('enrollments.show', enrollment.id)" class="text-gray-400 hover:text-primary transition-colors">Enrollment</Link>
                            <span class="mx-2 text-gray-300">/</span>
                            <span class="text-primary">Invoice</span>
                        </nav>
                        <h1 class="text-3xl font-black uppercase text-primary">Invoice <span class="text-gray-300 text-2xl ml-2">#{{ payment.invoice_number }}</span></h1>
                    </div>
                    <div :class="getStatusColor(payment.status)" class="px-8 py-3 rounded-2xl text-sm font-black uppercase tracking-widest text-center">
                        {{ payment.status_label }}
                    </div>
                </div>

                <!-- Invoice Content -->
                <div class="bg-white rounded-[3.5rem] p-8 md:p-16 shadow-2xl shadow-primary/5 border border-gray-100 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-12 opacity-5 pointer-events-none">
                        <svg class="w-48 h-48 text-primary" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                        </svg>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-16 relative">
                        <div>
                            <h3 class="text-xs font-black uppercase tracking-widest text-gray-300 mb-4 text-primary">Penagihan Ke:</h3>
                            <div class="space-y-1">
                                <p class="text-lg font-black text-primary uppercase">{{ enrollment.student_name }}</p>
                                <p class="text-sm font-bold text-gray-500">{{ enrollment.student_email }}</p>
                            </div>
                        </div>
                        <div class="md:text-right">
                            <h3 class="text-xs font-black uppercase tracking-widest text-gray-300 mb-4 text-primary">Tanggal Invoice:</h3>
                            <div class="space-y-1">
                                <p class="text-sm font-black text-primary">{{ payment.created_at_formatted }}</p>
                                <p v-if="payment.paid_at" class="text-[10px] font-black uppercase text-green-500">Lunas pada {{ payment.paid_at_formatted }}</p>
                                <p v-else-if="payment.expired_at" class="text-[10px] font-black uppercase text-red-400 italic">Berlaku sampai {{ payment.expired_at_formatted }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="mb-16 relative">
                        <div class="bg-gray-50/50 rounded-3xl overflow-hidden border border-gray-100">
                            <table class="w-full text-left">
                                <thead class="border-b border-gray-100">
                                    <tr>
                                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Deskripsi Kursus</th>
                                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <tr>
                                        <td class="px-8 py-8">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-black text-secondary uppercase tracking-widest mb-1">{{ courseClass.program_name }}</span>
                                                <span class="text-lg font-black text-primary uppercase leading-tight">{{ courseClass.name }}</span>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Kode: {{ courseClass.code }}</span>
                                                <div v-if="schedule" class="mt-4 flex items-center gap-4">
                                                    <span class="px-3 py-1 bg-primary/5 text-primary text-[10px] font-black rounded-lg">{{ schedule.batch_name }}</span>
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ schedule.start_date }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-8 text-right align-top">
                                            <span class="text-lg font-black text-primary">{{ formatCurrency(payment.amount) }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="flex flex-col md:flex-row justify-between gap-12 pt-8 relative">
                        <div class="max-w-xs">
                            <h4 class="text-xs font-black uppercase tracking-widest text-primary mb-4">Metode Pembayaran</h4>
                            <div class="bg-accent/30 px-6 py-4 rounded-2xl border border-primary/5">
                                <p class="text-sm font-black text-primary uppercase">{{ payment.payment_method || 'Virtual Account / E-Wallet / QRIS' }}</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Dikelola oleh Xendit</p>
                            </div>
                        </div>
                        
                        <div class="w-full md:w-80 space-y-4">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-400 font-bold uppercase tracking-widest">Subtotal</span>
                                <span class="font-black text-primary">{{ formatCurrency(payment.amount) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-400 font-bold uppercase tracking-widest">Biaya Admin</span>
                                <span class="font-black text-primary">{{ formatCurrency(payment.admin_fee) }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-6 border-t border-gray-100">
                                <span class="text-primary font-black uppercase tracking-widest">Total Bayar</span>
                                <span class="text-2xl font-black text-secondary">{{ payment.total_amount_formatted }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- CTA Bar -->
                    <div class="mt-16 pt-12 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-8 relative">
                        <div class="flex items-center gap-4 text-left">
                            <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-primary">Status Pembayaran Aman</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Transaksi dienkripsi oleh Xendit</p>
                            </div>
                        </div>

                        <div class="flex gap-4 w-full sm:w-auto">
                            <a 
                                v-if="payment.status === 'pending' && payment.xendit_invoice_url"
                                :href="payment.xendit_invoice_url"
                                target="_blank"
                                class="flex-1 sm:flex-none bg-secondary text-white px-12 py-5 rounded-2xl font-black uppercase tracking-[0.2em] text-sm hover:brightness-110 transition-all shadow-xl shadow-secondary/20 text-center"
                            >
                                Bayar Sekarang
                            </a>
                            <a 
                                v-if="payment.status === 'paid'"
                                :href="route('payments.receipt', payment.id)"
                                target="_blank"
                                class="flex-1 sm:flex-none bg-primary text-white px-12 py-5 rounded-2xl font-black uppercase tracking-[0.2em] text-sm hover:brightness-110 transition-all shadow-xl shadow-primary/20 flex items-center justify-center"
                            >
                                Simpan Kuitansi
                                <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </a>
                            <Link 
                                v-if="payment.status === 'pending'"
                                :href="route('payments.check', payment.id)"
                                class="flex-1 sm:flex-none bg-gray-100 text-gray-500 px-8 py-5 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-gray-200 transition-all text-center flex items-center justify-center"
                            >
                                Cek Status
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Footer Info -->
                <div class="mt-12 text-center text-[10px] font-bold text-gray-400 uppercase tracking-[0.3em] space-y-2">
                    <p>&copy; 2026 Artificial Intelligence Center Indonesia</p>
                    <p>Pembatalan dan perubahan jadwal dapat dilakukan melalui dashboard pendaftaran</p>
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
