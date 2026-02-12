<script setup>
import { Head, useForm, Link } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
import { computed } from "vue";

const props = defineProps({
    courseClass: Object,
    schedules: Array,
    testResult: Object,
    user: Object,
});

const form = useForm({
    class_id: props.courseClass.id,
    class_schedule_id: null,
    student_name: props.user.name,
    student_email: props.user.email,
    student_phone: "",
    student_age: null,
    student_grade: "",
    parent_name: "",
    parent_phone: "",
    parent_email: "",
    special_requirements: "",
    notes: "",
});

const isYoung = computed(() => {
    return form.student_age && form.student_age < 17;
});

const submit = () => {
    form.post(route('enrollments.store'));
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <Head title="Pendaftaran Kursus - AICI-UMG" />

    <MainLayout>
        <section class="pt-32 pb-20 bg-accent/10 min-h-screen">
            <div class="max-w-6xl mx-auto px-6 lg:px-12">
                <!-- Header -->
                <div class="mb-12">
                    <nav class="flex mb-6 text-xs font-black uppercase tracking-[0.2em] overflow-x-auto whitespace-nowrap pb-2">
                        <Link href="/" class="text-gray-400 hover:text-primary transition-colors">Home</Link>
                        <span class="mx-3 text-gray-300">/</span>
                        <Link href="/program" class="text-gray-400 hover:text-primary transition-colors">Program</Link>
                        <span class="mx-3 text-gray-300">/</span>
                        <span class="text-primary">{{ courseClass.name }}</span>
                    </nav>
                    <h1 class="text-3xl md:text-4xl font-black uppercase text-primary leading-tight">
                        Formulir <span class="text-secondary italic">Pendaftaran</span>
                    </h1>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    <!-- Form Area -->
                    <div class="lg:col-span-2 space-y-8">
                        <form @submit.prevent="submit" class="space-y-8">
                            <!-- Section: Schedule Selection -->
                            <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-gray-100">
                                <h3 class="text-xl font-black uppercase text-primary mb-8 flex items-center">
                                    <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center mr-3 text-sm">01</span>
                                    Pilih Jadwal Kursus
                                </h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <label 
                                        v-for="schedule in schedules" 
                                        :key="schedule.id"
                                        class="relative cursor-pointer group"
                                    >
                                        <input 
                                            type="radio" 
                                            name="class_schedule_id" 
                                            v-model="form.class_schedule_id" 
                                            :value="schedule.id"
                                            class="peer sr-only"
                                        />
                                        <div class="p-6 rounded-2xl border-2 transition-all peer-checked:border-primary peer-checked:bg-primary/5 bg-gray-50 border-transparent hover:border-primary/20">
                                            <div class="flex justify-between items-start mb-4">
                                                <h4 class="font-black text-primary uppercase tracking-tight">{{ schedule.batch_name }}</h4>
                                                <div class="w-5 h-5 rounded-full border-2 border-gray-200 peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center">
                                                    <div v-if="form.class_schedule_id === schedule.id" class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                                </div>
                                            </div>
                                            <div class="space-y-2 text-xs font-bold text-gray-500 uppercase tracking-widest">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    Mulai: {{ schedule.start_date }}
                                                </div>
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ schedule.day_of_week }}, {{ schedule.time }}
                                                </div>
                                            </div>
                                            <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                                                <span class="text-[10px] font-black text-secondary">{{ schedule.remaining_slots }} Sisa Slot</span>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ schedule.location }}</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div v-if="form.errors.class_schedule_id" class="mt-4 text-xs font-bold text-red-500 uppercase tracking-widest">{{ form.errors.class_schedule_id }}</div>
                            </div>

                            <!-- Section: Student Info -->
                            <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-gray-100">
                                <h3 class="text-xl font-black uppercase text-primary mb-8 flex items-center">
                                    <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center mr-3 text-sm">02</span>
                                    Data Diri Siswa
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-xs font-black uppercase tracking-widest text-gray-400">Nama Lengkap</label>
                                        <input v-model="form.student_name" type="text" class="w-full bg-accent/20 border-transparent rounded-xl p-4 font-bold focus:ring-primary focus:border-primary transition-all" />
                                        <div v-if="form.errors.student_name" class="text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ form.errors.student_name }}</div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-black uppercase tracking-widest text-gray-400">Email Siswa</label>
                                        <input v-model="form.student_email" type="email" class="w-full bg-accent/20 border-transparent rounded-xl p-4 font-bold focus:ring-primary focus:border-primary transition-all" />
                                        <div v-if="form.errors.student_email" class="text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ form.errors.student_email }}</div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-black uppercase tracking-widest text-gray-400">No. HP / WhatsApp</label>
                                        <input v-model="form.student_phone" type="text" placeholder="08..." class="w-full bg-accent/20 border-transparent rounded-xl p-4 font-bold focus:ring-primary focus:border-primary transition-all" />
                                        <div v-if="form.errors.student_phone" class="text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ form.errors.student_phone }}</div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <label class="text-xs font-black uppercase tracking-widest text-gray-400">Usia</label>
                                            <input v-model="form.student_age" type="number" class="w-full bg-accent/20 border-transparent rounded-xl p-4 font-bold focus:ring-primary focus:border-primary transition-all" />
                                            <div v-if="form.errors.student_age" class="text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ form.errors.student_age }}</div>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-xs font-black uppercase tracking-widest text-gray-400">Kelas/Tingkat</label>
                                            <input v-model="form.student_grade" type="text" placeholder="Cth: 8 SMP" class="w-full bg-accent/20 border-transparent rounded-xl p-4 font-bold focus:ring-primary focus:border-primary transition-all" />
                                            <div v-if="form.errors.student_grade" class="text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ form.errors.student_grade }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Parent Info (Conditional) -->
                            <Transition
                                enter-active-class="transition duration-300 ease-out"
                                enter-from-class="opacity-0 -translate-y-4"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition duration-200 ease-in"
                                leave-from-class="opacity-100 translate-y-0"
                                leave-to-class="opacity-0 -translate-y-4"
                            >
                                <div v-if="isYoung" class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-gray-100">
                                    <h3 class="text-xl font-black uppercase text-primary mb-8 flex items-center">
                                        <span class="w-8 h-8 rounded-xl bg-orange-100 text-orange-500 flex items-center justify-center mr-3 text-sm">03</span>
                                        Data Orang Tua / Wali
                                    </h3>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <label class="text-xs font-black uppercase tracking-widest text-gray-400">Nama Orang Tua</label>
                                            <input v-model="form.parent_name" type="text" class="w-full bg-orange-50/50 border-transparent rounded-xl p-4 font-bold focus:ring-orange-500 focus:border-orange-500 transition-all" />
                                            <div v-if="form.errors.parent_name" class="text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ form.errors.parent_name }}</div>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-xs font-black uppercase tracking-widest text-gray-400">WhatsApp Orang Tua</label>
                                            <input v-model="form.parent_phone" type="text" class="w-full bg-orange-50/50 border-transparent rounded-xl p-4 font-bold focus:ring-orange-500 focus:border-orange-500 transition-all" />
                                            <div v-if="form.errors.parent_phone" class="text-[10px] font-bold text-red-500 uppercase tracking-widest">{{ form.errors.parent_phone }}</div>
                                        </div>
                                    </div>
                                </div>
                            </Transition>

                            <!-- Section: Additional Info -->
                            <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-gray-100">
                                <h3 class="text-xl font-black uppercase text-primary mb-8 flex items-center">
                                    <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center mr-3 text-sm">opt</span>
                                    Informasi Tambahan
                                </h3>

                                <div class="space-y-6">
                                    <div class="space-y-2">
                                        <label class="text-xs font-black uppercase tracking-widest text-gray-400">Kebutuhan Khusus / Alergi</label>
                                        <textarea v-model="form.special_requirements" rows="3" class="w-full bg-accent/20 border-transparent rounded-xl p-4 font-bold focus:ring-primary focus:border-primary transition-all"></textarea>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-black uppercase tracking-widest text-gray-400">Catatan Lainnya</label>
                                        <textarea v-model="form.notes" rows="3" class="w-full bg-accent/20 border-transparent rounded-xl p-4 font-bold focus:ring-primary focus:border-primary transition-all"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Bar -->
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-6 pt-4">
                                <p class="text-xs font-bold text-gray-400 leading-relaxed italic max-w-sm">
                                    Dengan menekan tombol daftar, Anda menyetujui syarat dan ketentuan pendaftaran di AICI-UMG.
                                </p>
                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="w-full sm:w-auto bg-primary text-white px-12 py-5 rounded-2xl font-black uppercase tracking-[0.2em] transition-all hover:bg-primary/90 hover:scale-[1.05] active:scale-95 shadow-2xl shadow-primary/30 disabled:opacity-50"
                                >
                                    Daftar Sekarang
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Sidebar: Summary -->
                    <div class="space-y-8">
                        <div class="bg-primary text-white rounded-[2.5rem] p-8 md:p-10 shadow-2xl shadow-primary/20 sticky top-32 overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-16 -mt-16"></div>
                            
                            <h3 class="text-lg font-black uppercase tracking-widest mb-8 relative">Ringkasan Pendaftaran</h3>
                            
                            <div class="space-y-6 relative mb-10">
                                <div>
                                    <span class="block text-[10px] font-black uppercase tracking-widest opacity-50 mb-1">Nama Kursus</span>
                                    <span class="block text-xl font-black leading-tight">{{ courseClass.name }}</span>
                                </div>
                                <div class="flex justify-between items-end">
                                    <div>
                                        <span class="block text-[10px] font-black uppercase tracking-widest opacity-50 mb-1">Level</span>
                                        <span class="block text-sm font-black">{{ courseClass.level }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="block text-[10px] font-black uppercase tracking-widest opacity-50 mb-1">Kode</span>
                                        <span class="block text-sm font-black">{{ courseClass.code }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4 border-t border-white/10 pt-8 mb-8">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="opacity-60 font-bold">Biaya Kursus</span>
                                    <span class="font-black">{{ courseClass.price_formatted }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="opacity-60 font-bold">Biaya Admin (Xendit)</span>
                                    <span class="font-black">Sesuai Metode</span>
                                </div>
                            </div>

                            <div class="bg-white/10 p-6 rounded-2xl">
                                <span class="block text-[10px] font-black uppercase tracking-widest opacity-50 mb-2 text-center">Estimasi Total</span>
                                <span class="block text-3xl font-black text-center">{{ courseClass.price_formatted }}</span>
                            </div>

                            <div v-if="testResult" class="mt-8 pt-8 border-t border-white/10">
                                <div class="flex items-start gap-4">
                                    <span class="text-2xl">🏆</span>
                                    <div>
                                        <span class="block text-[10px] font-black uppercase tracking-widest opacity-50">Hasil Penempatan</span>
                                        <span class="block text-xs font-bold leading-relaxed">Anda memenuhi syarat untuk kelas ini (Level: {{ testResult.level_achieved }})</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100">
                            <h4 class="text-xs font-black uppercase tracking-widest text-primary mb-4">Butuh Bantuan?</h4>
                            <p class="text-[10px] text-gray-500 font-bold leading-relaxed mb-6">Jika Anda menemukan kendala saat pengisian formulir, silakan hubungi Customer Service kami via WhatsApp.</p>
                            <a href="https://wa.me/6282110103938" target="_blank" class="flex items-center justify-center gap-3 bg-[#25D366] text-white py-3 rounded-xl text-xs font-black uppercase tracking-widest hover:brightness-110 transition-all">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.414 0 .013 5.403.01 12.039c0 2.12.553 4.189 1.603 6.04L0 24l6.103-1.6c1.78.97 3.793 1.487 5.848 1.488 6.635 0 12.037-5.403 12.04-12.041a11.85 11.85 0 00-3.528-8.528z"/></svg>
                                WhatsApp CS
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </MainLayout>
</template>

<style scoped>
.font-black {
    font-weight: 900;
}
</style>
