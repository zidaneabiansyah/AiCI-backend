<?php

namespace Database\Seeders;

use App\Models\PlacementTest;
use App\Models\TestQuestion;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk Placement Test & Questions
 * 
 * Creates:
 * 1. Placement tests untuk setiap education level
 * 2. Sample questions untuk setiap test
 */
class PlacementTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create placement tests
        $tests = [
            [
                'title' => 'Placement Test SD/MI',
                'education_level' => 'sd_mi',
                'description' => 'Test penempatan untuk siswa SD/MI. Test ini akan membantu menentukan kelas yang sesuai dengan kemampuan Anda.',
                'duration_minutes' => 30,
                'passing_score' => 60,
                'instructions' => 'Jawab semua pertanyaan dengan jujur. Tidak ada jawaban benar atau salah, kami hanya ingin mengetahui level kemampuan Anda saat ini.',
                'is_active' => true,
                'show_result_immediately' => true,
                'allow_retake' => true,
                'retake_cooldown_days' => 30,
            ],
            [
                'title' => 'Placement Test SMP/MTs',
                'education_level' => 'smp_mts',
                'description' => 'Test penempatan untuk siswa SMP/MTs. Test ini akan membantu menentukan kelas yang sesuai dengan kemampuan Anda.',
                'duration_minutes' => 45,
                'passing_score' => 60,
                'instructions' => 'Jawab semua pertanyaan dengan jujur. Test ini mencakup logical thinking, basic programming, dan AI awareness.',
                'is_active' => true,
                'show_result_immediately' => true,
                'allow_retake' => true,
                'retake_cooldown_days' => 30,
            ],
            [
                'title' => 'Placement Test SMA/MA/SMK',
                'education_level' => 'sma_ma_smk',
                'description' => 'Test penempatan untuk siswa SMA/MA/SMK. Test ini akan membantu menentukan kelas yang sesuai dengan kemampuan Anda.',
                'duration_minutes' => 60,
                'passing_score' => 60,
                'instructions' => 'Test ini mencakup programming concepts, AI fundamentals, dan robotics. Kerjakan dengan tenang dan fokus.',
                'is_active' => true,
                'show_result_immediately' => true,
                'allow_retake' => true,
                'retake_cooldown_days' => 30,
            ],
        ];

        foreach ($tests as $testData) {
            $test = PlacementTest::create($testData);
            
            // Create sample questions for this test
            $this->createQuestionsForTest($test);
        }

        $this->command->info('Placement tests and questions seeded successfully!');
    }

    /**
     * Create sample questions for a test
     * 
     * @param PlacementTest $test
     */
    protected function createQuestionsForTest(PlacementTest $test): void
    {
        $questions = $this->getQuestionsForLevel($test->education_level);

        foreach ($questions as $index => $questionData) {
            TestQuestion::create([
                'placement_test_id' => $test->id,
                'category' => $questionData['category'],
                'type' => $questionData['type'],
                'difficulty' => $questionData['difficulty'],
                'question' => $questionData['question'],
                'options' => $questionData['options'] ?? null,
                'correct_answer' => $questionData['correct_answer'],
                'explanation' => $questionData['explanation'] ?? null,
                'points' => $questionData['points'] ?? 1,
                'time_limit_seconds' => $questionData['time_limit'] ?? 120,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }

        // Update total questions count
        $test->update(['total_questions' => count($questions)]);
    }

    /**
     * Get questions based on education level
     * 
     * @param string $level
     * @return array
     */
    protected function getQuestionsForLevel(string $level): array
    {
        return match($level) {
            'sd_mi' => $this->getSDQuestions(),
            'smp_mts' => $this->getSMPQuestions(),
            'sma_ma_smk' => $this->getSMAQuestions(),
            default => [],
        };
    }

    /**
     * Questions untuk SD/MI
     */
    protected function getSDQuestions(): array
    {
        return [
            // Logical Thinking
            [
                'category' => 'logical_thinking',
                'type' => 'multiple_choice',
                'difficulty' => 'beginner',
                'question' => 'Jika semua kucing adalah hewan, dan Mimi adalah kucing, maka Mimi adalah...',
                'options' => ['Hewan', 'Tumbuhan', 'Benda', 'Makanan'],
                'correct_answer' => 'Hewan',
                'explanation' => 'Karena Mimi adalah kucing, dan semua kucing adalah hewan, maka Mimi juga hewan.',
                'points' => 1,
            ],
            [
                'category' => 'logical_thinking',
                'type' => 'multiple_choice',
                'difficulty' => 'beginner',
                'question' => 'Urutan angka berikut: 2, 4, 6, 8, ... Angka selanjutnya adalah?',
                'options' => ['9', '10', '11', '12'],
                'correct_answer' => '10',
                'explanation' => 'Pola bilangan genap: 2, 4, 6, 8, 10',
                'points' => 1,
            ],

            // AI Awareness
            [
                'category' => 'ai_awareness',
                'type' => 'true_false',
                'difficulty' => 'beginner',
                'question' => 'Robot dapat membantu manusia melakukan pekerjaan.',
                'options' => ['Benar', 'Salah'],
                'correct_answer' => 'Benar',
                'explanation' => 'Robot dirancang untuk membantu manusia dalam berbagai pekerjaan.',
                'points' => 1,
            ],
            [
                'category' => 'ai_awareness',
                'type' => 'multiple_choice',
                'difficulty' => 'elementary',
                'question' => 'Apa yang dimaksud dengan AI (Artificial Intelligence)?',
                'options' => [
                    'Kecerdasan Buatan',
                    'Alat Industri',
                    'Aplikasi Internet',
                    'Animasi Interaktif'
                ],
                'correct_answer' => 'Kecerdasan Buatan',
                'explanation' => 'AI adalah singkatan dari Artificial Intelligence atau Kecerdasan Buatan.',
                'points' => 1,
            ],

            // Robotics Fundamentals
            [
                'category' => 'robotics_fundamentals',
                'type' => 'multiple_choice',
                'difficulty' => 'beginner',
                'question' => 'Bagian robot yang digunakan untuk bergerak adalah...',
                'options' => ['Motor', 'Lampu', 'Speaker', 'Layar'],
                'correct_answer' => 'Motor',
                'explanation' => 'Motor adalah komponen yang membuat robot dapat bergerak.',
                'points' => 1,
            ],

            // Interest Mapping
            [
                'category' => 'interest_mapping',
                'type' => 'multiple_choice',
                'difficulty' => 'beginner',
                'question' => 'Apa yang paling kamu suka tentang teknologi?',
                'options' => [
                    'Membuat sesuatu yang baru',
                    'Bermain game',
                    'Menonton video',
                    'Semua menarik'
                ],
                'correct_answer' => 'Membuat sesuatu yang baru',
                'explanation' => 'Tidak ada jawaban salah, ini untuk mengetahui minat Anda.',
                'points' => 1,
            ],
        ];
    }

    /**
     * Questions untuk SMP/MTs
     */
    protected function getSMPQuestions(): array
    {
        return [
            // Logical Thinking
            [
                'category' => 'logical_thinking',
                'type' => 'multiple_choice',
                'difficulty' => 'intermediate',
                'question' => 'Jika A > B dan B > C, maka...',
                'options' => ['A > C', 'A < C', 'A = C', 'Tidak dapat ditentukan'],
                'correct_answer' => 'A > C',
                'explanation' => 'Sifat transitif: jika A lebih besar dari B, dan B lebih besar dari C, maka A pasti lebih besar dari C.',
                'points' => 2,
            ],

            // Basic Programming
            [
                'category' => 'basic_programming',
                'type' => 'multiple_choice',
                'difficulty' => 'elementary',
                'question' => 'Apa fungsi dari "loop" dalam programming?',
                'options' => [
                    'Mengulang perintah',
                    'Menghentikan program',
                    'Menyimpan data',
                    'Menampilkan output'
                ],
                'correct_answer' => 'Mengulang perintah',
                'explanation' => 'Loop digunakan untuk mengulang serangkaian perintah.',
                'points' => 2,
            ],
            [
                'category' => 'basic_programming',
                'type' => 'multiple_choice',
                'difficulty' => 'intermediate',
                'question' => 'Apa hasil dari: 5 + 3 * 2?',
                'options' => ['16', '11', '13', '10'],
                'correct_answer' => '11',
                'explanation' => 'Urutan operasi: perkalian dulu (3*2=6), lalu penjumlahan (5+6=11).',
                'points' => 2,
            ],

            // AI Awareness
            [
                'category' => 'ai_awareness',
                'type' => 'multiple_choice',
                'difficulty' => 'intermediate',
                'question' => 'Machine Learning adalah...',
                'options' => [
                    'Komputer belajar dari data',
                    'Mesin yang belajar bergerak',
                    'Pembelajaran mesin industri',
                    'Mesin untuk belajar'
                ],
                'correct_answer' => 'Komputer belajar dari data',
                'explanation' => 'Machine Learning adalah cabang AI dimana komputer belajar dari data tanpa diprogram secara eksplisit.',
                'points' => 2,
            ],

            // Robotics Fundamentals
            [
                'category' => 'robotics_fundamentals',
                'type' => 'multiple_choice',
                'difficulty' => 'intermediate',
                'question' => 'Sensor pada robot berfungsi untuk...',
                'options' => [
                    'Mendeteksi lingkungan',
                    'Menyimpan program',
                    'Menghasilkan suara',
                    'Menampilkan gambar'
                ],
                'correct_answer' => 'Mendeteksi lingkungan',
                'explanation' => 'Sensor membantu robot mendeteksi dan merespon lingkungan sekitarnya.',
                'points' => 2,
            ],
        ];
    }

    /**
     * Questions untuk SMA/MA/SMK
     */
    protected function getSMAQuestions(): array
    {
        return [
            // Logical Thinking
            [
                'category' => 'logical_thinking',
                'type' => 'multiple_choice',
                'difficulty' => 'advanced',
                'question' => 'Dalam logika Boolean, hasil dari (True AND False) OR True adalah...',
                'options' => ['True', 'False', 'Null', 'Undefined'],
                'correct_answer' => 'True',
                'explanation' => '(True AND False) = False, kemudian False OR True = True',
                'points' => 3,
            ],

            // Basic Programming
            [
                'category' => 'basic_programming',
                'type' => 'multiple_choice',
                'difficulty' => 'advanced',
                'question' => 'Apa output dari kode Python berikut?\nfor i in range(3):\n    print(i)',
                'options' => ['0 1 2', '1 2 3', '0 1 2 3', 'Error'],
                'correct_answer' => '0 1 2',
                'explanation' => 'range(3) menghasilkan angka 0, 1, 2 (tidak termasuk 3).',
                'points' => 3,
            ],
            [
                'category' => 'basic_programming',
                'type' => 'multiple_choice',
                'difficulty' => 'advanced',
                'question' => 'Apa itu variable dalam programming?',
                'options' => [
                    'Tempat menyimpan data',
                    'Fungsi matematika',
                    'Jenis loop',
                    'Operator logika'
                ],
                'correct_answer' => 'Tempat menyimpan data',
                'explanation' => 'Variable adalah container untuk menyimpan nilai data.',
                'points' => 2,
            ],

            // AI Awareness
            [
                'category' => 'ai_awareness',
                'type' => 'multiple_choice',
                'difficulty' => 'advanced',
                'question' => 'Neural Network terinspirasi dari...',
                'options' => [
                    'Otak manusia',
                    'Jaringan komputer',
                    'Sistem listrik',
                    'Struktur bangunan'
                ],
                'correct_answer' => 'Otak manusia',
                'explanation' => 'Neural Network meniru cara kerja neuron di otak manusia.',
                'points' => 3,
            ],

            // Robotics Fundamentals
            [
                'category' => 'robotics_fundamentals',
                'type' => 'multiple_choice',
                'difficulty' => 'advanced',
                'question' => 'Apa yang dimaksud dengan actuator pada robot?',
                'options' => [
                    'Komponen yang menghasilkan gerakan',
                    'Sensor untuk mendeteksi',
                    'Processor untuk berpikir',
                    'Memory untuk menyimpan'
                ],
                'correct_answer' => 'Komponen yang menghasilkan gerakan',
                'explanation' => 'Actuator adalah komponen yang mengubah sinyal menjadi gerakan fisik.',
                'points' => 3,
            ],
        ];
    }
}
