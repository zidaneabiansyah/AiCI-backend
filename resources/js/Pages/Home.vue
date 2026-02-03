<script setup>
import { Head, Link } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Home - AICI-UMG" />

    <MainLayout>
        <!-- Hero Section -->
        <section
            class="relative min-h-screen flex items-center pt-20 overflow-hidden bg-aici-light/20"
        >
            <!-- Background Elements -->
            <div
                class="absolute top-0 right-0 w-1/2 h-full bg-primary/5 -skew-x-12 transform translate-x-20"
            ></div>

            <div
                class="max-w-7xl mx-auto px-4 md:px-12 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center relative z-10"
            >
                <div class="space-y-8 animate-shimmer">
                    <h1
                        class="text-5xl md:text-7xl font-bold leading-tight uppercase"
                    >
                        Indonesian <br />
                        <span class="text-secondary"
                            >Artificial Intelligence</span
                        >
                        <br />
                        Center
                    </h1>
                    <p class="text-lg opacity-80 max-w-xl">
                        An institution founded in collaboration between the
                        Faculty of Mathematics and Natural Sciences, University
                        of Indonesia, and UMG Idealab, which focuses on
                        developing Human Resources in the field of Artificial
                        Intelligence.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <Link
                            href="/program"
                            class="bg-secondary text-white px-8 py-4 rounded-lg font-bold uppercase tracking-widest hover:brightness-110 transition-all shadow-xl"
                        >
                            Explore Program
                        </Link>
                    </div>
                </div>

                <div class="relative">
                    <div
                        class="w-full aspect-square rounded-full border-2 border-primary/20 p-4 animate-[spin_20s_linear_infinite]"
                    >
                        <div
                            class="w-full h-full rounded-full border-2 border-secondary/20 p-4 animate-[spin_10s_linear_infinite_reverse]"
                        ></div>
                    </div>
                    <!-- Mock Image placeholder -->
                    <div
                        class="absolute inset-0 flex items-center justify-center p-12"
                    >
                        <div
                            class="w-full h-full bg-gray-200 rounded-2xl shadow-2xl overflow-hidden"
                        >
                            <img
                                src="/images/hero-image.jpg"
                                alt="AICI Showcase"
                                class="w-full h-full object-cover"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-24 bg-primary text-white">
            <div
                class="max-w-7xl mx-auto px-4 md:px-12 grid grid-cols-2 md:grid-cols-4 gap-8"
            >
                <div class="text-center space-y-2">
                    <div class="text-4xl font-bold text-secondary">10+</div>
                    <p class="text-sm opacity-60 uppercase tracking-widest">
                        Programs
                    </p>
                </div>
                <div class="text-center space-y-2">
                    <div class="text-4xl font-bold text-secondary">500+</div>
                    <p class="text-sm opacity-60 uppercase tracking-widest">
                        Students
                    </p>
                </div>
                <div class="text-center space-y-2">
                    <div class="text-4xl font-bold text-secondary">20+</div>
                    <p class="text-sm opacity-60 uppercase tracking-widest">
                        Partners
                    </p>
                </div>
                <div class="text-center space-y-2">
                    <div class="text-4xl font-bold text-secondary">5+</div>
                    <p class="text-sm opacity-60 uppercase tracking-widest">
                        Locations
                    </p>
                </div>
            </div>
        </section>

        <!-- Programs Preview (Cards) -->
        <section class="py-32 px-4 md:px-12">
            <div class="max-w-7xl mx-auto space-y-16">
                <div
                    class="flex flex-col md:flex-row justify-between items-end gap-6"
                >
                    <div class="space-y-4">
                        <h2 class="text-4xl font-bold uppercase">
                            Our
                            <span
                                class="text-secondary underline decoration-4 underline-offset-8 transition-all hover:decoration-secondary/50"
                                >Core</span
                            >
                            Programs
                        </h2>
                        <p class="text-muted-foreground">
                            Pilih program yang sesuai dengan tingkat pendidikan
                            dan minat Anda.
                        </p>
                    </div>
                    <Link
                        href="/program"
                        class="text-primary font-bold uppercase tracking-wider hover:opacity-70 flex items-center group"
                    >
                        See All Programs
                        <svg
                            class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3"
                            ></path>
                        </svg>
                    </Link>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
                >
                    <!-- Fun Learning Card -->
                    <div
                        class="group bg-primary p-12 rounded-2xl text-white space-y-6 hover:-translate-y-2 transition-transform duration-500 shadow-2xl relative overflow-hidden"
                    >
                        <div
                            class="absolute top-0 right-0 p-6 opacity-10 scale-150 rotate-12 group-hover:rotate-45 transition-transform duration-700"
                        >
                            <span class="text-8xl italic">AI</span>
                        </div>
                        <h3 class="text-2xl font-bold leading-tight">
                            Fun Learning with AI for Elementary Students
                        </h3>
                        <p class="text-sm opacity-70 leading-relaxed">
                            Program belajar interaktif untuk tingkat SD kelas
                            1-6 dengan fokus pada kreativitas dan pengenalan
                            dasar robotik.
                        </p>
                        <Link
                            href="/program"
                            class="inline-block border-b-2 border-secondary pb-1 text-sm font-bold uppercase tracking-widest hover:text-secondary hover:border-white transition-all"
                            >Download Brochure</Link
                        >
                    </div>

                    <!-- AI for Education -->
                    <div
                        class="group bg-primary p-12 rounded-2xl text-white space-y-6 hover:-translate-y-2 transition-transform duration-500 shadow-2xl relative overflow-hidden"
                    >
                        <h3 class="text-2xl font-bold leading-tight">
                            AI for Education
                        </h3>
                        <p class="text-sm opacity-70 leading-relaxed">
                            Pemberdayaan tenaga pendidik (Guru & Dosen) untuk
                            mengintegrasikan AI dalam proses belajar mengajar.
                        </p>
                        <Link
                            href="/program"
                            class="inline-block border-b-2 border-secondary pb-1 text-sm font-bold uppercase tracking-widest hover:text-secondary hover:border-white transition-all"
                            >Download Brochure</Link
                        >
                    </div>

                    <!-- AI Day -->
                    <div
                        class="group bg-primary p-12 rounded-2xl text-white space-y-6 hover:-translate-y-2 transition-transform duration-500 shadow-2xl relative overflow-hidden"
                    >
                        <h3 class="text-2xl font-bold leading-tight">AI Day</h3>
                        <p class="text-sm opacity-70 leading-relaxed">
                            Satu hari penuh eksplorasi teknologi AI melalui
                            workshop singkat dan demo proyek robotik.
                        </p>
                        <Link
                            href="/program"
                            class="inline-block border-b-2 border-secondary pb-1 text-sm font-bold uppercase tracking-widest hover:text-secondary hover:border-white transition-all"
                            >Download Brochure</Link
                        >
                    </div>
                </div>
            </div>
        </section>

        <!-- Facilities Preview -->
        <section class="py-32 bg-aici-light relative overflow-hidden">
            <div
                class="max-w-7xl mx-auto px-4 md:px-12 grid grid-cols-1 lg:grid-cols-2 gap-20 items-center"
            >
                <div class="order-2 lg:order-1 relative">
                    <div
                        class="aspect-video bg-gray-300 rounded-3xl overflow-hidden shadow-2xl"
                    >
                        <img
                            src="/images/facility-preview.jpg"
                            alt="AICI Facilities"
                            class="w-full h-full object-cover"
                        />
                    </div>
                    <div
                        class="absolute -bottom-8 -right-8 w-48 aspect-square bg-secondary rounded-2xl flex items-center justify-center p-6 text-white text-center shadow-xl"
                    >
                        <p class="text-sm font-bold uppercase">
                            Modern Laboratory
                        </p>
                    </div>
                </div>
                <div class="order-1 lg:order-2 space-y-8">
                    <h2 class="text-4xl font-bold uppercase leading-tight">
                        Facilities Provided
                    </h2>
                    <p class="opacity-80 leading-relaxed">
                        To support activities at AICI, facilities are available
                        in the form of Rooms, VR Labs, Learning kits/Training
                        Media in the form of AI Kits, and Robotics Learning
                        Modules for Elementary, Middle School, and High School
                        levels.
                    </p>
                    <Link
                        href="/facility"
                        class="inline-block bg-primary text-white px-8 py-3 rounded uppercase font-bold text-sm hover:brightness-110 shadow-lg"
                        >Explore Facilities</Link
                    >
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="py-32 px-4 md:px-12">
            <div class="max-w-7xl mx-auto space-y-20">
                <div class="text-center space-y-4">
                    <h2 class="text-4xl font-bold uppercase">Testimonials</h2>
                    <p class="text-muted-foreground">
                        What Do They Think After Studying At AICI?
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <!-- Testi 1 -->
                    <div
                        class="bg-white p-10 rounded-3xl border border-gray-100 shadow-xl space-y-6 flex flex-col items-center text-center"
                    >
                        <div
                            class="w-24 h-24 rounded-full bg-gray-200 overflow-hidden border-4 border-secondary/20"
                        >
                            <img
                                src="/images/testi-1.jpg"
                                alt="User"
                                class="w-full h-full object-cover"
                            />
                        </div>
                        <div class="space-y-2">
                            <h4 class="font-bold text-xl">Kafi</h4>
                            <p
                                class="text-xs uppercase text-primary font-bold opacity-60 italic tracking-widest"
                            >
                                Elementary School Student
                            </p>
                        </div>
                        <p class="text-sm opacity-70 leading-relaxed italic">
                            "Belajar robotik di AICI seru banget! Kakaknya baik
                            dan sabar bantuin."
                        </p>
                    </div>

                    <!-- Testi 2 -->
                    <div
                        class="bg-white p-10 rounded-3xl border border-gray-100 shadow-xl space-y-6 flex flex-col items-center text-center"
                    >
                        <div
                            class="w-24 h-24 rounded-full bg-gray-200 overflow-hidden border-4 border-secondary/20"
                        >
                            <img
                                src="/images/testi-2.jpg"
                                alt="User"
                                class="w-full h-full object-cover"
                            />
                        </div>
                        <div class="space-y-2">
                            <h4 class="font-bold text-xl">Syachira</h4>
                            <p
                                class="text-xs uppercase text-primary font-bold opacity-60 italic tracking-widest"
                            >
                                Junior High School Student
                            </p>
                        </div>
                        <p class="text-sm opacity-70 leading-relaxed italic">
                            "Di sini aku bisa kenal teknologi AI terbaru yang
                            belum diajarin di sekolah."
                        </p>
                    </div>

                    <!-- Testi 3 -->
                    <div
                        class="bg-white p-10 rounded-3xl border border-gray-100 shadow-xl space-y-6 flex flex-col items-center text-center"
                    >
                        <div
                            class="w-24 h-24 rounded-full bg-gray-200 overflow-hidden border-4 border-secondary/20"
                        >
                            <img
                                src="/images/testi-3.jpg"
                                alt="User"
                                class="w-full h-full object-cover"
                            />
                        </div>
                        <div class="space-y-2">
                            <h4 class="font-bold text-xl">Rulia</h4>
                            <p
                                class="text-xs uppercase text-primary font-bold opacity-60 italic tracking-widest"
                            >
                                High School Student
                            </p>
                        </div>
                        <p class="text-sm opacity-70 leading-relaxed italic">
                            "AICI ngebantu banget buat nambah skill coding dan
                            persiapan olimpiade robotik."
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>
