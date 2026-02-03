<script setup>
import { Head } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
</script>

<template>
    <Head title="Join Now - AICI-UMG" />

    <MainLayout>
        <section
            class="min-h-screen pt-40 pb-20 flex items-center justify-center bg-aici-light/20"
        >
            <div
                class="max-w-3xl w-full px-4 text-center space-y-8 animate-shimmer"
            >
                <div class="inline-block p-4 bg-secondary/10 rounded-full mb-4">
                    <span class="text-4xl">🚀</span>
                </div>
                <h1
                    class="text-4xl md:text-5xl font-black uppercase text-primary"
                >
                    Ready to
                    <span class="text-secondary italic"
                        >Step Into The Future</span
                    >?
                </h1>
                <p class="text-lg opacity-70 leading-relaxed italic">
                    Halaman pendaftaran dan Placement Test akan segera hadir di
                    Phase 3. <br />
                    Pantau terus untuk update selanjutnya!
                </p>
                <div class="pt-8">
                    <Link
                        href="/"
                        class="inline-block bg-primary text-white px-10 py-4 rounded-xl font-bold uppercase tracking-widest hover:brightness-110 shadow-2xl"
                        >Back to Home</Link
                    >
                </div>
            </div>
        </section>
    </MainLayout>
</template>
