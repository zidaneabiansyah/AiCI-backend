<script setup>
import { Head } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
</script>

<template>
    <Head title="Contact - AICI-UMG" />

    <MainLayout>
        <section
            class="pt-40 pb-20 bg-background border-b border-gray-100 text-center"
        >
            <div class="max-w-7xl mx-auto px-4 md:px-12 space-y-6">
                <h1
                    class="text-4xl md:text-6xl font-black uppercase text-primary"
                >
                    Get <span class="text-secondary italic">In Touch</span>
                </h1>
                <p class="text-lg opacity-60 max-w-2xl mx-auto">
                    Kami siap melayani pertanyaan seputar program, pendaftaran,
                    dan kolaborasi.
                </p>
            </div>
        </section>

        <section class="py-24 px-4 md:px-12">
            <div
                class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-20"
            >
                <!-- Info -->
                <div class="space-y-12">
                    <div class="space-y-4">
                        <h2 class="text-3xl font-bold uppercase text-primary">
                            Location & Office
                        </h2>
                        <p class="opacity-70 italic text-lg leading-relaxed">
                            FMIPA UI, Gedung Multidisiplin, Lt 4, Depok, West
                            Java 16424
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <h4
                                class="font-bold text-xs uppercase tracking-widest text-secondary"
                            >
                                WhatsApp
                            </h4>
                            <p class="font-bold text-xl text-primary underline">
                                +62 821 1010 3938
                            </p>
                        </div>
                        <div class="space-y-3">
                            <h4
                                class="font-bold text-xs uppercase tracking-widest text-secondary"
                            >
                                Email
                            </h4>
                            <p class="font-bold text-xl text-primary underline">
                                info@aici-umg.com
                            </p>
                        </div>
                    </div>

                    <div
                        class="aspect-video bg-gray-200 rounded-3xl overflow-hidden shadow-2xl relative"
                    >
                        <!-- Map placeholder -->
                        <div
                            class="absolute inset-0 flex items-center justify-center text-xs opacity-40 italic uppercase tracking-widest"
                        >
                            Google Maps Integration Here
                        </div>
                        <img
                            src="/images/map-static.jpg"
                            alt="Map"
                            class="w-full h-full object-cover"
                        />
                    </div>
                </div>

                <!-- Form -->
                <div
                    class="bg-primary p-12 rounded-[3rem] text-white shadow-2xl relative overflow-hidden"
                >
                    <div
                        class="absolute top-0 right-0 w-32 h-32 bg-secondary opacity-20 -mr-12 -mt-12 rounded-full"
                    ></div>

                    <form class="space-y-8 relative z-10">
                        <div class="space-y-4">
                            <h3 class="text-2xl font-bold uppercase">
                                Send Us a Message
                            </h3>
                            <p class="text-sm opacity-60">
                                Isi formulir di bawah ini, tim kami akan segera
                                menghubungi Anda.
                            </p>
                        </div>

                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-bold uppercase tracking-widest opacity-60"
                                    >Full Name</label
                                >
                                <input
                                    type="text"
                                    placeholder="Your Name"
                                    class="w-full bg-white/5 border-b border-white/20 p-4 focus:outline-none focus:border-secondary transition-colors"
                                />
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-bold uppercase tracking-widest opacity-60"
                                    >Email Address</label
                                >
                                <input
                                    type="email"
                                    placeholder="email@example.com"
                                    class="w-full bg-white/5 border-b border-white/20 p-4 focus:outline-none focus:border-secondary transition-colors"
                                />
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-bold uppercase tracking-widest opacity-60"
                                    >Message</label
                                >
                                <textarea
                                    rows="4"
                                    placeholder="How can we help you?"
                                    class="w-full bg-white/5 border-b border-white/20 p-4 focus:outline-none focus:border-secondary transition-colors resize-none"
                                ></textarea>
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="w-full bg-secondary text-white py-5 rounded-2xl font-bold uppercase tracking-widest hover:brightness-110 shadow-xl transition-all"
                        >
                            Submit Inquery
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </MainLayout>
</template>
