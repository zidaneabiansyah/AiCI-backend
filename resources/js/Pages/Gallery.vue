<script setup>
import { Head } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";

const galleryItems = [
    { title: "AI Workshop 2024", category: "Event", img: "/images/gal-1.jpg" },
    {
        title: "Robotics Competition",
        category: "Competition",
        img: "/images/gal-2.jpg",
    },
    {
        title: "Student Projects",
        category: "Project",
        img: "/images/gal-3.jpg",
    },
    { title: "Campus Tour", category: "Visit", img: "/images/gal-4.jpg" },
    { title: "Mentoring Session", category: "Class", img: "/images/gal-5.jpg" },
    { title: "Graduation Day", category: "Event", img: "/images/gal-6.jpg" },
];
</script>

<template>
    <Head title="Gallery - AICI-UMG" />

    <MainLayout>
        <section
            class="pt-40 pb-20 bg-background text-primary border-b border-gray-100"
        >
            <div class="max-w-7xl mx-auto px-4 md:px-12 text-center space-y-4">
                <h1 class="text-4xl md:text-6xl font-bold uppercase">
                    Our <span class="text-secondary">Gallery</span>
                </h1>
                <p class="text-lg opacity-60 max-w-2xl mx-auto">
                    Dokumentasi kegiatan dan karya terbaik dari para peserta
                    AICI.
                </p>
            </div>
        </section>

        <section class="py-24 px-4 md:px-12">
            <div
                class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8"
            >
                <div
                    v-for="item in galleryItems"
                    :key="item.title"
                    class="group relative aspect-square overflow-hidden rounded-2xl bg-gray-100 shadow-xl"
                >
                    <img
                        :src="item.img"
                        :alt="item.title"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                    />
                    <div
                        class="absolute inset-0 bg-linear-to-t from-primary/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex flex-col justify-end p-8"
                    >
                        <span
                            class="text-secondary text-xs font-bold uppercase tracking-widest mb-2"
                            >{{ item.category }}</span
                        >
                        <h4
                            class="text-white font-bold text-xl uppercase italic"
                        >
                            {{ item.title }}
                        </h4>
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>
