<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { Head, router, Link } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
import axios from "axios";

const props = defineProps({
    attempt: Object,
    test: Object,
    questions: Array,
    progress: Object,
    timeRemaining: Number,
});

const currentQuestionIndex = ref(0);
const answers = ref([...props.questions]);
const secondsLeft = ref(props.timeRemaining);
const isSaving = ref(false);
const showCompletionModal = ref(false);

const currentQuestion = computed(() => answers.value[currentQuestionIndex.value]);

// Timer logic
let timerInterval;
const startTimer = () => {
    timerInterval = setInterval(() => {
        if (secondsLeft.value > 0) {
            secondsLeft.value--;
        } else {
            clearInterval(timerInterval);
            autoSubmit();
        }
    }, 1000);
};

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, "0")}`;
};

const selectOption = async (option) => {
    if (isSaving.value) return;

    currentQuestion.value.user_answer = option;
    await saveAnswer(currentQuestion.value.id, option);
};

const saveAnswer = async (questionId, answer) => {
    isSaving.value = true;
    try {
        await axios.post(route('placement-test.answer', props.attempt.id), {
            test_question_id: questionId,
            user_answer: answer,
            time_spent_seconds: 0, // Bisa diimprove dengan tracking waktu per soal
        });
        currentQuestion.value.is_answered = true;
    } catch (error) {
        console.error("Gagal menyimpan jawaban:", error);
    } finally {
        isSaving.value = false;
    }
};

const nextQuestion = () => {
    if (currentQuestionIndex.value < answers.value.length - 1) {
        currentQuestionIndex.value++;
    }
};

const prevQuestion = () => {
    if (currentQuestionIndex.value > 0) {
        currentQuestionIndex.value--;
    }
};

const goToQuestion = (index) => {
    currentQuestionIndex.value = index;
};

const completeTest = () => {
    router.post(route('placement-test.complete', props.attempt.id));
};

const autoSubmit = () => {
    completeTest();
};

onMounted(() => {
    if (secondsLeft.value !== null) {
        startTimer();
    }
});

onUnmounted(() => {
    clearInterval(timerInterval);
});
</script>

<template>
    <Head :title="`Mengerjakan ${test.title} - AICI-UMG`" />

    <MainLayout>
        <section class="pt-32 pb-20 bg-accent/10 min-h-screen">
            <div class="max-w-7xl mx-auto px-6 lg:px-12">
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Sidebar Navigation -->
                    <div class="w-full lg:w-80 order-2 lg:order-1">
                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 sticky top-32">
                            <h3 class="text-sm font-black uppercase tracking-widest text-primary mb-6 flex items-center justify-between">
                                Navigasi Soal
                                <span class="text-[10px] bg-primary/10 px-2 py-0.5 rounded text-primary">{{ currentQuestionIndex + 1 }} / {{ answers.length }}</span>
                            </h3>

                            <div class="grid grid-cols-5 gap-3 mb-8">
                                <button
                                    v-for="(q, index) in answers"
                                    :key="q.id"
                                    @click="goToQuestion(index)"
                                    class="w-10 h-10 rounded-xl text-xs font-black transition-all border-2 flex items-center justify-center"
                                    :class="[
                                        currentQuestionIndex === index 
                                            ? 'bg-primary text-white border-primary shadow-lg shadow-primary/20 scale-110' 
                                            : q.is_answered 
                                                ? 'bg-secondary/10 text-secondary border-secondary/20 hover:bg-secondary/20' 
                                                : 'bg-gray-50 text-gray-400 border-gray-100 hover:border-primary/30'
                                    ]"
                                >
                                    {{ index + 1 }}
                                </button>
                            </div>

                            <div class="space-y-3 pt-6 border-t border-gray-100 font-bold text-xs uppercase tracking-wider">
                                <div class="flex items-center text-gray-400">
                                    <span class="w-3 h-3 rounded bg-gray-100 border border-gray-200 mr-2"></span> Belum Dijawab
                                </div>
                                <div class="flex items-center text-secondary">
                                    <span class="w-3 h-3 rounded bg-secondary/10 border border-secondary/20 mr-2"></span> Terjawab
                                </div>
                                <div class="flex items-center text-primary">
                                    <span class="w-3 h-3 rounded bg-primary mr-2"></span> Sedang Dibuka
                                </div>
                            </div>

                            <button
                                @click="showCompletionModal = true"
                                class="w-full mt-10 bg-primary text-white py-4 rounded-xl font-black uppercase tracking-widest hover:brightness-110 active:scale-95 transition-all shadow-xl shadow-primary/20"
                            >
                                Selesaikan Tes
                            </button>
                        </div>
                    </div>

                    <!-- Main Question Area -->
                    <div class="flex-1 order-1 lg:order-2">
                        <!-- Top Bar -->
                        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 mb-6 flex items-center justify-between">
                            <h2 class="text-lg font-black text-primary uppercase tracking-tighter truncate max-w-[200px] md:max-w-none">
                                {{ test.title }}
                            </h2>
                            <div 
                                class="flex items-center px-6 py-2 rounded-2xl font-black text-xl"
                                :class="secondsLeft < 300 ? 'bg-red-50 text-red-500 animate-pulse' : 'bg-secondary/10 text-secondary'"
                            >
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ formatTime(secondsLeft) }}
                            </div>
                        </div>

                        <!-- Question Card -->
                        <div class="bg-white rounded-[3rem] p-8 md:p-12 shadow-sm border border-gray-100 min-h-[500px] flex flex-col transition-all duration-300">
                            <div class="flex items-center mb-8">
                                <span class="bg-primary text-white w-12 h-12 rounded-2xl flex items-center justify-center font-black text-xl mr-6 shadow-lg shadow-primary/20">
                                    {{ currentQuestionIndex + 1 }}
                                </span>
                                <h3 class="text-xl md:text-2xl font-black text-primary leading-tight">
                                    {{ currentQuestion.question }}
                                </h3>
                            </div>

                            <!-- Options -->
                            <div class="space-y-4 mb-12 flex-grow">
                                <button
                                    v-for="(option, key) in currentQuestion.options"
                                    :key="key"
                                    @click="selectOption(key)"
                                    class="w-full p-6 bg-accent/30 rounded-2xl text-left font-bold transition-all border-2 group flex items-start gap-4"
                                    :class="[
                                        currentQuestion.user_answer === key
                                            ? 'border-primary bg-primary/5 text-primary scale-[1.01]'
                                            : 'border-transparent text-gray-700 hover:bg-white hover:border-primary/20 hover:shadow-lg'
                                    ]"
                                >
                                    <span 
                                        class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center font-black transition-colors"
                                        :class="currentQuestion.user_answer === key ? 'bg-primary text-white' : 'bg-white text-primary group-hover:bg-primary/10'"
                                    >
                                        {{ key.toUpperCase() }}
                                    </span>
                                    <span class="pt-0.5 leading-relaxed">{{ option }}</span>
                                </button>
                            </div>

                            <!-- Footer Nav -->
                            <div class="flex items-center justify-between pt-8 border-t border-gray-100">
                                <button
                                    @click="prevQuestion"
                                    :disabled="currentQuestionIndex === 0"
                                    class="group flex items-center font-black uppercase tracking-widest text-sm disabled:opacity-30 transition-all"
                                    :class="currentQuestionIndex > 0 ? 'text-primary hover:-translate-x-1' : 'text-gray-300'"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                                    </svg>
                                    Kembali
                                </button>

                                <div class="hidden md:flex items-center gap-1">
                                    <span v-if="isSaving" class="text-[10px] font-black uppercase text-secondary animate-pulse mr-2">Menyimpan...</span>
                                    <span v-else class="text-[10px] font-black uppercase text-gray-300 mr-2">Tersimpan</span>
                                    <div class="w-2 h-2 rounded-full" :class="isSaving ? 'bg-secondary' : 'bg-green-500'"></div>
                                </div>

                                <button
                                    @click="nextQuestion"
                                    :disabled="currentQuestionIndex === answers.length - 1"
                                    class="group flex items-center font-black uppercase tracking-widest text-sm disabled:opacity-30 transition-all"
                                    :class="currentQuestionIndex < answers.length - 1 ? 'text-primary hover:translate-x-1' : 'text-gray-300'"
                                >
                                    Lanjut
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Completion Confirmation Modal -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showCompletionModal" class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-primary/40 backdrop-blur-sm">
                <div class="bg-white rounded-[3rem] p-8 md:p-12 max-w-lg w-full shadow-2xl overflow-hidden relative">
                    <div class="absolute -top-12 -right-12 w-48 h-48 bg-secondary/10 rounded-full blur-3xl"></div>
                    
                    <div class="relative">
                        <div class="bg-secondary/10 w-20 h-20 rounded-3xl flex items-center justify-center text-4xl mb-8">
                            🏁
                        </div>
                        <h3 class="text-3xl font-black uppercase text-primary mb-4 leading-tight">Yakin ingin mengumpulkan tes?</h3>
                        <p class="text-gray-500 font-bold mb-10 leading-relaxed italic">
                            Pastikan Anda sudah mengecek kembali semua jawaban. Anda tidak bisa mengubah jawaban setelah menekan tombol kumpulkan.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-4">
                            <button
                                @click="completeTest"
                                class="flex-1 bg-primary text-white py-5 rounded-2xl font-black uppercase tracking-widest hover:brightness-110 active:scale-95 transition-all shadow-xl shadow-primary/20"
                            >
                                Kumpulkan Sekarang
                            </button>
                            <button
                                @click="showCompletionModal = false"
                                class="flex-1 bg-gray-100 text-gray-500 py-5 rounded-2xl uppercase tracking-widest hover:bg-gray-200 transition-all font-black"
                            >
                                Cek Lagi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </MainLayout>
</template>

<style scoped>
.font-black {
    font-weight: 900;
}
</style>
