<script setup>
import { Head } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";

const facilities = [
    {
        title: "Room",
        desc: "Comfortable in the University of Indonesia Environment. Space for elementary, middle, and high school learners.",
        img: "/images/fac-room.jpg",
    },
    {
        title: "Module",
        desc: "Based on STEAM (Science, Tech, Engineering, Art, Math). Comprehensive and tiered modules.",
        img: "/images/fac-module.jpg",
    },
    {
        title: "Media Kit",
        desc: "Complete kits adapted for each school level to foster creativity and design thinking.",
        img: "/images/fac-kit.jpg",
    },
    {
        title: "Robot",
        desc: "Interact directly with humanoid and industrial robots during learning sessions.",
        img: "/images/fac-robot.jpg",
    },
];
</script>

<template>
    <Head title="Facilities - AICI-UMG" />

    <MainLayout>
        <section
            class="pt-40 pb-20 bg-secondary text-white relative overflow-hidden"
        >
            <div
                class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-12 translate-x-12"
            ></div>
            <div class="max-w-7xl mx-auto px-4 md:px-12 relative z-10">
                <h1 class="text-4xl md:text-6xl font-bold uppercase mb-6">
                    Our
                    <span class="text-primary underline decoration-white"
                        >Facilities</span
                    >
                </h1>
                <p class="text-lg opacity-90 max-w-2xl">
                    AICI didukung oleh fasilitas berstandar internasional untuk
                    memberikan pengalaman belajar terbaik.
                </p>
            </div>
        </section>

        <section class="py-24 px-4 md:px-12">
            <div class="max-w-7xl mx-auto space-y-32">
                <div
                    v-for="(fac, idx) in facilities"
                    :key="fac.title"
                    :class="[
                        'flex flex-col lg:flex-row items-center gap-16',
                        idx % 2 !== 0 ? 'lg:flex-row-reverse' : '',
                    ]"
                >
                    <div class="flex-1 w-full relative">
                        <div
                            class="aspect-video bg-gray-200 rounded-2xl overflow-hidden shadow-2xl skew-x-1 group hover:skew-x-0 transition-transform duration-700"
                        >
                            <img
                                :src="fac.img"
                                :alt="fac.title"
                                class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500 scale-110 group-hover:scale-100"
                            />
                        </div>
                    </div>
                    <div class="flex-1 space-y-6">
                        <h4
                            class="text-secondary font-bold uppercase tracking-widest text-sm italic"
                        >
                            {{ fac.title }}
                        </h4>
                        <h2
                            class="text-3xl md:text-4xl font-bold uppercase text-primary leading-tight"
                        >
                            {{ fac.title }} Provided
                        </h2>
                        <p class="opacity-70 leading-relaxed text-lg">
                            {{ fac.desc }}
                        </p>
                        <div class="pt-4">
                            <a
                                :href="'#' + fac.title"
                                class="inline-flex items-center text-primary font-bold uppercase tracking-widest text-xs border-b-2 border-primary/20 hover:border-primary transition-all pb-1"
                            >
                                View Details
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
                                        d="M14 5l7 7-7 7"
                                    ></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-24 bg-primary text-white text-center">
            <div class="max-w-4xl mx-auto px-4 space-y-8">
                <h2 class="text-3xl font-bold uppercase">
                    Ingin Melihat Langsung Fasilitas Kami?
                </h2>
                <p class="opacity-70 italic">
                    Kunjungi AICI di Kampus UI Depok. Kami melayani tour
                    fasilitas untuk sekolah dan instansi.
                </p>
                <Link
                    href="/contact"
                    class="inline-block bg-white text-primary px-10 py-3 rounded-lg font-bold uppercase tracking-widest hover:brightness-90 transition-all shadow-xl"
                    >Contact Us for Visit</Link
                >
            </div>
        </section>
    </MainLayout>
</template>
