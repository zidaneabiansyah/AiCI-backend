<?php

namespace Database\Seeders;

use App\Models\ClassModel;
use App\Models\Program;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = Program::all();

        foreach ($programs as $program) {
            // Create 2-3 classes per program
            $classCount = rand(2, 3);
            
            for ($i = 1; $i <= $classCount; $i++) {
                $level = match($i) {
                    1 => 'beginner',
                    2 => 'intermediate',
                    default => 'advanced',
                };

                $minScore = match($level) {
                    'beginner' => 0,
                    'intermediate' => 60,
                    'advanced' => 80,
                };

                ClassModel::create([
                    'program_id' => $program->id,
                    'name' => $program->name . ' - ' . ucfirst($level),
                    'slug' => $program->slug . '-' . $level,
                    'code' => strtoupper(substr(str_replace('-', '', $program->slug), 0, 6)) . '-' . $program->id . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'level' => $level,
                    'description' => "Kelas {$level} untuk program {$program->name}. Cocok untuk siswa yang sudah memiliki pemahaman dasar.",
                    'curriculum' => [
                        'Week 1-2: Introduction & Fundamentals',
                        'Week 3-4: Core Concepts',
                        'Week 5-6: Practical Applications',
                        'Week 7-8: Project Development',
                    ],
                    'prerequisites' => $i > 1 ? ['Menyelesaikan level sebelumnya'] : [],
                    'min_score' => $minScore,
                    'min_age' => $program->min_age,
                    'max_age' => $program->max_age,
                    'duration_hours' => 40,
                    'total_sessions' => 16,
                    'price' => match($level) {
                        'beginner' => 1500000,
                        'intermediate' => 2000000,
                        'advanced' => 2500000,
                    },
                    'capacity' => 20,
                    'enrolled_count' => 0,
                    'is_active' => true,
                    'is_featured' => $i === 1,
                    'sort_order' => $i,
                ]);
            }
        }

        $this->command->info('Classes seeded successfully!');
    }
}
