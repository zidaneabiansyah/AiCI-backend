<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    testAttempts: Array,
    enrollments: Array,
});

const getStatusColor = (status) => {
    switch (status) {
        case 'confirmed':
        case 'paid':
        case 'completed':
            return 'bg-green-100 text-green-600';
        case 'pending':
            return 'bg-yellow-100 text-yellow-600';
        case 'cancelled':
        case 'failed':
        case 'expired':
            return 'bg-red-100 text-red-600';
        default:
            return 'bg-gray-100 text-gray-600';
    }
};

const getLevelColor = (level) => {
    switch (level?.toLowerCase()) {
        case 'advanced': return 'text-purple-500 bg-purple-50';
        case 'intermediate': return 'text-blue-500 bg-blue-50';
        case 'elementary': return 'text-green-500 bg-green-50';
        default: return 'text-secondary bg-secondary/10';
    }
};
</script>

<template>
    <Head title="My Dashboard - AICI-UMG" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h2 class="text-2xl font-black uppercase text-primary leading-tight dark:text-white">
                        Welcome back, <span class="text-secondary italic">{{ $page.props.auth.user.name }}</span>!
                    </h2>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                        Artificial Intelligence Center Indonesia Learner Portal
                    </p>
                </div>
                <div class="flex gap-4">
                    <Link 
                        :href="route('placement-test.index')"
                        class="bg-primary text-white px-6 py-2.5 rounded-xl font-black uppercase text-xs tracking-widest hover:brightness-110 transition-all shadow-lg shadow-primary/20"
                    >
                        New Test
                    </Link>
                    <Link 
                        href="/program"
                        class="bg-secondary text-white px-6 py-2.5 rounded-xl font-black uppercase text-xs tracking-widest hover:brightness-110 transition-all shadow-lg shadow-secondary/20"
                    >
                        Browse Course
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                
                <!-- Section: Active Enrollments -->
                <div>
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-lg font-black uppercase text-primary tracking-widest border-l-4 border-secondary pl-4">
                            Pendaftaran Kursus Saya
                        </h3>
                        <Link v-if="enrollments.length > 5" :href="route('enrollments.index')" class="text-xs font-black uppercase text-gray-400 hover:text-primary transition-colors">
                            Lihat Semua
                        </Link>
                    </div>

                    <div v-if="enrollments.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div 
                            v-for="enrollment in enrollments" 
                            :key="enrollment.id"
                            class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 hover:shadow-xl transition-all group relative overflow-hidden"
                        >
                            <div class="absolute top-0 right-0 p-6">
                                <span :class="getStatusColor(enrollment.status)" class="px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest">
                                    {{ enrollment.status_label }}
                                </span>
                            </div>

                            <div class="mb-8">
                                <span class="block text-[10px] font-black text-secondary uppercase tracking-widest mb-1">{{ enrollment.program_name }}</span>
                                <h4 class="text-xl font-black text-primary uppercase leading-tight group-hover:text-secondary transition-colors">{{ enrollment.class_name }}</h4>
                                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-2">#{{ enrollment.enrollment_number }}</span>
                            </div>

                            <div class="space-y-3 mb-10">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-gray-400 font-bold uppercase tracking-widest">Daftar Pada</span>
                                    <span class="font-black text-primary">{{ enrollment.enrolled_at }}</span>
                                </div>
                                <div v-if="enrollment.payment" class="flex justify-between items-center text-xs">
                                    <span class="text-gray-400 font-bold uppercase tracking-widest">Total Bayar</span>
                                    <span class="font-black text-primary">{{ enrollment.payment.total_amount }}</span>
                                </div>
                            </div>

                            <Link 
                                :href="route('enrollments.show', enrollment.id)"
                                class="block w-full text-center bg-accent text-primary py-4 rounded-xl font-black uppercase text-xs tracking-widest hover:bg-primary hover:text-white transition-all shadow-md"
                            >
                                Detail & Pembayaran
                            </Link>
                        </div>
                    </div>
                    <div v-else class="bg-white rounded-[2.5rem] p-12 text-center border-2 border-dashed border-gray-100">
                        <span class="text-4xl mb-4 block">📚</span>
                        <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">
                            Belum ada pendaftaran kursus aktif.
                        </p>
                    </div>
                </div>

                <!-- Section: Placement Test History -->
                <div>
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-lg font-black uppercase text-primary tracking-widest border-l-4 border-primary pl-4">
                            Riwayat Placement Test
                        </h3>
                    </div>

                    <div v-if="testAttempts.length > 0" class="bg-white rounded-[3rem] shadow-sm border border-gray-100 overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-accent/30">
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-primary">Nama Test</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-primary">Status</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-primary">Skor & Level</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-primary">Tanggal</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-primary text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="attempt in testAttempts" :key="attempt.id" class="hover:bg-accent/10 transition-colors">
                                    <td class="px-8 py-6">
                                        <span class="text-sm font-black text-primary uppercase">{{ attempt.test_title }}</span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <span :class="getStatusColor(attempt.status)" class="px-3 py-1 rounded-lg text-xs font-black uppercase">
                                            {{ attempt.status }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div v-if="attempt.status === 'completed'" class="flex items-center gap-3">
                                            <span class="text-sm font-black text-primary">{{ Math.round(attempt.score) }}</span>
                                            <span :class="getLevelColor(attempt.level_result)" class="px-2 py-0.5 rounded text-[10px] font-black uppercase">
                                                {{ attempt.level_result }}
                                            </span>
                                        </div>
                                        <span v-else class="text-xs text-gray-400">-</span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ attempt.completed_at || '-' }}</span>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <Link 
                                            v-if="attempt.status === 'completed'"
                                            :href="route('placement-test.result', attempt.id)"
                                            class="inline-flex items-center text-[10px] font-black uppercase tracking-widest text-secondary hover:text-primary transition-colors"
                                        >
                                            View Result
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="bg-white rounded-[2.5rem] p-12 text-center border-2 border-dashed border-gray-100">
                        <span class="text-4xl mb-4 block">📝</span>
                        <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">
                            Anda belum pernah mengikuti Placement Test.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.font-black {
    font-weight: 900;
}
</style>
