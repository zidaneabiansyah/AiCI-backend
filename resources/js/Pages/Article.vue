<script setup>
import { Head, Link } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";

const articles = [
    {
        id: 1,
        title: "Menjelajahi Potensi AI di Dunia Pendidikan",
        date: "Feb 01, 2024",
        category: "Education",
        img: "/images/art-1.jpg",
    },
    {
        id: 2,
        title: "Workshop Robotika Terbesar di UI Depok",
        date: "Jan 28, 2024",
        category: "Event",
        img: "/images/art-2.jpg",
    },
    {
        id: 3,
        title: "Masa Depan Generasi Alpha dengan Coding",
        date: "Jan 15, 2024",
        category: "Insight",
        img: "/images/art-3.jpg",
    },
];
</script>

<template>
    <Head title="Article - AICI-UMG" />

    <MainLayout>
        <section class="pt-40 pb-20 bg-background">
            <div class="max-w-7xl mx-auto px-4 md:px-12 space-y-4">
                <span
                    class="text-secondary font-bold uppercase tracking-widest text-xs"
                    >Knowledge Hub</span
                >
                <h1
                    class="text-4xl md:text-6xl font-bold uppercase text-primary"
                >
                    Latest <span class="text-secondary">Articles</span>
                </h1>
            </div>
        </section>

        <section class="py-24 px-4 md:px-12 bg-gray-50/50">
            <div class="max-w-7xl mx-auto space-y-16">
                <!-- Featured Article -->
                <div
                    class="group relative bg-primary rounded-3xl overflow-hidden shadow-2xl flex flex-col lg:flex-row items-center border border-white/10"
                >
                    <div
                        class="flex-1 w-full aspect-video lg:aspect-auto self-stretch overflow-hidden"
                    >
                        <img
                            src="/images/art-featured.jpg"
                            alt="Featured"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 opacity-80"
                        />
                    </div>
                    <div class="flex-1 p-12 space-y-6">
                        <span
                            class="inline-block bg-secondary text-white px-4 py-1 rounded text-[10px] font-bold uppercase tracking-widest"
                            >Featured Post</span
                        >
                        <h2
                            class="text-3xl md:text-4xl font-bold text-white uppercase italic"
                        >
                            Digital Transformation in Indonesia's Schools with
                            AI Integration
                        </h2>
                        <p class="text-white/70 leading-relaxed italic">
                            Eksplorasi mendalam tentang bagaimana kurikulum AICI
                            membantu sekolah-sekolah di Indonesia beradaptasi
                            dengan revolusi AI.
                        </p>
                        <Link
                            href="#"
                            class="inline-block bg-white text-primary px-8 py-3 rounded-lg font-bold uppercase tracking-widest text-xs hover:bg-secondary hover:text-white transition-all"
                            >Read More</Link
                        >
                    </div>
                </div>

                <!-- Article Grid -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
                >
                    <div
                        v-for="art in articles"
                        :key="art.id"
                        class="bg-white rounded-2xl overflow-hidden shadow-lg border border-gray-100 group"
                    >
                        <div class="aspect-video overflow-hidden">
                            <img
                                :src="art.img"
                                :alt="art.title"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                            />
                        </div>
                        <div class="p-8 space-y-4">
                            <div
                                class="flex justify-between items-center text-[10px] font-bold uppercase tracking-widest opacity-40"
                            >
                                <span>{{ art.category }}</span>
                                <span>{{ art.date }}</span>
                            </div>
                            <h3
                                class="text-xl font-bold text-primary group-hover:text-secondary transition-colors"
                            >
                                {{ art.title }}
                            </h3>
                            <p
                                class="text-sm opacity-60 leading-relaxed line-clamp-2"
                            >
                                Ringkasan singkat mengenai artikel ini untuk
                                menarik minat pembaca lebih lanjut.
                            </p>
                            <Link
                                href="#"
                                class="inline-flex items-center text-primary font-bold uppercase tracking-widest text-xs border-b-2 border-primary/10 hover:border-secondary transition-all pb-1"
                                >Read Article</Link
                            >
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>
