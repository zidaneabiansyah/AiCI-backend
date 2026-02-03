<script setup>
import { Head, Link } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";

const programs = [
    {
        title: "SD/MI",
        levels: [
            "AI Fantasy Zoo (Kelas 1-3 SD)",
            "Ukit Advanced (Sudah Lulus AI Fantasy Zoo)",
            "AI Smart Life (Kelas 4-5 SD)",
            "Advanced Smart Life (Sudahan Lulus AI Smart Life)",
            "AI Super Assistant (Kelas 6 SD)",
        ],
    },
    {
        title: "SMP/MTS",
        levels: ["AI Transformer Workshop", "AI Magic World"],
    },
    {
        title: "SMA/MA",
        levels: ["AI Super Engineer", "AI Future Town", "AI Super Designer"],
    },
];
</script>

<template>
    <Head title="Programs - AICI-UMG" />

    <MainLayout>
        <section class="pt-40 pb-20 bg-primary text-white">
            <div class="max-w-7xl mx-auto px-4 md:px-12">
                <h1 class="text-4xl md:text-6xl font-bold uppercase mb-6">
                    Our Educational <span class="text-secondary">Programs</span>
                </h1>
                <p class="text-lg opacity-80 max-w-2xl">
                    Kurikulum terstruktur untuk mempersiapkan talenta masa depan
                    Indonesia di bidang AI dan Robotika.
                </p>
            </div>
        </section>

        <section class="py-24 px-4 md:px-12">
            <div class="max-w-7xl mx-auto space-y-24">
                <div
                    v-for="(group, idx) in programs"
                    :key="idx"
                    class="space-y-12"
                >
                    <div class="flex items-center space-x-6">
                        <span class="text-6xl font-bold text-gray-100">{{
                            (idx + 1).toString().padStart(2, "0")
                        }}</span>
                        <h2
                            class="text-3xl font-bold uppercase tracking-widest text-primary"
                        >
                            {{ group.title }}
                        </h2>
                    </div>

                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
                    >
                        <div
                            v-for="level in group.levels"
                            :key="level"
                            class="bg-background cursor-pointer hover:bg-aici-light/50 p-8 rounded-xl border border-gray-100 shadow-lg hover:shadow-2xl transition-all group overflow-hidden relative"
                        >
                            <div
                                class="absolute top-0 right-0 w-2 h-0 group-hover:h-full bg-secondary transition-all duration-300"
                            ></div>
                            <h4
                                class="font-bold text-lg mb-4 text-primary group-hover:text-secondary transition-colors"
                            >
                                {{ level }}
                            </h4>
                            <p
                                class="text-sm opacity-60 mb-6 italic leading-relaxed"
                            >
                                Program ini dirancang khusus untuk mengasah
                                logika dan kreativitas sejak dini melalui proyek
                                seru.
                            </p>
                            <button
                                class="text-xs font-bold uppercase tracking-widest text-primary flex items-center group-hover:translate-x-2 transition-transform"
                            >
                                Learn More
                                <svg
                                    class="w-4 h-4 ml-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Download -->
        <section class="py-20 bg-gray-50 text-center space-y-8">
            <h2 class="text-3xl font-bold uppercase text-primary">
                Butuh Informasi Lebih Lanjut?
            </h2>
            <div class="flex flex-wrap justify-center gap-6">
                <a
                    href="#"
                    class="inline-block bg-primary text-white px-10 py-4 rounded-lg font-bold uppercase tracking-widest shadow-xl hover:brightness-110"
                    >Download Full Curriculum</a
                >
                <Link
                    href="/join"
                    class="inline-block bg-secondary text-white px-10 py-4 rounded-lg font-bold uppercase tracking-widest shadow-xl hover:brightness-110"
                    >Daftar Sekarang</Link
                >
            </div>
        </section>
    </MainLayout>
</template>
