<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";

const props = defineProps({
    test: {
        type: Object,
        required: true,
    },
    existingAttempt: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    // Tambahkan field pre-assessment jika diperlukan di masa depan
});

const startTest = () => {
    form.post(route('placement-test.start', props.test.id));
};
</script>

<template>
    <Head :title="`${test.title} - AICI-UMG`" />

    <MainLayout>
        <section class="pt-32 pb-20 bg-accent/20">
            <div class="max-w-4xl mx-auto px-6 lg:px-12">
                <!-- Breadcrumbs -->
                <nav class="flex mb-8 text-sm font-bold uppercase tracking-widest overflow-x-auto whitespace-nowrap pb-2">
                    <Link href="/" class="text-gray-400 hover:text-primary transition-colors">Home</Link>
                    <span class="mx-3 text-gray-300">/</span>
                    <Link :href="route('placement-test.index')" class="text-gray-400 hover:text-primary transition-colors">Placement Test</Link>
                    <span class="mx-3 text-gray-300">/</span>
                    <span class="text-primary">{{ test.title }}</span>
                </nav>

                <div class="bg-white rounded-[3rem] p-8 md:p-12 shadow-sm border border-gray-100">
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-8 mb-12">
                        <div class="max-w-2xl">
                            <h1 class="text-3xl md:text-4xl font-black uppercase text-primary mb-6 leading-tight">
                                {{ test.title }}
                            </h1>
                            <p class="text-lg opacity-70 leading-relaxed italic">
                                {{ test.description }}
                            </p>
                        </div>
                        <div class="bg-secondary/10 p-6 rounded-3xl text-center min-w-[160px]">
                            <span class="block text-4xl mb-2">⏱️</span>
                            <span class="block text-2xl font-black text-secondary">{{ test.duration_minutes }}</span>
                            <span class="text-xs font-black uppercase tracking-widest text-secondary opacity-70">Menit Durasi</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-12">
                        <div>
                            <h3 class="text-xl font-black uppercase text-primary mb-6 flex items-center">
                                <span class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center mr-3 text-sm">1</span>
                                Instruksi Tes
                            </h3>
                            <div class="prose prose-sm text-gray-600 leading-relaxed bg-accent/30 p-6 rounded-3xl border border-primary/5">
                                <p v-if="test.instructions">{{ test.instructions }}</p>
                                <p v-else>
                                    Silakan jawab semua pertanyaan dengan jujur. Anda akan memiliki waktu terbatas untuk mengerjakan tes ini. Pastikan koneksi internet Anda stabil sebelum memulai.
                                </p>
                                <ul class="mt-4 space-y-2 list-disc list-inside">
                                    <li>Durasi pengerjaan: <strong>{{ test.duration_minutes }} menit</strong></li>
                                    <li>Total pertanyaan: <strong>{{ test.total_questions }} soal</strong></li>
                                    <li>Format: Pilihan Ganda</li>
                                </ul>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xl font-black uppercase text-primary mb-6 flex items-center">
                                <span class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center mr-3 text-sm">2</span>
                                Ketentuan Retake
                            </h3>
                            <div class="bg-accent/30 p-6 rounded-3xl border border-primary/5">
                                <div v-if="test.allow_retake" class="space-y-4">
                                    <div class="flex items-center text-sm font-bold text-gray-700">
                                        <div class="w-2 h-2 rounded-full bg-green-500 mr-3"></div>
                                        Retake diizinkan
                                    </div>
                                    <p class="text-sm text-gray-500 leading-relaxed italic">
                                        Jika Anda belum puas dengan hasil tes, Anda dapat mengulanginya setelah periode cooldown selama <strong>{{ test.retake_cooldown_days }} hari</strong>.
                                    </p>
                                </div>
                                <div v-else class="flex items-center text-sm font-bold text-gray-700">
                                    <div class="w-2 h-2 rounded-full bg-red-500 mr-3"></div>
                                    Hanya diizinkan sekali pengerjaan
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="existingAttempt" class="bg-primary/5 p-8 rounded-[2.5rem] border border-primary/10 mb-12">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div>
                                <h4 class="text-lg font-black text-primary mb-1 uppercase tracking-wider">Anda Sudah Menyelesaikan Tes Ini</h4>
                                <p class="text-sm opacity-70">Hasil terakhir Anda: <span class="font-bold text-primary">{{ existingAttempt.level_result }} (Skor: {{ existingAttempt.score }})</span></p>
                            </div>
                            <Link 
                                :href="route('placement-test.result', existingAttempt.id)"
                                class="bg-primary text-white px-8 py-3 rounded-xl font-black uppercase text-sm tracking-widest hover:brightness-110 transition-all shadow-lg shadow-primary/20"
                            >
                                Lihat Hasil
                            </Link>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                        <button 
                            @click="startTest"
                            :disabled="form.processing"
                            class="w-full sm:w-auto bg-primary text-white px-12 py-5 rounded-2xl font-black uppercase tracking-[0.2em] transition-all hover:bg-primary/90 hover:scale-[1.02] active:scale-95 shadow-2xl shadow-primary/30 disabled:opacity-50 flex items-center justify-center"
                        >
                            <span v-if="form.processing">Mempersiapkan Test...</span>
                            <span v-else>Mulai Sekarang</span>
                            <svg v-if="!form.processing" class="w-6 h-6 ml-3 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                        <Link 
                            :href="route('placement-test.index')"
                            class="text-sm font-black uppercase tracking-widest text-primary/50 hover:text-primary transition-colors"
                        >
                            Batal & Kembali
                        </Link>
                    </div>
                </div>

                <div class="mt-12 text-center">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-[0.2em]">
                        &copy; Powered by AICI AI Assessment Engine
                    </p>
                </div>
            </div>
        </section>
    </MainLayout>
</template>
