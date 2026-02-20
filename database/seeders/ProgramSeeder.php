<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            // SD/MI Programs
            [
                'name' => 'AI Fantasy Zoo',
                'slug' => 'ai-fantasy-zoo',
                'education_level' => 'sd_mi',
                'description' => 'Program pengenalan AI untuk siswa kelas 1-3 SD dengan konsep fun learning menggunakan tema kebun binatang fantasi.',
                'objectives' => [
                    'Mengenal konsep dasar AI',
                    'Belajar logika sederhana',
                    'Mengenal robot dan sensor',
                ],
                'min_age' => 6,
                'max_age' => 9,
                'duration_weeks' => 8,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Ukit Advanced',
                'slug' => 'ukit-advanced',
                'education_level' => 'sd_mi',
                'description' => 'Program lanjutan untuk siswa yang sudah menyelesaikan AI Fantasy Zoo.',
                'objectives' => [
                    'Pemrograman visual tingkat lanjut',
                    'Robotika dasar',
                    'Project-based learning',
                ],
                'min_age' => 7,
                'max_age' => 10,
                'duration_weeks' => 10,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'AI Smart Life',
                'slug' => 'ai-smart-life',
                'education_level' => 'sd_mi',
                'description' => 'Program AI untuk siswa kelas 4-5 SD dengan fokus pada aplikasi AI dalam kehidupan sehari-hari.',
                'objectives' => [
                    'Memahami AI dalam kehidupan sehari-hari',
                    'Pemrograman block-based',
                    'Membuat project smart home sederhana',
                ],
                'min_age' => 9,
                'max_age' => 11,
                'duration_weeks' => 12,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Advanced Smart Life',
                'slug' => 'advanced-smart-life',
                'education_level' => 'sd_mi',
                'description' => 'Program lanjutan AI Smart Life dengan project yang lebih kompleks.',
                'objectives' => [
                    'IoT dan sensor integration',
                    'Advanced programming concepts',
                    'Team project development',
                ],
                'min_age' => 10,
                'max_age' => 12,
                'duration_weeks' => 12,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'AI Super Assistant',
                'slug' => 'ai-super-assistant',
                'education_level' => 'sd_mi',
                'description' => 'Program untuk siswa kelas 6 SD yang sudah menyelesaikan Advanced Smart Life.',
                'objectives' => [
                    'Machine Learning basics',
                    'Voice recognition',
                    'Building AI assistant',
                ],
                'min_age' => 11,
                'max_age' => 13,
                'duration_weeks' => 14,
                'is_active' => true,
                'sort_order' => 5,
            ],

            // SMP/MTS Programs
            [
                'name' => 'AI Transformer Workshop',
                'slug' => 'ai-transformer-workshop',
                'education_level' => 'smp_mts',
                'description' => 'Program AI untuk siswa SMP kelas 7-9 dengan fokus pada robotika dan transformasi digital.',
                'objectives' => [
                    'Python programming basics',
                    'Robotics engineering',
                    'AI model training',
                ],
                'min_age' => 12,
                'max_age' => 15,
                'duration_weeks' => 16,
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'AI Magic World',
                'slug' => 'ai-magic-world',
                'education_level' => 'smp_mts',
                'description' => 'Program lanjutan untuk siswa yang sudah menyelesaikan AI Transformer Workshop.',
                'objectives' => [
                    'Advanced Python programming',
                    'Computer Vision basics',
                    'Deep Learning introduction',
                ],
                'min_age' => 13,
                'max_age' => 16,
                'duration_weeks' => 18,
                'is_active' => true,
                'sort_order' => 7,
            ],

            // SMA/MA Programs
            [
                'name' => 'AI Super Engineer',
                'slug' => 'ai-super-engineer',
                'education_level' => 'sma_ma_smk',
                'description' => 'Program AI engineering untuk siswa SMA kelas 10-12.',
                'objectives' => [
                    'Advanced programming',
                    'Machine Learning algorithms',
                    'Real-world AI projects',
                ],
                'min_age' => 15,
                'max_age' => 18,
                'duration_weeks' => 20,
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'AI Future Town',
                'slug' => 'ai-future-town',
                'education_level' => 'sma_ma_smk',
                'description' => 'Program untuk siswa yang sudah menyelesaikan AI Super Engineer dengan fokus pada smart city.',
                'objectives' => [
                    'IoT ecosystem design',
                    'AI for smart cities',
                    'Capstone project',
                ],
                'min_age' => 16,
                'max_age' => 19,
                'duration_weeks' => 22,
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'AI Super Designer',
                'slug' => 'ai-super-designer',
                'education_level' => 'sma_ma_smk',
                'description' => 'Program elite untuk siswa dengan predikat excellent dari AI Future Town.',
                'objectives' => [
                    'AI system architecture',
                    'Research methodology',
                    'Publication-ready projects',
                ],
                'min_age' => 17,
                'max_age' => 19,
                'duration_weeks' => 24,
                'is_active' => true,
                'sort_order' => 10,
            ],
        ];

        foreach ($programs as $program) {
            Program::create($program);
        }

        $this->command->info('Programs seeded successfully!');
    }
}
