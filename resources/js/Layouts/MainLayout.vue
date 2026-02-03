<script setup>
import { Link } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted } from "vue";

const isScrolled = ref(false);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

onMounted(() => {
    window.addEventListener("scroll", handleScroll);
});

onUnmounted(() => {
    window.removeEventListener("scroll", handleScroll);
});
</script>

<template>
    <div
        class="min-h-screen font-comfortaa text-foreground bg-background selection:bg-primary selection:text-white"
    >
        <!-- Navigation -->
        <nav
            :class="[
                'fixed top-0 w-full z-50 transition-all duration-300 px-4 md:px-12',
                isScrolled ? 'bg-white shadow-md py-3' : 'bg-transparent py-5',
            ]"
        >
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <!-- Logo -->
                <Link href="/" class="flex items-center space-x-2">
                    <img
                        src="/images/logo-aici.png"
                        alt="AICI Logo"
                        class="h-10 md:h-12 object-contain"
                    />
                </Link>

                <!-- Menu Links (Desktop) -->
                <div
                    class="hidden lg:flex items-center space-x-8 text-sm font-medium"
                >
                    <Link href="/" class="hover:text-primary transition-colors"
                        >Home</Link
                    >
                    <Link
                        href="/program"
                        class="hover:text-primary transition-colors"
                        >Program</Link
                    >
                    <Link
                        href="/profile"
                        class="hover:text-primary transition-colors"
                        >Profile</Link
                    >
                    <Link
                        href="/facility"
                        class="hover:text-primary transition-colors"
                        >Facility</Link
                    >
                    <Link
                        href="/gallery"
                        class="hover:text-primary transition-colors"
                        >Gallery</Link
                    >
                    <Link
                        href="/research"
                        class="hover:text-primary transition-colors"
                        >Research</Link
                    >
                    <Link
                        href="/contact"
                        class="hover:text-primary transition-colors"
                        >Contact</Link
                    >
                    <Link
                        href="/articles"
                        class="hover:text-primary transition-colors"
                        >Article</Link
                    >
                </div>

                <!-- CTA Button -->
                <Link
                    href="/join"
                    class="bg-primary text-white px-6 py-2.5 rounded shadow-lg hover:brightness-110 transition-all text-sm font-semibold uppercase tracking-wider"
                >
                    Daftar Sekarang
                </Link>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-primary text-white pt-16 pb-8 px-4 md:px-12">
            <div
                class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12"
            >
                <!-- Branding & Contact -->
                <div class="space-y-6">
                    <img
                        src="/images/logo-aici-white.png"
                        alt="AICI Logo"
                        class="h-12 object-contain"
                    />
                    <p class="text-sm opacity-80 leading-relaxed">
                        Artificial Intelligence Center Indonesia (AICI) adalah
                        lembaga pusat pembelajaran AI & Robotics kolaborasi
                        FMIPA UI & UMG IdeaLab.
                    </p>
                    <div class="space-y-3 text-sm text-accent">
                        <p class="flex items-start">
                            <span class="mr-3">📍</span>
                            <span
                                >FMIPA UI, Gedung Multidisiplin, Lt 4, Depok,
                                West Java 16424</span
                            >
                        </p>
                        <p class="flex items-center">
                            <span class="mr-3">📞</span>
                            <span>+62 821 1010 3938</span>
                        </p>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-bold mb-6">PAGES</h4>
                    <ul class="space-y-4 text-sm opacity-80">
                        <li>
                            <Link href="/profile" class="hover:underline"
                                >Profile</Link
                            >
                        </li>
                        <li>
                            <Link href="/facility" class="hover:underline"
                                >Facility</Link
                            >
                        </li>
                        <li>
                            <Link href="/program" class="hover:underline"
                                >Program</Link
                            >
                        </li>
                        <li>
                            <Link href="/contact" class="hover:underline"
                                >Contact</Link
                            >
                        </li>
                        <li>
                            <Link href="/articles" class="hover:underline"
                                >Latest Articles</Link
                            >
                        </li>
                    </ul>
                </div>

                <!-- Programs -->
                <div>
                    <h4 class="text-lg font-bold mb-6">DOWNLOAD</h4>
                    <ul class="space-y-4 text-sm opacity-80">
                        <li>
                            <a href="#" class="hover:underline"
                                >AI Fun Learning</a
                            >
                        </li>
                        <li>
                            <a href="#" class="hover:underline"
                                >Prompt Engineer Workshop</a
                            >
                        </li>
                        <li>
                            <a href="#" class="hover:underline"
                                >Extracurricular AI and Robotics</a
                            >
                        </li>
                        <li>
                            <a href="#" class="hover:underline"
                                >AI for Education</a
                            >
                        </li>
                        <li><a href="#" class="hover:underline">AI Day</a></li>
                    </ul>
                </div>

                <!-- Social Media -->
                <div>
                    <h4 class="text-lg font-bold mb-6">SOCIAL MEDIA</h4>
                    <div class="flex space-x-4 mb-8">
                        <a
                            href="#"
                            class="w-10 h-10 border border-white/20 flex items-center justify-center hover:bg-white/10 transition-colors uppercase italic text-xs"
                            >IG</a
                        >
                        <a
                            href="#"
                            class="w-10 h-10 border border-white/20 flex items-center justify-center hover:bg-white/10 transition-colors uppercase italic text-xs"
                            >FB</a
                        >
                        <a
                            href="#"
                            class="w-10 h-10 border border-white/20 flex items-center justify-center hover:bg-white/10 transition-colors uppercase italic text-xs"
                            >LI</a
                        >
                        <a
                            href="#"
                            class="w-10 h-10 border border-white/20 flex items-center justify-center hover:bg-white/10 transition-colors uppercase italic text-xs"
                            >YT</a
                        >
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div
                class="max-w-7xl mx-auto pt-8 border-t border-white/10 text-center text-xs opacity-60"
            >
                <p>
                    &copy; {{ new Date().getFullYear() }} AICI-UMG. All Rights
                    Reserved.
                </p>
            </div>
        </footer>

        <!-- Floating Action Buttons -->
        <div class="fixed bottom-8 left-8 z-40">
            <a
                href="https://wa.me/6282110103938"
                target="_blank"
                class="block bg-[#25D366] p-3 rounded-full shadow-2xl hover:scale-110 transition-transform"
            >
                <span
                    class="text-white text-xs font-bold px-2 whitespace-nowrap"
                    >WhatsApp Us</span
                >
            </a>
        </div>
    </div>
</template>
