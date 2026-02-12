<script setup>
import { Head, Link } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";

defineProps({
    tests: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <Head title="Placement Test - AICI-UMG" />

    <MainLayout>
        <section class="pt-32 pb-20 bg-accent/20">
            <div class="max-w-7xl mx-auto px-6 lg:px-12">
                <div class="max-w-2xl mb-12">
                    <h1
                        class="text-4xl font-black uppercase text-primary mb-4 leading-tight"
                    >
                        Temukan Kursus yang
                        <span class="text-secondary italic">Tepat</span> untuk
                        Anda
                    </h1>
                    <p class="text-lg opacity-70 leading-relaxed">
                        Ikuti Placement Test kami untuk mengetahui tingkat
                        kemampuan Anda dan mendapatkan rekomendasi kelas yang
                        paling sesuai dengan kebutuhan belajar Anda.
                    </p>
                </div>

                <div
                    v-if="tests.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
                >
                    <div
                        v-for="test in tests"
                        :key="test.id"
                        class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-2 transition-all group flex flex-col h-full"
                    >
                        <div class="flex items-center justify-between mb-6">
                            <span
                                class="px-4 py-1.5 bg-primary/10 text-primary text-xs font-black rounded-full uppercase tracking-widest"
                            >
                                {{ test.education_level }}
                            </span>
                            <div
                                class="text-gray-400 group-hover:text-secondary transition-colors"
                            >
                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                    />
                                </svg>
                            </div>
                        </div>

                        <h3
                            class="text-2xl font-black text-primary mb-4 leading-tight group-hover:text-primary/80 transition-colors"
                        >
                            {{ test.title }}
                        </h3>

                        <p
                            class="text-gray-600 text-sm leading-relaxed mb-6 flex-grow"
                        >
                            {{ test.description }}
                        </p>

                        <div class="space-y-4 mb-8">
                            <div
                                class="flex items-center text-sm font-bold text-gray-500"
                            >
                                <svg
                                    class="w-5 h-5 mr-3 text-secondary"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                {{ test.duration_minutes }} Menit pengerjaan
                            </div>
                            <div
                                class="flex items-center text-sm font-bold text-gray-500"
                            >
                                <svg
                                    class="w-5 h-5 mr-3 text-secondary"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                {{ test.total_questions }} Pertanyaan pilihan
                                ganda
                            </div>
                        </div>

                        <Link
                            :href="route('placement-test.show', test.slug)"
                            class="block w-full text-center bg-primary text-white py-4 rounded-xl font-black uppercase tracking-widest hover:bg-primary/90 hover:scale-[1.02] active:scale-95 transition-all shadow-lg shadow-primary/20"
                        >
                            Ikuti Tes
                        </Link>
                    </div>
                </div>

                <div
                    v-else
                    class="text-center py-20 bg-white rounded-3xl shadow-sm border border-gray-100"
                >
                    <div class="text-6xl mb-6">🔍</div>
                    <h3 class="text-2xl font-black text-primary mb-2">
                        Pendaftaran Belum Dibuka
                    </h3>
                    <p class="text-gray-500">
                        Saat ini belum ada Placement Test yang aktif. Silakan
                        kembali lagi nanti.
                    </p>
                </div>
            </div>
        </section>
    </MainLayout>
</template>
