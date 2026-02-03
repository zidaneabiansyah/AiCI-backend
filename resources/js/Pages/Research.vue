<script setup>
import { Head } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
</script>

<template>
    <Head title="Research - AICI-UMG" />

    <MainLayout>
        <section
            class="pt-40 pb-20 bg-primary text-white overflow-hidden relative"
        >
            <div
                class="absolute -top-24 -left-24 w-96 h-96 bg-secondary/10 rounded-full blur-3xl"
            ></div>
            <div class="max-w-7xl mx-auto px-4 md:px-12 relative z-10">
                <h1 class="text-4xl md:text-6xl font-bold uppercase mb-6">
                    <span class="text-secondary">AI</span> Research
                </h1>
                <p class="text-lg opacity-80 max-w-2xl">
                    Mendorong inovasi melalui riset kolaboratif bersama FMIPA UI
                    dan mitra industri.
                </p>
            </div>
        </section>

        <section class="py-24 px-4 md:px-12">
            <div
                class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-20"
            >
                <div class="space-y-12">
                    <div class="space-y-4">
                        <h2
                            class="text-3xl font-bold uppercase tracking-tighter text-primary"
                        >
                            Research Pillars
                        </h2>
                        <div class="w-20 h-1.5 bg-secondary"></div>
                    </div>

                    <div class="space-y-8">
                        <div class="flex gap-6 items-start">
                            <span class="text-4xl font-black text-secondary/20"
                                >01</span
                            >
                            <div class="space-y-2">
                                <h4
                                    class="font-bold text-xl uppercase tracking-widest text-primary"
                                >
                                    Computer Vision
                                </h4>
                                <p class="opacity-70 leading-relaxed italic">
                                    Pengembangan sistem pengenalan objek dan
                                    analisis citra medis berbasis AI.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-6 items-start">
                            <span class="text-4xl font-black text-secondary/20"
                                >02</span
                            >
                            <div class="space-y-2">
                                <h4
                                    class="font-bold text-xl uppercase tracking-widest text-primary"
                                >
                                    Autonomous Systems
                                </h4>
                                <p class="opacity-70 leading-relaxed italic">
                                    Riset pada kontrol robot otonom untuk
                                    navigasi dalam ruangan dan luar ruangan.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-6 items-start">
                            <span class="text-4xl font-black text-secondary/20"
                                >03</span
                            >
                            <div class="space-y-2">
                                <h4
                                    class="font-bold text-xl uppercase tracking-widest text-primary"
                                >
                                    Natural Language Processing
                                </h4>
                                <p class="opacity-70 leading-relaxed italic">
                                    Eksplorasi LLM untuk bahasa daerah di
                                    Indonesia dan analisis sentimen lokal.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-primary p-12 rounded-3xl text-white space-y-8 shadow-2xl skew-y-1"
                >
                    <h3
                        class="text-2xl font-bold uppercase italic text-secondary"
                    >
                        Join Our Research Lab
                    </h3>
                    <p class="opacity-80 leading-relaxed">
                        Kami membuka kesempatan kolaborasi bagi Mahasiswa Final
                        Year, Akademisi, dan Peneliti untuk bergabung dalam
                        proyek riset kami.
                    </p>
                    <ul class="space-y-4 text-sm font-medium">
                        <li class="flex items-center space-x-3">
                            <span class="text-secondary font-black">✔</span>
                            <span>Access to AI Workstations</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <span class="text-secondary font-black">✔</span>
                            <span>FMIPA UI Mentorship</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <span class="text-secondary font-black">✔</span>
                            <span>Industry Networking</span>
                        </li>
                    </ul>
                    <a
                        href="#"
                        class="inline-block bg-secondary text-white px-8 py-3 rounded uppercase font-bold text-xs tracking-widest hover:brightness-110"
                        >Apply for Fellowship</a
                    >
                </div>
            </div>
        </section>
    </MainLayout>
</template>
