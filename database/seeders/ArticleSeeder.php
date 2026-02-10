<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        $articles = $this->getArticles($admin->id);

        foreach ($articles as $article) {
            Article::create($article);
        }

        $this->command->info('Articles seeded successfully!');
    }

    protected function getArticles(int $adminId): array
    {
        return [
            [
                'title' => 'Mengenal Artificial Intelligence: Teknologi Masa Depan',
                'slug' => 'mengenal-artificial-intelligence-teknologi-masa-depan',
                'excerpt' => 'Artificial Intelligence (AI) atau Kecerdasan Buatan adalah teknologi yang memungkinkan mesin untuk belajar dari pengalaman dan melakukan tugas-tugas yang biasanya memerlukan kecerdasan manusia.',
                'content' => "# Apa itu Artificial Intelligence?\n\nArtificial Intelligence (AI) atau Kecerdasan Buatan adalah cabang ilmu komputer yang berfokus pada pembuatan mesin yang dapat berpikir dan belajar seperti manusia.",
                'category' => 'tutorial',
                'tags' => json_encode(['AI', 'Technology', 'Education']),
                'status' => 'published',
                'views_count' => 1250,
                'published_at' => now()->subDays(30),
                'created_by' => $adminId,
            ],
            [
                'title' => 'Robotika untuk Anak: Mengapa Penting Dipelajari Sejak Dini?',
                'slug' => 'robotika-untuk-anak-mengapa-penting-dipelajari-sejak-dini',
                'excerpt' => 'Belajar robotika sejak dini membantu anak mengembangkan kemampuan problem-solving, kreativitas, dan computational thinking.',
                'content' => "# Mengapa Anak Harus Belajar Robotika?\n\nRobotika bukan hanya tentang membuat robot, tetapi juga tentang mengembangkan keterampilan abad 21.",
                'category' => 'tutorial',
                'tags' => json_encode(['Robotics', 'Kids', 'Education']),
                'status' => 'published',
                'views_count' => 980,
                'published_at' => now()->subDays(25),
                'created_by' => $adminId,
            ],
            [
                'title' => 'Machine Learning: Cara Komputer Belajar dari Data',
                'slug' => 'machine-learning-cara-komputer-belajar-dari-data',
                'excerpt' => 'Machine Learning adalah subset dari AI yang memungkinkan komputer belajar dan meningkatkan performanya dari pengalaman.',
                'content' => "# Apa itu Machine Learning?\n\nMachine Learning (ML) adalah metode analisis data yang mengotomatisasi pembangunan model analitik.",
                'category' => 'tutorial',
                'tags' => json_encode(['Machine Learning', 'AI', 'Data Science']),
                'status' => 'published',
                'views_count' => 756,
                'published_at' => now()->subDays(20),
                'created_by' => $adminId,
            ],
            [
                'title' => 'Python untuk AI: Bahasa Pemrograman Pilihan Para Data Scientist',
                'slug' => 'python-untuk-ai-bahasa-pemrograman-pilihan-para-data-scientist',
                'excerpt' => 'Python telah menjadi bahasa pemrograman paling populer untuk AI dan Machine Learning berkat sintaksnya yang sederhana.',
                'content' => "# Mengapa Python untuk AI?\n\nPython telah menjadi bahasa pemrograman de facto untuk Artificial Intelligence dan Machine Learning.",
                'category' => 'tutorial',
                'tags' => json_encode(['Python', 'Programming', 'AI']),
                'status' => 'published',
                'views_count' => 892,
                'published_at' => now()->subDays(15),
                'created_by' => $adminId,
            ],
            [
                'title' => 'IoT dan AI: Kombinasi Teknologi untuk Smart City',
                'slug' => 'iot-dan-ai-kombinasi-teknologi-untuk-smart-city',
                'excerpt' => 'Internet of Things (IoT) dan Artificial Intelligence bekerja bersama untuk menciptakan kota pintar yang lebih efisien.',
                'content' => "# Smart City: Masa Depan Perkotaan\n\nSmart City menggunakan teknologi IoT dan AI untuk meningkatkan kualitas hidup warga kota.",
                'category' => 'news',
                'tags' => json_encode(['IoT', 'AI', 'Smart City']),
                'status' => 'published',
                'views_count' => 645,
                'published_at' => now()->subDays(10),
                'created_by' => $adminId,
            ],
        ];
    }
}
