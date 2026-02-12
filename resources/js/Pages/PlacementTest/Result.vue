<script setup>
import { Head, Link } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";

const props = defineProps({
    attempt: Object,
    result: Object,
    recommendations: Array,
    recommendedClasses: Array,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
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
    <Head title="Hasil Placement Test - AICI-UMG" />

    <MainLayout>
        <section class="pt-32 pb-20 bg-accent/10 min-h-screen">
            <div class="max-w-5xl mx-auto px-6 lg:px-12">
                <!-- Success Header -->
                <div class="text-center mb-16 relative">
                    <div class="absolute inset-0 -top-20 flex justify-center opacity-10 pointer-events-none">
                        <span class="text-[15rem] leading-none">🎉</span>
                    </div>
                    <div class="inline-block bg-secondary/10 px-6 py-2 rounded-full text-secondary text-sm font-black uppercase tracking-widest mb-6">
                        Test Selesai!
                    </div>
                    <h1 class="text-4xl md:text-5xl font-black uppercase text-primary mb-6 leading-tight">
                        Selamat, Anda Telah <br /><span class="text-secondary italic">Berhasil</span> Menyelesaikan Tes
                    </h1>
                </div>

                <!-- Main Result Card -->
                <div class="bg-white rounded-[3.5rem] p-8 md:p-16 shadow-sm border border-gray-100 mb-12 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full -mr-32 -mt-32 blur-3xl"></div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 relative">
                        <!-- Score Visualization -->
                        <div class="flex flex-col items-center justify-center border-b lg:border-b-0 lg:border-r border-gray-100 pb-12 lg:pb-0 lg:pr-16">
                            <div class="relative w-48 h-48 md:w-64 md:h-64 mb-8">
                                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                                    <!-- Background Circle -->
                                    <circle class="text-gray-100" stroke-width="8" stroke="currentColor" fill="transparent" r="40" cx="50" cy="50" />
                                    <!-- Progress Circle -->
                                    <circle 
                                        class="text-primary transition-all duration-1000 ease-out" 
                                        stroke-width="8" 
                                        :stroke-dasharray="251.2" 
                                        :stroke-dashoffset="251.2 - (251.2 * attempt.score / 100)" 
                                        stroke-linecap="round" 
                                        stroke="currentColor" 
                                        fill="transparent" 
                                        r="40" cx="50" cy="50" 
                                    />
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-5xl md:text-7xl font-black text-primary">{{ Math.round(attempt.score) }}</span>
                                    <span class="text-xs font-black uppercase tracking-widest text-gray-400">Total Skor</span>
                                </div>
                            </div>
                            
                            <div class="inline-flex items-center px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-xl mb-4" :class="getLevelColor(attempt.level_result)">
                                Level: {{ attempt.level_result }}
                            </div>
                            <p class="text-sm text-gray-400 font-bold uppercase tracking-widest">Pencapaian Anda</p>
                        </div>

                        <!-- Stats & Recommendations Summary -->
                        <div>
                            <h3 class="text-2xl font-black uppercase text-primary mb-8 leading-tight">
                                Ringkasan <span class="text-secondary italic">Performa</span>
                            </h3>
                            
                            <div class="space-y-6 mb-10">
                                <div v-for="(score, category) in result.category_scores" :key="category">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs font-black uppercase tracking-widest text-gray-500">{{ category }}</span>
                                        <span class="text-xs font-black text-primary">{{ score }}%</span>
                                    </div>
                                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-primary rounded-full transition-all duration-1000" :style="{ width: `${score}%` }"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-accent/30 p-8 rounded-3xl border border-primary/5">
                                <h4 class="text-sm font-black uppercase tracking-widest text-primary mb-4 flex items-center">
                                    <span class="text-xl mr-3">💡</span> Kesimpulan
                                </h4>
                                <p class="text-sm text-gray-600 leading-relaxed italic">
                                    {{ result.performance_summary || 'Anda menunjukkan pemahaman yang baik pada sebagian besar materi. Kami merekomendasikan kelas-kelas berikut untuk membantu Anda mencapai tingkat selanjutnya.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Bar -->
                    <div class="mt-16 pt-12 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-8">
                        <div class="flex items-center gap-4">
                            <div class="bg-gray-100 p-4 rounded-2xl">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-black text-primary uppercase tracking-tight">Sertifikat Hasil Tes</h4>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Versi PDF tersedia untuk diunduh</p>
                            </div>
                        </div>
                        <a 
                            :href="route('placement-test.download-result', attempt.id)"
                            target="_blank"
                            class="w-full sm:w-auto bg-primary text-white px-10 py-4 rounded-xl font-black uppercase text-sm tracking-widest hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all shadow-xl shadow-primary/20 flex items-center justify-center"
                        >
                            Download PDF Result
                            <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Recommendations Grid -->
                <div class="mb-20">
                    <h2 class="text-3xl font-black uppercase text-primary mb-10 leading-tight">
                        Rekomendasi <span class="text-secondary italic">Kelas</span> Untuk Anda
                    </h2>

                    <div v-if="recommendedClasses.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div 
                            v-for="cls in recommendedClasses" 
                            :key="cls.id"
                            class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-2 transition-all group flex flex-col h-full"
                        >
                            <div class="flex items-center justify-between mb-6">
                                <span class="px-4 py-1.5 bg-primary/10 text-primary text-[10px] font-black rounded-full uppercase tracking-widest">
                                    {{ cls.program_name }}
                                </span>
                                <span class="text-secondary font-black text-lg">
                                    {{ formatCurrency(cls.price) }}
                                </span>
                            </div>

                            <h3 class="text-xl font-black text-primary mb-4 leading-tight group-hover:text-secondary transition-colors">
                                {{ cls.name }}
                            </h3>

                            <div class="flex items-center text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 gap-6">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ cls.duration_hours }} Jam
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    {{ cls.level }}
                                </div>
                            </div>

                            <p class="text-gray-500 text-sm leading-relaxed mb-8 flex-grow line-clamp-3">
                                {{ cls.description }}
                            </p>

                            <Link 
                                :href="route('enrollments.create', cls.id)"
                                class="w-full text-center bg-accent text-primary py-4 rounded-xl font-black uppercase text-xs tracking-widest hover:bg-primary hover:text-white transition-all shadow-md group-hover:scale-[1.02]"
                            >
                                Daftar Sekarang
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Footer Navigation -->
                <div class="text-center">
                    <Link 
                        href="/dashboard"
                        class="text-sm font-black uppercase tracking-widest text-primary/40 hover:text-primary transition-colors"
                    >
                        Ke Dashboard Saya
                    </Link>
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
